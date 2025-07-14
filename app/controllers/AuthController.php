<?php
// app/controllers/AuthController.php

class AuthController extends Controller {
    
    private $userModel;
    
    public function __construct() {
        // Session sudah dimulai di public/index.php
        $this->userModel = $this->model('User');
    }
    
    public function index() {
        $this->login();
    }
    
    public function login() {
        // If user already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard');
        }
        
        $data = [];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Debug: Log POST data
            error_log("Login attempt: " . json_encode($_POST));
            
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';
            
            if (empty($username) || empty($password) || empty($role)) {
                $data['error'] = 'Semua field harus diisi!';
                error_log("Login validation failed: username='$username', password='" . (!empty($password) ? 'filled' : 'empty') . "', role='$role'");
            } else {
                try {
                    $result = $this->userModel->login($username, $password, $role);
                    
                    if ($result) {
                        $_SESSION['user_id'] = $result['id'];
                        $_SESSION['username'] = $result['username'];
                        $_SESSION['email'] = $result['email'];
                        $_SESSION['role'] = $result['role'];
                        $_SESSION['login_time'] = time();
                        
                        // Get additional user data
                        $userData = $this->userModel->getUserData($result['id'], $result['role']);
                        if ($userData) {
                            $_SESSION['user_data'] = $userData;
                            $_SESSION['full_name'] = $userData['nama_lengkap'] ?? $userData['nama_organisasi'] ?? 'User';
                        }
                        
                        // Log activity
                        $this->userModel->logActivity($result['id'], 'login', 'User logged in as ' . $role);
                        
                        error_log("Login successful for user: " . $result['username']);
                        
                        // Redirect based on role
                        switch ($role) {
                            case 'organisasi':
                                $this->redirect('organisasi/dashboard');
                                break;
                            case 'mahasiswa':
                                $this->redirect('mahasiswa/dashboard');
                                break;
                            case 'staff':
                                $this->redirect('staff/dashboard');
                                break;
                            default:
                                $this->redirect('dashboard');
                        }
                        return;
                        
                    } else {
                        $data['error'] = 'Username, password, atau role tidak valid!';
                        error_log("Login failed: Invalid credentials for username='$username', role='$role'");
                    }
                } catch (Exception $e) {
                    $data['error'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
                    error_log("Login error: " . $e->getMessage());
                }
            }
        }
        
        $this->view('auth/login', $data);
    }
    
    public function register() {
        // If user already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard');
        }
        
        $data = [];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $role = $_POST['role'] ?? '';
                
                if (!in_array($role, ['mahasiswa', 'organisasi'])) {
                    throw new Exception('Role tidak valid!');
                }
                
                // Handle file upload
                $uploadedFile = $this->handleFileUpload($role);
                
                // Prepare data
                $postData = $_POST;
                
                // Ensure required keys exist with default values
                if ($role === 'organisasi') {
                    $postData['deskripsi_organisasi'] = $postData['deskripsi_organisasi'] ?? '';
                }
                
                if ($role === 'mahasiswa') {
                    $result = $this->userModel->registerMahasiswa($postData, $uploadedFile);
                } else if ($role === 'organisasi') {
                    $result = $this->userModel->registerOrganisasi($postData, $uploadedFile);
                }
                
                if ($result['success']) {
                    $data['success'] = $result['message'];
                    // Clear POST data on success
                    $_POST = [];
                } else {
                    $data['error'] = $result['message'];
                }
                
            } catch (Exception $e) {
                $data['error'] = $e->getMessage();
            }
        }
        
        $this->view('auth/register', $data);
    }
    
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->userModel->logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }
        
        session_destroy();
        $this->redirect('auth/login');
    }
    
    private function handleFileUpload($role) {
        // Create upload directory if not exists
        $uploadDir = 'uploads/' . $role . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Get the appropriate file field
        $fileField = '';
        switch ($role) {
            case 'mahasiswa':
                $fileField = 'jadwal_kuliah';
                break;
            case 'organisasi':
                $fileField = 'surat_pengesahan';
                break;
            default:
                throw new Exception('Role tidak valid untuk upload file');
        }
        
        if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File wajib diupload!');
        }
        
        $file = $_FILES[$fileField];
        
        // Validate file
        $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', "xlsx"];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Check file size
        if ($fileSize > $maxSize) {
            throw new Exception('Ukuran file terlalu besar! Maksimal 5MB');
        }
        
        // Check file type
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Format file tidak valid! Gunakan PDF, JPG, atau PNG');
        }
        
        // Generate unique filename
        $newFileName = uniqid() . '_' . time() . '.' . $fileType;
        $uploadPath = $uploadDir . $newFileName;
        
        // Move uploaded file
        if (move_uploaded_file($fileTmp, $uploadPath)) {
            return $newFileName;
        } else {
            throw new Exception('Gagal mengupload file!');
        }
    }
    
    // API method for AJAX validation
    public function checkAvailability() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $field = $_POST['field'] ?? '';
        $value = trim($_POST['value'] ?? '');
        
        if (empty($field) || empty($value)) {
            echo json_encode(['error' => 'Field and value required']);
            return;
        }
        
        $result = false;
        
        switch ($field) {
            case 'email':
                $result = $this->userModel->isEmailAvailable($value);
                break;
            case 'nim':
                $result = $this->userModel->isNimAvailable($value);
                break;
            case 'organisasi_name':
                $result = $this->userModel->isOrganisasiNameAvailable($value);
                break;
        }
        
        echo json_encode(['available' => $result]);
    }
}