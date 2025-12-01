<?php
require_once 'Database.php';

class ColorVariantManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Get all color variants with optional filtering
    public function getColorVariants($productId = null, $status = 'active') {
        $variants = $this->db->readCSV('color_variants.csv');
        
        if ($productId) {
            $variants = array_filter($variants, function($variant) use ($productId) {
                return $variant['product_id'] === $productId;
            });
        }
        
        if ($status) {
            $variants = array_filter($variants, function($variant) use ($status) {
                return $variant['status'] === $status;
            });
        }
        
        return array_values($variants);
    }
    
    // Get single color variant
    public function getColorVariant($id) {
        return $this->db->findById('color_variants.csv', $id);
    }
    
    // Save new color variant
    public function saveColorVariant($data) {
        // Generate unique ID
        $id = 'color-' . time() . '-' . rand(1000, 9999);
        
        $variantData = [
            'id' => $id,
            'product_id' => $data['product_id'],
            'product_name' => $data['product_name'],
            'product_category' => $data['product_category'],
            'color_name' => $data['color_name'],
            'color_hex' => $data['color_hex'] ?? '#000000',
            'image_1' => $data['image_1'] ?? '',
            'image_2' => $data['image_2'] ?? '',
            'image_3' => $data['image_3'] ?? '',
            'image_4' => $data['image_4'] ?? '',
            'image_5' => $data['image_5'] ?? '',
            'status' => 'active',
            'created_date' => date('Y-m-d'),
            'updated_date' => date('Y-m-d')
        ];
        
        $headers = ['id', 'product_id', 'product_name', 'product_category', 'color_name', 'color_hex', 
                    'image_1', 'image_2', 'image_3', 'image_4', 'image_5', 'status', 'created_date', 'updated_date'];
        
        return $this->db->appendCSV('color_variants.csv', $variantData, $headers);
    }
    
    // Update color variant
    public function updateColorVariant($id, $data) {
        $variantData = [
            'product_id' => $data['product_id'],
            'product_name' => $data['product_name'],
            'product_category' => $data['product_category'],
            'color_name' => $data['color_name'],
            'color_hex' => $data['color_hex'] ?? '#000000',
            'image_1' => $data['image_1'] ?? '',
            'image_2' => $data['image_2'] ?? '',
            'image_3' => $data['image_3'] ?? '',
            'image_4' => $data['image_4'] ?? '',
            'image_5' => $data['image_5'] ?? '',
            'status' => $data['status'] ?? 'active',
            'updated_date' => date('Y-m-d')
        ];
        
        return $this->db->updateById('color_variants.csv', $id, $variantData);
    }
    
    // Delete color variant
    public function deleteColorVariant($id) {
        return $this->db->deleteById('color_variants.csv', $id);
    }
    
    // Get all products for dropdown
    public function getAllProducts() {
        require_once 'ProductManager.php';
        $productManager = new ProductManager();
        
        $digitalProducts = $productManager->getDigitalProducts('active');
        $hardwareProducts = $productManager->getHardwareProducts('active');
        
        $products = [];
        
        foreach ($digitalProducts as $product) {
            $products[] = [
                'id' => $product['id'],
                'title' => $product['title'],
                'category' => 'Digital Lock'
            ];
        }
        
        foreach ($hardwareProducts as $product) {
            $products[] = [
                'id' => $product['id'],
                'title' => $product['title'],
                'category' => 'Hardware'
            ];
        }
        
        return $products;
    }
}
?>
