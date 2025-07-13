php<?php
$org_initials = '';
if (isset($data['org_data']['nama_organisasi'])) {
    $words = explode(' ', $data['org_data']['nama_organisasi']);
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

// Set default BASE_URL if not defined
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script_path = dirname($_SERVER['SCRIPT_NAME']);
    define('BASE_URL', $protocol . $host . $script_path . '/');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title'] ?? 'Dashboard'; ?> - UACAD</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <style>
        /* Include all CSS here untuk avoid file missing errors */
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
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
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
        
        .welcome-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-text h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }
        
        .welcome-text p {
            color: #64748b;
            font-size: 15px;
            margin: 0;
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
        
        .profile-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
        }
        
        .profile-role {
            color: #64748b;
            font-size: 12px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stats-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
            transition: all 0.2s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }
        
        .stats-icon i {
            color: #d97706;
            font-size: 20px;
        }
        
        .stats-value {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }
        
        .stats-label {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 28px;
        }
        
        .chart-container {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
            margin-bottom: 24px;
        }
        
        .chart-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .chart-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .chart-header i {
            color: #f59e0b;
            margin-right: 8px;
        }
        
        .chart-canvas {
            height: 300px !important;
            width: 100% !important;
        }
        
        .shortcut-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
            text-align: center;
        }
        
        .shortcut-section h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
        }
        
        .shortcut-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .shortcut-btn {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border: none;
            border-radius: 12px;
            color: white;
            padding: 12px 20px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);
            cursor: pointer;
        }
        
        .shortcut-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            color: white;
        }
        
        .events-panel {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
            height: fit-content;
        }
        
        .events-header {
            padding: 24px 24px 16px 24px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .events-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .events-list {
            max-height: 600px;
            overflow-y: auto;
            padding: 16px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }
        
        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }
        
        .empty-state h4 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
        }
        
        .empty-state p {
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .btn-create-first {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border: none;
            border-radius: 8px;
            color: white;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .btn-create-first:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            color: white;
        }
        
        .event-item {
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            margin-bottom: 12px;
            transition: all 0.2s ease;
            background: #fafafa;
        }
        
        .event-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            background: white;
        }
        
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        
        .event-date {
            background: #f59e0b;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .event-status {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-aktif {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-selesai {
            background: #e5e7eb;
            color: #374151;
        }
        
        .event-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .event-description {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 12px;
            line-height: 1.5;
        }
        
        .session-info {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #fbbf24;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }
        
        .session-info h4 {
            color: #92400e;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .session-info p {
            color: #a16207;
            font-size: 14px;
            margin: 0;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-bottom: 1px solid #fbbf24;
        }
        
        .modal-title {
            color: #92400e;
            font-weight: 600;
        }
        
        .status-option {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .status-option:hover {
            border-color: #fbbf24 !important;
            background-color: #fef3c7;
        }
        
        .status-option.selected {
            border-color: #f59e0b !important;
            background-color: #fef3c7;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .shortcut-buttons {
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
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="events.php" class="nav-link">
                <i class="fas fa-calendar-check"></i> Event Saya
            </a>
            <button type="button" class="nav-link" data-bs-toggle="modal" data-bs-target="#createEventModal">
                <i class="fas fa-plus-circle"></i> Buat Event
            </button>
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
        <!-- Error Display -->
        <?php if (isset($data['error'])): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong> <?php echo htmlspecialchars($data['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Session Info -->
        <div class="session-info">
            <h4><i class="fas fa-info-circle"></i> Session Organisasi</h4>
            <p>
                Login sebagai: <strong><?php echo htmlspecialchars($data['org_data']['nama_organisasi'] ?? 'Organisasi'); ?></strong> | 
                ID: <strong><?php echo htmlspecialchars($data['org_data']['id'] ?? 'N/A'); ?></strong> | 
                Status: <strong><?php echo htmlspecialchars(ucfirst($data['org_data']['status_verifikasi'] ?? 'pending')); ?></strong> |
                Jenis: <strong><?php echo htmlspecialchars($data['org_data']['jenis_organisasi'] ?? 'N/A'); ?></strong>
            </p>
        </div>

        <!-- Header -->
        <div class="header">
            <div class="welcome-section">
                <div class="welcome-text">
                    <h1>Dashboard <?php echo htmlspecialchars($data['org_data']['nama_organisasi'] ?? 'Organisasi'); ?></h1>
                    <p>Kelola event dan kegiatan organisasi Anda dengan mudah</p>
                </div>
                <div class="profile-section">
                    <div class="profile-img"><?php echo $org_initials; ?></div>
                    <div>
                        <div class="profile-name">
                            <?php echo htmlspecialchars($data['org_data']['username'] ?? 'Admin'); ?>
                        </div>
                        <div class="profile-role">
                            <?php echo htmlspecialchars($data['org_data']['jenis_organisasi'] ?? 'Organisasi'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="stats-value"><?php echo $data['stats']['total_events'] ?? 0; ?></div>
                <div class="stats-label">Total Event Dibuat</div>
            </div>
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stats-value"><?php echo $data['stats']['total_capacity'] ?? 0; ?></div>
                <div class="stats-label">Total Kapasitas</div>
            </div>
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stats-value"><?php echo $data['stats']['active_events'] ?? 0; ?></div>
                <div class="stats-label">Event Aktif</div>
            </div>
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stats-value">-</div>
                <div class="stats-label">Rating Organisasi</div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Left Column -->
            <div class="left-column">
                <!-- Charts Row -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                    <div class="chart-container">
                        <div class="chart-header">
                            <i class="fas fa-trending-up"></i>
                            <h3>Trend Event (6 Bulan)</h3>
                        </div>
                        <canvas id="trendChart" class="chart-canvas"></canvas>
                    </div>
                    <div class="chart-container">
                        <div class="chart-header">
                            <i class="fas fa-chart-pie"></i>
                            <h3>Kategori Event</h3>
                        </div>
                        <canvas id="categoryChart" class="chart-canvas"></canvas>
                    </div>
                </div>

                <!-- Shortcut Section -->
                <div class="shortcut-section">
                    <h3>Aksi Cepat</h3>
                    <div class="shortcut-buttons">
                        <button type="button" class="shortcut-btn" data-bs-toggle="modal" data-bs-target="#createEventModal">
                            <i class="fas fa-plus"></i> Buat Event Baru
                        </button>
                        <a href="duplicate-event.php" class="shortcut-btn">
                            <i class="fas fa-copy"></i> Duplikat Event
                        </a>
                        <a href="schedule.php" class="shortcut-btn">
                            <i class="fas fa-calendar-check"></i> Cek Jadwal
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Column - Events -->
            <div class="events-panel">
                <div class="events-header">
                    <h3><i class="fas fa-fire" style="color: #f59e0b; margin-right: 8px;"></i>Event Aktif</h3>
                </div>
                <div class="events-list">
                    <?php if (empty($data['active_events'])): ?>
                        <!-- Empty State -->
                        <div class="empty-state">
                            <i class="fas fa-calendar-alt"></i>
                            <h4>Belum Ada Event</h4>
                            <p>Organisasi Anda belum memiliki event aktif. Mulai buat event pertama untuk melihat aktivitas di sini.</p>
                            <button type="button" class="btn-create-first" data-bs-toggle="modal" data-bs-target="#createEventModal">
                                <i class="fas fa-plus"></i> Buat Event Pertama
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($data['active_events'] as $event): ?>
                        <div class="event-item">
                            <div class="event-header">
                                <div class="event-date">
                                    <?php echo date('d M Y', strtotime($event['tanggal_mulai'] ?? $event['created_at'])); ?>
                                </div>
                                <span class="event-status status-<?php echo $event['status']; ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </div>
                            <div class="event-title"><?php echo htmlspecialchars($event['nama_event']); ?></div>
                            <div class="event-description">
                                <?php echo htmlspecialchars(substr($event['deskripsi'] ?? '', 0, 100)) . (strlen($event['deskripsi'] ?? '') > 100 ? '...' : ''); ?>
                            </div>
                            <?php if (isset($event['kapasitas'])): ?>
                            <div style="color: #64748b; font-size: 13px; margin-bottom: 12px;">
                                <i class="fas fa-users"></i> Kapasitas: <?php echo $event['kapasitas']; ?> peserta
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Event Modal -->
    <div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle text-warning"></i> Buat Event Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="createEventForm">
                    <div class="modal-body">
                        <!-- Alert container -->
                        <div id="alertContainer"></div>
                        
                        <!-- Event Basic Info -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="nama_event" class="form-label fw-bold">
                                    Nama Event <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="nama_event" name="nama_event" 
                                       placeholder="Masukkan nama event" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="kategori" class="form-label fw-bold">
                                    Kategori <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="kategori" name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="seminar">Seminar</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="kompetisi">Kompetisi</option>
                                    <option value="webinar">Webinar</option>
                                    <option value="pelatihan">Pelatihan</option>
                                    <option value="expo">Expo/Pameran</option>
                                    <option value="rapat">Rapat</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="deskripsi" class="form-label fw-bold">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" 
                                          placeholder="Jelaskan detail event..."></textarea>
                                <div class="form-text text-end">
                                    <span id="charCount">0</span>/500 karakter
                                </div>
                            </div>
                        </div>
                        
                        <!-- Date & Time -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tanggal_mulai" class="form-label fw-bold">
                                    Tanggal Mulai <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tanggal_selesai" class="form-label fw-bold">Tanggal Selesai</label>
                                <input type="datetime-local" class="form-control" id="tanggal_selesai" name="tanggal_selesai">
                            </div>
                        </div>
                        
                        <!-- Location & Capacity -->
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="lokasi" class="form-label fw-bold">
                                    Lokasi <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="lokasi" name="lokasi" 
                                       placeholder="Contoh: Aula Universitas" required>
                            </div>
                            <div class="col-md-4">
                                <label for="kapasitas" class="form-label fw-bold">
                                    Kapasitas <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="kapasitas" name="kapasitas" 
                                       min="1" max="10000" placeholder="100" required>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Status Event</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="status-option p-3 border rounded selected" data-status="draft">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status" id="status_draft" value="draft" checked>
                                                <label class="form-check-label fw-bold" for="status_draft">
                                                    <i class="fas fa-edit text-secondary"></i> Draft
                                                </label>
                                            </div>
                                            <small class="text-muted">Belum dipublikasi</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="status-option p-3 border rounded" data-status="aktif">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status" id="status_aktif" value="aktif">
                                                <label class="form-check-label fw-bold" for="status_aktif">
                                                    <i class="fas fa-play text-success"></i> Aktif
                                                </label>
                                            </div>
                                            <small class="text-muted">Siap menerima pendaftaran</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Simpan Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart.js Configuration
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#64748b';

        // Initialize with PHP data
        const dashboardData = {
            orgData: <?php echo json_encode($data['org_data'] ?? []); ?>,
            stats: <?php echo json_encode($data['stats'] ?? []); ?>,
            trendData: <?php echo json_encode($data['trend_data'] ?? []); ?>,
            categoryData: <?php echo json_encode($data['category_data'] ?? []); ?>,
            activeEvents: <?php echo json_encode($data['active_events'] ?? []); ?>
        };

        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const trendLabels = [];
        const trendValues = [];
        
        // Get last 6 months
        for (let i = 5; i >= 0; i--) {
            const date = new Date();
            date.setMonth(date.getMonth() - i);
            const monthName = months[date.getMonth()];
            trendLabels.push(monthName);
            
            const monthData = dashboardData.trendData.find(item => 
                item.month == (date.getMonth() + 1) && item.year == date.getFullYear()
            );
            trendValues.push(monthData ? monthData.count : 0);
        }

        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Event Dibuat',
                    data: trendValues,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#f59e0b',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: Math.max(...trendValues) + 2 || 10,
                        ticks: {
                            stepSize: 1,
                            color: '#94a3b8'
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.1)',
                            borderColor: 'rgba(148, 163, 184, 0.2)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#94a3b8'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        let categoryLabels, categoryValues, categoryColors;
        
        if (dashboardData.categoryData.length > 0) {
            categoryLabels = dashboardData.categoryData.map(item => item.kategori || 'Tanpa Kategori');
            categoryValues = dashboardData.categoryData.map(item => item.count);
            categoryColors = ['#f59e0b', '#fbbf24', '#fcd34d', '#fde68a', '#fef3c7'];
        } else {
            categoryLabels = ['Belum Ada Data'];
            categoryValues = [1];
            categoryColors = ['#e5e7eb'];
        }
        
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryValues,
                    backgroundColor: categoryColors,
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12,
                                weight: '500'
                            },
                            color: '#64748b'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#f59e0b',
                        borderWidth: 1,
                        cornerRadius: 8,
                        enabled: dashboardData.categoryData.length > 0,
                        callbacks: {
                            label: function(context) {
                                if (dashboardData.categoryData.length === 0) return '';
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((context.parsed / total) * 100);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Modal Event Handlers
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('createEventForm');
            const alertContainer = document.getElementById('alertContainer');
            
            // Character counter
            const deskripsiField = document.getElementById('deskripsi');
            const charCount = document.getElementById('charCount');
            
            deskripsiField.addEventListener('input', function() {
                const currentLength = this.value.length;
                charCount.textContent = currentLength;
                
                if (currentLength > 450) {
                    charCount.style.color = '#dc2626';
                } else {
                    charCount.style.color = '#6b7280';
                }
            });
            
            // Set minimum date
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            
            document.getElementById('tanggal_mulai').min = currentDateTime;
            document.getElementById('tanggal_selesai').min = currentDateTime;
            
            // Status selection
            document.querySelectorAll('.status-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.status-option').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    
                    this.classList.add('selected');
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                });
            });
            
            // Auto-update end date minimum when start date changes
            document.getElementById('tanggal_mulai').addEventListener('change', function() {
                document.getElementById('tanggal_selesai').min = this.value;
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                submitBtn.disabled = true;
                
                // Clear previous alerts
                alertContainer.innerHTML = '';
                
                // Prepare form data
                const formData = new FormData(form);
                
                // Send AJAX request
                fetch('dashboard.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        alertContainer.innerHTML = `
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> ${data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                        
                        // Reset form
                        form.reset();
                        document.querySelectorAll('.status-option').forEach(opt => {
                            opt.classList.remove('selected');
                        });
                        document.querySelector('.status-option[data-status="draft"]').classList.add('selected');
                        charCount.textContent = '0';
                        
                        // Close modal after 2 seconds and refresh page
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('createEventModal'));
                            modal.hide();
                            window.location.reload();
                        }, 2000);
                        
                    } else {
                        // Show error message
                        alertContainer.innerHTML = `
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> ${data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alertContainer.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan sistem. Silakan coba lagi.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                })
                .finally(() => {
                    // Restore button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });
        });

        // Sidebar navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.tagName.toLowerCase() === 'button') return;
                
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        console.log('Dashboard loaded successfully!');
        console.log('Organization data:', dashboardData.orgData);
        console.log('Stats:', dashboardData.stats);
    </script>
</body>
</html>
