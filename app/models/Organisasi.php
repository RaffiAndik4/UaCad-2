<?php
// models/Organisasi.php

class Organisasi {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Get organisasi data by user_id
     */
    public function getByUserId($userId) {
        try {
            $query = "SELECT o.*, u.username, u.email, u.status as user_status
                      FROM organisasi o 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.user_id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("Error getting organisasi by user_id: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get organisasi data by id
     */
    public function getById($id) {
        try {
            $query = "SELECT o.*, u.username, u.email, u.status as user_status
                      FROM organisasi o 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("Error getting organisasi by id: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new organisasi
     */
    public function create($data) {
        try {
            $query = "INSERT INTO organisasi (user_id, nama_organisasi, jenis_organisasi, deskripsi, dokumen_pengesahan) 
                      VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("issss", 
                $data['user_id'],
                $data['nama_organisasi'],
                $data['jenis_organisasi'],
                $data['deskripsi'],
                $data['dokumen_pengesahan']
            );
            
            if ($stmt->execute()) {
                return $this->conn->insert_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error creating organisasi: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update organisasi data
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE organisasi SET 
                        nama_organisasi = ?,
                        jenis_organisasi = ?,
                        deskripsi = ?,
                        dokumen_pengesahan = ?,
                        updated_at = CURRENT_TIMESTAMP
                      WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssssi",
                $data['nama_organisasi'],
                $data['jenis_organisasi'],
                $data['deskripsi'],
                $data['dokumen_pengesahan'],
                $id
            );
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error updating organisasi: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update verification status
     */
    public function updateVerificationStatus($id, $status) {
        try {
            $query = "UPDATE organisasi SET 
                        status_verifikasi = ?,
                        updated_at = CURRENT_TIMESTAMP
                      WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $status, $id);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error updating verification status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all organisasi with pagination
     */
    public function getAll($limit = 10, $offset = 0) {
        try {
            $query = "SELECT o.*, u.username, u.email, u.status as user_status
                      FROM organisasi o 
                      JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC
                      LIMIT ? OFFSET ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $organisasi = [];
            while ($row = $result->fetch_assoc()) {
                $organisasi[] = $row;
            }
            
            return $organisasi;
            
        } catch (Exception $e) {
            error_log("Error getting all organisasi: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get organisasi count
     */
    public function getCount() {
        try {
            $query = "SELECT COUNT(*) as total FROM organisasi";
            $result = $this->conn->query($query);
            $row = $result->fetch_assoc();
            
            return $row['total'];
            
        } catch (Exception $e) {
            error_log("Error getting organisasi count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Search organisasi
     */
    public function search($keyword, $limit = 10, $offset = 0) {
        try {
            $searchTerm = "%$keyword%";
            $query = "SELECT o.*, u.username, u.email, u.status as user_status
                      FROM organisasi o 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.nama_organisasi LIKE ? 
                         OR o.jenis_organisasi LIKE ?
                         OR u.username LIKE ?
                      ORDER BY o.created_at DESC
                      LIMIT ? OFFSET ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $organisasi = [];
            while ($row = $result->fetch_assoc()) {
                $organisasi[] = $row;
            }
            
            return $organisasi;
            
        } catch (Exception $e) {
            error_log("Error searching organisasi: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete organisasi
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM organisasi WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error deleting organisasi: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if organisasi exists for user
     */
    public function existsForUser($userId) {
        try {
            $query = "SELECT COUNT(*) as count FROM organisasi WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] > 0;
            
        } catch (Exception $e) {
            error_log("Error checking organisasi exists: " . $e->getMessage());
            return false;
        }
    }
}