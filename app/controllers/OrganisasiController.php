<?php
class OrganisasiController extends Controller {
    private $organisasiModel;
    private $eventModel;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisasi') {
            $this->redirect('auth/login');
        }
        
        $this->organisasiModel = $this->model('Organisasi');
        $this->eventModel = $this->model('Event');
    }
    
    public function index() {
        $this->dashboard();
    }
    
    public function dashboard() {
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);
            
            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }
            
            // Get comprehensive event data
            $all_events = $this->eventModel->getEventsByOrganisasiId($orgData['id']);
            
            // Calculate stats with proper error handling
            $rawStats = $this->eventModel->getStatsByOrganisasiId($orgData['id']);
            
            // Ensure all required keys exist with default values
            $stats = [
                'total_events' => $rawStats['total_events'] ?? 0,
                'total_capacity' => $rawStats['total_capacity'] ?? 0,
                'active_events' => $rawStats['active_events'] ?? 0,
                'draft' => 0,
                'aktif' => 0,
                'selesai' => 0,
                'dibatalkan' => 0
            ];
            
            // Calculate detailed status counts
            if (!empty($all_events)) {
                foreach ($all_events as $event) {
                    switch ($event['status']) {
                        case 'draft':
                            $stats['draft']++;
                            break;
                        case 'aktif':
                            $stats['aktif']++;
                            break;
                        case 'selesai':
                            $stats['selesai']++;
                            break;
                        case 'dibatalkan':
                            $stats['dibatalkan']++;
                            break;
                    }
                }
            }
            
            // Get other data with error handling
            $trend_data = $this->eventModel->getTrendData($orgData['id']) ?? [];
            $category_data = $this->eventModel->getCategoryData($orgData['id']) ?? [];
            $active_events = $this->eventModel->getActiveEvents($orgData['id']) ?? [];
            
            $data = [
                'title' => 'Dashboard Organisasi',
                'org_data' => $orgData,
                'stats' => $stats,
                'trend_data' => $trend_data,
                'category_data' => $category_data,
                'active_events' => $active_events,
                'all_events' => $all_events, // Include all events for comprehensive data
                'user_session' => [
                    'user_id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'role' => $_SESSION['role']
                ]
            ];
            
            // Handle AJAX create event
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama_event'])) {
                $this->handleCreateEvent($orgData);
                return;
            }
            
            $this->view('organisasi/dashboard', $data);
            
        } catch (Exception $e) {
            error_log("Dashboard error: " . $e->getMessage());
            
            // Provide safe fallback data
            $this->view('organisasi/dashboard', [
                'title' => 'Dashboard Organisasi',
                'error' => $e->getMessage(),
                'org_data' => [
                    'nama_organisasi' => 'Organisasi', 
                    'username' => $_SESSION['username'] ?? 'Admin', 
                    'jenis_organisasi' => 'Organisasi'
                ],
                'stats' => [
                    'total_events' => 0, 
                    'total_capacity' => 0, 
                    'active_events' => 0,
                    'draft' => 0,
                    'aktif' => 0,
                    'selesai' => 0,
                    'dibatalkan' => 0
                ],
                'trend_data' => [],
                'category_data' => [],
                'active_events' => [],
                'all_events' => []
            ]);
        }
    }
    
    public function events() {
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);
            
            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }
            
            // Handle AJAX requests
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';
                
                switch ($action) {
                    case 'get_events':
                        $this->handleGetEvents($orgData);
                        return;
                    case 'delete_event':
                        $this->handleDeleteEvent();
                        return;
                    case 'publish_event':
                        $this->handlePublishEvent();
                        return;
                }
            }
            
            // Get all events data
            $all_events = $this->eventModel->getEventsByOrganisasiId($orgData['id']);
            $stats = $this->calculateEventStats($all_events);
            
            $data = [
                'title' => 'Kelola Event',
                'org_data' => $orgData,
                'all_events' => $all_events,
                'stats' => $stats
            ];
            
            $this->view('organisasi/events', $data);
            
        } catch (Exception $e) {
            error_log("Events error: " . $e->getMessage());
            $this->view('organisasi/events', [
                'title' => 'Kelola Event',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function analytics() {
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);
            
            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }
            
            // Get comprehensive analytics data
            $all_events = $this->eventModel->getEventsByOrganisasiId($orgData['id']);
            $stats = $this->calculateEventStats($all_events);
            $trend_data = $this->eventModel->getTrendData($orgData['id']);
            $category_data = $this->eventModel->getCategoryData($orgData['id']);
            
            $data = [
                'title' => 'Analitik Event',
                'org_data' => $orgData,
                'all_events' => $all_events,
                'stats' => $stats,
                'trend_data' => $trend_data,
                'category_data' => $category_data
            ];
            
            $this->view('organisasi/analytics', $data);
            
        } catch (Exception $e) {
            error_log("Analytics error: " . $e->getMessage());
            $this->view('organisasi/analytics', [
                'title' => 'Analitik Event',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function participants() {
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);
            
            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }
            
            $eventId = $_GET['event'] ?? null;
            $data = [
                'title' => 'Kelola Peserta',
                'org_data' => $orgData
            ];
            
            if ($eventId) {
                $event = $this->eventModel->getById($eventId);
                $participants = $this->eventModel->getParticipants($eventId);
                
                $data['event'] = $event;
                $data['participants'] = $participants;
            }
            
            $this->view('organisasi/participants', $data);
            
        } catch (Exception $e) {
            error_log("Participants error: " . $e->getMessage());
            $this->view('organisasi/participants', [
                'title' => 'Kelola Peserta',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function reports() {
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);
            
            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }
            
            $eventId = $_GET['event'] ?? null;
            $data = [
                'title' => 'Laporan Event',
                'org_data' => $orgData
            ];
            
            if ($eventId) {
                $event = $this->eventModel->getById($eventId);
                $report_data = $this->eventModel->getEventReport($eventId);
                
                $data['event'] = $event;
                $data['report_data'] = $report_data;
            } else {
                $all_reports = $this->eventModel->getAllReports($orgData['id']);
                $data['all_reports'] = $all_reports;
            }
            
            $this->view('organisasi/reports', $data);
            
        } catch (Exception $e) {
            error_log("Reports error: " . $e->getMessage());
            $this->view('organisasi/reports', [
                'title' => 'Laporan Event',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function profile() {
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);
            
            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }
            
            // Get stats for profile display
            $all_events = $this->eventModel->getEventsByOrganisasiId($orgData['id']);
            $stats = $this->calculateEventStats($all_events);
            
            $data = [
                'title' => 'Profil Organisasi',
                'org_data' => $orgData,
                'all_events' => $all_events,
                'stats' => $stats
            ];
            
            // Handle profile update
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handleUpdateProfile($orgData);
                return;
            }
            
            $this->view('organisasi/profile', $data);
            
        } catch (Exception $e) {
            error_log("Profile error: " . $e->getMessage());
            $this->view('organisasi/profile', [
                'title' => 'Profil Organisasi',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function handleCreateEvent($orgData) {
        header('Content-Type: application/json');
        
        try {
            // Handle poster upload - SIMPLE VERSION
            $posterName = null;
            if (isset($_FILES['poster_event']) && $_FILES['poster_event']['error'] == 0) {
                $poster = $_FILES['poster_event'];
                
                // Simple validation
                $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
                $fileExt = strtolower(pathinfo($poster['name'], PATHINFO_EXTENSION));
                
                if (in_array($fileExt, $allowedTypes) && $poster['size'] <= 5000000) { // 5MB
                    // Create folder if not exists
                    $uploadDir = '../public/uploads/posters/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Simple filename
                    $posterName = time() . '_' . $poster['name'];
                    move_uploaded_file($poster['tmp_name'], $uploadDir . $posterName);
                }
            }
            
            // Simple event data with validation
            $eventData = [
                'organisasi_id' => $orgData['id'],
                'nama_event' => trim($_POST['nama_event'] ?? ''),
                'deskripsi' => trim($_POST['deskripsi'] ?? ''),
                'kategori' => trim($_POST['kategori'] ?? ''),
                'tanggal_mulai' => $_POST['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $_POST['tanggal_selesai'] ?? null,
                'lokasi' => trim($_POST['lokasi'] ?? ''),
                'kapasitas' => intval($_POST['kapasitas'] ?? 0),
                'poster' => $posterName,
                'status' => $_POST['status'] ?? 'draft'
            ];
            
            // Simple validation
            if (empty($eventData['nama_event']) || empty($eventData['kategori']) || empty($eventData['lokasi'])) {
                echo json_encode(['success' => false, 'message' => 'Data tidak lengkap!']);
                return;
            }
            
            if ($eventData['kapasitas'] <= 0) {
                echo json_encode(['success' => false, 'message' => 'Kapasitas harus lebih dari 0!']);
                return;
            }
            
            if (empty($eventData['tanggal_mulai'])) {
                echo json_encode(['success' => false, 'message' => 'Tanggal mulai harus diisi!']);
                return;
            }
            
            $eventId = $this->eventModel->create($eventData);
            
            if ($eventId) {
                echo json_encode(['success' => true, 'message' => 'Event berhasil dibuat!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan event!']);
            }
            
        } catch (Exception $e) {
            error_log("Create event error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    private function handleGetEvents($orgData) {
        header('Content-Type: application/json');
        
        try {
            $all_events = $this->eventModel->getEventsByOrganisasiId($orgData['id']);
            $stats = $this->calculateEventStats($all_events);
            
            echo json_encode([
                'success' => true,
                'events' => $all_events,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    private function handleDeleteEvent() {
        header('Content-Type: application/json');
        
        try {
            $eventId = $_POST['event_id'] ?? null;
            
            if (!$eventId) {
                echo json_encode(['success' => false, 'message' => 'Event ID tidak valid!']);
                return;
            }
            
            if ($this->eventModel->delete($eventId)) {
                echo json_encode(['success' => true, 'message' => 'Event berhasil dihapus!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus event!']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    private function handlePublishEvent() {
        header('Content-Type: application/json');
        
        try {
            $eventId = $_POST['event_id'] ?? null;
            
            if (!$eventId) {
                echo json_encode(['success' => false, 'message' => 'Event ID tidak valid!']);
                return;
            }
            
            $updateData = ['status' => 'aktif'];
            
            if ($this->eventModel->update($eventId, $updateData)) {
                echo json_encode(['success' => true, 'message' => 'Event berhasil dipublikasi!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mempublikasi event!']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    private function calculateEventStats($events) {
        $stats = [
            'total' => count($events),
            'draft' => 0,
            'aktif' => 0,
            'selesai' => 0,
            'dibatalkan' => 0,
            'total_capacity' => 0,
            'total_events' => count($events),
            'active_events' => 0
        ];
        
        foreach ($events as $event) {
            $stats[$event['status']]++;
            $stats['total_capacity'] += intval($event['kapasitas'] ?? 0);
            
            if ($event['status'] === 'aktif') {
                $stats['active_events']++;
            }
        }
        
        return $stats;
    }
    
    private function handleUpdateProfile($orgData) {
        // Handle profile update logic here
        // This is a placeholder for profile update functionality
    }
}