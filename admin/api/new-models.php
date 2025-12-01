<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../includes/NewModelManager.php';

$newModelManager = new NewModelManager();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get new models for website display
        $models = $newModelManager->getNewModelsForWebsite();
        
        echo json_encode([
            'success' => true,
            'data' => $models
        ]);
    } else {
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
