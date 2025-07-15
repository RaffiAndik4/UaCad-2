<?php
// app/models/Aspirasi.php

class Aspirasi {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->createTableIfNotExists();
    }
    
    private function createTableIfNotExists() {
        // Create aspirasi table if not exists
        $checkTable = "SHOW TABLES LIKE 'aspirasi'";
        $result = $this->conn->query($checkTable);
        
        if ($result->num_rows == 0) {
            $createTable = "CREATE TABLE aspirasi (
                id INT AUTO_INCREMENT PRIMARY KEY,
                mahasiswa_id INT NOT NULL,
                judul VARCHAR(255) NOT NULL,
                kategori_event ENUM('seminar', 'workshop', 'kompetisi', 'webinar', 'pelatihan', 'sosial', 'olahraga', 'seni', 'teknologi', 'lainnya') NOT NULL,
                deskripsi TEXT NOT NULL,
                sasaran_peserta VARCHAR(100),
                estimasi_waktu VARCHAR(50),
                lokasi_prefer VARCHAR(100),
                urgency ENUM('rendah', 'sedang', 'tinggi', 'sangat_tinggi') DEFAULT 'sedang',
                status ENUM('pending', 'review', 'approved', 'rejected', 'completed') DEFAULT 'pending',
                vote_count INT DEFAULT 0,
                organisasi_id INT NULL,
                admin_notes TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
                FOREIGN KEY (organisasi_id) REFERENCES organisasi(id) ON DELETE SET NULL
            )";
            $this->conn->query($createTable);
        }
        
        // Create aspirasi_votes table for voting system
        $checkVotesTable = "SHOW TABLES LIKE 'aspirasi_votes'";
        $result = $this->conn->query($checkVotesTable);
        
        if ($result->num_rows == 0) {
            $createVotesTable = "CREATE TABLE aspirasi_votes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                aspirasi_id INT NOT NULL,
                mahasiswa_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (aspirasi_id) REFERENCES aspirasi(id) ON DELETE CASCADE,
                FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
                UNIQUE KEY unique_vote (aspirasi_id, mahasiswa_id)
            )";
            $this->conn->query($createVotesTable);
        }
        
        // Create aspirasi_comments table for comments
        $checkCommentsTable = "SHOW TABLES LIKE 'aspirasi_comments'";
        $result = $this->conn->query($checkCommentsTable);
        
        if ($result->num_rows == 0) {
            $createCommentsTable = "CREATE TABLE aspirasi_comments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                aspirasi_id INT NOT NULL,
                user_id INT NOT NULL,
                user_type ENUM('mahasiswa', 'organisasi') NOT NULL,
                comment TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (aspirasi_id) REFERENCES aspirasi(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $this->conn->query($createCommentsTable);
        }
    }
    
    // Create new aspirasi
    public function create($data) {
        try {
            $query = "INSERT INTO aspirasi (mahasiswa_id, judul, kategori_event, deskripsi, sasaran_peserta, estimasi_waktu, lokasi_prefer, urgency) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("isssssss",
                $data['mahasiswa_id'],
                $data['judul'],
                $data['kategori_event'],
                $data['deskripsi'],
                $data['sasaran_peserta'],
                $data['estimasi_waktu'],
                $data['lokasi_prefer'],
                $data['urgency']
            );
            
            if ($stmt->execute()) {
                return $this->conn->insert_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error creating aspirasi: " . $e->getMessage());
            return false;
        }
    }
    
    // Get all aspirasi with filters
    public function getAll($filters = [], $limit = 20, $offset = 0) {
        try {
            $query = "SELECT a.*, 
                             m.nama_lengkap as pengusul_nama,
                             m.fakultas as pengusul_fakultas,
                             m.jurusan as pengusul_jurusan,
                             o.nama_organisasi,
                             COUNT(av.id) as vote_count
                      FROM aspirasi a
                      JOIN mahasiswa m ON a.mahasiswa_id = m.id
                      LEFT JOIN organisasi o ON a.organisasi_id = o.id
                      LEFT JOIN aspirasi_votes av ON a.id = av.aspirasi_id";
            
            $conditions = [];
            $params = [];
            $types = "";
            
            // Apply filters
            if (!empty($filters['kategori'])) {
                $conditions[] = "a.kategori_event = ?";
                $params[] = $filters['kategori'];
                $types .= "s";
            }
            
            if (!empty($filters['status'])) {
                $conditions[] = "a.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            if (!empty($filters['urgency'])) {
                $conditions[] = "a.urgency = ?";
                $params[] = $filters['urgency'];
                $types .= "s";
            }
            
            if (!empty($filters['fakultas'])) {
                $conditions[] = "m.fakultas = ?";
                $params[] = $filters['fakultas'];
                $types .= "s";
            }
            
            if (!empty($filters['search'])) {
                $conditions[] = "(a.judul LIKE ? OR a.deskripsi LIKE ?)";
                $searchTerm = "%" . $filters['search'] . "%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= "ss";
            }
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $query .= " GROUP BY a.id ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
            
            $stmt = $this->conn->prepare($query);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $aspirasi = [];
            while ($row = $result->fetch_assoc()) {
                $aspirasi[] = $row;
            }
            
            return $aspirasi;
            
        } catch (Exception $e) {
            error_log("Error getting all aspirasi: " . $e->getMessage());
            return [];
        }
    }
    
    // Get aspirasi by mahasiswa
    public function getByMahasiswa($mahasiswaId) {
        try {
            $query = "SELECT a.*, 
                             COUNT(av.id) as vote_count,
                             o.nama_organisasi
                      FROM aspirasi a
                      LEFT JOIN aspirasi_votes av ON a.id = av.aspirasi_id
                      LEFT JOIN organisasi o ON a.organisasi_id = o.id
                      WHERE a.mahasiswa_id = ?
                      GROUP BY a.id
                      ORDER BY a.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $aspirasi = [];
            while ($row = $result->fetch_assoc()) {
                $aspirasi[] = $row;
            }
            
            return $aspirasi;
            
        } catch (Exception $e) {
            error_log("Error getting aspirasi by mahasiswa: " . $e->getMessage());
            return [];
        }
    }
    
    // Get single aspirasi with details
    public function getById($id) {
        try {
            $query = "SELECT a.*, 
                             m.nama_lengkap as pengusul_nama,
                             m.fakultas as pengusul_fakultas,
                             m.jurusan as pengusul_jurusan,
                             m.nim as pengusul_nim,
                             o.nama_organisasi,
                             COUNT(av.id) as vote_count
                      FROM aspirasi a
                      JOIN mahasiswa m ON a.mahasiswa_id = m.id
                      LEFT JOIN organisasi o ON a.organisasi_id = o.id
                      LEFT JOIN aspirasi_votes av ON a.id = av.aspirasi_id
                      WHERE a.id = ?
                      GROUP BY a.id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("Error getting aspirasi by id: " . $e->getMessage());
            return null;
        }
    }
    
    // Update aspirasi
    public function update($id, $data) {
        try {
            $query = "UPDATE aspirasi SET 
                        judul = ?, 
                        kategori_event = ?, 
                        deskripsi = ?, 
                        sasaran_peserta = ?, 
                        estimasi_waktu = ?, 
                        lokasi_prefer = ?, 
                        urgency = ?,
                        updated_at = CURRENT_TIMESTAMP
                      WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssssssi",
                $data['judul'],
                $data['kategori_event'],
                $data['deskripsi'],
                $data['sasaran_peserta'],
                $data['estimasi_waktu'],
                $data['lokasi_prefer'],
                $data['urgency'],
                $id
            );
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error updating aspirasi: " . $e->getMessage());
            return false;
        }
    }
    
    // Update status (for organization/admin)
    public function updateStatus($id, $status, $organisasiId = null, $adminNotes = null) {
        try {
            $query = "UPDATE aspirasi SET 
                        status = ?, 
                        organisasi_id = ?, 
                        admin_notes = ?,
                        updated_at = CURRENT_TIMESTAMP
                      WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sisi", $status, $organisasiId, $adminNotes, $id);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error updating aspirasi status: " . $e->getMessage());
            return false;
        }
    }
    
    // Vote aspirasi
    public function vote($aspirasiId, $mahasiswaId) {
        try {
            // Check if already voted
            $checkQuery = "SELECT id FROM aspirasi_votes WHERE aspirasi_id = ? AND mahasiswa_id = ?";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->bind_param("ii", $aspirasiId, $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Remove vote (unlike)
                $deleteQuery = "DELETE FROM aspirasi_votes WHERE aspirasi_id = ? AND mahasiswa_id = ?";
                $stmt = $this->conn->prepare($deleteQuery);
                $stmt->bind_param("ii", $aspirasiId, $mahasiswaId);
                $stmt->execute();
                
                return ['action' => 'unvoted', 'success' => true];
            } else {
                // Add vote (like)
                $insertQuery = "INSERT INTO aspirasi_votes (aspirasi_id, mahasiswa_id) VALUES (?, ?)";
                $stmt = $this->conn->prepare($insertQuery);
                $stmt->bind_param("ii", $aspirasiId, $mahasiswaId);
                $stmt->execute();
                
                return ['action' => 'voted', 'success' => true];
            }
            
        } catch (Exception $e) {
            error_log("Error voting aspirasi: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Check if user has voted
    public function hasVoted($aspirasiId, $mahasiswaId) {
        try {
            $query = "SELECT id FROM aspirasi_votes WHERE aspirasi_id = ? AND mahasiswa_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $aspirasiId, $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0;
            
        } catch (Exception $e) {
            error_log("Error checking vote: " . $e->getMessage());
            return false;
        }
    }
    
    // Add comment
    public function addComment($aspirasiId, $userId, $userType, $comment) {
        try {
            $query = "INSERT INTO aspirasi_comments (aspirasi_id, user_id, user_type, comment) 
                      VALUES (?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("iiss", $aspirasiId, $userId, $userType, $comment);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error adding comment: " . $e->getMessage());
            return false;
        }
    }
    
    // Get comments
    public function getComments($aspirasiId) {
        try {
            $query = "SELECT c.*, u.username,
                             CASE 
                                WHEN c.user_type = 'mahasiswa' THEN m.nama_lengkap
                                WHEN c.user_type = 'organisasi' THEN o.nama_organisasi
                                ELSE u.username
                             END as display_name
                      FROM aspirasi_comments c
                      JOIN users u ON c.user_id = u.id
                      LEFT JOIN mahasiswa m ON c.user_id = m.user_id AND c.user_type = 'mahasiswa'
                      LEFT JOIN organisasi o ON c.user_id = o.user_id AND c.user_type = 'organisasi'
                      WHERE c.aspirasi_id = ?
                      ORDER BY c.created_at ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $aspirasiId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $comments = [];
            while ($row = $result->fetch_assoc()) {
                $comments[] = $row;
            }
            
            return $comments;
            
        } catch (Exception $e) {
            error_log("Error getting comments: " . $e->getMessage());
            return [];
        }
    }
    
    // Delete aspirasi
    public function delete($id, $mahasiswaId = null) {
        try {
            $query = "DELETE FROM aspirasi WHERE id = ?";
            $params = [$id];
            $types = "i";
            
            // If mahasiswaId is provided, ensure only owner can delete
            if ($mahasiswaId) {
                $query .= " AND mahasiswa_id = ?";
                $params[] = $mahasiswaId;
                $types .= "i";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error deleting aspirasi: " . $e->getMessage());
            return false;
        }
    }
    
    // Get statistics
    public function getStatistics() {
        try {
            $stats = [];
            
            // Total aspirasi
            $query = "SELECT COUNT(*) as total FROM aspirasi";
            $result = $this->conn->query($query);
            $stats['total'] = $result->fetch_assoc()['total'];
            
            // By status
            $query = "SELECT status, COUNT(*) as count FROM aspirasi GROUP BY status";
            $result = $this->conn->query($query);
            $statusStats = [];
            while ($row = $result->fetch_assoc()) {
                $statusStats[$row['status']] = $row['count'];
            }
            $stats['by_status'] = $statusStats;
            
            // By category
            $query = "SELECT kategori_event, COUNT(*) as count FROM aspirasi GROUP BY kategori_event ORDER BY count DESC";
            $result = $this->conn->query($query);
            $categoryStats = [];
            while ($row = $result->fetch_assoc()) {
                $categoryStats[] = $row;
            }
            $stats['by_category'] = $categoryStats;
            
            // By urgency
            $query = "SELECT urgency, COUNT(*) as count FROM aspirasi GROUP BY urgency";
            $result = $this->conn->query($query);
            $urgencyStats = [];
            while ($row = $result->fetch_assoc()) {
                $urgencyStats[$row['urgency']] = $row['count'];
            }
            $stats['by_urgency'] = $urgencyStats;
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error getting statistics: " . $e->getMessage());
            return [];
        }
    }
    
    // Get trending aspirasi (most voted)
    public function getTrending($limit = 10) {
        try {
            $query = "SELECT a.*, 
                             m.nama_lengkap as pengusul_nama,
                             m.fakultas as pengusul_fakultas,
                             COUNT(av.id) as vote_count
                      FROM aspirasi a
                      JOIN mahasiswa m ON a.mahasiswa_id = m.id
                      LEFT JOIN aspirasi_votes av ON a.id = av.aspirasi_id
                      WHERE a.status = 'pending'
                      GROUP BY a.id
                      HAVING vote_count > 0
                      ORDER BY vote_count DESC, a.created_at DESC
                      LIMIT ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trending = [];
            while ($row = $result->fetch_assoc()) {
                $trending[] = $row;
            }
            
            return $trending;
            
        } catch (Exception $e) {
            error_log("Error getting trending aspirasi: " . $e->getMessage());
            return [];
        }
    }
}