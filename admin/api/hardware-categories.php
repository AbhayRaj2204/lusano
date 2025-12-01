<?php
ob_start();
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

session_start();
require_once '../includes/HardwareCategoryManager.php';

header('Content-Type: application/json');

$categoryManager = new HardwareCategoryManager();

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'add':
        case 'edit':
            $result = saveCategory($categoryManager, $action === 'edit');
            $response = $result;
            break;
            
        case 'delete':
            $result = deleteCategory($categoryManager);
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

function saveCategory($categoryManager, $isUpdate) {
    // Validate required fields
    $requiredFields = ['id', 'name', 'icon', 'description'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            return ['success' => false, 'message' => "Field '$field' is required"];
        }
    }
    
    // Prepare category data
    $categoryData = [
        'id' => $_POST['id'],
        'name' => $_POST['name'],
        'icon' => $_POST['icon'],
        'description' => $_POST['description'],
        'status' => $_POST['status'] ?? 'active'
    ];
    
    $result = $categoryManager->saveCategory($categoryData, $isUpdate);
    
    if ($result) {
        return ['success' => true, 'message' => 'Category saved successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to save category. Check file permissions for admin/data/ directory.'];
    }
}

function deleteCategory($categoryManager) {
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        return ['success' => false, 'message' => 'Category ID is required'];
    }
    
    // Check if category has products
    require_once '../includes/ProductManager.php';
    $productManager = new ProductManager();
    $products = $productManager->getHardwareProducts();
    
    foreach ($products as $product) {
        if (isset($product['categories']) && strpos($product['categories'], $id) !== false) {
            return ['success' => false, 'message' => 'Cannot delete category that has products assigned to it'];
        }
    }
    
    $result = $categoryManager->deleteCategory($id);
    
    if ($result) {
        return ['success' => true, 'message' => 'Category deleted successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to delete category'];
    }
}
?>
