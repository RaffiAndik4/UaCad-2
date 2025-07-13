<?php
// app/models/User.php

class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function login($username, $password, $role) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND role = ?");
        $stmt->bind_param("sss", $username, $username, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $this->updateLastLogin($user['id']);
                return $user;
            }
        }
        
        return false;
    }
    
    public function registerMahasiswa($data, $uploadedFile) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();
            
            // Validate required fields
            $required = ['nama_lengkap', 'nim', 'fakultas', 'jurusan', 'angkatan', 'minat', 'email', 'password'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field $field harus diisi!");
                }
            }
            
            // Validate email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Format email tidak valid!');
            }
            
            // Validate password
            if (strlen($data['password']) < 6) {
                throw new Exception('Password minimal 6 karakter!');
            }
            
            // Validate NIM format
            if (!preg_match('/^\d{8,12}$/', $data['nim'])) {
                throw new Exception('Format NIM tidak valid!');
            }
            
            // Check if email or NIM already exists
            if (!$this->isEmailAvailable($data['email'])) {
                throw new Exception('Email sudah terdaftar!');
            }
            
            if (!$this->isNimAvailable($data['nim'])) {
                throw new Exception('NIM sudah terdaftar!');
            }
            
            // Generate username from NIM
            $username = strtolower($data['nim']);
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'mahasiswa', 'pending')");
            $stmt->bind_param("sss", $username, $data['email'], $hashedPassword);
            
            if (!$stmt->execute()) {
                throw new Exception('Gagal membuat akun user!');
            }
            
            $userId = $conn->insert_id;
            
            // Insert mahasiswa data
            $stmt = $conn->prepare("INSERT INTO mahasiswa (user_id, nim, nama_lengkap, fakultas, jurusan, angkatan, minat, dokumen_jadwal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssss", $userId, $data['nim'], $data['nama_lengkap'], $data['fakultas'], $data['jurusan'], $data['angkatan'], $data['minat'], $uploadedFile);
            
            if (!$stmt->execute()) {
                throw new Exception('Gagal menyimpan data mahasiswa!');
            }
            
            $conn->commit();
            return ['success' => true, 'message' => 'Registrasi mahasiswa berhasil! Akun Anda akan diverifikasi dalam 1x24 jam.'];
            
        } catch (Exception $e) {
            $conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function registerOrganisasi($data, $uploadedFile) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();
            
            // Validate required fields
            $required = ['nama_organisasi', 'jenis_organisasi', 'email_organisasi', 'password_organisasi'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field $field harus diisi!");
                }
            }
            
            // Validate email
            if (!filter_var($data['email_organisasi'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Format email tidak valid!');
            }
            
            // Validate password
            if (strlen($data['password_organisasi']) < 6) {
                throw new Exception('Password minimal 6 karakter!');
            }
            
            // Check if email already exists
            if (!$this->isEmailAvailable($data['email_organisasi'])) {
                throw new Exception('Email sudah terdaftar!');
            }
            
            // Check if organization name already exists
            if (!$this->isOrganisasiNameAvailable($data['nama_organisasi'])) {
                throw new Exception('Nama organisasi sudah terdaftar!');
            }
            
            // Generate username from organization name
            $username = strtolower(str_replace(' ', '_', $data['nama_organisasi']));
            
            // Hash password
            $hashedPassword = password_hash($data['password_organisasi'], PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'organisasi', 'pending')");
            $stmt->bind_param("sss", $username, $data['email_organisasi'], $hashedPassword);
            
            if (!$stmt->execute()) {
                throw new Exception('Gagal membuat akun user!');
            }
            
            $userId = $conn->insert_id;
            
            // Insert organisasi data
            $status = 'pending';
            $deskripsi = $data['deskripsi_organisasi'] ?? '';
            $stmt = $conn->prepare("INSERT INTO organisasi (user_id, nama_organisasi, jenis_organisasi, deskripsi, dokumen_pengesahan, status_verifikasi) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $userId, $data['nama_organisasi'], $data['jenis_organisasi'], $deskripsi, $uploadedFile, $status);
            
            if (!$stmt->execute()) {
                throw new Exception('Gagal menyimpan data organisasi!');
            }
            
            $conn->commit();
            return ['success' => true, 'message' => 'Registrasi organisasi berhasil! Akun Anda akan diverifikasi dalam 1x24 jam.'];
            
        } catch (Exception $e) {
            $conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function isEmailAvailable($email) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows === 0;
    }
    
    public function isNimAvailable($nim) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT id FROM mahasiswa WHERE nim = ?");
        $stmt->bind_param("s", $nim);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows === 0;
    }
    
    public function isOrganisasiNameAvailable($name) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT id FROM organisasi WHERE nama_organisasi = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows === 0;
    }
    
    public function getUserById($id) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    public function getUserData($userId, $role) {
        $conn = $this->db->getConnection();
        
        switch ($role) {
            case 'mahasiswa':
                $stmt = $conn->prepare("SELECT m.*, u.username, u.email FROM mahasiswa m JOIN users u ON m.user_id = u.id WHERE m.user_id = ?");
                break;
                
            case 'organisasi':
                $stmt = $conn->prepare("SELECT o.*, u.username, u.email FROM organisasi o JOIN users u ON o.user_id = u.id WHERE o.user_id = ?");
                break;
                
            case 'staff':
                $stmt = $conn->prepare("SELECT s.*, u.username, u.email FROM staff s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
                break;
                
            default:
                return null;
        }
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    private function updateLastLogin($userId) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    public function logActivity($userId, $action, $description = '') {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt->bind_param("issss", $userId, $action, $description, $ipAddress, $userAgent);
        return $stmt->execute();
    }
}
?>