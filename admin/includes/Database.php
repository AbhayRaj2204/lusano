<?php
class Database {
    private $dataPath;
    
    public function __construct($dataPath = null) {
        if ($dataPath === null) {
            // Get the root directory path (two levels up from admin/includes/)
            $this->dataPath = dirname(__DIR__) . '/data/';
        } else {
            $this->dataPath = $dataPath;
        }
        $this->ensureDataDirectory();
    }
    
    private function ensureDataDirectory() {
        if (!is_dir($this->dataPath)) {
            if (!mkdir($this->dataPath, 0777, true)) {
                error_log("Failed to create data directory: " . $this->dataPath);
                return false;
            }
            chmod($this->dataPath, 0777);
        }
        return true;
    }
    
    public function readCSV($filename) {
        $filepath = $this->dataPath . $filename;
        $data = [];
        
        if (!file_exists($filepath)) {
            return $data;
        }
        
        $handle = fopen($filepath, 'r');
        if ($handle === false) {
            return $data;
        }
        
        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            return $data;
        }
        
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }
        
        fclose($handle);
        return $data;
    }
    
    public function writeCSV($filename, $data, $headers = null) {
        $filepath = $this->dataPath . $filename;
        
        if (!$this->ensureDataDirectory()) {
            return false;
        }
        
        $handle = fopen($filepath, 'w');
        if ($handle === false) {
            error_log("Failed to open file for writing: " . $filepath);
            return false;
        }
        
        // Write headers
        if ($headers) {
            fputcsv($handle, $headers);
        } elseif (!empty($data)) {
            fputcsv($handle, array_keys($data[0]));
        }
        
        // Write data
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        
        chmod($filepath, 0666);
        return true;
    }
    
    public function appendCSV($filename, $row, $headers = null) {
        $filepath = $this->dataPath . $filename;
        $fileExists = file_exists($filepath) && filesize($filepath) > 0;
        
        if (!$this->ensureDataDirectory()) {
            return false;
        }
        
        if (!is_writable($this->dataPath)) {
            if (!chmod($this->dataPath, 0777)) {
                error_log("Directory not writable and cannot fix permissions: " . $this->dataPath);
                return false;
            }
        }
        
        $handle = fopen($filepath, 'a');
        if ($handle === false) {
            error_log("Failed to open file for writing: " . $filepath);
            return false;
        }
        
        // Write headers if file is new
        if (!$fileExists && $headers) {
            if (fputcsv($handle, $headers) === false) {
                error_log("Failed to write headers");
                fclose($handle);
                return false;
            }
        }
        
        $result = fputcsv($handle, $row);
        fclose($handle);
        
        if ($result === false) {
            error_log("Failed to write row data");
            return false;
        }
        
        chmod($filepath, 0666);
        return true;
    }
    
    public function findById($filename, $id, $idColumn = 'id') {
        $data = $this->readCSV($filename);
        foreach ($data as $row) {
            if ($row[$idColumn] === $id) {
                return $row;
            }
        }
        return null;
    }
    
    public function updateById($filename, $id, $newData, $idColumn = 'id') {
        $data = $this->readCSV($filename);
        $updated = false;
        
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i][$idColumn] === $id) {
                $data[$i] = array_merge($data[$i], $newData);
                $data[$i]['updated_date'] = date('Y-m-d');
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            return $this->writeCSV($filename, $data);
        }
        
        return false;
    }
    
    public function deleteById($filename, $id, $idColumn = 'id') {
        $data = $this->readCSV($filename);
        $newData = [];
        $deleted = false;
        
        foreach ($data as $row) {
            if ($row[$idColumn] !== $id) {
                $newData[] = $row;
            } else {
                $deleted = true;
            }
        }
        
        if ($deleted) {
            return $this->writeCSV($filename, $newData);
        }
        
        return false;
    }
}
?>
