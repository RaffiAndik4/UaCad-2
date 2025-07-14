<?php
// Methods untuk Mahasiswa Model

class Mahasiswa {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->createTablesIfNotExists();
    }
    
    private function createTablesIfNotExists() {
        // Create mahasiswa table if not exists
        $checkTable = "SHOW TABLES LIKE 'mahasiswa'";
        $result = $this->conn->query($checkTable);
        
        if ($result->num_rows == 0) {
            $createTable = "CREATE TABLE mahasiswa (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL UNIQUE,
                nim VARCHAR(20) NOT NULL UNIQUE,
                nama_lengkap VARCHAR(100) NOT NULL,
                fakultas VARCHAR(50) NOT NULL,
                jurusan VARCHAR(50) NOT NULL,
                angkatan YEAR NOT NULL,
                minat TEXT,
                bio TEXT,
                no_hp VARCHAR(20),
                alamat TEXT,
                foto_profil VARCHAR(255),
                dokumen_jadwal VARCHAR(255),
                status_verifikasi ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $this->conn->query($createTable);
        }
        
        // Create prestasi table
        $checkPrestasi = "SHOW TABLES LIKE 'prestasi'";
        $result = $this->conn->query($checkPrestasi);
        
        if ($result->num_rows == 0) {
            $createPrestasi = "CREATE TABLE prestasi (
                id INT AUTO_INCREMENT PRIMARY KEY,
                mahasiswa_id INT NOT NULL,
                nama_prestasi VARCHAR(255) NOT NULL,
                jenis_prestasi ENUM('akademik', 'non-akademik', 'organisasi', 'kompetisi') NOT NULL,
                tingkat ENUM('kampus', 'regional', 'nasional', 'internasional') NOT NULL,
                tahun YEAR NOT NULL,
                deskripsi TEXT,
                bukti_dokumen VARCHAR(255),
                status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
            )";
            $this->conn->query($createPrestasi);
        }
        
        // Create sertifikat table
        $checkSertifikat = "SHOW TABLES LIKE 'sertifikat'";
        $result = $this->conn->query($checkSertifikat);
        
        if ($result->num_rows == 0) {
            $createSertifikat = "CREATE TABLE sertifikat (
                id INT AUTO_INCREMENT PRIMARY KEY,
                mahasiswa_id INT NOT NULL,
                event_id INT,
                nama_sertifikat VARCHAR(255) NOT NULL,
                penerbit VARCHAR(255),
                tanggal_terbit DATE,
                file_sertifikat VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
                FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL
            )";
            $this->conn->query($createSertifikat);
        }
        
        // Create jadwal_kuliah table
        $checkJadwal = "SHOW TABLES LIKE 'jadwal_kuliah'";
        $result = $this->conn->query($checkJadwal);
        
        if ($result->num_rows == 0) {
            $createJadwal = "CREATE TABLE jadwal_kuliah (
                id INT AUTO_INCREMENT PRIMARY KEY,
                mahasiswa_id INT NOT NULL,
                mata_kuliah VARCHAR(255) NOT NULL,
                dosen VARCHAR(255),
                hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
                jam_mulai TIME NOT NULL,
                jam_selesai TIME NOT NULL,
                ruangan VARCHAR(50),
                semester VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
            )";
            $this->conn->query($createJadwal);
        }
        
        // Create mahasiswa_organisasi table (following organizations)
        $checkMahasiswaOrg = "SHOW TABLES LIKE 'mahasiswa_organisasi'";
        $result = $this->conn->query($checkMahasiswaOrg);
        
        if ($result->num_rows == 0) {
            $createMahasiswaOrg = "CREATE TABLE mahasiswa_organisasi (
                id INT AUTO_INCREMENT PRIMARY KEY,
                mahasiswa_id INT NOT NULL,
                organisasi_id INT NOT NULL,
                status ENUM('following', 'member', 'alumni') DEFAULT 'following',
                joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
                FOREIGN KEY (organisasi_id) REFERENCES organisasi(id) ON DELETE CASCADE,
                UNIQUE KEY unique_follow (mahasiswa_id, organisasi_id)
            )";
            $this->conn->query($createMahasiswaOrg);
        }
    }
    
    // Basic CRUD Operations
    public function getByUserId($userId) {
        try {
            $query = "SELECT m.*, u.username, u.email, u.status as user_status
                      FROM mahasiswa m 
                      JOIN users u ON m.user_id = u.id 
                      WHERE m.user_id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("Error getting mahasiswa by user_id: " . $e->getMessage());
            return false;
        }
    }
    
    
    public function updateProfile($id, $data) {
        try {
            $query = "UPDATE mahasiswa SET 
                        nama_lengkap = ?,
                        minat = ?,
                        bio = ?,
                        no_hp = ?,
                        alamat = ?,
                        updated_at = CURRENT_TIMESTAMP";
            
            $params = [
                $data['nama_lengkap'],
                $data['minat'],
                $data['bio'],
                $data['no_hp'],
                $data['alamat']
            ];
            $types = "sssss";
            
            if (isset($data['foto_profil'])) {
                $query .= ", foto_profil = ?";
                $params[] = $data['foto_profil'];
                $types .= "s";
            }
            
            $query .= " WHERE id = ?";
            $params[] = $id;
            $types .= "i";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error updating mahasiswa profile: " . $e->getMessage());
            return false;
        }
    }
    
    // Jadwal Kuliah Methods
    public function getJadwalKuliah($mahasiswaId) {
        try {
            $query = "SELECT * FROM jadwal_kuliah 
                      WHERE mahasiswa_id = ? 
                      ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jam_mulai";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $jadwal = [];
            while ($row = $result->fetch_assoc()) {
                $jadwal[] = $row;
            }
            
            return $jadwal;
            
        } catch (Exception $e) {
            error_log("Error getting jadwal kuliah: " . $e->getMessage());
            return [];
        }
    }
    
    public function addJadwalKuliah($mahasiswaId, $jadwalData) {
        try {
            $query = "INSERT INTO jadwal_kuliah (mahasiswa_id, mata_kuliah, dosen, hari, jam_mulai, jam_selesai, ruangan, semester) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("isssssss",
                $mahasiswaId,
                $jadwalData['mata_kuliah'],
                $jadwalData['dosen'],
                $jadwalData['hari'],
                $jadwalData['jam_mulai'],
                $jadwalData['jam_selesai'],
                $jadwalData['ruangan'],
                $jadwalData['semester']
            );
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error adding jadwal kuliah: " . $e->getMessage());
            return false;
        }
    }
    
    // Prestasi Methods
    public function getPrestasi($mahasiswaId) {
        try {
            $query = "SELECT * FROM prestasi 
                      WHERE mahasiswa_id = ? 
                      ORDER BY tahun DESC, created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $prestasi = [];
            while ($row = $result->fetch_assoc()) {
                $prestasi[] = $row;
            }
            
            return $prestasi;
            
        } catch (Exception $e) {
            error_log("Error getting prestasi: " . $e->getMessage());
            return [];
        }
    }
    
    public function createPrestasi($prestasiData) {
        try {
            $query = "INSERT INTO prestasi (mahasiswa_id, nama_prestasi, jenis_prestasi, tingkat, tahun, deskripsi, bukti_dokumen) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("issssss",
                $prestasiData['mahasiswa_id'],
                $prestasiData['nama_prestasi'],
                $prestasiData['jenis_prestasi'],
                $prestasiData['tingkat'],
                $prestasiData['tahun'],
                $prestasiData['deskripsi'],
                $prestasiData['bukti_dokumen']
            );
            
            if ($stmt->execute()) {
                return $this->conn->insert_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error creating prestasi: " . $e->getMessage());
            return false;
        }
    }
    
    // Sertifikat Methods
    public function getSertifikat($mahasiswaId) {
        try {
            $query = "SELECT s.*, e.nama_event 
                      FROM sertifikat s
                      LEFT JOIN events e ON s.event_id = e.id
                      WHERE s.mahasiswa_id = ? 
                      ORDER BY s.tanggal_terbit DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $sertifikat = [];
            while ($row = $result->fetch_assoc()) {
                $sertifikat[] = $row;
            }
            
            return $sertifikat;
            
        } catch (Exception $e) {
            error_log("Error getting sertifikat: " . $e->getMessage());
            return [];
        }
    }
    
    public function addSertifikat($sertifikatData) {
        try {
            $query = "INSERT INTO sertifikat (mahasiswa_id, event_id, nama_sertifikat, penerbit, tanggal_terbit, file_sertifikat) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("iissss",
                $sertifikatData['mahasiswa_id'],
                $sertifikatData['event_id'],
                $sertifikatData['nama_sertifikat'],
                $sertifikatData['penerbit'],
                $sertifikatData['tanggal_terbit'],
                $sertifikatData['file_sertifikat']
            );
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error adding sertifikat: " . $e->getMessage());
            return false;
        }
    }
    
    // Organisasi Methods
    public function getAllOrganisasi() {
        try {
            $query = "SELECT o.*, u.username, u.email,
                             COUNT(mo.mahasiswa_id) as follower_count
                      FROM organisasi o 
                      JOIN users u ON o.user_id = u.id 
                      LEFT JOIN mahasiswa_organisasi mo ON o.id = mo.organisasi_id
                      WHERE o.status_verifikasi = 'verified'
                      GROUP BY o.id
                      ORDER BY o.nama_organisasi";
            
            $result = $this->conn->query($query);
            
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
    
    public function getOrganisasiFakultas($fakultas) {
        try {
            $query = "SELECT o.*, u.username, u.email,
                             COUNT(mo.mahasiswa_id) as follower_count
                      FROM organisasi o 
                      JOIN users u ON o.user_id = u.id 
                      LEFT JOIN mahasiswa_organisasi mo ON o.id = mo.organisasi_id
                      WHERE o.status_verifikasi = 'verified' 
                      AND (o.jenis_organisasi LIKE '%Fakultas%' OR o.nama_organisasi LIKE ?)
                      GROUP BY o.id
                      ORDER BY o.nama_organisasi";
            
            $searchTerm = "%$fakultas%";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $organisasi = [];
            while ($row = $result->fetch_assoc()) {
                $organisasi[] = $row;
            }
            
            return $organisasi;
            
        } catch (Exception $e) {
            error_log("Error getting organisasi fakultas: " . $e->getMessage());
            return [];
        }
    }
    
    public function getFollowedOrganisasi($mahasiswaId) {
        try {
            $query = "SELECT o.*, u.username, u.email, mo.status, mo.joined_at
                      FROM mahasiswa_organisasi mo
                      JOIN organisasi o ON mo.organisasi_id = o.id
                      JOIN users u ON o.user_id = u.id
                      WHERE mo.mahasiswa_id = ?
                      ORDER BY mo.joined_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $organisasi = [];
            while ($row = $result->fetch_assoc()) {
                $organisasi[] = $row;
            }
            
            return $organisasi;
            
        } catch (Exception $e) {
            error_log("Error getting followed organisasi: " . $e->getMessage());
            return [];
        }
    }
    
    public function getOrganisasiRecommendations($fakultas, $minat, $mahasiswaId) {
        try {
            // Get organizations not yet followed by the student
            $query = "SELECT o.*, u.username, u.email,
                             COUNT(mo.mahasiswa_id) as follower_count
                      FROM organisasi o 
                      JOIN users u ON o.user_id = u.id 
                      LEFT JOIN mahasiswa_organisasi mo ON o.id = mo.organisasi_id
                      WHERE o.status_verifikasi = 'verified'
                      AND o.id NOT IN (
                          SELECT organisasi_id FROM mahasiswa_organisasi WHERE mahasiswa_id = ?
                      )
                      AND (o.nama_organisasi LIKE ? OR o.deskripsi LIKE ? OR o.jenis_organisasi LIKE ?)
                      GROUP BY o.id
                      ORDER BY follower_count DESC
                      LIMIT 6";
            
            $searchTerm = "%$minat%";
            $fakultasTerm = "%$fakultas%";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("isss", $mahasiswaId, $searchTerm, $searchTerm, $fakultasTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $recommendations = [];
            while ($row = $result->fetch_assoc()) {
                $recommendations[] = $row;
            }
            
            return $recommendations;
            
        } catch (Exception $e) {
            error_log("Error getting organisasi recommendations: " . $e->getMessage());
            return [];
        }
    }
    
    public function followOrganisasi($mahasiswaId, $organisasiId) {
        try {
            $query = "INSERT INTO mahasiswa_organisasi (mahasiswa_id, organisasi_id, status) 
                      VALUES (?, ?, 'following')
                      ON DUPLICATE KEY UPDATE status = 'following', joined_at = CURRENT_TIMESTAMP";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $mahasiswaId, $organisasiId);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error following organisasi: " . $e->getMessage());
            return false;
        }
    }
    
    public function unfollowOrganisasi($mahasiswaId, $organisasiId) {
        try {
            $query = "DELETE FROM mahasiswa_organisasi WHERE mahasiswa_id = ? AND organisasi_id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $mahasiswaId, $organisasiId);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error unfollowing organisasi: " . $e->getMessage());
            return false;
        }
    }
    
    // Statistics Methods
    public function countCertificates($mahasiswaId) {
        try {
            $query = "SELECT COUNT(*) as count FROM sertifikat WHERE mahasiswa_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)($row['count'] ?? 0);
            
        } catch (Exception $e) {
            error_log("Error counting certificates: " . $e->getMessage());
            return 0;
        }
    }
    
    public function countFollowedOrganizations($mahasiswaId) {
        try {
            $query = "SELECT COUNT(*) as count FROM mahasiswa_organisasi WHERE mahasiswa_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)($row['count'] ?? 0);
            
        } catch (Exception $e) {
            error_log("Error counting followed organizations: " . $e->getMessage());
            return 0;
        }
    }
    
    public function calculateProfileCompletion($mahasiswaId) {
        try {
            $mahasiswa = $this->getById($mahasiswaId);
            if (!$mahasiswa) return 0;
            
            $totalFields = 10;
            $completedFields = 0;
            
            $fields = ['nama_lengkap', 'nim', 'fakultas', 'jurusan', 'angkatan', 'minat', 'bio', 'no_hp', 'alamat', 'foto_profil'];
            
            foreach ($fields as $field) {
                if (!empty($mahasiswa[$field])) {
                    $completedFields++;
                }
            }
            
            return round(($completedFields / $totalFields) * 100);
            
        } catch (Exception $e) {
            error_log("Error calculating profile completion: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getLastActivity($mahasiswaId) {
        try {
            // Get last activity from event registrations or other activities
            $query = "SELECT MAX(registered_at) as last_activity 
                      FROM event_participants ep
                      JOIN mahasiswa m ON ep.user_id = m.user_id
                      WHERE m.id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['last_activity'] ?? null;
            
        } catch (Exception $e) {
            error_log("Error getting last activity: " . $e->getMessage());
            return null;
        }
    }
    
    public function getAccountCreationDate($mahasiswaId) {
        try {
            $query = "SELECT u.created_at 
                      FROM users u 
                      JOIN mahasiswa m ON u.id = m.user_id 
                      WHERE m.id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['created_at'] ?? null;
            
        } catch (Exception $e) {
            error_log("Error getting account creation date: " . $e->getMessage());
            return null;
        }
    }
    
    public function getTotalPoints($mahasiswaId) {
        try {
            // Calculate points based on activities (events attended, achievements, etc.)
            $points = 0;
            
            // Points from attended events
            $query = "SELECT COUNT(*) as attended_events
                      FROM event_participants ep
                      JOIN mahasiswa m ON ep.user_id = m.user_id
                      WHERE m.id = ? AND ep.status = 'hadir'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $points += ($row['attended_events'] ?? 0) * 10; // 10 points per attended event
            
            // Points from achievements
            $query = "SELECT COUNT(*) as achievements FROM prestasi WHERE mahasiswa_id = ? AND status = 'verified'";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $points += ($row['achievements'] ?? 0) * 25; // 25 points per achievement
            
            // Points from certificates
            $query = "SELECT COUNT(*) as certificates FROM sertifikat WHERE mahasiswa_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $points += ($row['certificates'] ?? 0) * 15; // 15 points per certificate
            
            return $points;
            
        } catch (Exception $e) {
            error_log("Error calculating total points: " . $e->getMessage());
            return 0;
        }
    }
    
    // Search and Filter Methods
    public function searchMahasiswa($keyword, $limit = 10, $offset = 0) {
        try {
            $searchTerm = "%$keyword%";
            $query = "SELECT m.*, u.username, u.email 
                      FROM mahasiswa m 
                      JOIN users u ON m.user_id = u.id 
                      WHERE m.nama_lengkap LIKE ? 
                         OR m.nim LIKE ?
                         OR m.fakultas LIKE ?
                         OR m.jurusan LIKE ?
                      ORDER BY m.nama_lengkap
                      LIMIT ? OFFSET ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssssii", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $mahasiswa = [];
            while ($row = $result->fetch_assoc()) {
                $mahasiswa[] = $row;
            }
            
            return $mahasiswa;
            
        } catch (Exception $e) {
            error_log("Error searching mahasiswa: " . $e->getMessage());
            return [];
        }
    }
    
    public function getMahasiswaByFakultas($fakultas) {
        try {
            $query = "SELECT m.*, u.username, u.email 
                      FROM mahasiswa m 
                      JOIN users u ON m.user_id = u.id 
                      WHERE m.fakultas = ?
                      ORDER BY m.nama_lengkap";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $fakultas);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $mahasiswa = [];
            while ($row = $result->fetch_assoc()) {
                $mahasiswa[] = $row;
            }
            
            return $mahasiswa;
            
        } catch (Exception $e) {
            error_log("Error getting mahasiswa by fakultas: " . $e->getMessage());
            return [];
        }
    }
    
    // Validation Methods
    public function isNimExists($nim, $excludeId = null) {
        try {
            $query = "SELECT id FROM mahasiswa WHERE nim = ?";
            $params = [$nim];
            $types = "s";
            
            if ($excludeId) {
                $query .= " AND id != ?";
                $params[] = $excludeId;
                $types .= "i";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0;
            
        } catch (Exception $e) {
            error_log("Error checking NIM exists: " . $e->getMessage());
            return false;
        }
    }
    
    // Utility Methods
    public function getDistinctFakultas() {
        try {
            $query = "SELECT DISTINCT fakultas FROM mahasiswa ORDER BY fakultas";
            $result = $this->conn->query($query);
            
            $fakultas = [];
            while ($row = $result->fetch_assoc()) {
                $fakultas[] = $row['fakultas'];
            }
            
            return $fakultas;
            
        } catch (Exception $e) {
            error_log("Error getting distinct fakultas: " . $e->getMessage());
            return [];
        }
    }
    
    public function getDistinctJurusan($fakultas = null) {
        try {
            $query = "SELECT DISTINCT jurusan FROM mahasiswa";
            
            if ($fakultas) {
                $query .= " WHERE fakultas = ?";
            }
            
            $query .= " ORDER BY jurusan";
            
            $stmt = $this->conn->prepare($query);
            
            if ($fakultas) {
                $stmt->bind_param("s", $fakultas);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $jurusan = [];
            while ($row = $result->fetch_assoc()) {
                $jurusan[] = $row['jurusan'];
            }
            
            return $jurusan;
            
        } catch (Exception $e) {
            error_log("Error getting distinct jurusan: " . $e->getMessage());
            return [];
        }
    }
    
    // Delete Methods
    public function delete($id) {
        try {
            $query = "DELETE FROM mahasiswa WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error deleting mahasiswa: " . $e->getMessage());
            return false;
        }
    }
}