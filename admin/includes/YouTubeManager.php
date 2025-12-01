<?php
require_once 'Database.php';

class YouTubeManager {
    private $db;
    private $filename = 'youtube_videos.csv';
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function createTable() {
        // Check if CSV file exists, if not create it with headers
        $data = $this->db->readCSV($this->filename);
        if (empty($data)) {
            $headers = ['id', 'title', 'description', 'youtube_url', 'video_id', 'status', 'display_order', 'created_at', 'updated_at'];
            return $this->db->writeCSV($this->filename, [], $headers);
        }
        return true;
    }
    
    public function addVideo($data) {
        // Extract YouTube video ID from URL
        $videoId = $this->extractYouTubeId($data['youtube_url']);
        if (!$videoId) {
            return false;
        }
        
        // Generate new ID
        $existingData = $this->db->readCSV($this->filename);
        $newId = 1;
        if (!empty($existingData)) {
            $maxId = max(array_column($existingData, 'id'));
            $newId = $maxId + 1;
        }
        
        $row = [
            'id' => $newId,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'youtube_url' => $data['youtube_url'],
            'video_id' => $videoId,
            'status' => $data['status'] ?? 'active',
            'display_order' => $data['display_order'] ?? 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $headers = ['id', 'title', 'description', 'youtube_url', 'video_id', 'status', 'display_order', 'created_at', 'updated_at'];
        return $this->db->appendCSV($this->filename, $row, $headers);
    }
    
    public function updateVideo($id, $data) {
        // Extract YouTube video ID from URL
        $videoId = $this->extractYouTubeId($data['youtube_url']);
        if (!$videoId) {
            return false;
        }
        
        $updateData = [
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'youtube_url' => $data['youtube_url'],
            'video_id' => $videoId,
            'status' => $data['status'] ?? 'active',
            'display_order' => $data['display_order'] ?? 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->updateById($this->filename, (string)$id, $updateData);
    }
    
    public function deleteVideo($id) {
        return $this->db->deleteById($this->filename, (string)$id);
    }
    
    public function getVideo($id) {
        return $this->db->findById($this->filename, (string)$id);
    }
    
    public function getAllVideos() {
        $data = $this->db->readCSV($this->filename);
        
        // Sort by display_order ASC, then by created_at DESC
        usort($data, function($a, $b) {
            $orderCompare = (int)$a['display_order'] - (int)$b['display_order'];
            if ($orderCompare === 0) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            }
            return $orderCompare;
        });
        
        return $data;
    }
    
    public function getActiveVideos() {
        $data = $this->db->readCSV($this->filename);
        
        // Filter active videos
        $activeVideos = array_filter($data, function($video) {
            return $video['status'] === 'active';
        });
        
        // Sort by display_order ASC, then by created_at DESC
        usort($activeVideos, function($a, $b) {
            $orderCompare = (int)$a['display_order'] - (int)$b['display_order'];
            if ($orderCompare === 0) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            }
            return $orderCompare;
        });
        
        return array_values($activeVideos);
    }
    
    public function updateOrder($videoIds) {
        foreach ($videoIds as $index => $id) {
            $updateData = [
                'display_order' => $index + 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->updateById($this->filename, (string)$id, $updateData);
        }
        return true;
    }
    
    private function extractYouTubeId($url) {
        // Handle various YouTube URL formats
        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return false;
    }
    
    public function getEmbedUrl($videoId) {
        return "https://www.youtube.com/embed/{$videoId}?rel=0&modestbranding=1";
    }
    
    public function getThumbnailUrl($videoId) {
        return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
    }
}
?>
    