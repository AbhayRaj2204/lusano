<?php
require_once '../includes/ColorVariantManager.php';

header('Content-Type: application/json');

try {
    $colorVariantManager = new ColorVariantManager();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_products':
            $products = $colorVariantManager->getAllProducts();
            echo json_encode(['success' => true, 'data' => $products]);
            break;
            
        case 'get_variants':
            $productId = $_GET['product_id'] ?? null;
            $variants = $colorVariantManager->getColorVariants($productId);
            echo json_encode(['success' => true, 'data' => $variants]);
            break;

        case 'get_by_product_name':
            $productName = $_GET['product_name'] ?? null;
            if (!$productName) {
                echo json_encode(['success' => false, 'message' => 'Product name required']);
                break;
            }
            
            $variants = $colorVariantManager->getColorVariants(null);
            $filtered = array_filter($variants, function($v) use ($productName) {
                return strtolower(trim($v['product_name'])) === strtolower(trim($productName)) && 
                       strtolower($v['status']) === 'active';
            });
            
            echo json_encode(['success' => true, 'variants' => array_values($filtered)]);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('Missing variant ID');
            }
            
            $variant = $colorVariantManager->getColorVariant($id);
            echo json_encode(['success' => true, 'data' => $variant]);
            break;
            
        case 'save':
            // Handle file uploads
            $images = [];
            $uploadDir = '../uploads/color-variants/' . time() . '/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Process up to 5 images
            for ($i = 1; $i <= 5; $i++) {
                if (isset($_FILES['image_' . $i])) {
                    $file = $_FILES['image_' . $i];
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $filename = time() . '_' . $i . '_' . basename($file['name']);
                        $filepath = $uploadDir . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            $images['image_' . $i] = str_replace('../', '', $filepath);
                        }
                    }
                }
            }
            
            $data = [
                'product_id' => $_POST['product_id'],
                'product_name' => $_POST['product_name'],
                'product_category' => $_POST['product_category'],
                'color_name' => $_POST['color_name'],
                'color_hex' => $_POST['color_hex'] ?? '#000000',
                'image_1' => $images['image_1'] ?? '',
                'image_2' => $images['image_2'] ?? '',
                'image_3' => $images['image_3'] ?? '',
                'image_4' => $images['image_4'] ?? '',
                'image_5' => $images['image_5'] ?? ''
            ];
            
            $result = $colorVariantManager->saveColorVariant($data);
            echo json_encode(['success' => $result, 'message' => $result ? 'Color variant saved successfully' : 'Failed to save color variant']);
            break;
            
        case 'update':
            $id = $_POST['id'] ?? null;
            if (!$id) {
                throw new Exception('Missing variant ID');
            }
            
            // Get existing variant
            $existingVariant = $colorVariantManager->getColorVariant($id);
            
            // Handle file uploads
            $images = [];
            $uploadDir = '../uploads/color-variants/' . time() . '/';
            
            for ($i = 1; $i <= 5; $i++) {
                if (isset($_FILES['image_' . $i])) {
                    $file = $_FILES['image_' . $i];
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $filename = time() . '_' . $i . '_' . basename($file['name']);
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        $filepath = $uploadDir . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            $images['image_' . $i] = str_replace('../', '', $filepath);
                        } else {
                            $images['image_' . $i] = $existingVariant['image_' . $i] ?? '';
                        }
                    } else {
                        $images['image_' . $i] = $existingVariant['image_' . $i] ?? '';
                    }
                } else {
                    $images['image_' . $i] = $existingVariant['image_' . $i] ?? '';
                }
            }
            
            $data = [
                'product_id' => $_POST['product_id'],
                'product_name' => $_POST['product_name'],
                'product_category' => $_POST['product_category'],
                'color_name' => $_POST['color_name'],
                'color_hex' => $_POST['color_hex'] ?? '#000000',
                'image_1' => $images['image_1'],
                'image_2' => $images['image_2'],
                'image_3' => $images['image_3'],
                'image_4' => $images['image_4'],
                'image_5' => $images['image_5'],
                'status' => $_POST['status'] ?? 'active'
            ];
            
            $result = $colorVariantManager->updateColorVariant($id, $data);
            echo json_encode(['success' => $result, 'message' => $result ? 'Color variant updated successfully' : 'Failed to update color variant']);
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? null;
            if (!$id) {
                throw new Exception('Missing variant ID');
            }
            
            $result = $colorVariantManager->deleteColorVariant($id);
            echo json_encode(['success' => $result, 'message' => $result ? 'Color variant deleted successfully' : 'Failed to delete color variant']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
