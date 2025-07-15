<?php
// app/controllers/AspirasiController.php

class AspirasiController extends Controller {
    private $aspirasiModel;
    private $mahasiswaModel;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
        
        $this->aspirasiModel = $this->model('Aspirasi');
        $this->mahasiswaModel = $this->model('Mahasiswa');
    }
    
    public function index() {
        try {
            // Get current user role
            $userRole = $_SESSION['role'];
            
            // Handle AJAX requests
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';
                
                switch ($action) {
                    case 'vote':
                        $this->handleVote();
                        return;
                    case 'comment':
                        $this->handleComment();
                        return;
                    case 'update_status':
                        $this->handleUpdateStatus();
                        return;
                }
            }
            
            // Get filters from request
            $filters = [
                'kategori' => $_GET['kategori'] ?? '',
                'status' => $_GET['status'] ?? '',
                'urgency' => $_GET['urgency'] ?? '',
                'fakultas' => $_GET['fakultas'] ?? '',
                'search' => $_GET['search'] ?? ''
            ];
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            // Get aspirasi list
            $aspirasi_list = $this->aspirasiModel->getAll($filters, $limit, $offset);
            
            // Get statistics
            $statistics = $this->aspirasiModel->getStatistics();
            
            // Get trending aspirasi
            $trending = $this->aspirasiModel->getTrending(5);
            
            $data = [
                'title' => 'Aspirasi Event',
                'current_page' => 'aspirasi',
                'user_role' => $userRole,
                'aspirasi_list' => $aspirasi_list,
                'statistics' => $statistics,
                'trending' => $trending,
                'filters' => $filters,
                'current_page_num' => $page
            ];
            
            // Add user-specific data
            if ($userRole === 'mahasiswa') {
                $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
                $data['mahasiswa_data'] = $mahasiswaData;
                
                // Add voting status for each aspirasi
                foreach ($data['aspirasi_list'] as &$aspirasi) {
                    $aspirasi['has_voted'] = $this->aspirasiModel->hasVoted($aspirasi['id'], $mahasiswaData['id']);
                }
            } elseif ($userRole === 'organisasi') {
                $organisasiModel = $this->model('Organisasi');
                $orgData = $organisasiModel->getByUserId($_SESSION['user_id']);
                $data['org_data'] = $orgData;
            }
            
            $this->view('aspirasi/index', $data);
            
        } catch (Exception $e) {
            error_log("Aspirasi index error: " . $e->getMessage());
            $this->view('aspirasi/index', [
                'title' => 'Aspirasi Event',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'current_page' => 'aspirasi'
            ]);
        }
    }
    
    public function create() {
        // Only mahasiswa can create aspirasi
        if ($_SESSION['role'] !== 'mahasiswa') {
            $this->redirect('aspirasi');
        }
        
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$mahasiswaData) {
                throw new Exception("Data mahasiswa tidak ditemukan");
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handleCreateAspirai($mahasiswaData);
                return;
            }
            
            $data = [
                'title' => 'Buat Aspirasi Event',
                'current_page' => 'aspirasi',
                'mahasiswa_data' => $mahasiswaData
            ];
            
            $this->view('aspirasi/create', $data);
            
        } catch (Exception $e) {
            error_log("Create aspirasi error: " . $e->getMessage());
            $this->redirect('aspirasi');
        }
    }
    
    public function detail($id) {
        try {
            $aspirasi = $this->aspirasiModel->getById($id);
            
            if (!$aspirasi) {
                throw new Exception("Aspirasi tidak ditemukan");
            }
            
            // Get comments
            $comments = $this->aspirasiModel->getComments($id);
            
            // Check if user has voted (for mahasiswa)
            $hasVoted = false;
            if ($_SESSION['role'] === 'mahasiswa') {
                $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
                if ($mahasiswaData) {
                    $hasVoted = $this->aspirasiModel->hasVoted($id, $mahasiswaData['id']);
                }
            }
            
            $data = [
                'title' => 'Detail Aspirasi',
                'current_page' => 'aspirasi',
                'aspirasi' => $aspirasi,
                'comments' => $comments,
                'has_voted' => $hasVoted,
                'user_role' => $_SESSION['role']
            ];
            
            // Add user-specific data
            if ($_SESSION['role'] === 'mahasiswa') {
                $data['mahasiswa_data'] = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            } elseif ($_SESSION['role'] === 'organisasi') {
                $organisasiModel = $this->model('Organisasi');
                $data['org_data'] = $organisasiModel->getByUserId($_SESSION['user_id']);
            }
            
            $this->view('aspirasi/detail', $data);
            
        } catch (Exception $e) {
            error_log("Aspirasi detail error: " . $e->getMessage());
            $this->redirect('aspirasi');
        }
    }
    
    public function edit($id) {
        // Only mahasiswa can edit their own aspirasi
        if ($_SESSION['role'] !== 'mahasiswa') {
            $this->redirect('aspirasi');
        }
        
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            $aspirasi = $this->aspirasiModel->getById($id);
            
            if (!$aspirasi || $aspirasi['mahasiswa_id'] != $mahasiswaData['id']) {
                throw new Exception("Aspirasi tidak ditemukan atau bukan milik Anda");
            }
            
            // Only allow editing if status is pending
            if ($aspirasi['status'] !== 'pending') {
                throw new Exception("Aspirasi tidak dapat diedit karena sudah diproses");
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handleUpdateAspirai($id);
                return;
            }
            
            $data = [
                'title' => 'Edit Aspirasi',
                'current_page' => 'aspirasi',
                'aspirasi' => $aspirasi,
                'mahasiswa_data' => $mahasiswaData
            ];
            
            $this->view('aspirasi/edit', $data);
            
        } catch (Exception $e) {
            error_log("Edit aspirasi error: " . $e->getMessage());
            $_SESSION['error_message'] = $e->getMessage();
            $this->redirect('aspirasi');
        }
    }
    
    public function delete($id) {
        if ($_SESSION['role'] !== 'mahasiswa') {
            $this->json(['success' => false, 'message' => 'Tidak memiliki akses'], 403);
        }
        
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if ($this->aspirasiModel->delete($id, $mahasiswaData['id'])) {
                $_SESSION['success_message'] = 'Aspirasi berhasil dihapus';
                $this->json(['success' => true, 'message' => 'Aspirasi berhasil dihapus']);
            } else {
                $this->json(['success' => false, 'message' => 'Gagal menghapus aspirasi']);
            }
            
        } catch (Exception $e) {
            error_log("Delete aspirasi error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }
    
    public function myAspirai() {
        if ($_SESSION['role'] !== 'mahasiswa') {
            $this->redirect('aspirasi');
        }
        
        try {
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            $myAspirai = $this->aspirasiModel->getByMahasiswa($mahasiswaData['id']);
            
            $data = [
                'title' => 'Aspirasi Saya',
                'current_page' => 'aspirasi',
                'mahasiswa_data' => $mahasiswaData,
                'aspirasi_list' => $myAspirai
            ];
            
            $this->view('aspirasi/my_aspirasi', $data);
            
        } catch (Exception $e) {
            error_log("My aspirasi error: " . $e->getMessage());
            $this->redirect('aspirasi');
        }
    }
    
    // AJAX Handlers
    private function handleVote() {
        header('Content-Type: application/json');
        
        if ($_SESSION['role'] !== 'mahasiswa') {
            echo json_encode(['success' => false, 'message' => 'Hanya mahasiswa yang dapat vote']);
            return;
        }
        
        try {
            $aspirasiId = $_POST['aspirasi_id'] ?? null;
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            
            if (!$aspirasiId || !$mahasiswaData) {
                echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
                return;
            }
            
            $result = $this->aspirasiModel->vote($aspirasiId, $mahasiswaData['id']);
            
            if ($result['success']) {
                // Get updated vote count
                $aspirasi = $this->aspirasiModel->getById($aspirasiId);
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
    
    private function handleComment() {
        header('Content-Type: application/json');
        
        try {
            $aspirasiId = $_POST['aspirasi_id'] ?? null;
            $comment = trim($_POST['comment'] ?? '');
            
            if (!$aspirasiId || empty($comment)) {
                echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
                return;
            }
            
            if (strlen($comment) < 10) {
                echo json_encode(['success' => false, 'message' => 'Komentar minimal 10 karakter']);
                return;
            }
            
            $userId = $_SESSION['user_id'];
            $userType = $_SESSION['role'];
            
            if ($this->aspirasiModel->addComment($aspirasiId, $userId, $userType, $comment)) {
                echo json_encode(['success' => true, 'message' => 'Komentar berhasil ditambahkan']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menambahkan komentar']);
            }
            
        } catch (Exception $e) {
            error_log("Comment error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }
    
    private function handleUpdateStatus() {
        header('Content-Type: application/json');
        
        if ($_SESSION['role'] !== 'organisasi') {
            echo json_encode(['success' => false, 'message' => 'Tidak memiliki akses']);
            return;
        }
        
        try {
            $aspirasiId = $_POST['aspirasi_id'] ?? null;
            $status = $_POST['status'] ?? '';
            $adminNotes = trim($_POST['admin_notes'] ?? '');
            
            if (!$aspirasiId || !in_array($status, ['review', 'approved', 'rejected', 'completed'])) {
                echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
                return;
            }
            
            $organisasiModel = $this->model('Organisasi');
            $orgData = $organisasiModel->getByUserId($_SESSION['user_id']);
            
            if (!$orgData) {
                echo json_encode(['success' => false, 'message' => 'Data organisasi tidak ditemukan']);
                return;
            }
            
            if ($this->aspirasiModel->updateStatus($aspirasiId, $status, $orgData['id'], $adminNotes)) {
                echo json_encode(['success' => true, 'message' => 'Status berhasil diupdate']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mengupdate status']);
            }
            
        } catch (Exception $e) {
            error_log("Update status error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }
    
    private function handleCreateAspirai($mahasiswaData) {
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
            
            if (strlen($data['deskripsi']) < 50) {
                throw new Exception("Deskripsi minimal 50 karakter!");
            }
            
            $aspirasiId = $this->aspirasiModel->create($data);
            
            if ($aspirasiId) {
                $_SESSION['success_message'] = 'Aspirasi berhasil dibuat! Tunggu respon dari organisasi.';
                $this->redirect('aspirasi/detail/' . $aspirasiId);
            } else {
                throw new Exception("Gagal menyimpan aspirasi!");
            }
            
        } catch (Exception $e) {
            error_log("Create aspirasi error: " . $e->getMessage());
            $_SESSION['error_message'] = $e->getMessage();
            $this->redirect('aspirasi/create');
        }
    }
    
    private function handleUpdateAspirai($id) {
        try {
            // Validate input
            $required = ['judul', 'kategori_event', 'deskripsi', 'urgency'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field harus diisi!");
                }
            }
            
            $data = [
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
            
            if (strlen($data['deskripsi']) < 50) {
                throw new Exception("Deskripsi minimal 50 karakter!");
            }
            
            if ($this->aspirasiModel->update($id, $data)) {
                $_SESSION['success_message'] = 'Aspirasi berhasil diupdate!';
                $this->redirect('aspirasi/detail/' . $id);
            } else {
                throw new Exception("Gagal mengupdate aspirasi!");
            }
            
        } catch (Exception $e) {
            error_log("Update aspirasi error: " . $e->getMessage());
            $_SESSION['error_message'] = $e->getMessage();
            $this->redirect('aspirasi/edit/' . $id);
        }
    }
}