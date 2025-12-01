<?php
require_once 'Database.php';

class HardwareCategoryManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getCategories($status = 'active') {
        $categories = $this->db->readCSV('hardware_categories.csv');
        if ($status) {
            $categories = array_filter($categories, function($category) use ($status) {
                return $category['status'] === $status;
            });
        }
        return $categories;
    }
    
    public function getCategory($id) {
        return $this->db->findById('hardware_categories.csv', $id);
    }
    
    public function saveCategory($data, $isUpdate = false) {
        $csvData = [
            'id' => $data['id'],
            'name' => $data['name'],
            'icon' => $data['icon'],
            'description' => $data['description'],
            'status' => $data['status'] ?? 'active',
            'updated_date' => date('Y-m-d')
        ];
        
        if (!$isUpdate) {
            $csvData['created_date'] = date('Y-m-d');
        }
        
        if ($isUpdate) {
            return $this->db->updateById('hardware_categories.csv', $data['id'], $csvData);
        } else {
            // Check if ID already exists
            if ($this->db->findById('hardware_categories.csv', $data['id'])) {
                return false; // ID already exists
            }
            
            $headers = ['id', 'name', 'icon', 'description', 'status', 'created_date', 'updated_date'];
            return $this->db->appendCSV('hardware_categories.csv', $csvData, $headers);
        }
    }
    
    public function deleteCategory($id) {
        return $this->db->deleteById('hardware_categories.csv', $id);
    }
}
?>
