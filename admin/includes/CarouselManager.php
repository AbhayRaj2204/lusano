<?php
require_once 'Database.php';

class CarouselManager {
    private $db;
    private $filename = 'carousel_banners.csv';
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getCarouselBanners() {
        $banners = $this->db->readCSV($this->filename);
        
        // Sort by order
        usort($banners, function($a, $b) {
            return intval($a['order']) - intval($b['order']);
        });
        
        return $banners;
    }
    
    public function getActiveBanners() {
        $banners = $this->getCarouselBanners();
        return array_filter($banners, function($banner) {
            return $banner['status'] === 'active';
        });
    }
    
    public function addBanner($data) {
        $id = uniqid('banner_');
        $banner = [
            'id' => $id,
            'title' => $data['title'],
            'type' => $data['type'], // 'image' or 'video'
            'media_url' => $data['media_url'],
            'order' => $data['order'],
            'status' => $data['status'],
            'created_date' => date('Y-m-d'),
            'updated_date' => date('Y-m-d')
        ];
        
        $headers = ['id', 'title', 'type', 'media_url', 'order', 'status', 'created_date', 'updated_date'];
        return $this->db->appendCSV($this->filename, $banner, $headers);
    }
    
    public function updateBanner($id, $data) {
        return $this->db->updateById($this->filename, $id, $data);
    }
    
    public function deleteBanner($id) {
        return $this->db->deleteById($this->filename, $id);
    }
    
    public function getBannerById($id) {
        return $this->db->findById($this->filename, $id);
    }
    
    public function reorderBanners($bannerIds) {
        $banners = $this->getCarouselBanners();
        $reorderedBanners = [];
        
        foreach ($bannerIds as $index => $id) {
            foreach ($banners as $banner) {
                if ($banner['id'] === $id) {
                    $banner['order'] = $index + 1;
                    $banner['updated_date'] = date('Y-m-d');
                    $reorderedBanners[] = $banner;
                    break;
                }
            }
        }
        
        return $this->db->writeCSV($this->filename, $reorderedBanners);
    }
}
?>
