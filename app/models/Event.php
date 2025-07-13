<?php
class Event {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->createTableIfNotExists();
    }
    
    private function createTableIfNotExists() {
        $checkTable = "SHOW TABLES LIKE 'events'";
        $result = $this->conn->query($checkTable);
        
        if ($result->num_rows == 0) {
            $createTable = "CREATE TABLE events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                organisasi_id INT NOT NULL,
                nama_event VARCHAR(200) NOT NULL,
                deskripsi TEXT,
                kategori VARCHAR(50),
                tanggal_mulai DATETIME,
                tanggal_selesai DATETIME,
                lokasi VARCHAR(200),
                kapasitas INT,
                poster VARCHAR(255),
                status ENUM('draft', 'aktif', 'selesai', 'dibatalkan') DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $this->conn->query($createTable);
        } else {
            // Add poster column if not exists - SIMPLE CHECK
            $checkColumn = "SHOW COLUMNS FROM events LIKE 'poster'";
            $result = $this->conn->query($checkColumn);
            if ($result->num_rows == 0) {
                $this->conn->query("ALTER TABLE events ADD poster VARCHAR(255) AFTER kapasitas");
            }
        }
    }
    
    public function create($data) {
        $query = "INSERT INTO events (organisasi_id, nama_event, deskripsi, kategori, tanggal_mulai, tanggal_selesai, lokasi, kapasitas, poster, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isssssssss",
            $data['organisasi_id'],
            $data['nama_event'],
            $data['deskripsi'],
            $data['kategori'],
            $data['tanggal_mulai'],
            $data['tanggal_selesai'],
            $data['lokasi'],
            $data['kapasitas'],
            $data['poster'],
            $data['status']
        );
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    public function getStatsByOrganisasiId($orgId) {
        $query = "SELECT 
                    COUNT(*) as total_events,
                    COUNT(CASE WHEN status = 'aktif' THEN 1 END) as active_events,
                    SUM(CASE WHEN kapasitas IS NOT NULL THEN kapasitas ELSE 0 END) as total_capacity
                  FROM events WHERE organisasi_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orgId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc() ?: [
            'total_events' => 0,
            'active_events' => 0, 
            'total_capacity' => 0
        ];
    }
    
    public function getTrendData($orgId) {
        $query = "SELECT MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as count
                  FROM events WHERE organisasi_id = ? 
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                  GROUP BY YEAR(created_at), MONTH(created_at)
                  ORDER BY year, month";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orgId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    public function getCategoryData($orgId) {
        $query = "SELECT kategori, COUNT(*) as count FROM events 
                  WHERE organisasi_id = ? GROUP BY kategori ORDER BY count DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orgId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    public function getActiveEvents($orgId, $limit = 5) {
        $query = "SELECT * FROM events 
                  WHERE organisasi_id = ? AND status IN ('aktif', 'draft')
                  ORDER BY created_at DESC LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $orgId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        return $events;
    }
}