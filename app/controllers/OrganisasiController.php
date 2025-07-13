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
            
            $data = [
                'title' => 'Dashboard Organisasi',
                'org_data' => $orgData,
                'stats' => $this->eventModel->getStatsByOrganisasiId($orgData['id']),
                'trend_data' => $this->eventModel->getTrendData($orgData['id']),
                'category_data' => $this->eventModel->getCategoryData($orgData['id']),
                'active_events' => $this->eventModel->getActiveEvents($orgData['id'])
            ];
            
            // Handle AJAX create event
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama_event'])) {
                $this->handleCreateEvent($orgData);
                return;
            }
            
            $this->view('organisasi/dashboard', $data);
            
        } catch (Exception $e) {
            error_log("Dashboard error: " . $e->getMessage());
            $this->view('organisasi/dashboard', [
                'title' => 'Dashboard Organisasi',
                'error' => $e->getMessage(),
                'org_data' => ['nama_organisasi' => 'Organisasi', 'username' => 'Admin', 'jenis_organisasi' => 'Organisasi'],
                'stats' => ['total_events' => 0, 'total_capacity' => 0, 'active_events' => 0],
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
            
            // Simple event data
            $eventData = [
                'organisasi_id' => $orgData['id'],
                'nama_event' => $_POST['nama_event'],
                'deskripsi' => $_POST['deskripsi'] ?? '',
                'kategori' => $_POST['kategori'],
                'tanggal_mulai' => $_POST['tanggal_mulai'],
                'tanggal_selesai' => $_POST['tanggal_selesai'] ?? null,
                'lokasi' => $_POST['lokasi'],
                'kapasitas' => intval($_POST['kapasitas']),
                'poster' => $posterName,
                'status' => $_POST['status'] ?? 'draft'
            ];
            
            // Simple validation
            if (empty($eventData['nama_event']) || empty($eventData['kategori']) || empty($eventData['lokasi'])) {
                echo json_encode(['success' => false, 'message' => 'Data tidak lengkap!']);
                return;
            }
            
            $eventId = $this->eventModel->create($eventData);
            
            if ($eventId) {
                echo json_encode(['success' => true, 'message' => 'Event berhasil dibuat!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan event!']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}