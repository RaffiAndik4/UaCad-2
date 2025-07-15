<?php
// app/views/mahasiswa/kegiatan.php - Updated Version with CRUD

// Get mahasiswa data from session
$mahasiswa_data = $mahasiswa_data ?? ['nama_lengkap' => 'Mahasiswa', 'id' => 1];
$my_events = $my_events ?? [];
$available_events = $available_events ?? [];
$need_feedback = $need_feedback ?? [];

// Handle AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $action = $_POST['action'];
        $user_id = $_SESSION['user_id'];
        
        // Get mahasiswa_id from user_id
        $stmt = $conn->prepare("SELECT id FROM mahasiswa WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $mahasiswa = $result->fetch_assoc();
        $mahasiswa_id = $mahasiswa['id'];
        
        switch ($action) {
            case 'register_event':
                $event_id = intval($_POST['event_id']);
                
                // Check if already registered
                $stmt = $conn->prepare("SELECT id FROM event_participants WHERE event_id = ? AND user_id = ?");
                $stmt->bind_param("ii", $event_id, $user_id);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    echo json_encode(['success' => false, 'message' => 'Anda sudah terdaftar di event ini!']);
                    exit;
                }
                
                // Check capacity
                $stmt = $conn->prepare("SELECT e.kapasitas, COUNT(ep.id) as registered FROM events e LEFT JOIN event_participants ep ON e.id = ep.event_id WHERE e.id = ? GROUP BY e.id");
                $stmt->bind_param("i", $event_id);
                $stmt->execute();
                $capacity_check = $stmt->get_result()->fetch_assoc();
                
                if ($capacity_check['registered'] >= $capacity_check['kapasitas']) {
                    echo json_encode(['success' => false, 'message' => 'Event sudah penuh!']);
                    exit;
                }
                
                // Register to event
                $stmt = $conn->prepare("INSERT INTO event_participants (event_id, user_id, status, verification_status, registered_at) VALUES (?, ?, 'pending', 'pending', NOW())");
                $stmt->bind_param("ii", $event_id, $user_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Berhasil mendaftar! Menunggu verifikasi organisasi.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal mendaftar event!']);
                }
                break;
                
            case 'cancel_registration':
                $event_id = intval($_POST['event_id']);
                
                // Check if can cancel (pending/accepted and event not started)
                $stmt = $conn->prepare("
                    SELECT ep.id FROM event_participants ep 
                    JOIN events e ON ep.event_id = e.id 
                    WHERE ep.event_id = ? AND ep.user_id = ? 
                    AND ep.verification_status IN ('pending', 'accepted') 
                    AND ep.status IN ('pending', 'terdaftar')
                    AND e.tanggal_mulai > NOW()
                ");
                $stmt->bind_param("ii", $event_id, $user_id);
                $stmt->execute();
                
                if ($stmt->get_result()->num_rows === 0) {
                    echo json_encode(['success' => false, 'message' => 'Tidak dapat membatalkan pendaftaran!']);
                    exit;
                }
                
                // Cancel registration
                $stmt = $conn->prepare("DELETE FROM event_participants WHERE event_id = ? AND user_id = ?");
                $stmt->bind_param("ii", $event_id, $user_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Pendaftaran berhasil dibatalkan!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal membatalkan pendaftaran!']);
                }
                break;
                
            case 'give_feedback':
                $event_id = intval($_POST['event_id']);
                $rating = intval($_POST['rating']);
                $feedback = trim($_POST['feedback']);
                
                if ($rating < 1 || $rating > 5) {
                    echo json_encode(['success' => false, 'message' => 'Rating harus antara 1-5!']);
                    exit;
                }
                
                // Check if can give feedback
                $stmt = $conn->prepare("
                    SELECT ep.id FROM event_participants ep
                    JOIN events e ON ep.event_id = e.id
                    WHERE ep.event_id = ? AND ep.user_id = ? 
                    AND e.status = 'selesai' AND ep.status = 'hadir'
                ");
                $stmt->bind_param("ii", $event_id, $user_id);
                $stmt->execute();
                
                if ($stmt->get_result()->num_rows === 0) {
                    echo json_encode(['success' => false, 'message' => 'Anda tidak dapat memberikan feedback untuk event ini!']);
                    exit;
                }
                
                // Insert feedback
                $stmt = $conn->prepare("
                    INSERT INTO event_feedback (event_id, mahasiswa_id, user_id, rating, feedback_text, attendance_confirmed, created_at) 
                    VALUES (?, ?, ?, ?, ?, TRUE, NOW())
                    ON DUPLICATE KEY UPDATE 
                    rating = VALUES(rating), 
                    feedback_text = VALUES(feedback_text), 
                    updated_at = NOW()
                ");
                $stmt->bind_param("iiiis", $event_id, $mahasiswa_id, $user_id, $rating, $feedback);
                
                if ($stmt->execute()) {
                    // Generate certificate after feedback
                    $cert_code = 'CERT-' . $event_id . '-' . $mahasiswa_id . '-' . date('Ymd');
                    $stmt = $conn->prepare("
                        INSERT IGNORE INTO certificates (event_id, mahasiswa_id, user_id, certificate_code, issued_date, status) 
                        VALUES (?, ?, ?, ?, CURDATE(), 'generated')
                    ");
                    $stmt->bind_param("iiis", $event_id, $mahasiswa_id, $user_id, $cert_code);
                    $stmt->execute();
                    
                    echo json_encode(['success' => true, 'message' => 'Feedback berhasil diberikan! Sertifikat telah digenerate.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal memberikan feedback!']);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Action tidak valid!']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Get data for display (READ operation)
try {
    $db = new Database();
    $conn = $db->getConnection();
    $user_id = $_SESSION['user_id'];
    
    // Get mahasiswa data
    $stmt = $conn->prepare("SELECT id, nama_lengkap, nim, fakultas FROM mahasiswa WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $mahasiswa_data = $stmt->get_result()->fetch_assoc();
    
    if (!$mahasiswa_data) {
        throw new Exception("Data mahasiswa tidak ditemukan");
    }
    
    // Get my events using the view
    $stmt = $conn->prepare("SELECT * FROM mahasiswa_events_view WHERE user_id = ? ORDER BY registered_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $my_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get available events (not registered)
    $stmt = $conn->prepare("
        SELECT e.*, o.nama_organisasi, 
               COUNT(ep.id) as registered_count,
               (e.kapasitas - COUNT(ep.id)) as remaining_slots
        FROM events e
        JOIN organisasi o ON e.organisasi_id = o.id
        LEFT JOIN event_participants ep ON e.id = ep.event_id
        WHERE e.status = 'aktif' 
        AND e.tanggal_mulai > NOW()
        AND e.id NOT IN (
            SELECT event_id FROM event_participants WHERE user_id = ?
        )
        GROUP BY e.id
        HAVING remaining_slots > 0
        ORDER BY e.tanggal_mulai ASC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $available_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get events that need feedback
    $need_feedback = array_filter($my_events, function($event) {
        return $event['can_give_feedback'] == 1;
    });
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kegiatan Saya - UACAD</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(16, 185, 129, 0.15);
        }
        
        .sidebar .logo {
            text-align: center;
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin: 24px 0 40px 0;
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
            text-decoration: none;
        }
        
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.25);
            color: white;
            font-weight: 600;
        }
        
        .sidebar .nav-link i {
            margin-right: 12px;
            width: 18px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 24px;
            min-height: 100vh;
        }
        
        .page-header {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }
        
        .event-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        
        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-accepted { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #dc2626; }
        .status-hadir { background: #e0f2fe; color: #0277bd; }
        .status-terdaftar { background: #e1f5fe; color: #01579b; }
        
        .rating-stars {
            color: #fbbf24;
            margin-right: 8px;
        }
        
        .feedback-form {
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            margin-top: 12px;
        }
        
        .btn-custom {
            border-radius: 6px;
            font-weight: 500;
            padding: 8px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }
        
        .alert-custom {
            border: none;
            border-radius: 8px;
            padding: 12px 16px;
        }
        
        .tab-content {
            margin-top: 20px;
        }
        
        .certificate-badge {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i> UACAD
        </div>
        
        <nav class="nav flex-column">
            <a href="<?= BASE_URL ?>mahasiswa/landing" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="<?= BASE_URL ?>mahasiswa/dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="<?= BASE_URL ?>mahasiswa/kegiatan" class="nav-link active"><i class="fas fa-calendar-check"></i> Kegiatan</a>
            <a href="<?= BASE_URL ?>mahasiswa/jadwal" class="nav-link"><i class="fas fa-calendar-alt"></i> Jadwal</a>
            <a href="<?= BASE_URL ?>mahasiswa/aspirasi" class="nav-link"><i class="fas fa-lightbulb"></i> Aspirasi</a>
            <a href="<?= BASE_URL ?>mahasiswa/profile" class="nav-link"><i class="fas fa-user"></i> Profil</a>
            <a href="<?= BASE_URL ?>auth/logout" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <h2>Kegiatan Saya</h2>
            <p class="text-muted">Kelola pendaftaran event dan berikan feedback setelah mengikuti kegiatan</p>
            <?php if (isset($mahasiswa_data)): ?>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($mahasiswa_data['nama_lengkap']) ?> 
                        | <i class="fas fa-id-card"></i> <?= htmlspecialchars($mahasiswa_data['nim']) ?>
                        | <i class="fas fa-building"></i> <?= htmlspecialchars($mahasiswa_data['fakultas']) ?>
                    </small>
                </div>
            <?php endif; ?>
        </div>

        <!-- Error Display -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-custom">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- Alert Container for AJAX responses -->
        <div id="alertContainer"></div>

        <!-- Feedback Reminder -->
        <?php if (!empty($need_feedback)): ?>
            <div class="alert alert-warning alert-custom">
                <h6><i class="fas fa-star"></i> Berikan Feedback</h6>
                <p class="mb-0">Anda memiliki <?= count($need_feedback) ?> event yang perlu feedback untuk mendapatkan sertifikat.</p>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="eventTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="my-events-tab" data-bs-toggle="tab" data-bs-target="#my-events" type="button">
                    <i class="fas fa-list me-2"></i>Event Saya (<?= count($my_events) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="available-events-tab" data-bs-toggle="tab" data-bs-target="#available-events" type="button">
                    <i class="fas fa-plus me-2"></i>Event Tersedia (<?= count($available_events) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="feedback-tab" data-bs-toggle="tab" data-bs-target="#feedback-events" type="button">
                    <i class="fas fa-star me-2"></i>Perlu Feedback (<?= count($need_feedback) ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="eventTabContent">
            <!-- My Events Tab -->
            <div class="tab-pane fade show active" id="my-events" role="tabpanel">
                <?php if (!empty($my_events)): ?>
                    <?php foreach ($my_events as $event): ?>
                    <div class="event-card">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="mb-1"><?= htmlspecialchars($event['nama_event']) ?></h5>
                                    <div>
                                        <span class="status-badge status-<?= $event['verification_status'] ?>">
                                            <?= ucfirst($event['verification_status']) ?>
                                        </span>
                                        <?php if ($event['certificate_code']): ?>
                                            <span class="certificate-badge ms-2">
                                                <i class="fas fa-certificate"></i> Tersertifikat
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <p class="text-muted mb-2"><?= htmlspecialchars($event['deskripsi']) ?></p>
                                
                                <div class="row text-sm mb-2">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-building text-primary"></i> <?= htmlspecialchars($event['nama_organisasi']) ?>
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar text-success"></i> <?= date('d M Y H:i', strtotime($event['tanggal_mulai'])) ?>
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($event['lokasi']) ?>
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-tag text-warning"></i> <?= htmlspecialchars($event['kategori']) ?>
                                        </small>
                                    </div>
                                </div>

                                <?php if ($event['verification_status'] === 'rejected' && $event['rejected_reason']): ?>
                                    <div class="alert alert-danger alert-custom">
                                        <small><strong>Alasan Ditolak:</strong> <?= htmlspecialchars($event['rejected_reason']) ?></small>
                                    </div>
                                <?php endif; ?>

                                <?php if ($event['rating']): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <span class="rating-stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $event['rating'] ? '' : 'text-muted' ?>"></i>
                                                <?php endfor; ?>
                                            </span>
                                            Rating Anda: <?= $event['rating'] ?>/5
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4 text-end">
                                <div class="d-flex flex-column gap-2">
                                    <button class="btn btn-outline-info btn-custom" onclick="viewEventDetail(<?= $event['event_id'] ?>)">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                    
                                    <?php if ($event['can_cancel']): ?>
                                        <button class="btn btn-outline-danger btn-custom" onclick="cancelRegistration(<?= $event['event_id'] ?>, '<?= htmlspecialchars($event['nama_event']) ?>')">
                                            <i class="fas fa-times"></i> Batal Daftar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($event['can_give_feedback']): ?>
                                        <button class="btn btn-warning btn-custom" onclick="showFeedbackForm(<?= $event['event_id'] ?>, '<?= htmlspecialchars($event['nama_event']) ?>')">
                                            <i class="fas fa-star"></i> Beri Feedback
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($event['certificate_code']): ?>
                                        <button class="btn btn-success btn-custom" onclick="downloadCertificate('<?= $event['certificate_code'] ?>')">
                                            <i class="fas fa-download"></i> Unduh Sertifikat
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Belum Ada Event Terdaftar</h5>
                        <p class="text-muted">Daftar event baru di tab "Event Tersedia"</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Available Events Tab -->
            <div class="tab-pane fade" id="available-events" role="tabpanel">
                <?php if (!empty($available_events)): ?>
                    <?php foreach ($available_events as $event): ?>
                    <div class="event-card">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-2"><?= htmlspecialchars($event['nama_event']) ?></h5>
                                <p class="text-muted mb-2"><?= htmlspecialchars($event['deskripsi']) ?></p>
                                
                                <div class="row text-sm mb-2">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-building text-primary"></i> <?= htmlspecialchars($event['nama_organisasi']) ?>
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar text-success"></i> <?= date('d M Y H:i', strtotime($event['tanggal_mulai'])) ?>
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($event['lokasi']) ?>
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-users text-info"></i> <?= $event['remaining_slots'] ?> slot tersisa
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 text-end">
                                <button class="btn btn-outline-info btn-custom mb-2" onclick="viewEventDetail(<?= $event['id'] ?>)">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                                <br>
                                <button class="btn btn-primary-custom" onclick="registerEvent(<?= $event['id'] ?>, '<?= htmlspecialchars($event['nama_event']) ?>')">
                                    <i class="fas fa-plus"></i> Daftar Event
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-plus text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Tidak Ada Event Tersedia</h5>
                        <p class="text-muted">Semua event sudah penuh atau belum ada event baru</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Feedback Events Tab -->
            <div class="tab-pane fade" id="feedback-events" role="tabpanel">
                <?php if (!empty($need_feedback)): ?>
                    <?php foreach ($need_feedback as $event): ?>
                    <div class="event-card">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="mb-2"><?= htmlspecialchars($event['nama_event']) ?></h5>
                                <p class="text-muted mb-2"><?= htmlspecialchars($event['deskripsi']) ?></p>
                                
                                <div class="alert alert-info alert-custom">
                                    <i class="fas fa-info-circle"></i> 
                                    Berikan feedback untuk mendapatkan sertifikat event ini
                                </div>
                                
                                <!-- Feedback Form -->
                                <div class="feedback-form">
                                    <form onsubmit="submitFeedback(event, <?= $event['event_id'] ?>)">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label"><strong>Rating (1-5 Bintang)</strong></label>
                                                <div class="rating-input mb-3">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <input type="radio" name="rating_<?= $event['event_id'] ?>" value="<?= $i ?>" id="star<?= $i ?>_<?= $event['event_id'] ?>" required>
                                                        <label for="star<?= $i ?>_<?= $event['event_id'] ?>" class="star-label">
                                                            <i class="fas fa-star"></i>
                                                        </label>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label"><strong>Feedback (Opsional)</strong></label>
                                                <textarea class="form-control" name="feedback_<?= $event['event_id'] ?>" rows="3" placeholder="Bagikan pengalaman Anda..."></textarea>
                                            </div>
                                        </div>
                                        <div class="text-end mt-3">
                                            <button type="submit" class="btn btn-primary-custom">
                                                <i class="fas fa-paper-plane"></i> Kirim Feedback
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-star text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Tidak Ada Event yang Perlu Feedback</h5>
                        <p class="text-muted">Event yang sudah Anda ikuti dan perlu feedback akan muncul di sini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Event Detail Modal -->
    <div class="modal fade" id="eventDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle"></i> Detail Event
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="eventDetailContent">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-star"></i> Berikan Feedback
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="modalFeedbackForm">
                    <div class="modal-body">
                        <input type="hidden" id="modalEventId" name="event_id">
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>Event:</strong></label>
                            <p id="modalEventName" class="text-muted"></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>Rating (1-5 Bintang) *</strong></label>
                            <div class="rating-modal d-flex gap-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <input type="radio" name="modal_rating" value="<?= $i ?>" id="modal_star<?= $i ?>" required>
                                    <label for="modal_star<?= $i ?>" class="star-label-modal">
                                        <i class="fas fa-star"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>Feedback</strong></label>
                            <textarea class="form-control" name="modal_feedback" rows="4" placeholder="Bagikan pengalaman Anda mengikuti event ini..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-certificate"></i> 
                            Setelah memberikan feedback, Anda akan mendapatkan sertifikat digital untuk event ini.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-paper-plane"></i> Kirim Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        const BASE_URL = '<?= BASE_URL ?>';
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            setupRatingStars();
            setupModalRatingStars();
        });
        
        // Setup rating stars interaction
        function setupRatingStars() {
            document.querySelectorAll('.rating-input').forEach(container => {
                const stars = container.querySelectorAll('input[type="radio"]');
                const labels = container.querySelectorAll('.star-label');
                
                labels.forEach((label, index) => {
                    label.addEventListener('mouseenter', function() {
                        highlightStars(labels, index + 1);
                    });
                    
                    label.addEventListener('click', function() {
                        const radio = this.previousElementSibling;
                        radio.checked = true;
                        highlightStars(labels, index + 1, true);
                    });
                });
                
                container.addEventListener('mouseleave', function() {
                    const checkedStar = container.querySelector('input[type="radio"]:checked');
                    if (checkedStar) {
                        const checkedIndex = Array.from(stars).indexOf(checkedStar);
                        highlightStars(labels, checkedIndex + 1, true);
                    } else {
                        highlightStars(labels, 0);
                    }
                });
            });
        }
        
        // Setup modal rating stars
        function setupModalRatingStars() {
            const container = document.querySelector('.rating-modal');
            const stars = container.querySelectorAll('input[type="radio"]');
            const labels = container.querySelectorAll('.star-label-modal');
            
            labels.forEach((label, index) => {
                label.addEventListener('mouseenter', function() {
                    highlightStars(labels, index + 1);
                });
                
                label.addEventListener('click', function() {
                    const radio = this.previousElementSibling;
                    radio.checked = true;
                    highlightStars(labels, index + 1, true);
                });
            });
            
            container.addEventListener('mouseleave', function() {
                const checkedStar = container.querySelector('input[type="radio"]:checked');
                if (checkedStar) {
                    const checkedIndex = Array.from(stars).indexOf(checkedStar);
                    highlightStars(labels, checkedIndex + 1, true);
                } else {
                    highlightStars(labels, 0);
                }
            });
        }
        
        // Highlight stars function
        function highlightStars(labels, count, permanent = false) {
            labels.forEach((label, index) => {
                const star = label.querySelector('i');
                if (index < count) {
                    star.style.color = '#fbbf24';
                } else {
                    star.style.color = permanent ? '#d1d5db' : '#e5e7eb';
                }
            });
        }
        
        // Register for event
        function registerEvent(eventId, eventName) {
            if (confirm(`Daftar event "${eventName}"?`)) {
                showLoading('Mendaftarkan Anda...');
                
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=register_event&event_id=${eventId}`
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    showAlert(data.message, data.success ? 'success' : 'danger');
                    if (data.success) {
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(error => {
                    hideLoading();
                    showAlert('Terjadi kesalahan sistem!', 'danger');
                });
            }
        }
        
        // Cancel registration
        function cancelRegistration(eventId, eventName) {
            if (confirm(`Batalkan pendaftaran event "${eventName}"?`)) {
                showLoading('Membatalkan pendaftaran...');
                
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=cancel_registration&event_id=${eventId}`
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    showAlert(data.message, data.success ? 'success' : 'danger');
                    if (data.success) {
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(error => {
                    hideLoading();
                    showAlert('Terjadi kesalahan sistem!', 'danger');
                });
            }
        }
        
        // Submit feedback from inline form
        function submitFeedback(event, eventId) {
            event.preventDefault();
            
            const form = event.target;
            const rating = form.querySelector(`input[name="rating_${eventId}"]:checked`)?.value;
            const feedback = form.querySelector(`textarea[name="feedback_${eventId}"]`).value;
            
            if (!rating) {
                showAlert('Pilih rating terlebih dahulu!', 'warning');
                return;
            }
            
            showLoading('Mengirim feedback...');
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=give_feedback&event_id=${eventId}&rating=${rating}&feedback=${encodeURIComponent(feedback)}`
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                showAlert(data.message, data.success ? 'success' : 'danger');
                if (data.success) {
                    setTimeout(() => location.reload(), 2000);
                }
            })
            .catch(error => {
                hideLoading();
                showAlert('Terjadi kesalahan sistem!', 'danger');
            });
        }
        
        // Show feedback modal
        function showFeedbackForm(eventId, eventName) {
            document.getElementById('modalEventId').value = eventId;
            document.getElementById('modalEventName').textContent = eventName;
            
            // Reset form
            document.getElementById('modalFeedbackForm').reset();
            const labels = document.querySelectorAll('.star-label-modal');
            highlightStars(labels, 0);
            
            new bootstrap.Modal(document.getElementById('feedbackModal')).show();
        }
        
        // Submit feedback from modal
        document.getElementById('modalFeedbackForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const eventId = formData.get('event_id');
            const rating = formData.get('modal_rating');
            const feedback = formData.get('modal_feedback');
            
            if (!rating) {
                showAlert('Pilih rating terlebih dahulu!', 'warning');
                return;
            }
            
            showLoading('Mengirim feedback...');
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=give_feedback&event_id=${eventId}&rating=${rating}&feedback=${encodeURIComponent(feedback)}`
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                showAlert(data.message, data.success ? 'success' : 'danger');
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('feedbackModal')).hide();
                    setTimeout(() => location.reload(), 2000);
                }
            })
            .catch(error => {
                hideLoading();
                showAlert('Terjadi kesalahan sistem!', 'danger');
            });
        });
        
        // View event detail
        function viewEventDetail(eventId) {
            // Get event data from page
            const events = [
                ...<?= json_encode($my_events) ?>,
                ...<?= json_encode($available_events) ?>
            ];
            
            const event = events.find(e => e.id == eventId || e.event_id == eventId);
            
            if (!event) {
                showAlert('Data event tidak ditemukan!', 'danger');
                return;
            }
            
            const content = `
                <div class="row">
                    <div class="col-md-8">
                        <h5>${event.nama_event}</h5>
                        <p class="text-muted">${event.deskripsi}</p>
                        
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Organisasi:</strong></td>
                                <td>${event.nama_organisasi}</td>
                            </tr>
                            <tr>
                                <td><strong>Kategori:</strong></td>
                                <td><span class="badge bg-secondary">${event.kategori}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal:</strong></td>
                                <td>${formatDateTime(event.tanggal_mulai)}</td>
                            </tr>
                            <tr>
                                <td><strong>Lokasi:</strong></td>
                                <td>${event.lokasi}</td>
                            </tr>
                            <tr>
                                <td><strong>Kapasitas:</strong></td>
                                <td>${event.kapasitas} peserta</td>
                            </tr>
                            ${event.verification_status ? `
                            <tr>
                                <td><strong>Status Verifikasi:</strong></td>
                                <td><span class="status-badge status-${event.verification_status}">${event.verification_status}</span></td>
                            </tr>
                            ` : ''}
                            ${event.rating ? `
                            <tr>
                                <td><strong>Rating Anda:</strong></td>
                                <td>
                                    <span class="rating-stars">
                                        ${Array.from({length: 5}, (_, i) => 
                                            `<i class="fas fa-star ${i < event.rating ? '' : 'text-muted'}"></i>`
                                        ).join('')}
                                    </span>
                                    ${event.rating}/5
                                </td>
                            </tr>
                            ` : ''}
                        </table>
                        
                        ${event.feedback_text ? `
                            <div class="mt-3">
                                <strong>Feedback Anda:</strong>
                                <div class="bg-light p-3 rounded mt-2">
                                    "${event.feedback_text}"
                                </div>
                            </div>
                        ` : ''}
                    </div>
                    <div class="col-md-4">
                        ${event.poster ? `
                            <img src="${BASE_URL}uploads/posters/${event.poster}" 
                                 alt="Poster Event" class="img-fluid rounded">
                        ` : `
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image text-muted fa-3x"></i>
                            </div>
                        `}
                        
                        ${event.certificate_code ? `
                            <div class="mt-3 text-center">
                                <div class="certificate-badge d-inline-block">
                                    <i class="fas fa-certificate"></i> Sertifikat Tersedia
                                </div>
                                <br>
                                <small class="text-muted">Kode: ${event.certificate_code}</small>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
            
            document.getElementById('eventDetailContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
        }
        
        // Download certificate
        function downloadCertificate(certificateCode) {
            showAlert('Mengunduh sertifikat...', 'info');
            
            // Simulate certificate download
            setTimeout(() => {
                showAlert('Sertifikat berhasil diunduh!', 'success');
                
                // Create download link (simulation)
                const link = document.createElement('a');
                link.href = `${BASE_URL}certificates/${certificateCode}.pdf`;
                link.download = `Sertifikat_${certificateCode}.pdf`;
                link.click();
            }, 1000);
        }
        
        // Utility functions
        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alertContainer');
            const alertId = 'alert-' + Date.now();
            
            const icons = {
                success: 'fa-check-circle',
                danger: 'fa-exclamation-triangle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            const alert = document.createElement('div');
            alert.id = alertId;
            alert.className = `alert alert-${type} alert-dismissible fade show alert-custom`;
            alert.innerHTML = `
                <i class="fas ${icons[type]}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            alertContainer.appendChild(alert);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                const element = document.getElementById(alertId);
                if (element) {
                    element.remove();
                }
            }, 5000);
        }
        
        function showLoading(message = 'Loading...') {
            showAlert(`<i class="fas fa-spinner fa-spin"></i> ${message}`, 'info');
        }
        
        function hideLoading() {
            // Remove loading alerts
            document.querySelectorAll('.alert .fa-spinner').forEach(spinner => {
                spinner.closest('.alert').remove();
            });
        }
        
        function formatDateTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // Add CSS for rating stars
        const style = document.createElement('style');
        style.textContent = `
            .rating-input, .rating-modal {
                display: flex;
                gap: 4px;
            }
            
            .rating-input input[type="radio"],
            .rating-modal input[type="radio"] {
                display: none;
            }
            
            .star-label, .star-label-modal {
                cursor: pointer;
                font-size: 1.5rem;
                color: #e5e7eb;
                transition: color 0.2s ease;
            }
            
            .star-label:hover, .star-label-modal:hover {
                color: #fbbf24;
            }
            
            .rating-input input[type="radio"]:checked ~ .star-label i,
            .rating-modal input[type="radio"]:checked ~ .star-label-modal i {
                color: #fbbf24;
            }
        `;
        document.head.appendChild(style);
        
        console.log('Kegiatan Mahasiswa page loaded successfully!');
        console.log('My events:', <?= json_encode(count($my_events)) ?>);
        console.log('Available events:', <?= json_encode(count($available_events)) ?>);
        console.log('Need feedback:', <?= json_encode(count($need_feedback)) ?>);
    </script>
</body>
</html>