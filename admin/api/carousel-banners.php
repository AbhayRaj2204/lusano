<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/CarouselManager.php';

$carouselManager = new CarouselManager();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $banner = $carouselManager->getBannerById($_GET['id']);
                if ($banner) {
                    echo json_encode(['success' => true, 'data' => $banner]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Banner not found']);
                }
            } else {
                $banners = $carouselManager->getCarouselBanners();
                echo json_encode(['success' => true, 'data' => $banners]);
            }
            break;
            
        case 'POST':
            $action = $_POST['action'] ?? '';
            
            if ($action === 'add') {
                $uploadResult = handleFileUpload();
                if (!$uploadResult['success']) {
                    echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                    break;
                }
                
                $data = [
                    'title' => $_POST['title'] ?? '',
                    'type' => $_POST['type'] ?? 'image',
                    'media_url' => $uploadResult['file_path'],
                    'order' => $_POST['order'] ?? 1,
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                if ($carouselManager->addBanner($data)) {
                    echo json_encode(['success' => true, 'message' => 'Banner added successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to add banner']);
                }
            } elseif ($action === 'update') {
                $id = $_POST['id'] ?? '';
                $data = [
                    'title' => $_POST['title'] ?? '',
                    'type' => $_POST['type'] ?? 'image',
                    'order' => $_POST['order'] ?? 1,
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = handleFileUpload();
                    if (!$uploadResult['success']) {
                        echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                        break;
                    }
                    $data['media_url'] = $uploadResult['file_path'];
                } else {
                    // Keep existing media_url if no new file uploaded
                    $existingBanner = $carouselManager->getBannerById($id);
                    if ($existingBanner) {
                        $data['media_url'] = $existingBanner['media_url'];
                    }
                }
                
                if ($carouselManager->updateBanner($id, $data)) {
                    echo json_encode(['success' => true, 'message' => 'Banner updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update banner']);
                }
            } elseif ($action === 'delete') {
                $id = $_POST['id'] ?? '';
                
                if ($carouselManager->deleteBanner($id)) {
                    echo json_encode(['success' => true, 'message' => 'Banner deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete banner']);
                }
            } elseif ($action === 'reorder') {
                $bannerIds = json_decode($_POST['banner_ids'] ?? '[]', true);
                
                if ($carouselManager->reorderBanners($bannerIds)) {
                    echo json_encode(['success' => true, 'message' => 'Banners reordered successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to reorder banners']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function handleFileUpload() {
    if (!isset($_FILES['media_file']) || $_FILES['media_file']['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    $file = $_FILES['media_file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];
    
    // Create uploads directory if it doesn't exist
    $uploadDir = '../../uploads/carousel/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file type
    $allowedTypes = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/webm', 'video/ogg'
    ];
    
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only images and videos are allowed.'];
    }
    
    // Validate file size (max 50MB)
    $maxSize = 50 * 1024 * 1024; // 50MB
    if ($fileSize > $maxSize) {
        return ['success' => false, 'message' => 'File size too large. Maximum 50MB allowed.'];
    }
    
    // Generate unique filename
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid('carousel_') . '.' . $fileExtension;
    $uploadPath = $uploadDir . $newFileName;
    
    // Move uploaded file
    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        // Return relative path for storage in database
        return ['success' => true, 'file_path' => 'uploads/carousel/' . $newFileName];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
}
?>
