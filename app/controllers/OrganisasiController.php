
<?php
// ================================================================
// FILE: controllers/OrganisasiController.php
// ================================================================

class OrganisasiController extends Controller {
    private $organisasiModel;
    private $eventModel;
    
    public function __construct() {
        session_start();
        
        // Check authentication
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisasi') {
            $this->redirect('auth/login');
        }
        
        $this->organisasiModel = $this->model('Organisasi');
        $this->eventModel = $this->model('Event');
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
            
            $this->view('organisasi/dashboard', $data);
            
        } catch (Exception $e) {
            error_log("Dashboard error: " . $e->getMessage());
            $this->view('organisasi/dashboard', [
                'title' => 'Dashboard Organisasi',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function createEvent() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->handleCreateEventPost();
            return;
        }
        
        // Return form for modal
        $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);
        $this->view('organisasi/create-event-modal', ['org_data' => $orgData]);
    }
    
    private function handleCreateEventPost() {
        header('Content-Type: application/json');
        
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);
            
            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }
            
            $eventData = [
                'organisasi_id' => $orgData['id'],
                'nama_event' => trim($_POST['nama_event'] ?? ''),
                'deskripsi' => trim($_POST['deskripsi'] ?? ''),
                'kategori' => trim($_POST['kategori'] ?? ''),
                'tanggal_mulai' => $_POST['tanggal_mulai'] ?? '',
                'tanggal_selesai' => $_POST['tanggal_selesai'] ?? '',
                'lokasi' => trim($_POST['lokasi'] ?? ''),
                'kapasitas' => intval($_POST['kapasitas'] ?? 0),
                'status' => $_POST['status'] ?? 'draft'
            ];
            
            $errors = $this->validateEventData($eventData);
            
            if (!empty($errors)) {
                echo json_encode([
                    'success' => false,
                    'message' => implode(", ", $errors)
                ]);
                return;
            }
            
            $eventId = $this->eventModel->create($eventData);
            
            if ($eventId) {
                echo json_encode([
                    'success' => true,
                    'message' => "Event '{$eventData['nama_event']}' berhasil dibuat!",
                    'event_id' => $eventId
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal menyimpan event. Silakan coba lagi.'
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getDashboardStats() {
        header('Content-Type: application/json');
        
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);
            
            echo json_encode([
                'success' => true,
                'stats' => $this->eventModel->getStatsByOrganisasiId($orgData['id']),
                'trend_data' => $this->eventModel->getTrendData($orgData['id']),
                'category_data' => $this->eventModel->getCategoryData($orgData['id'])
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    private function validateEventData($data) {
        $errors = [];
        
        if (empty($data['nama_event'])) {
            $errors[] = "Nama event harus diisi";
        }
        
        if (empty($data['kategori'])) {
            $errors[] = "Kategori event harus dipilih";
        }
        
        if (empty($data['tanggal_mulai'])) {
            $errors[] = "Tanggal mulai harus diisi";
        }
        
        if (empty($data['lokasi'])) {
            $errors[] = "Lokasi event harus diisi";
        }
        
        if ($data['kapasitas'] <= 0) {
            $errors[] = "Kapasitas harus lebih dari 0";
        }
        
        if (!empty($data['tanggal_mulai']) && !empty($data['tanggal_selesai'])) {
            if (strtotime($data['tanggal_selesai']) < strtotime($data['tanggal_mulai'])) {
                $errors[] = "Tanggal selesai harus setelah tanggal mulai";
            }
        }
        
        return $errors;
    }
}