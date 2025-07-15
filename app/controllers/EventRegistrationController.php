<?php
// app/controllers/EventRegistrationController.php
// Handler untuk pendaftaran event mahasiswa dengan integrasi database

class EventRegistrationController extends Controller {
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
    
    // Tampilkan form pendaftaran event
    public function index() {
        try {
            $eventId = $_GET['event'] ?? null;
            
            if (!$eventId) {
                $this->redirect('mahasiswa/kegiatan');
                return;
            }
            
            // Get event data
            $event = $this->eventModel->getById($eventId);
            if (!$event) {
                $_SESSION['error_message'] = 'Event tidak ditemukan';
                $this->redirect('mahasiswa/kegiatan');
                return;
            }
            
            // Check if event is still open for registration
            if ($event['status'] !== 'aktif') {
                $_SESSION['error_message'] = 'Event tidak dalam status aktif';
                $this->redirect('mahasiswa/kegiatan');
                return;
            }
            
            // Check if registration deadline has passed
            if (strtotime($event['tanggal_mulai']) <= time()) {
                $_SESSION['error_message'] = 'Pendaftaran event sudah ditutup';
                $this->redirect('mahasiswa/kegiatan');
                return;
            }
            
            // Get mahasiswa data
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            if (!$mahasiswaData) {
                $_SESSION['error_message'] = 'Data mahasiswa tidak ditemukan';
                $this->redirect('mahasiswa/kegiatan');
                return;
            }
            
            // Get remaining slots
            $event['remaining_slots'] = $this->eventModel->getRemainingSlots($eventId);
            
            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handleRegistration($event, $mahasiswaData);
                return;
            }
            
            $data = [
                'title' => 'Daftar Event - ' . $event['nama_event'],
                'event' => $event,
                'mahasiswa_data' => $mahasiswaData,
                'current_page' => 'kegiatan'
            ];
            
            $this->view('mahasiswa/event_registration', $data);
            
        } catch (Exception $e) {
            error_log("Event registration error: " . $e->getMessage());
            $_SESSION['error_message'] = 'Terjadi kesalahan sistem';
            $this->redirect('mahasiswa/kegiatan');
        }
    }
    
    // Handle form submission
    private function handleRegistration($event, $mahasiswaData) {
        header('Content-Type: application/json');
        
        try {
            // Validate input
            $registrationData = $this->validateRegistrationData($_POST);
            
            // Check if already registered
            if ($this->isAlreadyRegistered($event['id'], $mahasiswaData['user_id'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Anda sudah terdaftar untuk event ini'
                ]);
                return;
            }
            
            // Check remaining slots
            $remainingSlots = $this->eventModel->getRemainingSlots($event['id']);
            if ($remainingSlots <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Event sudah penuh, tidak dapat mendaftar lagi'
                ]);
                return;
            }
            
            // Handle file uploads
            $uploadedFiles = $this->handleFileUploads($mahasiswaData['id']);
            
            // Save registration to database
            $registrationId = $this->saveRegistration(
                $event, 
                $mahasiswaData, 
                $registrationData, 
                $uploadedFiles
            );
            
            if ($registrationId) {
                // Send notifications
                $this->sendRegistrationNotifications($event, $mahasiswaData, $registrationData);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pendaftaran berhasil! Status: Menunggu verifikasi organisasi',
                    'registration_id' => $registrationId
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal menyimpan data pendaftaran'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Registration submission error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
    
    // Validate registration data
    private function validateRegistrationData($data) {
        $errors = [];
        
        // Required fields validation
        $requiredFields = [
            'no_whatsapp' => 'Nomor WhatsApp',
            'email' => 'Email',
            'alasan_ikut' => 'Alasan mengikuti event',
            'terms_agreement' => 'Persetujuan syarat dan ketentuan',
            'data_agreement' => 'Persetujuan penggunaan data'
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                $errors[] = "$label harus diisi";
            }
        }
        
        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }
        
        // Phone number validation
        if (!empty($data['no_whatsapp'])) {
            $phone = preg_replace('/\D/', '', $data['no_whatsapp']);
            if (strlen($phone) < 10 || strlen($phone) > 13 || !preg_match('/^08/', $phone)) {
                $errors[] = 'Nomor WhatsApp tidak valid (harus 08xxxxxxxxxx)';
            }
        }
        
        // Reason length validation
        if (!empty($data['alasan_ikut']) && strlen(trim($data['alasan_ikut'])) < 50) {
            $errors[] = 'Alasan mengikuti event minimal 50 karakter';
        }
        
        // Agreement validation
        if (empty($data['terms_agreement']) || empty($data['data_agreement'])) {
            $errors[] = 'Anda harus menyetujui syarat dan ketentuan';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        return [
            'no_whatsapp' => preg_replace('/\D/', '', $data['no_whatsapp']),
            'email' => trim($data['email']),
            'alasan_ikut' => trim($data['alasan_ikut']),
            'pengalaman' => $data['pengalaman'] ?? '',
            'harapan' => $data['harapan'] ?? '',
            'pertanyaan' => trim($data['pertanyaan'] ?? ''),
            'terms_agreement' => !empty($data['terms_agreement']),
            'data_agreement' => !empty($data['data_agreement'])
        ];
    }
    
    // Check if already registered
    private function isAlreadyRegistered($eventId, $userId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT COUNT(*) as count FROM event_participants 
                      WHERE event_id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $eventId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] > 0;
            
        } catch (Exception $e) {
            error_log("Check registration error: " . $e->getMessage());
            return false;
        }
    }
    
    // Handle file uploads
    private function handleFileUploads($mahasiswaId) {
        $uploadedFiles = [];
        $uploadDir = 'uploads/event_registrations/' . $mahasiswaId . '/';
        
        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Handle CV upload
        if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
            $cvFile = $_FILES['cv_file'];
            $cvFileName = $this->processFileUpload($cvFile, $uploadDir, 'cv', 2 * 1024 * 1024); // 2MB limit
            if ($cvFileName) {
                $uploadedFiles['cv_file'] = $cvFileName;
            }
        }
        
        // Handle bukti file upload (for competitions)
        if (isset($_FILES['bukti_file']) && $_FILES['bukti_file']['error'] === UPLOAD_ERR_OK) {
            $buktiFile = $_FILES['bukti_file'];
            $buktiFileName = $this->processFileUpload($buktiFile, $uploadDir, 'bukti', 5 * 1024 * 1024); // 5MB limit
            if ($buktiFileName) {
                $uploadedFiles['bukti_file'] = $buktiFileName;
            }
        }
        
        return $uploadedFiles;
    }
    
    // Process individual file upload
    private function processFileUpload($file, $uploadDir, $prefix, $maxSize) {
        try {
            // Validate file size
            if ($file['size'] > $maxSize) {
                throw new Exception("Ukuran file {$prefix} terlalu besar");
            }
            
            // Validate file type
            $allowedTypes = [
                'cv' => ['pdf', 'doc', 'docx'],
                'bukti' => ['pdf', 'jpg', 'jpeg', 'png']
            ];
            
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, $allowedTypes[$prefix] ?? [])) {
                throw new Exception("Format file {$prefix} tidak didukung");
            }
            
            // Generate unique filename
            $fileName = $prefix . '_' . time() . '_' . uniqid() . '.' . $fileExt;
            $filePath = $uploadDir . $fileName;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                return $fileName;
            } else {
                throw new Exception("Gagal mengupload file {$prefix}");
            }
            
        } catch (Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Save registration to database
    private function saveRegistration($event, $mahasiswaData, $registrationData, $uploadedFiles) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Start transaction
            $conn->begin_transaction();
            
            // Insert into event_participants table
            $participantQuery = "INSERT INTO event_participants (
                event_id, user_id, status, registered_at, 
                no_whatsapp, email_peserta, alasan_ikut, pengalaman, 
                harapan, pertanyaan, cv_file, bukti_file
            ) VALUES (?, ?, 'menunggu_verifikasi', CURRENT_TIMESTAMP, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($participantQuery);
            $stmt->bind_param("iississsss", 
                $event['id'],
                $mahasiswaData['user_id'],
                $registrationData['no_whatsapp'],
                $registrationData['email'],
                $registrationData['alasan_ikut'],
                $registrationData['pengalaman'],
                $registrationData['harapan'],
                $registrationData['pertanyaan'],
                $uploadedFiles['cv_file'] ?? null,
                $uploadedFiles['bukti_file'] ?? null
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Gagal menyimpan data peserta");
            }
            
            $registrationId = $conn->insert_id;
            
            // Log activity
            $this->logActivity($mahasiswaData['user_id'], 'event_registration', 
                "Mendaftar event: {$event['nama_event']} (ID: {$event['id']})");
            
            // Commit transaction
            $conn->commit();
            
            return $registrationId;
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Save registration error: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Send notifications
    private function sendRegistrationNotifications($event, $mahasiswaData, $registrationData) {
        try {
            // Send email to participant
            $this->sendParticipantEmail($event, $mahasiswaData, $registrationData);
            
            // Send notification to organizer
            $this->sendOrganizerNotification($event, $mahasiswaData, $registrationData);
            
            // Send WhatsApp notification (if service available)
            $this->sendWhatsAppNotification($event, $mahasiswaData, $registrationData);
            
        } catch (Exception $e) {
            error_log("Notification error: " . $e->getMessage());
            // Don't throw exception here, registration should still succeed
        }
    }
    
    // Send email to participant
    private function sendParticipantEmail($event, $mahasiswaData, $registrationData) {
        $to = $registrationData['email'];
        $subject = "Konfirmasi Pendaftaran Event: {$event['nama_event']}";
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: #10b981; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .event-details { background: #f0fdf4; padding: 15px; border-radius: 8px; margin: 20px 0; }
                .footer { background: #f9fafb; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                .status-badge { background: #fef3c7; color: #92400e; padding: 5px 10px; border-radius: 15px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>UACAD - Sistem Informasi Kampus</h2>
                <p>Konfirmasi Pendaftaran Event</p>
            </div>
            
            <div class='content'>
                <h3>Halo, {$mahasiswaData['nama_lengkap']}!</h3>
                
                <p>Terima kasih telah mendaftar untuk event <strong>{$event['nama_event']}</strong>.</p>
                
                <div class='event-details'>
                    <h4>Detail Event:</h4>
                    <p><strong>Nama Event:</strong> {$event['nama_event']}</p>
                    <p><strong>Tanggal:</strong> " . date('d M Y, H:i', strtotime($event['tanggal_mulai'])) . " WIB</p>
                    <p><strong>Lokasi:</strong> {$event['lokasi']}</p>
                    <p><strong>Organisasi:</strong> {$event['nama_organisasi']}</p>
                </div>
                
                <p><strong>Status Pendaftaran:</strong> <span class='status-badge'>Menunggu Verifikasi</span></p>
                
                <p>Pendaftaran Anda sedang dalam proses verifikasi oleh organisasi penyelenggara. 
                Anda akan mendapat notifikasi lanjutan melalui email dan WhatsApp dalam 1x24 jam.</p>
                
                <h4>Data Pendaftaran Anda:</h4>
                <ul>
                    <li><strong>Nama:</strong> {$mahasiswaData['nama_lengkap']}</li>
                    <li><strong>NIM:</strong> {$mahasiswaData['nim']}</li>
                    <li><strong>Fakultas:</strong> {$mahasiswaData['fakultas']}</li>
                    <li><strong>WhatsApp:</strong> {$registrationData['no_whatsapp']}</li>
                </ul>
                
                <p><strong>Alasan Mengikuti:</strong><br>
                {$registrationData['alasan_ikut']}</p>
                
                <p>Jika ada pertanyaan, silakan hubungi organisasi penyelenggara atau admin sistem.</p>
                
                <p>Salam,<br>Tim UACAD</p>
            </div>
            
            <div class='footer'>
                <p>Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini.</p>
                <p>© 2024 UACAD - Universitas</p>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: noreply@university.ac.id" . "\r\n";
        
        mail($to, $subject, $message, $headers);
    }
    
    // Send notification to organizer
    private function sendOrganizerNotification($event, $mahasiswaData, $registrationData) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Get organizer email
            $query = "SELECT u.email FROM organisasi o 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $event['organisasi_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $organizer = $result->fetch_assoc();
            
            if ($organizer) {
                $to = $organizer['email'];
                $subject = "Pendaftar Baru Event: {$event['nama_event']}";
                
                $message = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .header { background: #667eea; color: white; padding: 20px; text-align: center; }
                        .content { padding: 20px; }
                        .participant-info { background: #f8fafc; padding: 15px; border-radius: 8px; margin: 20px 0; }
                        .footer { background: #f9fafb; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                        .btn { background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h2>UACAD - Notifikasi Organisasi</h2>
                        <p>Pendaftar Baru Event</p>
                    </div>
                    
                    <div class='content'>
                        <h3>Ada pendaftar baru untuk event Anda!</h3>
                        
                        <p><strong>Event:</strong> {$event['nama_event']}</p>
                        
                        <div class='participant-info'>
                            <h4>Data Pendaftar:</h4>
                            <p><strong>Nama:</strong> {$mahasiswaData['nama_lengkap']}</p>
                            <p><strong>NIM:</strong> {$mahasiswaData['nim']}</p>
                            <p><strong>Fakultas/Jurusan:</strong> {$mahasiswaData['fakultas']} / {$mahasiswaData['jurusan']}</p>
                            <p><strong>Email:</strong> {$registrationData['email']}</p>
                            <p><strong>WhatsApp:</strong> {$registrationData['no_whatsapp']}</p>
                            <p><strong>Pengalaman:</strong> {$registrationData['pengalaman']}</p>
                            
                            <p><strong>Alasan Mengikuti:</strong><br>
                            {$registrationData['alasan_ikut']}</p>
                            
                            " . (!empty($registrationData['pertanyaan']) ? "
                            <p><strong>Pertanyaan:</strong><br>
                            {$registrationData['pertanyaan']}</p>
                            " : "") . "
                        </div>
                        
                        <p>Silakan verifikasi pendaftaran ini melalui dashboard organisasi.</p>
                        
                        <p><a href='" . BASE_URL . "organisasi/participants?event={$event['id']}' class='btn'>Kelola Peserta</a></p>
                        
                        <p>Salam,<br>Tim UACAD</p>
                    </div>
                    
                    <div class='footer'>
                        <p>Email ini dikirim otomatis oleh sistem.</p>
                    </div>
                </body>
                </html>
                ";
                
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: noreply@university.ac.id" . "\r\n";
                
                mail($to, $subject, $message, $headers);
            }
            
        } catch (Exception $e) {
            error_log("Organizer notification error: " . $e->getMessage());
        }
    }
    
    // Send WhatsApp notification (placeholder for WhatsApp API integration)
    private function sendWhatsAppNotification($event, $mahasiswaData, $registrationData) {
        try {
            // This is a placeholder for WhatsApp API integration
            // You can integrate with services like Twilio, WooWa, or local WhatsApp gateway
            
            $phoneNumber = $registrationData['no_whatsapp'];
            $message = "*UACAD - Konfirmasi Pendaftaran Event*\n\n";
            $message .= "Halo {$mahasiswaData['nama_lengkap']},\n\n";
            $message .= "Pendaftaran Anda untuk event *{$event['nama_event']}* telah diterima!\n\n";
            $message .= "📅 Tanggal: " . date('d M Y, H:i', strtotime($event['tanggal_mulai'])) . " WIB\n";
            $message .= "📍 Lokasi: {$event['lokasi']}\n\n";
            $message .= "Status: *Menunggu Verifikasi*\n\n";
            $message .= "Anda akan mendapat notifikasi lanjutan dalam 1x24 jam.\n\n";
            $message .= "Terima kasih!\n";
            $message .= "Tim UACAD";
            
            // Log WhatsApp message for now (implement actual sending later)
            error_log("WhatsApp message to {$phoneNumber}: {$message}");
            
            /*
            // Example integration with WhatsApp API:
            $whatsappAPI = new WhatsAppAPI();
            $whatsappAPI->sendMessage($phoneNumber, $message);
            */
            
        } catch (Exception $e) {
            error_log("WhatsApp notification error: " . $e->getMessage());
        }
    }
    
    // Log user activity
    private function logActivity($userId, $action, $description) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "INSERT INTO user_activities (user_id, action, description, created_at) 
                      VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iss", $userId, $action, $description);
            $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Activity log error: " . $e->getMessage());
        }
    }
    
    // Additional method: Get registration status
    public function getRegistrationStatus() {
        header('Content-Type: application/json');
        
        try {
            $eventId = $_GET['event_id'] ?? null;
            
            if (!$eventId) {
                echo json_encode(['success' => false, 'message' => 'Event ID required']);
                return;
            }
            
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            if (!$mahasiswaData) {
                echo json_encode(['success' => false, 'message' => 'Data mahasiswa tidak ditemukan']);
                return;
            }
            
            $db = new Database();
            $conn = $db->getConnection();
            
            $query = "SELECT ep.*, e.nama_event 
                      FROM event_participants ep 
                      JOIN events e ON ep.event_id = e.id
                      WHERE ep.event_id = ? AND ep.user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $eventId, $mahasiswaData['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $registration = $result->fetch_assoc();
            
            if ($registration) {
                echo json_encode([
                    'success' => true,
                    'registered' => true,
                    'status' => $registration['status'],
                    'registered_at' => $registration['registered_at']
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'registered' => false
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Get registration status error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }
    
    // Cancel registration method
    public function cancelRegistration() {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                return;
            }
            
            $eventId = $_POST['event_id'] ?? null;
            
            if (!$eventId) {
                echo json_encode(['success' => false, 'message' => 'Event ID required']);
                return;
            }
            
            $mahasiswaData = $this->mahasiswaModel->getByUserId($_SESSION['user_id']);
            if (!$mahasiswaData) {
                echo json_encode(['success' => false, 'message' => 'Data mahasiswa tidak ditemukan']);
                return;
            }
            
            $db = new Database();
            $conn = $db->getConnection();
            
            // Check if registration exists and can be cancelled
            $checkQuery = "SELECT ep.*, e.nama_event, e.tanggal_mulai 
                           FROM event_participants ep 
                           JOIN events e ON ep.event_id = e.id
                           WHERE ep.event_id = ? AND ep.user_id = ?";
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param("ii", $eventId, $mahasiswaData['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $registration = $result->fetch_assoc();
            
            if (!$registration) {
                echo json_encode(['success' => false, 'message' => 'Pendaftaran tidak ditemukan']);
                return;
            }
            
            // Check if event is more than 24 hours away
            $eventTime = strtotime($registration['tanggal_mulai']);
            $currentTime = time();
            $timeDiff = $eventTime - $currentTime;
            
            if ($timeDiff < (24 * 60 * 60)) { // Less than 24 hours
                echo json_encode([
                    'success' => false, 
                    'message' => 'Pembatalan hanya dapat dilakukan minimal 24 jam sebelum event'
                ]);
                return;
            }
            
            // Delete registration
            $deleteQuery = "DELETE FROM event_participants WHERE event_id = ? AND user_id = ?";
            $stmt = $conn->prepare($deleteQuery);
            $stmt->bind_param("ii", $eventId, $mahasiswaData['user_id']);
            
            if ($stmt->execute()) {
                // Log activity
                $this->logActivity($mahasiswaData['user_id'], 'event_cancellation', 
                    "Membatalkan pendaftaran event: {$registration['nama_event']}");
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pendaftaran berhasil dibatalkan'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal membatalkan pendaftaran']);
            }
            
        } catch (Exception $e) {
            error_log("Cancel registration error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }
}