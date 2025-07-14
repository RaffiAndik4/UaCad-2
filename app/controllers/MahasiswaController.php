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
        $this->dashboard();
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
    public function jadwal() {
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$mahasiswaData) {
                throw new Exception("Data mahasiswa tidak ditemukan");
            }
            
            // Get mahasiswa's schedule with fallback
            $jadwalKuliah = $this->getJadwalKuliahSimple($mahasiswaData['id']);
            
            // Get registered events schedule
            $jadwalEvents = $this->getMahasiswaEventScheduleSimple($mahasiswaData['id']);
            
            // Combine and organize schedule
            $combinedSchedule = $this->organizeSchedule($jadwalKuliah, $jadwalEvents);
            
            // Get conflicts
            $conflicts = $this->detectScheduleConflicts($jadwalKuliah, $jadwalEvents);
            
            $data = [
                'title' => 'Jadwal Saya',
                'mahasiswa_data' => $mahasiswaData,
                'jadwal_kuliah' => $jadwalKuliah,
                'jadwal_events' => $jadwalEvents,
                'combined_schedule' => $combinedSchedule,
                'conflicts' => $conflicts,
                'current_page' => 'jadwal'
            ];
            
            $this->view('mahasiswa/jadwal', $data);
            
        } catch (Exception $e) {
            error_log("Jadwal error: " . $e->getMessage());
            $this->view('mahasiswa/jadwal', [
                'title' => 'Jadwal Saya',
                'error' => $e->getMessage(),
                'current_page' => 'jadwal'
            ]);
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
}