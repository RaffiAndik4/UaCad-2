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
            
            // Get stats with proper error handling and default values
            $rawStats = $this->eventModel->getStatsByOrganisasiId($orgData['id']);
            
            // Ensure all required keys exist with default values
            $stats = [
                'total_events' => $rawStats['total_events'] ?? 0,
                'total_capacity' => $rawStats['total_capacity'] ?? 0,
                'active_events' => $rawStats['active_events'] ?? 0
            ];
            
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
                'active_events' => $active_events
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
                    'active_events' => 0
                ],
                'trend_data' => [],
                'category_data' => [],
                'active_events' => []
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
}