<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', 0);

function getProjectRoot() {
    return dirname(dirname(__FILE__));
}

function parseCSVFile($filePath) {
    error_log("[v0] Attempting to read CSV file: " . $filePath);
    
    if (!file_exists($filePath)) {
        error_log("[v0] CSV file does not exist: " . $filePath);
        return [];
    }
    
    $products = [];
    $handle = fopen($filePath, 'r');
    
    if ($handle !== FALSE) {
        // Get header row
        $headers = fgetcsv($handle);
        
        if ($headers === FALSE || empty($headers)) {
             error_log("[v0] CSV is empty or has no headers: " . $filePath);
             fclose($handle);
             return [];
        }

        error_log("[v0] CSV headers: " . implode(', ', $headers));
        
        // Read data rows
        $rowCount = 0;
        while (($data = fgetcsv($handle)) !== FALSE) {
            $rowCount++;
            if (count($data) === count($headers)) {
                $product = array_combine($headers, $data);
                
                if (isset($product['image']) && !empty($product['image'])) {
                    // Clean up the image path and ensure it points to admin folder
                    $imagePath = $product['image'];
                    
                    // Remove leading slash if present
                    if (strpos($imagePath, '/') === 0) {
                        $imagePath = substr($imagePath, 1);
                    }
                    
                    // If path doesn't start with admin/, prepend it
                    if (strpos($imagePath, 'admin/') !== 0) {
                        $imagePath = 'admin/' . $imagePath;
                    }
                    
                    $product['image'] = $imagePath;
                }
                
                // Parse features (pipe-separated)
                if (isset($product['features'])) {
                    $product['features'] = explode('|', $product['features']);
                }
                
                // Parse detailed features (pipe-separated, then tilde-separated)
                if (isset($product['detailed_features'])) {
                    $detailedFeatures = [];
                    $features = explode('|', $product['detailed_features']);
                    foreach ($features as $feature) {
                        $parts = explode('~', $feature);
                        if (count($parts) >= 3) {
                            $detailedFeatures[] = [
                                'title' => $parts[0],
                                'description' => $parts[1],
                                'icon' => $parts[2]
                            ];
                        }
                    }
                    $product['detailed_features'] = $detailedFeatures;
                }
                
                // Parse specs (pipe-separated, then tilde-separated)
                if (isset($product['specs'])) {
                    $specs = [];
                    $specPairs = explode('|', $product['specs']);
                    foreach ($specPairs as $pair) {
                        $parts = explode('~', $pair);
                        if (count($parts) === 2) {
                            $specs[$parts[0]] = $parts[1];
                        }
                    }
                    $product['specs'] = $specs;
                }
                
                // Parse categories
                if (isset($product['categories'])) {
                    $product['categories'] = explode(',', $product['categories']);
                }
                
                // Convert price fields to numbers
                if (isset($product['price'])) {
                    $product['price'] = (int)$product['price'];
                }
                if (isset($product['original_price'])) {
                    $product['original_price'] = (int)$product['original_price'];
                }
                
                $products[] = $product;
            }
        }
        fclose($handle);
        error_log("[v0] Successfully parsed " . count($products) . " products from " . $rowCount . " rows");
    } else {
        error_log("[v0] Failed to open CSV file: " . $filePath);
    }
    
    return $products;
}

function loadProductImages($productId, $productType, $projectRoot) {
    $imagesFile = $projectRoot . '/admin/data/product_images.csv';
    $images = [];
    
    if (!file_exists($imagesFile)) {
        error_log("[v0] Product images CSV not found: " . $imagesFile);
        return $images;
    }
    
    $handle = fopen($imagesFile, 'r');
    if ($handle !== FALSE) {
        // Get header row
        $headers = fgetcsv($handle);
        
        if ($headers === FALSE || empty($headers)) {
             fclose($handle);
             return $images;
        }
        
        // Read data rows
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) === count($headers)) {
                $imageData = array_combine($headers, $data);
                
                // Check if this image belongs to the requested product
                if ($imageData['product_id'] === $productId && 
                    $imageData['product_type'] === $productType && 
                    $imageData['status'] === 'active') {
                    
                    $imagePath = $imageData['image_path'];
                    
                    // Clean up image path
                    if (strpos($imagePath, '/') === 0) {
                        $imagePath = substr($imagePath, 1);
                    }
                    
                    // If path doesn't start with admin/, prepend it
                    if (strpos($imagePath, 'admin/') !== 0 && strpos($imagePath, 'placeholder.svg') === false) {
                        $imagePath = 'admin/' . $imagePath;
                    }
                    
                    $images[] = [
                        'path' => $imagePath,
                        'alt' => $imageData['alt_text'],
                        'sort_order' => (int)$imageData['sort_order']
                    ];
                }
            }
        }
        fclose($handle);
        
        // Sort images by sort_order
        usort($images, function($a, $b) {
            return $a['sort_order'] - $b['sort_order'];
        });
    }
    
    return $images;
}

$type = $_GET['type'] ?? 'digital';
$id = $_GET['id'] ?? null;

$projectRoot = getProjectRoot();

if ($type === 'digital') {
    $csvFile = $projectRoot . '/admin/data/digital_products.csv';
    // Fallback to main data folder for backward compatibility
    if (!file_exists($csvFile)) {
        $csvFile = $projectRoot . '/data/digital_products.csv';
    }
} else if ($type === 'hardware') {
    $csvFile = $projectRoot . '/admin/data/hardware_products.csv';
    // Fallback to main data folder for backward compatibility
    if (!file_exists($csvFile)) {
        $csvFile = $projectRoot . '/data/hardware_products.csv';
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product type']);
    exit;
}

error_log("[v0] Using CSV file: " . $csvFile);

$products = parseCSVFile($csvFile);

if ($id) {
    // Return specific product
    $product = null;
    foreach ($products as $p) {
        if ($p['id'] === $id) {
            $product = $p;
            break;
        }
    }
    
    if ($product) {
        $additionalImages = loadProductImages($id, $type, $projectRoot);
        $product['images'] = $additionalImages;
        
        echo json_encode($product);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    foreach ($products as &$product) {
        $additionalImages = loadProductImages($product['id'], $type, $projectRoot);
        $product['images'] = $additionalImages;
    }
    
    // Return all products
    echo json_encode($products);
}
?>
 