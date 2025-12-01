<?php
require_once 'Database.php';

class NewModelManager {
    private $db;
    private $dataFile;
    
    public function __construct() {
        $this->db = new Database();
        // ✅ Use absolute path to avoid fopen() path errors
        $this->dataFile = 'new_models.csv';
        $this->initializeDataFile();
    }
    
    private function initializeDataFile() {
        $existingData = $this->db->readCSV($this->dataFile);
        if (empty($existingData)) {
            $headers = ['product_id', 'product_type', 'title', 'description', 'price', 'image', 'type', 'created_at'];
            $this->db->writeCSV($this->dataFile, [], $headers);
        }
    }
    
    public function getNewModels() {
        return $this->db->readCSV($this->dataFile);
    }
    
    public function addNewModel($data) {
        try {
            require_once 'ProductManager.php';
            $productManager = new ProductManager();
            
            $product = null;
            $productId = isset($data['product_id']) ? trim($data['product_id']) : '';
            $productType = isset($data['product_type']) ? trim($data['product_type']) : '';
            
            if (empty($productId) || empty($productType)) {
                return false;
            }
            
            // ✅ More robust type check
            $products = ($productType === 'digital') 
                        ? $productManager->getDigitalProducts() 
                        : $productManager->getHardwareProducts();

            foreach ($products as $p) {
                if (isset($p['id']) && (string)$p['id'] === (string)$productId) {
                    $product = $p;
                    break;
                }
            }
            
            if (!$product) {
                return false; // product not found
            }
            
            // ✅ Check max limit
            $currentModels = $this->getNewModels();
            if (count($currentModels) >= 3) {
                return false;
            }
            
            // ✅ Prevent duplicates
            foreach ($currentModels as $model) {
                if (isset($model['product_id']) && (string)$model['product_id'] === (string)$productId) {
                    return false;
                }
            }
            
            $newModel = [
                'product_id' => $productId,
                'product_type' => $productType,
                'title' => isset($product['title']) ? $product['title'] : 'Untitled',
                'description' => isset($product['badge']) ? $product['badge'] : (isset($product['description']) ? $product['description'] : ''),
                'price' => isset($product['price']) ? $product['price'] : 0,
                'image' => isset($product['image']) ? $product['image'] : '',
                'type' => $productType,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->db->appendCSV($this->dataFile, $newModel);
        } catch (Exception $e) {
            error_log("Error in addNewModel: " . $e->getMessage());
            return false;
        }
    }
    
    public function removeNewModel($productId) {
        try {
            $models = $this->getNewModels();
            $filteredModels = array_filter($models, function($model) use ($productId) {
                return isset($model['product_id']) && $model['product_id'] !== $productId;
            });
            
            $headers = ['product_id', 'product_type', 'title', 'description', 'price', 'image', 'type', 'created_at'];
            
            // ✅ Pass headers separately to avoid corrupt CSV
            return $this->db->writeCSV($this->dataFile, array_values($filteredModels), $headers);
        } catch (Exception $e) {
            error_log("Error in removeNewModel: " . $e->getMessage());
            return false;
        }
    }
    
    public function getNewModelsForWebsite() {
        $models = $this->getNewModels();
        $formattedModels = [];
        require_once 'ProductManager.php';
        require_once 'ColorVariantManager.php';
        $productManager = new ProductManager();
        $colorVariantManager = new ColorVariantManager();

        // Get all color variants once
        $allVariants = $colorVariantManager->getColorVariants();

        foreach ($models as $model) {
            $id = isset($model['product_id']) ? $model['product_id'] : '';
            // Prefer 'type' but fall back to 'product_type' for older rows
            $type = isset($model['type']) && $model['type'] !== '' 
                ? $model['type'] 
                : (isset($model['product_type']) ? $model['product_type'] : 'digital');

            // 1) Start with the CSV image value
            $image = isset($model['image']) ? trim((string)$model['image']) : '';

            // 2) If missing, try to get from color variants first
            if ($image === '') {
                // Find variants for this product - match by product_id first
                $productVariants = array_filter($allVariants, function($v) use ($id) {
                    return isset($v['product_id']) && $v['product_id'] === $id;
                });

                // If no match by ID, try matching by product_name
                if (empty($productVariants) && isset($model['title'])) {
                    $productVariants = array_filter($allVariants, function($v) use ($model) {
                        return isset($v['product_name']) && 
                               strtolower(trim($v['product_name'])) === strtolower(trim($model['title']));
                    });
                }

                // Use first variant's first image if available
                if (!empty($productVariants)) {
                    $firstVariant = reset($productVariants);
                    if (!empty($firstVariant['image_1'])) {
                        $image = $firstVariant['image_1'];
                    }
                }
            }

            // 3) If still missing, fall back to product_images.csv
            if ($image === '') {
                $images = $productManager->getProductImages($id, $type);
                if (!empty($images)) {
                    // ProductManager returns rows with key 'image_path'
                    $image = isset($images[0]['image_path']) ? $images[0]['image_path'] : '';
                }
            }

            // 4) Normalize path: ensure relative admin/ prefix when not absolute and not placeholder/data URLs
            if ($image !== '') {
                $lower = strtolower($image);
                $isAbsolute = (strpos($lower, 'http://') === 0) || (strpos($lower, 'https://') === 0) || (strpos($lower, 'data:') === 0);
                if (!$isAbsolute) {
                    $image = ltrim($image, '/'); // remove any leading slash
                    if (strpos($image, 'admin/') !== 0 && strpos($image, 'placeholder.svg') === false) {
                        $image = 'admin/' . $image;
                    }
                }
            }

            // 5) Final fallback: data URI SVG placeholder if still empty
            if ($image === '') {
                $title = isset($model['title']) ? $model['title'] : 'LUSANO Model';
                $encodedTitle = rawurlencode($title);
                $image = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150' viewBox='0 0 150 150'%3E%3Crect fill='%23222' width='150' height='150'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='12' fill='%23666'%3E{$encodedTitle}%3C/text%3E%3C/svg%3E";
            }

            $formattedModels[] = [
                'id' => $id,
                'title' => isset($model['title']) ? $model['title'] : '',
                'description' => isset($model['description']) ? $model['description'] : '',
                'price' => isset($model['price']) ? $model['price'] : 0,
                'image' => $image,
                'type' => $type
            ];
        }
        return $formattedModels;
    }
}
?>
