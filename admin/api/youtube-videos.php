<?php
header('Content-Type: application/json');
require_once '../includes/YouTubeManager.php';

$youtubeManager = new YouTubeManager();

// Create table if it doesn't exist
$youtubeManager->createTable();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            $videos = $youtubeManager->getActiveVideos();
            echo json_encode([
                'success' => true,
                'data' => $videos
            ]);
            break;
            
        case 'POST':
            $action = $input['action'] ?? '';
            
            switch ($action) {
                case 'delete':
                    $id = $input['id'] ?? 0;
                    $result = $youtubeManager->deleteVideo($id);
                    
                    echo json_encode([
                        'success' => $result,
                        'message' => $result ? 'Video deleted successfully' : 'Error deleting video'
                    ]);
                    break;
                    
                case 'reorder':
                    $videoIds = $input['video_ids'] ?? [];
                    $result = $youtubeManager->updateOrder($videoIds);
                    
                    echo json_encode([
                        'success' => $result,
                        'message' => $result ? 'Order updated successfully' : 'Error updating order'
                    ]);
                    break;
                    
                default:
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid action'
                    ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
