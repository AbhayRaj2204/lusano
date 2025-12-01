<?php
require_once 'Database.php';

class ProductManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Digital Products Methods
    public function getDigitalProducts($status = 'active') {
        $products = $this->db->readCSV('digital_products.csv');
        
        if (empty($products)) {
            error_log("[v0] No products found in CSV file");
            return [];
        }
        
        if ($status && $status !== '') {
            $products = array_filter($products, function($product) use ($status) {
                return isset($product['status']) && $product['status'] === $status;
            });
        }
        
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id'] ?? '', 'digital');
            // Set primary image from images array if not set
            if (empty($product['image']) && !empty($product['images'])) {
                $product['image'] = $product['images'][0]['image_path'];
            }
        }
        
        return $products;
    }
    
    public function getDigitalProduct($id) {
        $product = $this->db->findById('digital_products.csv', $id);
        if ($product) {
            $product['images'] = $this->getProductImages($id, 'digital');
            $product['installation_guide_video'] = $product['installation_guide_video'] ?? '';
            $product['features_array'] = explode('|', $product['features']);
            $product['categories_array'] = explode(',', $product['categories']);
            
            // Parse detailed features
            $detailedFeatures = [];
            $features = explode('|', $product['detailed_features']);
            foreach ($features as $feature) {
                $parts = explode('~', $feature);
                if (count($parts) === 3) {
                    $detailedFeatures[] = [
                        'title' => $parts[0],
                        'description' => $parts[1],
                        'icon' => $parts[2]
                    ];
                }
            }
            $product['detailed_features_array'] = $detailedFeatures;
            
            // Parse specs
            $specs = [];
            $specPairs = explode('|', $product['specs']);
            foreach ($specPairs as $pair) {
                $parts = explode('~', $pair);
                if (count($parts) === 2) {
                    $specs[$parts[0]] = $parts[1];
                }
            }
            $product['specs_array'] = $specs;
        }
        return $product;
    }
    
    public function saveDigitalProduct($data, $isUpdate = false) {
        error_log("[v0] saveDigitalProduct called with ID: " . $data['id'] . ", isUpdate: " . ($isUpdate ? 'true' : 'false'));
        
        $primaryImage = '';
        if (isset($data['primary_image']) && !empty($data['primary_image'])) {
            $primaryImage = $data['primary_image'];
        }
        
        // Prepare data for CSV
        $csvData = [
            'id' => $data['id'],
            'title' => $data['title'],
            'price' => $data['price'],
            'original_price' => $data['original_price'] ?? $data['price'],
            'icon' => $data['icon'],
            'badge' => $data['badge'],
            'tagline' => $data['tagline'],
            'features' => implode('|', $data['features'] ?? []),
            'detailed_features' => $this->formatDetailedFeatures($data['detailed_features'] ?? []),
            'specs' => $this->formatSpecs($data['specs'] ?? []),
            'installation_guide_video' => $data['installation_guide_video'] ?? '',
            'categories' => implode(',', $data['categories'] ?? []),
            'image' => $primaryImage,
            'status' => $data['status'] ?? 'active',
            'updated_date' => date('Y-m-d')
        ];
        
        if (!$isUpdate) {
            $csvData['created_date'] = date('Y-m-d');
        }
        
        error_log("[v0] CSV data prepared: " . print_r($csvData, true));
        
        if ($isUpdate) {
            $result = $this->db->updateById('digital_products.csv', $data['id'], $csvData);
            error_log("[v0] Update result: " . ($result ? 'success' : 'failed'));
            return $result;
        } else {
            // Check if ID already exists
            $existingProduct = $this->db->findById('digital_products.csv', $data['id']);
            if ($existingProduct) {
                error_log("[v0] Product ID already exists: " . $data['id']);
                return false; // ID already exists
            }
            
            $headers = ['id', 'title', 'price', 'original_price', 'icon', 'badge', 'tagline', 'features', 'detailed_features', 'specs', 'installation_guide_video', 'categories', 'image', 'status', 'created_date', 'updated_date'];
            $result = $this->db->appendCSV('digital_products.csv', $csvData, $headers);
            error_log("[v0] Append result: " . ($result ? 'success' : 'failed'));
            return $result;
        }
    }
    
    // Hardware Products Methods
    public function getHardwareProducts($status = null) {
        error_log("[v0] getHardwareProducts called with status: " . ($status ?? 'null'));
        $products = $this->db->readCSV('hardware_products.csv');
        error_log("[v0] Found " . count($products) . " products in CSV");
        
        if ($status && $status !== '' && $status !== 'all') {
            $products = array_filter($products, function($product) use ($status) {
                return isset($product['status']) && $product['status'] === $status;
            });
            error_log("[v0] After status filter: " . count($products) . " products");
        }
        
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id'] ?? '', 'hardware');
            // Set primary image from images array if not set
            if (empty($product['image']) && !empty($product['images'])) {
                $product['image'] = $product['images'][0]['image_path'];
            }
        }
        
        return $products;
    }
    
    public function getHardwareProduct($id) {
        error_log("[v0] getHardwareProduct called for ID: " . $id);
        $product = $this->db->findById('hardware_products.csv', $id);
        if ($product) {
            error_log("[v0] Product found: " . $product['title']);
            $product['images'] = $this->getProductImages($id, 'hardware');
            $product['installation_guide_video'] = $product['installation_guide_video'] ?? '';
            
            // Parse categories
            if (isset($product['categories'])) {
                $product['categories_array'] = explode(',', $product['categories']);
            } else {
                $product['categories_array'] = [];
            }
            
            // Parse features
            $features = [];
            if (!empty($product['features'])) {
                $featurePairs = explode('|', $product['features']);
                foreach ($featurePairs as $pair) {
                    $parts = explode('~', $pair);
                    if (count($parts) === 2) {
                        $features[] = [
                            'text' => $parts[0],
                            'icon' => $parts[1]
                        ];
                    }
                }
            }
            $product['features_array'] = $features;
            
            // Parse specs
            $specs = [];
            if (!empty($product['specs'])) {
                $specPairs = explode('|', $product['specs']);
                foreach ($specPairs as $pair) {
                    $parts = explode('~', $pair);
                    if (count($parts) === 2) {
                        $specs[$parts[0]] = $parts[1];
                    }
                }
            }
            $product['specs_array'] = $specs;
        } else {
            error_log("[v0] Product not found for ID: " . $id);
        }
        return $product;
    }
    
    public function saveHardwareProduct($data, $isUpdate = false) {
        error_log("[v0] saveHardwareProduct called - ID: " . $data['id'] . ", isUpdate: " . ($isUpdate ? 'true' : 'false'));
        
        // Prepare data for CSV
        $csvData = [
            'id' => $data['id'],
            'title' => $data['title'],
            'price' => $data['price'],
            'original_price' => $data['original_price'] ?? $data['price'],
            'badge' => $data['badge'],
            'tagline' => $data['tagline'],
            'features' => $this->formatHardwareFeatures($data['features'] ?? []),
            'specs' => $this->formatSpecs($data['specs'] ?? []),
            'installation_guide_video' => $data['installation_guide_video'] ?? '',
            'categories' => implode(',', $data['categories'] ?? []),
            'status' => $data['status'] ?? 'active',
            'updated_date' => date('Y-m-d')
        ];
        
        if (!$isUpdate) {
            $csvData['created_date'] = date('Y-m-d');
        }
        
        error_log("[v0] CSV data prepared: " . print_r($csvData, true));
        
        if ($isUpdate) {
            $result = $this->db->updateById('hardware_products.csv', $data['id'], $csvData);
            error_log("[v0] Update result: " . ($result ? 'success' : 'failed'));
            return $result;
        } else {
            // Check if ID already exists
            $existing = $this->db->findById('hardware_products.csv', $data['id']);
            if ($existing) {
                error_log("[v0] Product ID already exists: " . $data['id']);
                return false; // ID already exists
            }
            
            $headers = ['id', 'title', 'price', 'original_price', 'badge', 'tagline', 'features', 'specs', 'installation_guide_video', 'categories', 'status', 'created_date', 'updated_date'];
            $result = $this->db->appendCSV('hardware_products.csv', $csvData, $headers);
            error_log("[v0] Append result: " . ($result ? 'success' : 'failed'));
            return $result;
        }
    }
    
    // Image Management
    public function getProductImages($productId, $productType) {
        $images = $this->db->readCSV('product_images.csv');
        $productImages = array_filter($images, function($image) use ($productId, $productType) {
            return $image['product_id'] === $productId && 
                   $image['product_type'] === $productType && 
                   $image['status'] === 'active';
        });
        
        // Sort by sort_order
        usort($productImages, function($a, $b) {
            return intval($a['sort_order']) - intval($b['sort_order']);
        });
        
        return $productImages;
    }
    
    public function saveProductImage($productId, $productType, $imagePath, $altText, $sortOrder = 1) {
        $imageData = [
            'product_id' => $productId,
            'product_type' => $productType,
            'image_path' => $imagePath,
            'alt_text' => $altText,
            'sort_order' => $sortOrder,
            'status' => 'active',
            'created_date' => date('Y-m-d')
        ];
        
        $headers = ['product_id', 'product_type', 'image_path', 'alt_text', 'sort_order', 'status', 'created_date'];
        return $this->db->appendCSV('product_images.csv', $imageData, $headers);
    }
    
    public function deleteProductImages($productId, $productType) {
        $images = $this->db->readCSV('product_images.csv');
        $newImages = array_filter($images, function($image) use ($productId, $productType) {
            return !($image['product_id'] === $productId && $image['product_type'] === $productType);
        });
        
        return $this->db->writeCSV('product_images.csv', array_values($newImages));
    }
    
    // Delete Products
    public function deleteDigitalProduct($id) {
        $this->deleteProductImages($id, 'digital');
        return $this->db->deleteById('digital_products.csv', $id);
    }
    
    public function deleteHardwareProduct($id) {
        $this->deleteProductImages($id, 'hardware');
        return $this->db->deleteById('hardware_products.csv', $id);
    }
    
    // Helper Methods
    private function formatDetailedFeatures($features) {
        if (empty($features)) {
            return '';
        }
        
        $formatted = [];
        foreach ($features as $feature) {
            if (isset($feature['title']) && isset($feature['description']) && isset($feature['icon'])) {
                $formatted[] = $feature['title'] . '~' . $feature['description'] . '~' . $feature['icon'];
            }
        }
        return implode('|', $formatted);
    }
    
    private function formatHardwareFeatures($features) {
        if (empty($features)) {
            return '';
        }
        
        $formatted = [];
        foreach ($features as $feature) {
            if (isset($feature['text']) && isset($feature['icon'])) {
                $formatted[] = $feature['text'] . '~' . $feature['icon'];
            }
        }
        return implode('|', $formatted);
    }
    
    private function formatSpecs($specs) {
        if (empty($specs)) {
            return '';
        }
        
        $formatted = [];
        foreach ($specs as $key => $value) {
            if (!empty($key) && !empty($value)) {
                $formatted[] = $key . '~' . $value;
            }
        }
        return implode('|', $formatted);
    }
}
?>
