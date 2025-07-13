<?php
class DashboardController extends Controller {
    
    public function __construct() {
        // Session sudah dimulai di public/index.php, tidak perlu start lagi
    }
    
    public function index() {
        $this->checkAuth();
        
        $role = $_SESSION['role'];
        $data['user'] = $this->model('User')->getUserById($_SESSION['user_id']);
        
        switch ($role) {
            case 'mahasiswa':
                $this->view('mahasiswa/dashboard', $data);
                break;
            case 'organisasi':
                // Redirect ke OrganisasiController
                $this->redirect('organisasi/dashboard');
                break;
            case 'staff':
                $this->view('staff/dashboard', $data);
                break;
            default:
                $this->redirect('auth/login');
        }
    }
}