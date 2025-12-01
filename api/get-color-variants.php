<?php
require_once '../admin/includes/ColorVariantManager.php';

header('Content-Type: application/json');

try {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);

    $colorVariantManager = new ColorVariantManager();
    $productName = $_GET['product_name'] ?? null;
    $productId = $_GET['product_id'] ?? null;
    
    // Get all variants
    $variants = $colorVariantManager->getColorVariants(null, 'active');
    
    // Filter by product ID if provided (more precise)
    if ($productId) {
        $variants = array_filter($variants, function($v) use ($productId) {
            return isset($v['product_id']) && (string)$v['product_id'] === (string)$productId;
        });
    }
    // Fallback to product name if ID not provided or no matches found with ID (optional, but good for safety)
    elseif ($productName) {
        $variants = array_filter($variants, function($v) use ($productName) {
            // Loose matching for product name (check both ways)
            $vName = trim($v['product_name']);
            $qName = trim($productName);
            return stripos($vName, $qName) !== false || stripos($qName, $vName) !== false;
        });
    }
    
    // Transform variants to include images array
    $transformedVariants = array_map(function($variant) {
        $images = [];
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($variant['image_' . $i])) {
                // Ensure path is correct relative to public root
                // The admin saves it as /uploads/..., we might need to prepend admin/ if it's in admin/uploads
                // But let's check where the upload goes. 
                // Admin API saves to ../uploads/ which is admin/uploads.
                // And saves path as /uploads/... (stripping ..)
                // If the file is in admin/uploads, and we are at root, we need admin/uploads.
                
                $path = $variant['image_' . $i];
                
                // Handle paths
                if (strpos($path, '../') === 0) {
                    // If it starts with "../", remove it and prepend "admin/" (assuming it was relative to admin/api/)
                    // Actually, if it was ../uploads, relative to admin/api, it means admin/uploads.
                    // So just replace ../ with admin/
                    $path = str_replace('../', 'admin/', $path);
                } elseif (strpos($path, 'uploads/') === 0) {
                    // If it's a relative path "uploads/...", prepend "admin/"
                    $path = 'admin/' . $path;
                } elseif (strpos($path, '/uploads/') === 0) {
                    // If it's "/uploads/...", try to fix it for subdirectory
                    $path = 'admin' . $path;
                }
                
                $images[] = $path;
            }
        }
        $variant['images'] = $images; // Return array directly
        return $variant;
    }, $variants);
    
    echo json_encode(['success' => true, 'variants' => array_values($transformedVariants)]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
