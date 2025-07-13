<?php
class DashboardController extends Controller {
    public function index() {
        $this->checkAuth();
        
        $role = $_SESSION['role'];
        $data['user'] = $this->model('User')->getUserById($_SESSION['user_id']);
        
        switch ($role) {
            case 'mahasiswa':
                $this->view('mahasiswa/dashboard', $data);
                break;
            case 'organisasi':
                $this->view('organisasi/dashboard', $data);
                break;
            case 'staff':
                $this->view('staff/dashboard', $data);
                break;
            default:
                $this->redirect('auth/login');
        }
    }
}
?>