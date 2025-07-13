<?php
// create-event.php - Form untuk membuat event baru

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple session check - uncomment these lines when you have login
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisasi') {
//     header('Location: login.php');
//     exit();
// }

// For testing purposes, set dummy session data
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin_himatif';
    $_SESSION['role'] = 'organisasi';
}

$success_message = '';
$error_message = '';

// Database connection
try {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'kampus_system';
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    
    // Get organization data
    $org_data = null;
    try {
        $org_query = "SELECT o.*, u.username, u.email 
                      FROM organisasi o 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.user_id = ?";
        $stmt = $conn->prepare($org_query);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $org_result = $stmt->get_result();
        $org_data = $org_result->fetch_assoc();
    } catch (Exception $e) {
        $org_data = null;
    }
    
    // Use dummy data if no organization found
    if (!$org_data) {
        $org_data = [
            'id' => 1,
            'nama_organisasi' => 'Himpunan Mahasiswa Informatika',
            'jenis_organisasi' => 'Himpunan Mahasiswa',
            'status_verifikasi' => 'verified',
            'username' => $_SESSION['username'],
            'email' => 'himatif@university.ac.id'
        ];
    }
    
    // Check if events table exists, if not create it
    $check_table = "SHOW TABLES LIKE 'events'";
    $table_result = $conn->query($check_table);
    
    if ($table_result->num_rows == 0) {
        // Create events table
        $create_table = "CREATE TABLE events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            organisasi_id INT NOT NULL,
            nama_event VARCHAR(200) NOT NULL,
            deskripsi TEXT,
            kategori VARCHAR(50),
            tanggal_mulai DATETIME,
            tanggal_selesai DATETIME,
            lokasi VARCHAR(200),
            kapasitas INT,
            status ENUM('draft', 'aktif', 'selesai', 'dibatalkan') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (organisasi_id) REFERENCES organisasi(id) ON DELETE CASCADE
        )";
        
        if ($conn->query($create_table)) {
            $success_message = "Tabel events berhasil dibuat.";
        }
    }
    
} catch (Exception $e) {
    $error_message = "Database connection failed: " . $e->getMessage();
    // Use dummy data
    $org_data = [
        'id' => 1,
        'nama_organisasi' => 'Himpunan Mahasiswa Informatika',
        'jenis_organisasi' => 'Himpunan Mahasiswa',
        'status_verifikasi' => 'verified',
        'username' => $_SESSION['username'] ?? 'admin',
        'email' => 'himatif@university.ac.id'
    ];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate input
        $nama_event = trim($_POST['nama_event'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $kategori = trim($_POST['kategori'] ?? '');
        $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
        $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
        $lokasi = trim($_POST['lokasi'] ?? '');
        $kapasitas = intval($_POST['kapasitas'] ?? 0);
        $status = $_POST['status'] ?? 'draft';
        
        $errors = [];
        
        if (empty($nama_event)) {
            $errors[] = "Nama event harus diisi";
        }
        
        if (empty($kategori)) {
            $errors[] = "Kategori event harus dipilih";
        }
        
        if (empty($tanggal_mulai)) {
            $errors[] = "Tanggal mulai harus diisi";
        }
        
        if (empty($lokasi)) {
            $errors[] = "Lokasi event harus diisi";
        }
        
        if ($kapasitas <= 0) {
            $errors[] = "Kapasitas harus lebih dari 0";
        }
        
        if (!empty($tanggal_mulai) && !empty($tanggal_selesai)) {
            if (strtotime($tanggal_selesai) < strtotime($tanggal_mulai)) {
                $errors[] = "Tanggal selesai harus setelah tanggal mulai";
            }
        }
        
        if (empty($errors)) {
            // Insert event to database
            $insert_query = "INSERT INTO events (organisasi_id, nama_event, deskripsi, kategori, tanggal_mulai, tanggal_selesai, lokasi, kapasitas, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("issssssis", 
                $org_data['id'],
                $nama_event,
                $deskripsi,
                $kategori,
                $tanggal_mulai,
                $tanggal_selesai,
                $lokasi,
                $kapasitas,
                $status
            );
            
            if ($stmt->execute()) {
                $event_id = $conn->insert_id;
                $success_message = "Event '{$nama_event}' berhasil dibuat dengan ID: {$event_id}";
                
                // Clear form data
                $_POST = [];
            } else {
                $error_message = "Gagal menyimpan event: " . $stmt->error;
            }
        } else {
            $error_message = implode("<br>", $errors);
        }
        
    } catch (Exception $e) {
        $error_message = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Generate organization initials
$org_initials = '';
if (isset($org_data['nama_organisasi'])) {
    $words = explode(' ', $org_data['nama_organisasi']);
    foreach ($words as $word) {
        if (!empty($word)) {
            $org_initials .= strtoupper($word[0]);
            if (strlen($org_initials) >= 3) break;
        }
    }
}
if (empty($org_initials)) {
    $org_initials = 'ORG';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Event Baru - UACAD</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #fafafa;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #2d3748;
            line-height: 1.6;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 4px 0 20px rgba(245, 158, 11, 0.15);
        }
        
        .sidebar .logo {
            text-align: center;
            color: white;
            font-size: 22px;
            font-weight: 700;
            margin: 24px 0 40px 0;
            padding: 0 20px;
            letter-spacing: 1px;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 14px 24px;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            margin: 2px 12px 2px 0;
            border-radius: 0 12px 12px 0;
            font-weight: 500;
            font-size: 14px;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(4px);
        }
        
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            font-weight: 600;
        }
        
        .sidebar .nav-link i {
            margin-right: 12px;
            width: 18px;
            text-align: center;
            font-size: 16px;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 24px;
            min-height: 100vh;
        }
        
        .header {
            background: white;
            padding: 24px 28px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 28px;
            border: 1px solid #f1f5f9;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }
        
        .page-title p {
            color: #64748b;
            font-size: 15px;
            margin: 0;
        }
        
        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
            font-size: 14px;
        }
        
        .breadcrumb-item a {
            color: #f59e0b;
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: #64748b;
        }
        
        .profile-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .profile-img {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }
        
        .form-container {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
            max-width: 800px;
        }
        
        .form-section {
            margin-bottom: 32px;
        }
        
        .form-section h3 {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            display: block;
        }
        
        .required {
            color: #dc2626;
        }
        
        .form-control {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }
        
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
        }
        
        .form-select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }
        
        .form-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
        
        .btn-group-custom {
            display: flex;
            gap: 12px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #f1f5f9;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border: none;
            border-radius: 8px;
            color: white;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        
        .btn-secondary-custom {
            background: #f8f9fa;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            color: #374151;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .btn-secondary-custom:hover {
            background: #e9ecef;
            color: #1f2937;
        }
        
        .alert-success {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        
        .alert-danger {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        
        .datetime-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .capacity-info {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 12px;
            margin-top: 8px;
        }
        
        .capacity-info small {
            color: #92400e;
            font-size: 12px;
        }
        
        .status-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        .status-card {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .status-card:hover {
            border-color: #f59e0b;
            background: #fef3c7;
        }
        
        .status-card.selected {
            border-color: #f59e0b;
            background: #fef3c7;
        }
        
        .status-card h5 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .status-card p {
            font-size: 12px;
            color: #6b7280;
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .datetime-row {
                grid-template-columns: 1fr;
            }
            
            .status-options {
                grid-template-columns: 1fr;
            }
            
            .btn-group-custom {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-calendar-alt"></i> UACAD
        </div>
        <nav class="nav flex-column">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="events.php" class="nav-link">
                <i class="fas fa-calendar-check"></i> Event Saya
            </a>
            <a href="create-event.php" class="nav-link active">
                <i class="fas fa-plus-circle"></i> Buat Event
            </a>
            <a href="participants.php" class="nav-link">
                <i class="fas fa-users"></i> Kelola Pendaftar
            </a>
            <a href="analytics.php" class="nav-link">
                <i class="fas fa-chart-line"></i> Analitik
            </a>
            <a href="schedule.php" class="nav-link">
                <i class="fas fa-calendar-times"></i> Konflik Jadwal
            </a>
            <a href="reports.php" class="nav-link">
                <i class="fas fa-file-alt"></i> Laporan
            </a>
            <a href="profile.php" class="nav-link">
                <i class="fas fa-building"></i> Profil Organisasi
            </a>
            <a href="settings.php" class="nav-link">
                <i class="fas fa-cog"></i> Pengaturan
            </a>
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="page-header">
                <div class="page-title">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Buat Event</li>
                        </ol>
                    </nav>
                    <h1>Buat Event Baru</h1>
                    <p>Buat event baru untuk organisasi <?php echo htmlspecialchars($org_data['nama_organisasi']); ?></p>
                </div>
                <div class="profile-section">
                    <div class="profile-img"><?php echo $org_initials; ?></div>
                    <div>
                        <div style="font-weight: 600; color: #1e293b; font-size: 14px;">
                            <?php echo htmlspecialchars($org_data['username']); ?>
                        </div>
                        <div style="color: #64748b; font-size: 12px;">
                            <?php echo htmlspecialchars($org_data['jenis_organisasi']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($success_message)): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                <div style="margin-top: 8px;">
                    <a href="dashboard.php" style="color: #166534; font-weight: 600;">← Kembali ke Dashboard</a> | 
                    <a href="create-event.php" style="color: #166534; font-weight: 600;">Buat Event Lagi</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Form Container -->
        <div class="form-container">
            <form method="POST" action="create-event.php">
                <!-- Informasi Dasar -->
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Informasi Dasar Event</h3>
                    
                    <div class="form-group">
                        <label for="nama_event" class="form-label">
                            Nama Event <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="nama_event" name="nama_event" 
                               value="<?php echo htmlspecialchars($_POST['nama_event'] ?? ''); ?>" 
                               placeholder="Masukkan nama event" required>
                        <div class="form-text">Nama event harus jelas dan menarik</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="deskripsi" class="form-label">Deskripsi Event</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                                  placeholder="Jelaskan detail event, agenda, dan informasi penting lainnya"><?php echo htmlspecialchars($_POST['deskripsi'] ?? ''); ?></textarea>
                        <div class="form-text">Berikan deskripsi yang lengkap untuk menarik peserta</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori" class="form-label">
                            Kategori Event <span class="required">*</span>
                        </label>
                        <select class="form-select" id="kategori" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="seminar" <?php echo (($_POST['kategori'] ?? '') == 'seminar') ? 'selected' : ''; ?>>Seminar</option>
                            <option value="workshop" <?php echo (($_POST['kategori'] ?? '') == 'workshop') ? 'selected' : ''; ?>>Workshop</option>
                            <option value="kompetisi" <?php echo (($_POST['kategori'] ?? '') == 'kompetisi') ? 'selected' : ''; ?>>Kompetisi</option>
                            <option value="webinar" <?php echo (($_POST['kategori'] ?? '') == 'webinar') ? 'selected' : ''; ?>>Webinar</option>
                            <option value="pelatihan" <?php echo (($_POST['kategori'] ?? '') == 'pelatihan') ? 'selected' : ''; ?>>Pelatihan</option>
                            <option value="expo" <?php echo (($_POST['kategori'] ?? '') == 'expo') ? 'selected' : ''; ?>>Expo/Pameran</option>
                            <option value="rapat" <?php echo (($_POST['kategori'] ?? '') == 'rapat') ? 'selected' : ''; ?>>Rapat</option>
                            <option value="lainnya" <?php echo (($_POST['kategori'] ?? '') == 'lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>
                </div>

                <!-- Waktu dan Tempat -->
                <div class="form-section">
                    <h3><i class="fas fa-calendar-alt"></i> Waktu dan Tempat</h3>
                    
                    <div class="datetime-row">
                        <div class="form-group">
                            <label for="tanggal_mulai" class="form-label">
                                Tanggal & Waktu Mulai <span class="required">*</span>
                            </label>
                            <input type="datetime-local" class="form-control" id="tanggal_mulai" name="tanggal_mulai" 
                                   value="<?php echo $_POST['tanggal_mulai'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_selesai" class="form-label">Tanggal & Waktu Selesai</label>
                            <input type="datetime-local" class="form-control" id="tanggal_selesai" name="tanggal_selesai" 
                                   value="<?php echo $_POST['tanggal_selesai'] ?? ''; ?>">
                            <div class="form-text">Opsional - kosongkan jika belum pasti</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="lokasi" class="form-label">
                            Lokasi Event <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="lokasi" name="lokasi" 
                               value="<?php echo htmlspecialchars($_POST['lokasi'] ?? ''); ?>" 
                               placeholder="Contoh: Aula Universitas, Zoom Meeting, Google Meet" required>
                        <div class="form-text">Sebutkan lokasi fisik atau platform online</div>
                    </div>
                </div>

                <!-- Kapasitas dan Status -->
                <div class="form-section">
                    <h3><i class="fas fa-users"></i> Kapasitas dan Status</h3>
                    
                    <div class="form-group">
                        <label for="kapasitas" class="form-label">
                            Kapasitas Peserta <span class="required">*</span>
                        </label>
                        <input type="number" class="form-control" id="kapasitas" name="kapasitas" 
                               value="<?php echo $_POST['kapasitas'] ?? ''; ?>" 
                               min="1" max="10000" placeholder="Contoh: 100" required>
                        <div class="capacity-info">
                            <small><i class="fas fa-info-circle"></i> Tentukan jumlah maksimal peserta yang dapat mengikuti event</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status Event</label>
                        <div class="status-options">
                            <div class="status-card <?php echo (($_POST['status'] ?? 'draft') == 'draft') ? 'selected' : ''; ?>" onclick="selectStatus('draft')">
                                <input type="radio" name="status" value="draft" id="status_draft" 
                                       <?php echo (($_POST['status'] ?? 'draft') == 'draft') ? 'checked' : ''; ?> style="display: none;">
                                <h5><i class="fas fa-edit"></i> Draft</h5>
                                <p>Event masih dalam tahap persiapan, belum dipublikasi</p>
                            </div>
                            <div class="status-card <?php echo (($_POST['status'] ?? '') == 'aktif') ? 'selected' : ''; ?>" onclick="selectStatus('aktif')">
                                <input type="radio" name="status" value="aktif" id="status_aktif" 
                                       <?php echo (($_POST['status'] ?? '') == 'aktif') ? 'checked' : ''; ?> style="display: none;">
                                <h5><i class="fas fa-play"></i> Aktif</h5>
                                <p>Event siap dipublikasi dan menerima pendaftaran</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="btn-group-custom">
                    <button type="submit" class="btn-primary-custom">
                        <i class="fas fa-save"></i> Simpan Event
                    </button>
                    <a href="dashboard.php" class="btn-secondary-custom">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to select status
        function selectStatus(status) {
            // Remove selected class from all cards
            document.querySelectorAll('.status-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            event.target.closest('.status-card').classList.add('selected');
            
            // Check the radio button
            document.getElementById('status_' + status).checked = true;
        }

        // Set minimum date to today
        function setMinDate() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            
            document.getElementById('tanggal_mulai').min = currentDateTime;
            document.getElementById('tanggal_selesai').min = currentDateTime;
        }

        // Validate end date
        function validateEndDate() {
            const startDate = document.getElementById('tanggal_mulai').value;
            const endDate = document.getElementById('tanggal_selesai').value;
            
            if (startDate && endDate) {
                if (new Date(endDate) < new Date(startDate)) {
                    document.getElementById('tanggal_selesai').setCustomValidity('Tanggal selesai harus setelah tanggal mulai');
                } else {
                    document.getElementById('tanggal_selesai').setCustomValidity('');
                }
            }
        }

        // Auto update end date minimum
        document.getElementById('tanggal_mulai').addEventListener('change', function() {
            const startDate = this.value;
            document.getElementById('tanggal_selesai').min = startDate;
            validateEndDate();
        });

        document.getElementById('tanggal_selesai').addEventListener('change', validateEndDate);

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const namaEvent = document.getElementById('nama_event').value.trim();
            const kategori = document.getElementById('kategori').value;
            const tanggalMulai = document.getElementById('tanggal_mulai').value;
            const lokasi = document.getElementById('lokasi').value.trim();
            const kapasitas = parseInt(document.getElementById('kapasitas').value);
            
            let errors = [];
            
            if (!namaEvent) {
                errors.push('Nama event harus diisi');
            }
            
            if (!kategori) {
                errors.push('Kategori event harus dipilih');
            }
            
            if (!tanggalMulai) {
                errors.push('Tanggal mulai harus diisi');
            }
            
            if (!lokasi) {
                errors.push('Lokasi event harus diisi');
            }
            
            if (!kapasitas || kapasitas <= 0) {
                errors.push('Kapasitas harus lebih dari 0');
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                alert('Mohon lengkapi data berikut:\n\n' + errors.join('\n'));
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            submitBtn.disabled = true;
            
            // Re-enable after a delay if form doesn't submit
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });

        // Auto-generate event suggestions based on category
        document.getElementById('kategori').addEventListener('change', function() {
            const namaEventField = document.getElementById('nama_event');
            
            if (!namaEventField.value.trim()) {
                const suggestions = {
                    'seminar': 'Seminar ',
                    'workshop': 'Workshop ',
                    'kompetisi': 'Kompetisi ',
                    'webinar': 'Webinar ',
                    'pelatihan': 'Pelatihan ',
                    'expo': 'Expo ',
                    'rapat': 'Rapat '
                };
                
                if (suggestions[this.value]) {
                    namaEventField.placeholder = `Contoh: ${suggestions[this.value]}Teknologi 2025`;
                }
            }
        });

        // Auto-format capacity input
        document.getElementById('kapasitas').addEventListener('input', function() {
            let value = parseInt(this.value);
            
            if (value > 10000) {
                this.value = 10000;
                alert('Kapasitas maksimal adalah 10.000 peserta');
            } else if (value < 0) {
                this.value = 1;
            }
        });

        // Character counter for description
        const deskripsiField = document.getElementById('deskripsi');
        const charCounter = document.createElement('div');
        charCounter.className = 'form-text';
        charCounter.style.textAlign = 'right';
        deskripsiField.parentNode.appendChild(charCounter);

        function updateCharCounter() {
            const currentLength = deskripsiField.value.length;
            const maxLength = 1000;
            charCounter.textContent = `${currentLength}/${maxLength} karakter`;
            
            if (currentLength > maxLength * 0.9) {
                charCounter.style.color = '#dc2626';
            } else {
                charCounter.style.color = '#6b7280';
            }
        }

        deskripsiField.addEventListener('input', updateCharCounter);
        deskripsiField.setAttribute('maxlength', '1000');

        // Auto-save to localStorage (draft)
        function autoSave() {
            const formData = {
                nama_event: document.getElementById('nama_event').value,
                deskripsi: document.getElementById('deskripsi').value,
                kategori: document.getElementById('kategori').value,
                tanggal_mulai: document.getElementById('tanggal_mulai').value,
                tanggal_selesai: document.getElementById('tanggal_selesai').value,
                lokasi: document.getElementById('lokasi').value,
                kapasitas: document.getElementById('kapasitas').value,
                status: document.querySelector('input[name="status"]:checked')?.value
            };
            
            localStorage.setItem('event_draft', JSON.stringify(formData));
        }

        // Load draft from localStorage
        function loadDraft() {
            const draft = localStorage.getItem('event_draft');
            if (draft && !<?php echo !empty($_POST) ? 'true' : 'false'; ?>) {
                const formData = JSON.parse(draft);
                
                if (confirm('Ditemukan draft event yang belum disimpan. Muat draft tersebut?')) {
                    Object.keys(formData).forEach(key => {
                        const element = document.getElementById(key) || document.querySelector(`input[name="${key}"][value="${formData[key]}"]`);
                        if (element) {
                            if (element.type === 'radio') {
                                element.checked = true;
                                element.closest('.status-card').classList.add('selected');
                            } else {
                                element.value = formData[key];
                            }
                        }
                    });
                    updateCharCounter();
                }
            }
        }

        // Clear draft on successful submission
        <?php if (!empty($success_message)): ?>
        localStorage.removeItem('event_draft');
        <?php endif; ?>

        // Auto-save every 30 seconds
        setInterval(autoSave, 30000);

        // Save on form change
        document.querySelectorAll('input, textarea, select').forEach(element => {
            element.addEventListener('change', autoSave);
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            setMinDate();
            updateCharCounter();
            loadDraft();
            
            // Focus on first field
            document.getElementById('nama_event').focus();
        });

        // Sidebar navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        console.log('Create Event page loaded successfully!');
        console.log('Organization:', <?php echo json_encode($org_data); ?>);
    </script>
</body>
</html>