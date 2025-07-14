<?php
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

// Set default values if data not available
$stats = $stats ?? ['total_events' => 0, 'total_capacity' => 0, 'active_events' => 0];
$active_events = $active_events ?? [];
$org_data = $org_data ?? ['nama_organisasi' => 'Organisasi', 'username' => 'Admin', 'jenis_organisasi' => 'Organisasi'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Organisasi - UACAD</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #1e293b;
            line-height: 1.6;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 4px 0 20px rgba(102, 126, 234, 0.15);
        }
        
        .sidebar .logo {
            text-align: center;
            color: white;
            font-size: 24px;
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
            width: calc(100% - 12px);
            text-align: left;
            cursor: pointer;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(4px);
        }
        
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.25);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 28px;
            border: 1px solid #e2e8f0;
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
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stats-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stats-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stats-icon i {
            color: #7c3aed;
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
            grid-template-columns: 2fr 1fr;
            gap: 28px;
        }
        
        .chart-container {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
        }
        
        .chart-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .chart-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .chart-header i {
            color: #667eea;
            margin-right: 8px;
        }
        
        .chart-canvas {
            height: 300px !important;
            width: 100% !important;
        }
        
        .events-panel {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            height: fit-content;
        }
        
        .events-header {
            padding: 24px 24px 16px 24px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .events-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
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
            color: #cbd5e1;
            margin-bottom: 16px;
        }
        
        .btn-create-first {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            padding: 12px 20px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .event-item {
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 12px;
            transition: all 0.2s ease;
            background: white;
        }
        
        .event-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        
        .event-date {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }
        
        .event-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-aktif {
            background: #dcfce7;
            color: #166534;
        }
        
        .event-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .event-description {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        
        .event-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 12px;
            color: #64748b;
        }
        
        .event-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .shortcut-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
        }
        
        .shortcut-section h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 16px;
        }
        
        .shortcut-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        
        .shortcut-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: white;
            padding: 16px 20px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .shortcut-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            color: white;
            text-decoration: none;
        }
        
        /* Modal Styles */
        .modal-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: none;
        }
        
        .btn-primary-modal {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        
        .btn-primary-modal:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            color: white;
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
            
            .welcome-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .shortcut-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-university"></i> UACAD
        </div>
        
        <!-- Organization Info -->
        <div class="org-info text-center mb-3" style="padding: 0 20px;">
            <div class="org-avatar mb-2" style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; color: white; font-weight: bold;">
                <?= $org_initials ?>
            </div>
            <div style="color: rgba(255,255,255,0.9); font-size: 12px; line-height: 1.3;">
                <?= htmlspecialchars(substr($org_data['nama_organisasi'] ?? 'Organisasi', 0, 20)) ?>
                <?= strlen($org_data['nama_organisasi'] ?? '') > 20 ? '...' : '' ?>
            </div>
            <div style="color: rgba(255,255,255,0.7); font-size: 11px;">
                <?= htmlspecialchars($org_data['jenis_organisasi'] ?? '') ?>
            </div>
        </div>
        
        <nav class="nav flex-column">
            <a href="<?= BASE_URL ?>organisasi/dashboard" class="nav-link active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="<?= BASE_URL ?>organisasi/events" class="nav-link">
                <i class="fas fa-calendar-check"></i> Kelola Event
            </a>
            <a href="<?= BASE_URL ?>organisasi/participants" class="nav-link">
                <i class="fas fa-users"></i> Peserta
            </a>
            <a href="<?= BASE_URL ?>organisasi/analytics" class="nav-link">
                <i class="fas fa-chart-line"></i> Analitik
            </a>
            <a href="<?= BASE_URL ?>organisasi/reports" class="nav-link">
                <i class="fas fa-file-alt"></i> Laporan
            </a>
            <a href="<?= BASE_URL ?>organisasi/profile" class="nav-link">
                <i class="fas fa-building"></i> Profil Organisasi
            </a>
            <a href="<?= BASE_URL ?>auth/logout" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
        
        <!-- Footer -->
        <div style="position: absolute; bottom: 20px; left: 20px; right: 20px; text-align: center; color: rgba(255,255,255,0.6); font-size: 11px;">
            <div>Logged in as:</div>
            <div style="font-weight: 600; color: rgba(255,255,255,0.8);">
                <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="welcome-section">
                <div class="welcome-text">
                    <h1>Dashboard <?= htmlspecialchars($org_data['nama_organisasi']) ?></h1>
                    <p>Kelola event dan kegiatan organisasi Anda dengan mudah</p>
                </div>
                <div class="profile-section">
                    <div class="profile-img"><?= $org_initials ?></div>
                    <div>
                        <div class="profile-name"><?= htmlspecialchars($org_data['username']) ?></div>
                        <div class="profile-role"><?= htmlspecialchars($org_data['jenis_organisasi']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stats-card">
                <div class="stats-header">
                    <div>
                        <div class="stats-value"><?= $stats['total_events'] ?></div>
                        <div class="stats-label">Total Event Dibuat</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                </div>
            </div>
            <div class="stats-card">
                <div class="stats-header">
                    <div>
                        <div class="stats-value"><?= $stats['total_capacity'] ?></div>
                        <div class="stats-label">Total Kapasitas</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
            <div class="stats-card">
                <div class="stats-header">
                    <div>
                        <div class="stats-value"><?= $stats['active_events'] ?></div>
                        <div class="stats-label">Event Aktif</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="stats-card">
                <div class="stats-header">
                    <div>
                        <div class="stats-value">4.8</div>
                        <div class="stats-label">Rating Organisasi</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
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
                            <h3><i class="fas fa-trending-up"></i> Trend Event (6 Bulan)</h3>
                        </div>
                        <canvas id="trendChart" class="chart-canvas"></canvas>
                    </div>
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-pie"></i> Kategori Event</h3>
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
                        <a href="<?= BASE_URL ?>organisasi/events" class="shortcut-btn">
                            <i class="fas fa-calendar-check"></i> Lihat Semua Event
                        </a>
                        <a href="<?= BASE_URL ?>organisasi/analytics" class="shortcut-btn">
                            <i class="fas fa-chart-line"></i> Analitik
                        </a>
                        <a href="<?= BASE_URL ?>organisasi/profile" class="shortcut-btn">
                            <i class="fas fa-building"></i> Edit Profil
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Column - Events -->
            <div class="events-panel">
                <div class="events-header">
                    <h3><i class="fas fa-fire" style="color: #667eea; margin-right: 8px;"></i>Event Aktif</h3>
                </div>
                <div class="events-list">
                    <?php if (empty($active_events)): ?>
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
                        <?php foreach ($active_events as $event): ?>
                        <div class="event-item">
                            <div class="event-header">
                                <div class="event-date">
                                    <?= date('d M Y', strtotime($event['tanggal_mulai'] ?? $event['created_at'])) ?>
                                </div>
                                <span class="event-status status-<?= $event['status'] ?>">
                                    <?= ucfirst($event['status']) ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($event['poster'])): ?>
                            <div class="event-poster mb-2">
                                <img src="<?= BASE_URL ?>uploads/posters/<?= $event['poster'] ?>" 
                                     alt="Poster"
                                     style="width: 100%; height: 100px; object-fit: cover; border-radius: 8px;"
                                     onerror="this.style.display='none'">
                            </div>
                            <?php endif; ?>
                            
                            <div class="event-title"><?= htmlspecialchars($event['nama_event']) ?></div>
                            <div class="event-description">
                                <?= htmlspecialchars(substr($event['deskripsi'] ?? '', 0, 80)) ?>...
                            </div>
                            <div class="event-meta">
                                <span><i class="fas fa-users"></i> <?= $event['kapasitas'] ?> peserta</span>
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['lokasi']) ?></span>
                            </div>
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
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle"></i> Buat Event Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
                </div>

                <form id="createEventForm" enctype="multipart/form-data">
                    <div class="modal-body">
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <label for="kapasitas" class="form-label fw-bold">
                                    Kapasitas <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="kapasitas" name="kapasitas" 
                                       min="1" max="10000" placeholder="100" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="deskripsi" class="form-label fw-bold">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" 
                                          placeholder="Jelaskan detail event..."></textarea>
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
                        
                        <!-- Location -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="lokasi" class="form-label fw-bold">
                                    Lokasi <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="lokasi" name="lokasi" 
                                       placeholder="Contoh: Aula Universitas" required>
                            </div>
                        </div>

                        <!-- Upload Poster -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="poster_event" class="form-label fw-bold">Upload Poster Event</label>
                                <input type="file" class="form-control" id="poster_event" name="poster_event" 
                                       accept=".jpg,.jpeg,.png,.pdf">
                                <div class="form-text">
                                    <i class="fas fa-info-circle text-primary"></i> 
                                    Format: JPG, PNG, PDF (Max: 5MB) - Opsional
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Status Event</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check p-3 border rounded">
                                            <input class="form-check-input" type="radio" name="status" id="status_draft" value="draft" checked>
                                            <label class="form-check-label fw-bold" for="status_draft">
                                                <i class="fas fa-edit text-secondary"></i> Draft
                                            </label>
                                            <br><small class="text-muted">Belum dipublikasi</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check p-3 border rounded">
                                            <input class="form-check-input" type="radio" name="status" id="status_aktif" value="aktif">
                                            <label class="form-check-label fw-bold" for="status_aktif">
                                                <i class="fas fa-play text-success"></i> Aktif
                                            </label>
                                            <br><small class="text-muted">Siap menerima pendaftaran</small>
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
                        <button type="submit" class="btn btn-primary-modal">
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

        // Sample data untuk demo - dalam implementasi nyata, ambil dari PHP
        const dashboardData = {
            trendData: <?= json_encode($trend_data ?? []) ?>,
            categoryData: <?= json_encode($category_data ?? []) ?>
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
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.95)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#667eea',
                        borderWidth: 1,
                        cornerRadius: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#94a3b8' },
                        grid: { color: 'rgba(148, 163, 184, 0.1)' }
                    },
                    x: {
                        ticks: { color: '#94a3b8' },
                        grid: { display: false }
                    }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        let categoryLabels, categoryValues, categoryColors;
        
        if (dashboardData.categoryData.length > 0) {
            categoryLabels = dashboardData.categoryData.map(item => item.kategori);
            categoryValues = dashboardData.categoryData.map(item => item.count);
            categoryColors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe'];
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
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 20, usePointStyle: true, font: { size: 13 } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.95)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#667eea',
                        borderWidth: 1,
                        cornerRadius: 12
                    }
                }
            }
        });

        // Modal Event Handlers
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('createEventForm');
            const alertContainer = document.getElementById('alertContainer');
            
            // Set minimum date to today
            const now = new Date();
            const currentDateTime = now.toISOString().slice(0, 16);
            document.getElementById('tanggal_mulai').min = currentDateTime;
            document.getElementById('tanggal_selesai').min = currentDateTime;
            
            // Auto-update end date minimum when start date changes
            document.getElementById('tanggal_mulai').addEventListener('change', function() {
                document.getElementById('tanggal_selesai').min = this.value;
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Show loading
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                submitBtn.disabled = true;
                alertContainer.innerHTML = '';
                
                // Create FormData for file upload
                const formData = new FormData(form);
                
                // Send to server
                fetch('<?= BASE_URL ?>organisasi/dashboard', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alertContainer.innerHTML = `
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> ${data.message}
                            </div>
                        `;
                        form.reset();
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('createEventModal'));
                            modal.hide();
                            location.reload(); // Refresh to show new event
                        }, 1500);
                    } else {
                        alertContainer.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    alertContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan sistem!
                        </div>
                    `;
                })
                .finally(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });
        });

        // Dashboard loaded successfully dengan data terintegrasi
        console.log('Dashboard loaded successfully!');
        console.log('Organization data:', <?= json_encode($org_data) ?>);
        console.log('Stats:', <?= json_encode($stats) ?>);
        console.log('Active events count:', <?= count($active_events ?? []) ?>);
        
        // Real-time stats update
        document.addEventListener('DOMContentLoaded', function() {
            updateRealTimeStats();
        });
        
        function updateRealTimeStats() {
            // Stats yang konsisten dengan halaman Events
            const realStats = <?= json_encode($stats) ?>;
            const events = <?= json_encode($active_events ?? []) ?>;
            
            console.log('Real stats from integrated data:', realStats);
            console.log('Total events from database:', events.length);
        }
    </script>
</body>
</html>