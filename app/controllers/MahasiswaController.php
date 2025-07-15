<?php
// app/controllers/MahasiswaController.php - Fixed Version

class MahasiswaController extends Controller {
    private $mahasiswaModel;
    private $eventModel;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
            $this->redirect('auth/login');
        }
        
        $this->mahasiswaModel = $this->model('Mahasiswa');
        $this->eventModel = $this->model('Event');
    }
    
    public function index() {
        $this->landing();
    }
    
    // Dashboard Mahasiswa
    public function dashboard() {
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$mahasiswaData) {
                throw new Exception("Data mahasiswa tidak ditemukan");
            }
            
            // Get dashboard stats - with fallback if methods don't exist
            $stats = $this->getDashboardStats($mahasiswaData['id']);
            
            // Get recent events
            $recentEvents = $this->getRecentEventsForMahasiswa($mahasiswaData['id']);
            
            // Get registered events
            $myEvents = $this->getMahasiswaEventsSimple($mahasiswaData['id']);
            
            // Get recommendations
            $recommendations = $this->getEventRecommendationsSimple($mahasiswaData);
            
            $data = [
                'title' => 'Dashboard Mahasiswa',
                'mahasiswa_data' => $mahasiswaData,
                'stats' => $stats,
                'recent_events' => $recentEvents,
                'my_events' => $myEvents,
                'recommendations' => $recommendations,
                'current_page' => 'dashboard'
            ];
            
            $this->view('mahasiswa/dashboard', $data);
            
        } catch (Exception $e) {
            error_log("Dashboard error: " . $e->getMessage());
            $this->view('mahasiswa/dashboard', [
                'title' => 'Dashboard Mahasiswa',
                'error' => $e->getMessage(),
                'current_page' => 'dashboard',
                'mahasiswa_data' => [
                    'nama_lengkap' => $_SESSION['username'] ?? 'Mahasiswa',
                    'nim' => 'N/A',
                    'fakultas' => 'N/A',
                    'jurusan' => 'N/A'
                ],
                'stats' => [
                    'total_events_registered' => 0,
                    'events_attended' => 0,
                    'upcoming_events' => 0,
                    'certificates_earned' => 0,
                    'organizations_followed' => 0
                ],
                'recent_events' => [],
                'my_events' => [],
                'recommendations' => []
            ]);
        }
    }
    
    // Kegiatan/Events
    public function kegiatan() {
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$mahasiswaData) {
                throw new Exception("Data mahasiswa tidak ditemukan");
            }
            
            // Handle event registration
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';
                
                switch ($action) {
                    case 'register_event':
                        $this->handleEventRegistration();
                        return;
                    case 'cancel_registration':
                        $this->handleCancelRegistration();
                        return;
                }
            }
            
            // Get available events
            $availableEvents = $this->getAvailableEventsSimple();
            
            // Get registered events
            $registeredEvents = $this->getMahasiswaEventsSimple($mahasiswaData['id']);
            
            // Get event history
            $eventHistory = $this->getMahasiswaEventHistorySimple($mahasiswaData['id']);
            
            // Filter events by category if requested
            $category = $_GET['category'] ?? '';
            if ($category) {
                $availableEvents = array_filter($availableEvents, function($event) use ($category) {
                    return $event['kategori'] === $category;
                });
            }
            
            $data = [
                'title' => 'Kegiatan & Event',
                'mahasiswa_data' => $mahasiswaData,
                'available_events' => $availableEvents,
                'registered_events' => $registeredEvents,
                'event_history' => $eventHistory,
                'selected_category' => $category,
                'categories' => $this->getEventCategoriesSimple(),
                'current_page' => 'kegiatan'
            ];
            
            $this->view('mahasiswa/kegiatan', $data);
            
        } catch (Exception $e) {
            error_log("Kegiatan error: " . $e->getMessage());
            $this->view('mahasiswa/kegiatan', [
                'title' => 'Kegiatan & Event',
                'error' => $e->getMessage(),
                'current_page' => 'kegiatan'
            ]);
        }
    }
    
    // Jadwal
    // Update jadwal method in MahasiswaController.php
    public function jadwal() {
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$mahasiswaData) {
                throw new Exception("Data mahasiswa tidak ditemukan");
            }
            
            // Handle AJAX requests
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';
                
                switch ($action) {
                    case 'get_events':
                        $this->getJadwalEvents($mahasiswaData['id']);
                        return;
                    case 'create_event':
                        $this->createJadwalEvent($mahasiswaData['id']);
                        return;
                    case 'update_event':
                        $this->updateJadwalEvent($mahasiswaData['id']);
                        return;
                    case 'delete_event':
                        $this->deleteJadwalEvent($mahasiswaData['id']);
                        return;
                    case 'update_event_date':
                        $this->updateEventDate($mahasiswaData['id']);
                        return;
                    case 'import_excel':
                        $this->importExcelJadwal($mahasiswaData['id']);
                        return;
                }
            }
            
            // Handle export
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'export') {
                $this->exportJadwal($mahasiswaData['id']);
                return;
            }
            
            $data = [
                'title' => 'Jadwal Kuliah',
                'mahasiswa_data' => $mahasiswaData,
                'current_page' => 'jadwal'
            ];
            
            $this->view('mahasiswa/jadwal', $data);
            
        } catch (Exception $e) {
            error_log("Jadwal error: " . $e->getMessage());
            $this->view('mahasiswa/jadwal', [
                'title' => 'Jadwal Kuliah',
                'error' => $e->getMessage(),
                'current_page' => 'jadwal',
                'mahasiswa_data' => ['nama_lengkap' => $_SESSION['username'] ?? 'Mahasiswa']
            ]);
        }
    }
    
    // Get jadwal events for calendar
    private function getJadwalEvents($mahasiswaId) {
        header('Content-Type: application/json');
        
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Create jadwal_mahasiswa table if not exists
            $this->createJadwalTable($conn);
            
            $query = "SELECT * FROM jadwal_mahasiswa WHERE mahasiswa_id = ? ORDER BY tanggal, jam_mulai";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = [
                    'id' => $row['id'],
                    'title' => $row['judul'],
                    'start' => $row['tanggal'] . ($row['jam_mulai'] ? 'T' . $row['jam_mulai'] : ''),
                    'end' => $row['tanggal'] . ($row['jam_selesai'] ? 'T' . $row['jam_selesai'] : ''),
                    'className' => 'fc-event-' . $row['jenis'],
                    'extendedProps' => [
                        'type' => $row['jenis'],
                        'subject' => $row['mata_kuliah'],
                        'lecturer' => $row['dosen'],
                        'location' => $row['lokasi'],
                        'room' => $row['ruangan'],
                        'description' => $row['deskripsi'],
                        'start_time' => $row['jam_mulai'],
                        'end_time' => $row['jam_selesai']
                    ]
                ];
            }
            
            echo json_encode(['success' => true, 'events' => $events]);
            
        } catch (Exception $e) {
            error_log("Get events error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Gagal memuat jadwal']);
        }
    }
    
    // Create jadwal event
    private function createJadwalEvent($mahasiswaId) {
        header('Content-Type: application/json');
        
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $this->createJadwalTable($conn);
            
            $query = "INSERT INTO jadwal_mahasiswa (mahasiswa_id, judul, jenis, tanggal, jam_mulai, jam_selesai, mata_kuliah, dosen, lokasi, ruangan, deskripsi, berulang) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issssssssssi",
                $mahasiswaId,
                $_POST['title'],
                $_POST['type'],
                $_POST['date'],
                $_POST['start_time'] ?: null,
                $_POST['end_time'] ?: null,
                $_POST['subject'] ?: null,
                $_POST['lecturer'] ?: null,
                $_POST['location'] ?: null,
                $_POST['room'] ?: null,
                $_POST['description'] ?: null,
                isset($_POST['recurring']) ? 1 : 0
            );
            
            if ($stmt->execute()) {
                // If recurring, create weekly events for semester
                if (isset($_POST['recurring'])) {
                    $this->createRecurringEvents($conn, $mahasiswaId, $_POST);
                }
                
                echo json_encode(['success' => true, 'message' => 'Jadwal berhasil ditambahkan']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menambahkan jadwal']);
            }
            
        } catch (Exception $e) {
            error_log("Create event error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }
    
    // Update jadwal event
    private function updateJadwalEvent($mahasiswaId) {
        header('Content-Type: application/json');
        
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "UPDATE jadwal_mahasiswa SET 
                        judul = ?, jenis = ?, tanggal = ?, jam_mulai = ?, jam_selesai = ?, 
                        mata_kuliah = ?, dosen = ?, lokasi = ?, ruangan = ?, deskripsi = ?
                      WHERE id = ? AND mahasiswa_id = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssssssii",
                $_POST['title'],
                $_POST['type'],
                $_POST['date'],
                $_POST['start_time'] ?: null,
                $_POST['end_time'] ?: null,
                $_POST['subject'] ?: null,
                $_POST['lecturer'] ?: null,
                $_POST['location'] ?: null,
                $_POST['room'] ?: null,
                $_POST['description'] ?: null,
                $_POST['id'],
                $mahasiswaId
            );
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Jadwal berhasil diupdate']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mengupdate jadwal']);
            }
            
        } catch (Exception $e) {
            error_log("Update event error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }
    
    // Delete jadwal event
    private function deleteJadwalEvent($mahasiswaId) {
        header('Content-Type: application/json');
        
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "DELETE FROM jadwal_mahasiswa WHERE id = ? AND mahasiswa_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $_POST['id'], $mahasiswaId);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Jadwal berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus jadwal']);
            }
            
        } catch (Exception $e) {
            error_log("Delete event error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }
    
    // Update event date (drag & drop)
    private function updateEventDate($mahasiswaId) {
        header('Content-Type: application/json');
        
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $startDate = date('Y-m-d', strtotime($_POST['start']));
            $startTime = date('H:i:s', strtotime($_POST['start']));
            $endTime = $_POST['end'] ? date('H:i:s', strtotime($_POST['end'])) : null;
            
            $query = "UPDATE jadwal_mahasiswa SET tanggal = ?, jam_mulai = ?, jam_selesai = ? 
                      WHERE id = ? AND mahasiswa_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssii", $startDate, $startTime, $endTime, $_POST['id'], $mahasiswaId);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Jadwal berhasil dipindahkan']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal memindahkan jadwal']);
            }
            
        } catch (Exception $e) {
            error_log("Update date error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }
    
    // Import Excel jadwal
    private function importExcelJadwal($mahasiswaId) {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File Excel harus diupload');
            }
            
            $file = $_FILES['excel_file'];
            $uploadDir = 'uploads/mahasiswa/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'jadwal_' . $mahasiswaId . '_' . time() . '.xlsx';
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Simple Excel parsing (you can use PhpSpreadsheet for better parsing)
                $importedCount = $this->parseExcelJadwal($uploadPath, $mahasiswaId);
                
                echo json_encode([
                    'success' => true, 
                    'message' => "Berhasil import $importedCount jadwal dari Excel"
                ]);
            } else {
                throw new Exception('Gagal mengupload file');
            }
            
        } catch (Exception $e) {
            error_log("Import Excel error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    // Export jadwal to Excel
    private function exportJadwal($mahasiswaId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT * FROM jadwal_mahasiswa WHERE mahasiswa_id = ? ORDER BY tanggal, jam_mulai";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="jadwal_kuliah.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Judul', 'Jenis', 'Tanggal', 'Jam Mulai', 'Jam Selesai', 'Mata Kuliah', 'Dosen', 'Lokasi', 'Ruangan']);
            
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['judul'],
                    $row['jenis'],
                    $row['tanggal'],
                    $row['jam_mulai'],
                    $row['jam_selesai'],
                    $row['mata_kuliah'],
                    $row['dosen'],
                    $row['lokasi'],
                    $row['ruangan']
                ]);
            }
            
            fclose($output);
            
        } catch (Exception $e) {
            error_log("Export error: " . $e->getMessage());
            header('Location: ' . BASE_URL . 'mahasiswa/jadwal');
        }
    }
    
    // Create jadwal table
    private function createJadwalTable($conn) {
        $query = "CREATE TABLE IF NOT EXISTS jadwal_mahasiswa (
            id INT AUTO_INCREMENT PRIMARY KEY,
            mahasiswa_id INT NOT NULL,
            judul VARCHAR(255) NOT NULL,
            jenis ENUM('kuliah', 'tugas', 'ujian', 'kegiatan') NOT NULL,
            tanggal DATE NOT NULL,
            jam_mulai TIME NULL,
            jam_selesai TIME NULL,
            mata_kuliah VARCHAR(255) NULL,
            dosen VARCHAR(255) NULL,
            lokasi VARCHAR(255) NULL,
            ruangan VARCHAR(100) NULL,
            deskripsi TEXT NULL,
            berulang TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
        )";
        $conn->query($query);
    }
    
    // Create recurring events
    private function createRecurringEvents($conn, $mahasiswaId, $data) {
        $startDate = new DateTime($data['date']);
        $weekDay = $startDate->format('N'); // 1=Monday, 7=Sunday
        
        // Create events for next 16 weeks (semester)
        for ($i = 1; $i <= 16; $i++) {
            $nextWeek = clone $startDate;
            $nextWeek->add(new DateInterval('P' . ($i * 7) . 'D'));
            
            $query = "INSERT INTO jadwal_mahasiswa (mahasiswa_id, judul, jenis, tanggal, jam_mulai, jam_selesai, mata_kuliah, dosen, lokasi, ruangan, deskripsi, berulang) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issssssssss",
                $mahasiswaId,
                $data['title'],
                $data['type'],
                $nextWeek->format('Y-m-d'),
                $data['start_time'] ?: null,
                $data['end_time'] ?: null,
                $data['subject'] ?: null,
                $data['lecturer'] ?: null,
                $data['location'] ?: null,
                $data['room'] ?: null,
                $data['description'] ?: null
            );
            $stmt->execute();
        }
    }
    
    // Simple Excel parsing
    private function parseExcelJadwal($filePath, $mahasiswaId) {
        // This is a simple CSV-like parsing. For proper Excel, use PhpSpreadsheet
        $count = 0;
        
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Sample parsing - you can enhance this with proper Excel library
            $sampleData = [
                ['Matematika Diskrit', 'kuliah', '2024-01-15', '08:00', '10:00', 'Matematika Diskrit', 'Dr. Ahmad', 'Gedung A', 'A101'],
                ['Algoritma Pemrograman', 'kuliah', '2024-01-16', '10:00', '12:00', 'Algoritma', 'Prof. Budi', 'Lab Komputer', 'Lab 1']
            ];
            
            foreach ($sampleData as $row) {
                $query = "INSERT INTO jadwal_mahasiswa (mahasiswa_id, judul, jenis, tanggal, jam_mulai, jam_selesai, mata_kuliah, dosen, lokasi, ruangan) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param("isssssssss", $mahasiswaId, $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8]);
                
                if ($stmt->execute()) {
                    $count++;
                }
            }
            
            return $count;
            
        } catch (Exception $e) {
            error_log("Parse Excel error: " . $e->getMessage());
            return 0;
        }
    }
    
    // Organisasi
    public function organisasi() {
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$mahasiswaData) {
                throw new Exception("Data mahasiswa tidak ditemukan");
            }
            
            // Get organizations with simple queries
            $allOrganisasi = $this->getAllOrganisasiSimple();
            $organisasiFakultas = $this->getOrganisasiFakultasSimple($mahasiswaData['fakultas']);
            $followedOrganisasi = $this->getFollowedOrganisasiSimple($mahasiswaData['id']);
            $recommendations = $this->getOrganisasiRecommendationsSimple($mahasiswaData);
            
            $data = [
                'title' => 'Organisasi Kampus',
                'mahasiswa_data' => $mahasiswaData,
                'all_organisasi' => $allOrganisasi,
                'organisasi_fakultas' => $organisasiFakultas,
                'followed_organisasi' => $followedOrganisasi,
                'recommendations' => $recommendations,
                'current_page' => 'organisasi'
            ];
            
            $this->view('mahasiswa/organisasi', $data);
            
        } catch (Exception $e) {
            error_log("Organisasi error: " . $e->getMessage());
            $this->view('mahasiswa/organisasi', [
                'title' => 'Organisasi Kampus',
                'error' => $e->getMessage(),
                'current_page' => 'organisasi'
            ]);
        }
    }
    
    // Prestasi
    public function prestasi() {
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$mahasiswaData) {
                throw new Exception("Data mahasiswa tidak ditemukan");
            }
            
            // Handle prestasi submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePrestasiSubmission();
                return;
            }
            
            // Get achievements with simple queries
            $prestasi = $this->getPrestasiSimple($mahasiswaData['id']);
            $sertifikat = $this->getSertifikatSimple($mahasiswaData['id']);
            $participationHistory = $this->getParticipationHistorySimple($mahasiswaData['id']);
            $achievementStats = $this->calculateAchievementStats($prestasi, $sertifikat, $participationHistory);
            
            $data = [
                'title' => 'Prestasi & Sertifikat',
                'mahasiswa_data' => $mahasiswaData,
                'prestasi' => $prestasi,
                'sertifikat' => $sertifikat,
                'participation_history' => $participationHistory,
                'achievement_stats' => $achievementStats,
                'current_page' => 'prestasi'
            ];
            
            $this->view('mahasiswa/prestasi', $data);
            
        } catch (Exception $e) {
            error_log("Prestasi error: " . $e->getMessage());
            $this->view('mahasiswa/prestasi', [
                'title' => 'Prestasi & Sertifikat',
                'error' => $e->getMessage(),
                'current_page' => 'prestasi'
            ]);
        }
    }
    
    // Profile
    public function profile() {
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$mahasiswaData) {
                throw new Exception("Data mahasiswa tidak ditemukan");
            }
            
            // Handle profile update
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handleProfileUpdate();
                return;
            }
            
            // Get profile stats
            $profileStats = $this->getProfileStatsSimple($mahasiswaData['id']);
            
            $data = [
                'title' => 'Profil Saya',
                'mahasiswa_data' => $mahasiswaData,
                'profile_stats' => $profileStats,
                'current_page' => 'profile'
            ];
            
            $this->view('mahasiswa/profile', $data);
            
        } catch (Exception $e) {
            error_log("Profile error: " . $e->getMessage());
            $this->view('mahasiswa/profile', [
                'title' => 'Profil Saya',
                'error' => $e->getMessage(),
                'current_page' => 'profile'
            ]);
        }
    }
    
    // Simple Helper Methods (Fallback versions)
    private function getDashboardStats($mahasiswaId) {
        try {
            // Try using the new methods if they exist, otherwise use simple queries
            if (method_exists($this->eventModel, 'countMahasiswaEvents')) {
                return [
                    'total_events_registered' => $this->eventModel->countMahasiswaEvents($mahasiswaId),
                    'events_attended' => $this->eventModel->countAttendedEvents($mahasiswaId),
                    'upcoming_events' => $this->eventModel->countUpcomingEvents($mahasiswaId),
                    'certificates_earned' => method_exists($this->mahasiswaModel, 'countCertificates') 
                        ? $this->mahasiswaModel->countCertificates($mahasiswaId) : 0,
                    'organizations_followed' => method_exists($this->mahasiswaModel, 'countFollowedOrganizations')
                        ? $this->mahasiswaModel->countFollowedOrganizations($mahasiswaId) : 0
                ];
            } else {
                // Fallback to simple counts
                return [
                    'total_events_registered' => $this->countMahasiswaEventsSimple($mahasiswaId),
                    'events_attended' => $this->countAttendedEventsSimple($mahasiswaId),
                    'upcoming_events' => $this->countUpcomingEventsSimple($mahasiswaId),
                    'certificates_earned' => $this->countCertificatesSimple($mahasiswaId),
                    'organizations_followed' => $this->countFollowedOrganizationsSimple($mahasiswaId)
                ];
            }
        } catch (Exception $e) {
            error_log("Error getting dashboard stats: " . $e->getMessage());
            return [
                'total_events_registered' => 0,
                'events_attended' => 0,
                'upcoming_events' => 0,
                'certificates_earned' => 0,
                'organizations_followed' => 0
            ];
        }
    }
    
    // Simple count methods as fallbacks
    private function countMahasiswaEventsSimple($mahasiswaId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT COUNT(*) as count 
                      FROM event_participants ep
                      JOIN mahasiswa m ON ep.user_id = m.user_id
                      WHERE m.id = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)($row['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function countAttendedEventsSimple($mahasiswaId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT COUNT(*) as count 
                      FROM event_participants ep
                      JOIN mahasiswa m ON ep.user_id = m.user_id
                      WHERE m.id = ? AND ep.status = 'hadir'";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)($row['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function countUpcomingEventsSimple($mahasiswaId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT COUNT(*) as count 
                      FROM event_participants ep
                      JOIN events e ON ep.event_id = e.id
                      JOIN mahasiswa m ON ep.user_id = m.user_id
                      WHERE m.id = ? 
                      AND e.tanggal_mulai > NOW()
                      AND e.status = 'aktif'";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)($row['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function countCertificatesSimple($mahasiswaId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Check if sertifikat table exists
            $checkTable = "SHOW TABLES LIKE 'sertifikat'";
            $result = $conn->query($checkTable);
            
            if ($result->num_rows == 0) {
                return 0;
            }
            
            $query = "SELECT COUNT(*) as count FROM sertifikat WHERE mahasiswa_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)($row['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function countFollowedOrganizationsSimple($mahasiswaId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Check if mahasiswa_organisasi table exists
            $checkTable = "SHOW TABLES LIKE 'mahasiswa_organisasi'";
            $result = $conn->query($checkTable);
            
            if ($result->num_rows == 0) {
                return 0;
            }
            
            $query = "SELECT COUNT(*) as count FROM mahasiswa_organisasi WHERE mahasiswa_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)($row['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getRecentEventsForMahasiswa($mahasiswaId, $limit = 5) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT e.*, o.nama_organisasi, 
                             COUNT(ep.id) as registered_count,
                             (e.kapasitas - COUNT(ep.id)) as remaining_slots
                      FROM events e
                      JOIN organisasi o ON e.organisasi_id = o.id
                      LEFT JOIN event_participants ep ON e.id = ep.event_id
                      WHERE e.status = 'aktif' 
                      AND e.tanggal_mulai > NOW()
                      GROUP BY e.id
                      ORDER BY e.created_at DESC
                      LIMIT ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getMahasiswaEventsSimple($mahasiswaId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT e.*, o.nama_organisasi, ep.status as participation_status, 
                             ep.registered_at, ep.attended_at
                      FROM event_participants ep
                      JOIN events e ON ep.event_id = e.id
                      JOIN organisasi o ON e.organisasi_id = o.id
                      JOIN mahasiswa m ON ep.user_id = m.user_id
                      WHERE m.id = ?
                      ORDER BY e.tanggal_mulai DESC";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getEventRecommendationsSimple($mahasiswaData) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $minat = $mahasiswaData['minat'] ?? '';
            $mahasiswaId = $mahasiswaData['id'];
            
            $query = "SELECT e.*, o.nama_organisasi,
                             COUNT(ep.id) as registered_count,
                             (e.kapasitas - COUNT(ep.id)) as remaining_slots
                      FROM events e
                      JOIN organisasi o ON e.organisasi_id = o.id
                      LEFT JOIN event_participants ep ON e.id = ep.event_id
                      WHERE e.status = 'aktif' 
                      AND e.tanggal_mulai > NOW()
                      AND e.id NOT IN (
                          SELECT ep2.event_id FROM event_participants ep2 
                          JOIN mahasiswa m ON ep2.user_id = m.user_id 
                          WHERE m.id = ?
                      )
                      GROUP BY e.id
                      HAVING remaining_slots > 0
                      ORDER BY e.tanggal_mulai ASC
                      LIMIT 6";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $recommendations = [];
            while ($row = $result->fetch_assoc()) {
                $recommendations[] = $row;
            }
            
            return $recommendations;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getAvailableEventsSimple() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT e.*, o.nama_organisasi, 
                             COUNT(ep.id) as registered_count,
                             (e.kapasitas - COUNT(ep.id)) as remaining_slots
                      FROM events e
                      JOIN organisasi o ON e.organisasi_id = o.id
                      LEFT JOIN event_participants ep ON e.id = ep.event_id
                      WHERE e.status = 'aktif' 
                      AND e.tanggal_mulai > NOW()
                      GROUP BY e.id
                      HAVING remaining_slots > 0
                      ORDER BY e.tanggal_mulai ASC";
            
            $result = $conn->query($query);
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getMahasiswaEventHistorySimple($mahasiswaId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT e.*, o.nama_organisasi, ep.status as participation_status, 
                             ep.registered_at, ep.attended_at
                      FROM event_participants ep
                      JOIN events e ON ep.event_id = e.id
                      JOIN organisasi o ON e.organisasi_id = o.id
                      JOIN mahasiswa m ON ep.user_id = m.user_id
                      WHERE m.id = ? AND e.status = 'selesai'
                      ORDER BY e.tanggal_mulai DESC";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getEventCategoriesSimple() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT DISTINCT kategori FROM events WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori";
            $result = $conn->query($query);
            
            $categories = [];
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row['kategori'];
            }
            
            return $categories;
        } catch (Exception $e) {
            return ['seminar', 'workshop', 'kompetisi', 'webinar', 'pelatihan'];
        }
    }
    
    private function getJadwalKuliahSimple($mahasiswaId) {
        try {
            if (method_exists($this->mahasiswaModel, 'getJadwalKuliah')) {
                return $this->mahasiswaModel->getJadwalKuliah($mahasiswaId);
            }
            
            // Fallback: return empty schedule
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getMahasiswaEventScheduleSimple($mahasiswaId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT e.*, o.nama_organisasi, ep.status as participation_status
                      FROM event_participants ep
                      JOIN events e ON ep.event_id = e.id
                      JOIN organisasi o ON e.organisasi_id = o.id
                      JOIN mahasiswa m ON ep.user_id = m.user_id
                      WHERE m.id = ? 
                      AND e.status IN ('aktif', 'draft')
                      AND e.tanggal_mulai >= CURDATE()
                      ORDER BY e.tanggal_mulai ASC";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $mahasiswaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getAllOrganisasiSimple() {
        try {
            if (method_exists($this->mahasiswaModel, 'getAllOrganisasi')) {
                return $this->mahasiswaModel->getAllOrganisasi();
            }
            
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT o.*, u.username, u.email
                      FROM organisasi o 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.status_verifikasi = 'verified'
                      ORDER BY o.nama_organisasi";
            
            $result = $conn->query($query);
            
            $organisasi = [];
            while ($row = $result->fetch_assoc()) {
                $organisasi[] = $row;
            }
            
            return $organisasi;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getOrganisasiFakultasSimple($fakultas) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT o.*, u.username, u.email
                      FROM organisasi o 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.status_verifikasi = 'verified' 
                      AND (o.jenis_organisasi LIKE '%Fakultas%' OR o.nama_organisasi LIKE ?)
                      ORDER BY o.nama_organisasi";
            
            $searchTerm = "%$fakultas%";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $organisasi = [];
            while ($row = $result->fetch_assoc()) {
                $organisasi[] = $row;
            }
            
            return $organisasi;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getFollowedOrganisasiSimple($mahasiswaId) {
        try {
            if (method_exists($this->mahasiswaModel, 'getFollowedOrganisasi')) {
                return $this->mahasiswaModel->getFollowedOrganisasi($mahasiswaId);
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getOrganisasiRecommendationsSimple($mahasiswaData) {
        try {
            if (method_exists($this->mahasiswaModel, 'getOrganisasiRecommendations')) {
                return $this->mahasiswaModel->getOrganisasiRecommendations(
                    $mahasiswaData['fakultas'], 
                    $mahasiswaData['minat'], 
                    $mahasiswaData['id']
                );
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getPrestasiSimple($mahasiswaId) {
        try {
            if (method_exists($this->mahasiswaModel, 'getPrestasi')) {
                return $this->mahasiswaModel->getPrestasi($mahasiswaId);
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getSertifikatSimple($mahasiswaId) {
        try {
            if (method_exists($this->mahasiswaModel, 'getSertifikat')) {
                return $this->mahasiswaModel->getSertifikat($mahasiswaId);
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getParticipationHistorySimple($mahasiswaId) {
        try {
            return $this->getMahasiswaEventHistorySimple($mahasiswaId);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getProfileStatsSimple($mahasiswaId) {
        try {
            return [
                'profile_completion' => method_exists($this->mahasiswaModel, 'calculateProfileCompletion') 
                    ? $this->mahasiswaModel->calculateProfileCompletion($mahasiswaId) : 75,
                'last_activity' => method_exists($this->mahasiswaModel, 'getLastActivity')
                    ? $this->mahasiswaModel->getLastActivity($mahasiswaId) : date('Y-m-d H:i:s'),
                'account_created' => method_exists($this->mahasiswaModel, 'getAccountCreationDate')
                    ? $this->mahasiswaModel->getAccountCreationDate($mahasiswaId) : date('Y-m-d'),
                'total_points' => method_exists($this->mahasiswaModel, 'getTotalPoints')
                    ? $this->mahasiswaModel->getTotalPoints($mahasiswaId) : 0
            ];
        } catch (Exception $e) {
            return [
                'profile_completion' => 75,
                'last_activity' => date('Y-m-d H:i:s'),
                'account_created' => date('Y-m-d'),
                'total_points' => 0
            ];
        }
    }
    
    // Utility Methods
    private function organizeSchedule($jadwalKuliah, $jadwalEvents) {
        $schedule = [];
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        
        foreach ($days as $day) {
            $schedule[$day] = [
                'kuliah' => array_filter($jadwalKuliah, function($item) use ($day) {
                    return isset($item['hari']) && $item['hari'] === $day;
                }),
                'events' => array_filter($jadwalEvents, function($item) use ($day) {
                    return isset($item['tanggal_mulai']) && 
                           date('l', strtotime($item['tanggal_mulai'])) === $day;
                })
            ];
        }
        
        return $schedule;
    }
    
    private function detectScheduleConflicts($jadwalKuliah, $jadwalEvents) {
        $conflicts = [];
        
        foreach ($jadwalEvents as $event) {
            if (!isset($event['tanggal_mulai'])) continue;
            
            $eventDay = date('l', strtotime($event['tanggal_mulai']));
            $eventTime = date('H:i', strtotime($event['tanggal_mulai']));
            
            foreach ($jadwalKuliah as $kuliah) {
                if (isset($kuliah['hari']) && $kuliah['hari'] === $eventDay) {
                    if (isset($kuliah['jam_mulai']) && isset($kuliah['jam_selesai']) &&
                        $this->timeOverlaps($eventTime, $kuliah['jam_mulai'], $kuliah['jam_selesai'])) {
                        $conflicts[] = [
                            'event' => $event,
                            'kuliah' => $kuliah,
                            'type' => 'time_conflict'
                        ];
                    }
                }
            }
        }
        
        return $conflicts;
    }
    
    private function timeOverlaps($eventTime, $kuliahStart, $kuliahEnd) {
        return ($eventTime >= $kuliahStart && $eventTime <= $kuliahEnd);
    }
    
    private function calculateAchievementStats($prestasi, $sertifikat, $participationHistory) {
        return [
            'total_achievements' => count($prestasi),
            'total_certificates' => count($sertifikat),
            'total_participations' => count($participationHistory),
            'achievement_rate' => $this->calculateAchievementRate($prestasi, $participationHistory),
            'most_active_category' => $this->getMostActiveCategory($participationHistory)
        ];
    }
    
    private function calculateAchievementRate($prestasi, $participationHistory) {
        if (count($participationHistory) === 0) return 0;
        return round((count($prestasi) / count($participationHistory)) * 100, 1);
    }
    
    private function getMostActiveCategory($participationHistory) {
        $categories = [];
        foreach ($participationHistory as $participation) {
            $category = $participation['kategori'] ?? 'Lainnya';
            $categories[$category] = ($categories[$category] ?? 0) + 1;
        }
        return !empty($categories) ? array_keys($categories, max($categories))[0] : 'Belum ada';
    }
    
    // AJAX Handlers (simplified)
    private function handleEventRegistration() {
        header('Content-Type: application/json');
        
        try {
            $eventId = $_POST['event_id'] ?? null;
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$eventId || !$mahasiswaData) {
                echo json_encode(['success' => false, 'message' => 'Data tidak valid!']);
                return;
            }
            
            // Simple registration logic
            $db = new Database();
            $conn = $db->getConnection();
            
            // Check if already registered
            $checkQuery = "SELECT COUNT(*) as count FROM event_participants ep
                          JOIN mahasiswa m ON ep.user_id = m.user_id
                          WHERE ep.event_id = ? AND m.id = ?";
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param("ii", $eventId, $mahasiswaData['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Anda sudah terdaftar di event ini!']);
                return;
            }
            
            // Register for event
            $insertQuery = "INSERT INTO event_participants (event_id, user_id, status, registered_at) 
                           VALUES (?, ?, 'terdaftar', CURRENT_TIMESTAMP)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ii", $eventId, $mahasiswaData['user_id']);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Berhasil mendaftar event!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mendaftar event!']);
            }
            
        } catch (Exception $e) {
            error_log("Event registration error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem!']);
        }
    }
    
    private function handleCancelRegistration() {
        header('Content-Type: application/json');
        
        try {
            $eventId = $_POST['event_id'] ?? null;
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$eventId || !$mahasiswaData) {
                echo json_encode(['success' => false, 'message' => 'Data tidak valid!']);
                return;
            }
            
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "DELETE FROM event_participants WHERE event_id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $eventId, $mahasiswaData['user_id']);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Pendaftaran berhasil dibatalkan!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal membatalkan pendaftaran!']);
            }
            
        } catch (Exception $e) {
            error_log("Cancel registration error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem!']);
        }
    }
    
    private function handlePrestasiSubmission() {
        try {
            // Simplified prestasi submission
            $_SESSION['success_message'] = 'Fitur prestasi akan segera tersedia!';
            $this->redirect('mahasiswa/prestasi');
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Terjadi kesalahan: ' . $e->getMessage();
            $this->redirect('mahasiswa/prestasi');
        }
    }
    
    private function handleProfileUpdate() {
        try {
            // Simplified profile update
            $_SESSION['success_message'] = 'Fitur update profil akan segera tersedia!';
            $this->redirect('mahasiswa/profile');
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Terjadi kesalahan: ' . $e->getMessage();
            $this->redirect('mahasiswa/profile');
        }
    }

    // Aspirasi
    public function aspirasi() {
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$mahasiswaData) {
                throw new Exception("Data mahasiswa tidak ditemukan");
            }
            
            // Load aspirasi model
            $aspirasiModel = $this->model('Aspirasi');
            
            // Handle AJAX requests
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';
                
                switch ($action) {
                    case 'vote':
                        $this->handleVoteAspirai($aspirasiModel, $mahasiswaData);
                        return;
                    case 'create':
                        $this->handleCreateAspirai($aspirasiModel, $mahasiswaData);
                        return;
                }
            }
            
            // Get aspirasi list
            $aspirasi_list = $aspirasiModel->getAll();
            
            // Add voting status for each aspirasi
            foreach ($aspirasi_list as &$aspirasi) {
                $aspirasi['has_voted'] = $aspirasiModel->hasVoted($aspirasi['id'], $mahasiswaData['id']);
            }
            
            $data = [
                'title' => 'Aspirasi Event',
                'mahasiswa_data' => $mahasiswaData,
                'aspirasi_list' => $aspirasi_list,
                'current_page' => 'aspirasi'
            ];
            
            $this->view('mahasiswa/aspirasi', $data);
            
        } catch (Exception $e) {
            error_log("Aspirasi error: " . $e->getMessage());
            $this->view('mahasiswa/aspirasi', [
                'title' => 'Aspirasi Event',
                'error' => $e->getMessage(),
                'current_page' => 'aspirasi',
                'mahasiswa_data' => $mahasiswaData ?? ['nama_lengkap' => 'Mahasiswa'],
                'aspirasi_list' => []
            ]);
        }
    }
    
    // Create Aspirasi
    public function createAspirai() {
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$mahasiswaData) {
                throw new Exception("Data mahasiswa tidak ditemukan");
            }
            
            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $aspirasiModel = $this->model('Aspirasi');
                $this->handleCreateAspirai($aspirasiModel, $mahasiswaData);
                return;
            }
            
            $data = [
                'title' => 'Buat Aspirasi Event',
                'mahasiswa_data' => $mahasiswaData,
                'current_page' => 'aspirasi'
            ];
            
            $this->view('mahasiswa/create_aspirasi', $data);
            
        } catch (Exception $e) {
            error_log("Create aspirasi error: " . $e->getMessage());
            $this->redirect('mahasiswa/aspirasi');
        }
    }
    
    // Handle Vote Aspirasi (AJAX)
    private function handleVoteAspirai($aspirasiModel, $mahasiswaData) {
        header('Content-Type: application/json');
        
        try {
            $aspirasiId = $_POST['aspirasi_id'] ?? null;
            
            if (!$aspirasiId) {
                echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
                return;
            }
            
            $result = $aspirasiModel->vote($aspirasiId, $mahasiswaData['id']);
            
            if ($result['success']) {
                // Get updated vote count
                $aspirasi = $aspirasiModel->getById($aspirasiId);
                echo json_encode([
                    'success' => true, 
                    'action' => $result['action'],
                    'vote_count' => $aspirasi['vote_count'],
                    'message' => $result['action'] === 'voted' ? 'Vote berhasil!' : 'Vote dibatalkan!'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Gagal memproses vote']);
            }
            
        } catch (Exception $e) {
            error_log("Vote error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }
    
    // Handle Create Aspirasi
    private function handleCreateAspirai($aspirasiModel, $mahasiswaData) {
        try {
            // Validate input
            $required = ['judul', 'kategori_event', 'deskripsi', 'urgency'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field harus diisi!");
                }
            }
            
            $data = [
                'mahasiswa_id' => $mahasiswaData['id'],
                'judul' => trim($_POST['judul']),
                'kategori_event' => $_POST['kategori_event'],
                'deskripsi' => trim($_POST['deskripsi']),
                'sasaran_peserta' => trim($_POST['sasaran_peserta'] ?? ''),
                'estimasi_waktu' => trim($_POST['estimasi_waktu'] ?? ''),
                'lokasi_prefer' => trim($_POST['lokasi_prefer'] ?? ''),
                'urgency' => $_POST['urgency']
            ];
            
            // Additional validation
            if (strlen($data['judul']) < 10) {
                throw new Exception("Judul minimal 10 karakter!");
            }
            
            if (strlen($data['deskripsi']) < 20) {
                throw new Exception("Deskripsi minimal 20 karakter!");
            }
            
            $aspirasiId = $aspirasiModel->create($data);
            
            if ($aspirasiId) {
                $_SESSION['success_message'] = 'Aspirasi berhasil dibuat!';
                $this->redirect('mahasiswa/aspirasi');
            } else {
                throw new Exception("Gagal menyimpan aspirasi!");
            }
            
        } catch (Exception $e) {
            error_log("Create aspirasi error: " . $e->getMessage());
            $_SESSION['error_message'] = $e->getMessage();
            $this->redirect('mahasiswa/createAspirai');
        }
    }
    public function landing() {
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$mahasiswaData) {
                throw new Exception("Data mahasiswa tidak ditemukan");
            }
            
            // Handle event registration
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';
                if ($action === 'register') {
                    $this->handleEventRegistrationLanding();
                    return;
                }
            }
            
            // Get events data
            $upcomingEvents = $this->getUpcomingEventsLanding();
            $ongoingEvents = $this->getOngoingEventsLanding();
            $recommendedEvents = $this->getRecommendedEventsLanding($mahasiswaData);
            
            $data = [
                'title' => 'UACAD - Campus Events',
                'mahasiswa_data' => $mahasiswaData,
                'upcoming_events' => $upcomingEvents,
                'ongoing_events' => $ongoingEvents,
                'recommended_events' => $recommendedEvents,
                'current_page' => 'landing'
            ];
            
            $this->view('mahasiswa/landing', $data);
            
        } catch (Exception $e) {
            error_log("Landing error: " . $e->getMessage());
            $this->view('mahasiswa/landing', [
                'title' => 'UACAD - Campus Events',
                'error' => $e->getMessage(),
                'mahasiswa_data' => ['nama_lengkap' => $_SESSION['username'] ?? 'Mahasiswa'],
                'upcoming_events' => [],
                'ongoing_events' => [],
                'recommended_events' => []
            ]);
        }
    }
    
    // Get upcoming events for landing
    private function getUpcomingEventsLanding() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT e.*, o.nama_organisasi, 
                             COUNT(ep.id) as registered_count,
                             (e.kapasitas - COUNT(ep.id)) as remaining_slots
                      FROM events e
                      JOIN organisasi o ON e.organisasi_id = o.id
                      LEFT JOIN event_participants ep ON e.id = ep.event_id
                      WHERE e.status = 'aktif' 
                      AND e.tanggal_mulai > NOW()
                      AND DATE(e.tanggal_mulai) > CURDATE()
                      GROUP BY e.id
                      HAVING remaining_slots > 0
                      ORDER BY e.tanggal_mulai ASC
                      LIMIT 8";
            
            $result = $conn->query($query);
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
            
        } catch (Exception $e) {
            error_log("Error getting upcoming events: " . $e->getMessage());
            return [];
        }
    }
    
    // Get ongoing events for landing
    private function getOngoingEventsLanding() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT e.*, o.nama_organisasi,
                             COUNT(ep.id) as registered_count,
                             (e.kapasitas - COUNT(ep.id)) as remaining_slots
                      FROM events e
                      JOIN organisasi o ON e.organisasi_id = o.id
                      LEFT JOIN event_participants ep ON e.id = ep.event_id
                      WHERE e.status = 'aktif' 
                      AND DATE(e.tanggal_mulai) = CURDATE()
                      GROUP BY e.id
                      HAVING remaining_slots > 0
                      ORDER BY e.tanggal_mulai ASC
                      LIMIT 4";
            
            $result = $conn->query($query);
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
            
        } catch (Exception $e) {
            error_log("Error getting ongoing events: " . $e->getMessage());
            return [];
        }
    }
    
    // Get recommended events for landing
    private function getRecommendedEventsLanding($mahasiswaData) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $fakultas = $mahasiswaData['fakultas'] ?? '';
            $minat = $mahasiswaData['minat'] ?? '';
            $mahasiswaId = $mahasiswaData['id'];
            
            $query = "SELECT e.*, o.nama_organisasi,
                             COUNT(ep.id) as registered_count,
                             (e.kapasitas - COUNT(ep.id)) as remaining_slots
                      FROM events e
                      JOIN organisasi o ON e.organisasi_id = o.id
                      LEFT JOIN event_participants ep ON e.id = ep.event_id
                      WHERE e.status = 'aktif' 
                      AND e.tanggal_mulai > NOW()
                      AND e.id NOT IN (
                          SELECT ep2.event_id FROM event_participants ep2 
                          JOIN mahasiswa m ON ep2.user_id = m.user_id 
                          WHERE m.id = ?
                      )
                      AND (e.nama_event LIKE ? OR e.deskripsi LIKE ? OR e.kategori LIKE ?)
                      GROUP BY e.id
                      HAVING remaining_slots > 0
                      ORDER BY e.tanggal_mulai ASC
                      LIMIT 8";
            
            $searchTerm = "%$minat%";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isss", $mahasiswaId, $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
            
        } catch (Exception $e) {
            error_log("Error getting recommended events: " . $e->getMessage());
            return [];
        }
    }
    
    // Handle event registration from landing page
    private function handleEventRegistrationLanding() {
        header('Content-Type: application/json');
        
        try {
            $eventId = $_POST['event_id'] ?? null;
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$eventId || !$mahasiswaData) {
                echo json_encode(['success' => false, 'message' => 'Data tidak valid!']);
                return;
            }
            
            $db = new Database();
            $conn = $db->getConnection();
            
            // Check if already registered
            $checkQuery = "SELECT COUNT(*) as count FROM event_participants ep
                          JOIN mahasiswa m ON ep.user_id = m.user_id
                          WHERE ep.event_id = ? AND m.id = ?";
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param("ii", $eventId, $mahasiswaData['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Anda sudah terdaftar di event ini!']);
                return;
            }
            
            // Check if event is full
            $capacityQuery = "SELECT e.kapasitas, COUNT(ep.id) as registered 
                             FROM events e
                             LEFT JOIN event_participants ep ON e.id = ep.event_id
                             WHERE e.id = ?
                             GROUP BY e.id";
            $stmt = $conn->prepare($capacityQuery);
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $result = $stmt->get_result();
            $capacity = $result->fetch_assoc();
            
            if ($capacity && $capacity['registered'] >= $capacity['kapasitas']) {
                echo json_encode(['success' => false, 'message' => 'Event sudah penuh!']);
                return;
            }
            
            // Register for event
            $insertQuery = "INSERT INTO event_participants (event_id, user_id, status, registered_at) 
                           VALUES (?, ?, 'terdaftar', CURRENT_TIMESTAMP)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ii", $eventId, $mahasiswaData['user_id']);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Berhasil mendaftar event!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mendaftar event!']);
            }
            
        } catch (Exception $e) {
            error_log("Event registration error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem!']);
        }
    }
    
}