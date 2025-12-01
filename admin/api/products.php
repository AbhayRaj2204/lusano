<?php
ob_start();
error_reporting(E_ERROR | E_PARSE); // Only show critical errors
ini_set('display_errors', 0); // Don't display errors to browser

session_start();
require_once '../includes/ProductManager.php';
require_once '../includes/CategoryManager.php';
require_once '../includes/HardwareCategoryManager.php';

header('Content-Type: application/json');

$productManager = new ProductManager();
$categoryManager = new CategoryManager();
$hardwareCategoryManager = new HardwareCategoryManager();

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'add':
        case 'edit':
            $result = saveProduct($productManager, $action === 'edit');
            $response = $result;
            break;
            
        case 'delete':
            $result = deleteProduct($productManager);
            $response = $result;
            break;
            
        case 'duplicate':
            $result = duplicateProduct($productManager);
            $response = $result;
            break;
            
        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

ob_clean();
echo json_encode($response);
exit;

function saveProduct($productManager, $isUpdate) {
    error_log("[v0] saveProduct called - Action: " . ($isUpdate ? 'edit' : 'add') . ", Type: " . ($_POST['type'] ?? 'unknown'));
    
    // Validate required fields
    $requiredFields = ['id', 'title', 'price', 'badge', 'tagline'];
    
    $type = $_POST['type'] ?? 'digital';
    if ($type === 'digital') {
        $requiredFields[] = 'icon';
    }
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            error_log("[v0] Missing required field: " . $field);
            return ['success' => false, 'message' => "Field '$field' is required"];
        }
    }
    
    $categories = $_POST['categories'] ?? [];
    if (!is_array($categories)) {
        $categories = [$categories];
    }
    
    // Handle image uploads
    $imagePaths = [];
    if (!empty($_FILES['images']['name'][0])) {
        $imagePaths = handleImageUploads();
    }
    
    // Prepare product data
    $productData = [
        'id' => $_POST['id'],
        'title' => $_POST['title'],
        'price' => floatval($_POST['price']),
        'original_price' => floatval($_POST['original_price'] ?? $_POST['price']),
        'badge' => $_POST['badge'],
        'tagline' => $_POST['tagline'],
        'installation_guide_video' => $_POST['installation_guide_video'] ?? '',
        'categories' => $categories,
        'status' => $_POST['status'] ?? 'active'
    ];
    
    if ($type === 'digital') {
        $productData['icon'] = $_POST['icon'];
        $productData['features'] = $_POST['features'] ?? [];
        $productData['detailed_features'] = formatDetailedFeatures($_POST['detailed_features'] ?? []);
        $productData['specs'] = formatSpecs($_POST['specs_keys'] ?? [], $_POST['specs_values'] ?? []);
    } else {
        // Hardware products
        $productData['features'] = formatHardwareFeatures($_POST['features'] ?? []);
        $productData['specs'] = formatSpecs($_POST['specs_keys'] ?? [], $_POST['specs_values'] ?? []);
    }
    
    if (!empty($imagePaths)) {
        $productData['primary_image'] = $imagePaths[0];
    }
    
    error_log("[v0] Product data prepared: " . print_r($productData, true));
    
    if ($type === 'digital') {
        $result = $productManager->saveDigitalProduct($productData, $isUpdate);
    } else {
        $result = $productManager->saveHardwareProduct($productData, $isUpdate);
    }
    
    error_log("[v0] Save result: " . ($result ? 'SUCCESS' : 'FAILED'));
    
    if ($result) {
        // Save images
        foreach ($imagePaths as $index => $imagePath) {
            $productManager->saveProductImage(
                $productData['id'], 
                $type, 
                $imagePath, 
                $productData['title'] . ' Image ' . ($index + 1),
                $index + 1
            );
        }
        
        return ['success' => true, 'message' => 'Product saved successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to save product. Check file permissions for admin/data/ directory.'];
    }
}

function deleteProduct($productManager) {
    $id = $_POST['id'] ?? '';
    $type = $_POST['type'] ?? 'digital';
    
    if (empty($id)) {
        return ['success' => false, 'message' => 'Product ID is required'];
    }
    
    if ($type === 'digital') {
        $result = $productManager->deleteDigitalProduct($id);
    } else {
        $result = $productManager->deleteHardwareProduct($id);
    }
    
    if ($result) {
        return ['success' => true, 'message' => 'Product deleted successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to delete product'];
    }
}

function duplicateProduct($productManager) {
    $id = $_POST['id'] ?? '';
    $type = $_POST['type'] ?? 'digital';
    
    if (empty($id)) {
        return ['success' => false, 'message' => 'Product ID is required'];
    }
    
    // Get original product
    if ($type === 'digital') {
        $product = $productManager->getDigitalProduct($id);
    } else {
        $product = $productManager->getHardwareProduct($id);
    }
    
    if (!$product) {
        return ['success' => false, 'message' => 'Product not found'];
    }
    
    // Create new ID
    $newId = $id . '-copy-' . time();
    $product['id'] = $newId;
    $product['title'] = $product['title'] . ' (Copy)';
    
    // Save duplicated product
    if ($type === 'digital') {
        $result = $productManager->saveDigitalProduct($product, false);
    } else {
        $result = $productManager->saveHardwareProduct($product, false);
    }
    
    if ($result) {
        // Duplicate images
        $images = $productManager->getProductImages($id, $type);
        foreach ($images as $image) {
            $productManager->saveProductImage(
                $newId,
                $type,
                $image['image_path'],
                str_replace($id, $newId, $image['alt_text']),
                $image['sort_order']
            );
        }
        
        return ['success' => true, 'message' => 'Product duplicated successfully', 'new_id' => $newId];
    } else {
        return ['success' => false, 'message' => 'Failed to duplicate product'];
    }
}

function handleImageUploads() {
    $uploadDir = '../uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $imagePaths = [];
    $files = $_FILES['images'];
    
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK && !empty($files['name'][$i])) {
            $filename = time() . '_' . uniqid() . '_' . basename($files['name'][$i]);
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                $imagePaths[] = 'uploads/products/' . $filename;
            }
        }
    }
    
    return $imagePaths;
}

function formatDetailedFeatures($features) {
    $formatted = [];
    foreach ($features as $feature) {
        if (!empty($feature['title']) && !empty($feature['description'])) {
            $formatted[] = [
                'title' => $feature['title'],
                'description' => $feature['description'],
                'icon' => $feature['icon'] ?? 'fa-star'
            ];
        }
    }
    return $formatted;
}

function formatHardwareFeatures($features) {
    $formatted = [];
    foreach ($features as $feature) {
        if (!empty($feature['text']) && !empty($feature['icon'])) {
            $formatted[] = [
                'text' => $feature['text'],
                'icon' => $feature['icon']
            ];
        }
    }
    return $formatted;
}

function formatSpecs($keys, $values) {
    $specs = [];
    for ($i = 0; $i < count($keys); $i++) {
        if (!empty($keys[$i]) && !empty($values[$i])) {
            $specs[$keys[$i]] = $values[$i];
        }
    }
    return $specs;
}
?>
