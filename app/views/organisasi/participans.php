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
$org_data = $org_data ?? ['nama_organisasi' => 'Organisasi', 'username' => 'Admin', 'jenis_organisasi' => 'Organisasi'];
$participants = $participants ?? [];
$event = $event ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Peserta - UACAD</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
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
            box-shadow: 4px 0 20px rgba(102, 126, 234, 0.15);
            overflow-y: auto;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 24px;
            min-height: 100vh;
        }
        
        .page-header {
            background: white;
            padding: 24px 28px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 28px;
            border: 1px solid #e2e8f0;
        }
        
        .page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
        }
        
        .page-title h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .page-subtitle {
            color: #64748b;
            font-size: 15px;
            margin-top: 4px;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            color: white;
            text-decoration: none;
        }
        
        .btn-secondary-custom {
            background: #f8f9fa;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            color: #374151;
            padding: 12px 20px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-secondary-custom:hover {
            background: #e9ecef;
            color: #1f2937;
            text-decoration: none;
        }
        
        .content-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
            overflow: hidden;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 20px 24px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .card-header-custom h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
        }
        
        .card-header-custom i {
            color: #667eea;
            margin-right: 8px;
        }
        
        .card-body-custom {
            padding: 24px;
        }
        
        .event-info-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            align-items: start;
        }
        
        .event-details h5 {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .event-description {
            color: #64748b;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        
        .event-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            font-size: 14px;
        }
        
        .meta-item strong {
            color: #374151;
            margin-right: 8px;
            min-width: 80px;
        }
        
        .meta-item span {
            color: #64748b;
        }
        
        .event-stats {
            text-align: center;
        }
        
        .stats-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: white;
        }
        
        .stats-number {
            font-size: 28px;
            font-weight: 700;
            line-height: 1;
        }
        
        .stats-label {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 4px;
        }
        
        .capacity-bar {
            background: #f1f5f9;
            border-radius: 8px;
            height: 8px;
            margin: 16px 0;
            overflow: hidden;
        }
        
        .capacity-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            border-radius: 8px;
            transition: width 0.3s ease;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
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
            background: #e0e7ff;
            color: #3730a3;
        }
        
        .participants-section {
            margin-top: 8px;
        }
        
        .participants-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .participants-actions {
            display: flex;
            gap: 12px;
        }
        
        .search-box {
            position: relative;
            max-width: 300px;
        }
        
        .search-box input {
            padding: 10px 16px 10px 40px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            width: 100%;
            font-size: 14px;
        }
        
        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        
        .filter-dropdown {
            min-width: 150px;
        }
        
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .table {
            margin: 0;
        }
        
        .table thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            color: #374151;
            font-weight: 600;
            padding: 16px;
            font-size: 14px;
        }
        
        .table tbody td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            font-size: 14px;
        }
        
        .table tbody tr:hover {
            background: #f8fafc;
        }
        
        .participant-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .participant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }
        
        .participant-details h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .participant-details small {
            color: #64748b;
            font-size: 12px;
        }
        
        .attendance-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .attendance-terdaftar {
            background: #fef3c7;
            color: #92400e;
        }
        
        .attendance-hadir {
            background: #dcfce7;
            color: #166534;
        }
        
        .attendance-tidak_hadir {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .action-buttons {
            display: flex;
            gap: 4px;
        }
        
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 12px;
        }
        
        .btn-view {
            background: #e0f2fe;
            color: #0277bd;
        }
        
        .btn-view:hover {
            background: #b3e5fc;
        }
        
        .btn-check {
            background: #e8f5e8;
            color: #2e7d32;
        }
        
        .btn-check:hover {
            background: #c8e6c9;
        }
        
        .btn-message {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .btn-message:hover {
            background: #e1bee7;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }
        
        .empty-state i {
            font-size: 48px;
            color: #cbd5e1;
            margin-bottom: 16px;
        }
        
        .empty-state h4 {
            color: #374151;
            margin-bottom: 8px;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 20px;
        }
        
        .quick-action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .quick-action-btn.secondary {
            background: #f8f9fa;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .quick-action-btn.secondary:hover {
            background: #e9ecef;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .event-info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
                padding: 16px;
            }
            
            .page-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .event-meta {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .participants-header {
                flex-direction: column;
                align-items: stretch;
                gap: 16px;
            }
            
            .participants-actions {
                flex-direction: column;
            }
            
            .search-box {
                max-width: none;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .table-responsive {
                font-size: 12px;
            }
            
            .participant-info {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php 
    $current_page = 'participants';
    include '../app/views/layouts/organisasi_sidebar.php'; 
    ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <div class="page-title">
                <div>
                    <h1>Kelola Peserta</h1>
                    <p class="page-subtitle">Kelola pendaftar dan kehadiran peserta event</p>
                </div>
                <a href="<?= BASE_URL ?>organisasi/events" class="btn-secondary-custom">
                    <i class="fas fa-arrow-left"></i> Kembali ke Event
                </a>
            </div>
        </div>
        
        <?php if ($event): ?>
            <!-- Event Info Card -->
            <div class="content-card">
                <div class="card-body-custom">
                    <div class="event-info-grid">
                        <div class="event-details">
                            <h5><?= htmlspecialchars($event['nama_event']) ?></h5>
                            <p class="event-description"><?= htmlspecialchars($event['deskripsi']) ?></p>
                            
                            <div class="event-meta">
                                <div class="meta-item">
                                    <strong><i class="fas fa-calendar text-primary"></i> Tanggal:</strong>
                                    <span><?= date('d M Y H:i', strtotime($event['tanggal_mulai'])) ?></span>
                                </div>
                                <div class="meta-item">
                                    <strong><i class="fas fa-map-marker-alt text-danger"></i> Lokasi:</strong>
                                    <span><?= htmlspecialchars($event['lokasi']) ?></span>
                                </div>
                                <div class="meta-item">
                                    <strong><i class="fas fa-tag text-warning"></i> Kategori:</strong>
                                    <span><?= htmlspecialchars($event['kategori']) ?></span>
                                </div>
                                <div class="meta-item">
                                    <strong><i class="fas fa-info-circle text-info"></i> Status:</strong>
                                    <span class="status-badge status-<?= $event['status'] ?>">
                                        <?= ucfirst($event['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="event-stats">
                            <?php 
                            $participantCount = count($participants);
                            $capacity = $event['kapasitas'];
                            $percentage = $capacity > 0 ? ($participantCount / $capacity) * 100 : 0;
                            ?>
                            <div class="stats-circle">
                                <div class="stats-number"><?= $participantCount ?></div>
                                <div class="stats-label">Peserta Terdaftar</div>
                            </div>
                            
                            <div style="font-size: 14px; color: #64748b; margin-bottom: 8px;">
                                Kapasitas: <?= $capacity ?> peserta
                            </div>
                            
                            <div class="capacity-bar">
                                <div class="capacity-fill" style="width: <?= min($percentage, 100) ?>%"></div>
                            </div>
                            
                            <div style="font-size: 12px; color: #64748b;">
                                <?= number_format($percentage, 1) ?>% terisi
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Participants Section -->
            <div class="content-card participants-section">
                <div class="card-header-custom">
                    <h5><i class="fas fa-users"></i> Daftar Peserta (<?= count($participants) ?>)</h5>
                </div>
                
                <div class="card-body-custom">
                    <?php if (!empty($participants)): ?>
                        <!-- Participants Actions -->
                        <div class="participants-header">
                            <div class="participants-actions">
                                <div class="search-box">
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="searchParticipants" placeholder="Cari peserta..." class="form-control">
                                </div>
                                <select id="filterStatus" class="form-select filter-dropdown">
                                    <option value="">Semua Status</option>
                                    <option value="terdaftar">Terdaftar</option>
                                    <option value="hadir">Hadir</option>
                                    <option value="tidak_hadir">Tidak Hadir</option>
                                </select>
                            </div>
                            
                            <div class="participants-actions">
                                <button class="btn-primary-custom" onclick="exportParticipants()">
                                    <i class="fas fa-download"></i> Export Data
                                </button>
                                <button class="btn-primary-custom" onclick="markAllAttendance()">
                                    <i class="fas fa-check-double"></i> Tandai Semua Hadir
                                </button>
                            </div>
                        </div>
                        
                        <!-- Participants Table -->
                        <div class="table-responsive">
                            <div class="table-container">
                                <table class="table" id="participantsTable">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Peserta</th>
                                            <th>NIM</th>
                                            <th>Fakultas/Jurusan</th>
                                            <th>Email</th>
                                            <th>Status Kehadiran</th>
                                            <th>Tanggal Daftar</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($participants as $index => $participant): ?>
                                        <tr data-status="<?= $participant['status'] ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <div class="participant-info">
                                                    <div class="participant-avatar">
                                                        <?= strtoupper(substr($participant['nama'], 0, 2)) ?>
                                                    </div>
                                                    <div class="participant-details">
                                                        <h6><?= htmlspecialchars($participant['nama']) ?></h6>
                                                        <small><?= htmlspecialchars($participant['nim']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($participant['nim']) ?></td>
                                            <td>
                                                <div><?= htmlspecialchars($participant['fakultas']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($participant['jurusan']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($participant['email']) ?></td>
                                            <td>
                                                <span class="attendance-badge attendance-<?= $participant['status'] ?>">
                                                    <?php
                                                    switch($participant['status']) {
                                                        case 'hadir': echo '<i class="fas fa-check"></i> Hadir'; break;
                                                        case 'tidak_hadir': echo '<i class="fas fa-times"></i> Tidak Hadir'; break;
                                                        default: echo '<i class="fas fa-clock"></i> Terdaftar'; break;
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y H:i', strtotime($participant['tanggal_daftar'])) ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn-action btn-view" onclick="viewParticipantDetail(<?= $participant['id'] ?>)" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($participant['status'] != 'hadir'): ?>
                                                    <button class="btn-action btn-check" onclick="markAttendance(<?= $participant['id'] ?>, '<?= $participant['nama'] ?>')" title="Tandai Hadir">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <button class="btn-action btn-message" onclick="sendMessage(<?= $participant['id'] ?>)" title="Kirim Pesan">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="quick-actions">
                            <button class="quick-action-btn" onclick="generateCertificates()">
                                <i class="fas fa-certificate"></i> Generate Sertifikat
                            </button>
                            <button class="quick-action-btn secondary" onclick="sendBulkEmail()">
                                <i class="fas fa-mail-bulk"></i> Kirim Email Massal
                            </button>
                            <button class="quick-action-btn secondary" onclick="printAttendanceList()">
                                <i class="fas fa-print"></i> Cetak Daftar Hadir
                            </button>
                            <button class="quick-action-btn secondary" onclick="viewAnalytics()">
                                <i class="fas fa-chart-bar"></i> Lihat Analitik
                            </button>
                        </div>
                        
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="empty-state">
                            <i class="fas fa-user-plus"></i>
                            <h4>Belum Ada Peserta</h4>
                            <p>Peserta yang mendaftar untuk event ini akan muncul di sini</p>
                            <div class="quick-actions">
                                <button class="quick-action-btn" onclick="shareEvent()">
                                    <i class="fas fa-share"></i> Bagikan Event
                                </button>
                                <button class="quick-action-btn secondary" onclick="promoteEvent()">
                                    <i class="fas fa-bullhorn"></i> Promosikan Event
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php else: ?>
            <!-- No Event Selected -->
            <div class="content-card">
                <div class="empty-state" style="padding: 80px 20px;">
                    <i class="fas fa-calendar-times"></i>
                    <h4>Pilih Event</h4>
                    <p>Silakan pilih event dari halaman event untuk melihat dan mengelola peserta</p>
                    <div style="margin-top: 24px;">
                        <a href="<?= BASE_URL ?>organisasi/events" class="btn-primary-custom">
                            <i class="fas fa-calendar"></i> Lihat Daftar Event
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Participant Detail Modal -->
    <div class="modal fade" id="participantDetailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-user"></i> Detail Peserta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
                </div>
                <div class="modal-body" id="participantDetailContent">
                    <!-- Dynamic content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn-primary-custom" onclick="markAttendanceFromModal()">
                        <i class="fas fa-check"></i> Tandai Hadir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Action Modal -->
    <div class="modal fade" id="bulkActionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-tasks"></i> Aksi Massal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Aksi:</label>
                        <select class="form-select" id="bulkActionType">
                            <option value="">Pilih aksi...</option>
                            <option value="mark_attended">Tandai Hadir</option>
                            <option value="mark_absent">Tandai Tidak Hadir</option>
                            <option value="send_email">Kirim Email</option>
                            <option value="export_data">Export Data</option>
                        </select>
                    </div>
                    <div class="mb-3" id="emailTemplateSection" style="display: none;">
                        <label class="form-label">Template Email:</label>
                        <select class="form-select" id="emailTemplate">
                            <option value="reminder">Reminder Event</option>
                            <option value="confirmation">Konfirmasi Pendaftaran</option>
                            <option value="certificate">Sertifikat Event</option>
                            <option value="custom">Custom Message</option>
                        </select>
                    </div>
                    <div class="mb-3" id="customMessageSection" style="display: none;">
                        <label class="form-label">Pesan Custom:</label>
                        <textarea class="form-control" id="customMessage" rows="4" placeholder="Tulis pesan Anda..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <span id="bulkActionInfo">Pilih aksi untuk melanjutkan</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn-primary-custom" onclick="executeBulkAction()">
                        <i class="fas fa-play"></i> Jalankan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Global variables
        let participantsTable;
        let selectedParticipant = null;
        const BASE_URL = '<?= BASE_URL ?>';
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializeDataTable();
            setupEventListeners();
            updateStats();
        });
        
        // Initialize DataTable
        function initializeDataTable() {
            if (document.getElementById('participantsTable')) {
                participantsTable = $('#participantsTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                    },
                    columnDefs: [
                        { orderable: false, targets: [0, 7] }, // No and Actions columns
                        { width: '5%', targets: 0 },
                        { width: '20%', targets: 1 },
                        { width: '10%', targets: 2 },
                        { width: '15%', targets: 3 },
                        { width: '15%', targets: 4 },
                        { width: '12%', targets: 5 },
                        { width: '13%', targets: 6 },
                        { width: '10%', targets: 7 }
                    ],
                    order: [[6, 'desc']], // Sort by registration date
                    drawCallback: function() {
                        // Re-attach event listeners after table redraw
                        attachActionListeners();
                    }
                });
                
                // Custom search
                document.getElementById('searchParticipants').addEventListener('keyup', function() {
                    participantsTable.search(this.value).draw();
                });
                
                // Status filter
                document.getElementById('filterStatus').addEventListener('change', function() {
                    const status = this.value;
                    if (status) {
                        participantsTable.column(5).search(status).draw();
                    } else {
                        participantsTable.column(5).search('').draw();
                    }
                });
            }
        }
        
        // Setup event listeners
        function setupEventListeners() {
            // Bulk action modal
            document.getElementById('bulkActionType').addEventListener('change', function() {
                const action = this.value;
                const emailSection = document.getElementById('emailTemplateSection');
                const customSection = document.getElementById('customMessageSection');
                const infoText = document.getElementById('bulkActionInfo');
                
                emailSection.style.display = 'none';
                customSection.style.display = 'none';
                
                switch(action) {
                    case 'mark_attended':
                        infoText.textContent = 'Semua peserta yang dipilih akan ditandai sebagai hadir';
                        break;
                    case 'mark_absent':
                        infoText.textContent = 'Semua peserta yang dipilih akan ditandai sebagai tidak hadir';
                        break;
                    case 'send_email':
                        emailSection.style.display = 'block';
                        infoText.textContent = 'Email akan dikirim ke semua peserta yang dipilih';
                        break;
                    case 'export_data':
                        infoText.textContent = 'Data peserta akan diekspor ke file Excel';
                        break;
                    default:
                        infoText.textContent = 'Pilih aksi untuk melanjutkan';
                }
            });
            
            // Email template change
            document.getElementById('emailTemplate').addEventListener('change', function() {
                const customSection = document.getElementById('customMessageSection');
                customSection.style.display = this.value === 'custom' ? 'block' : 'none';
            });
        }
        
        // Attach action listeners to buttons
        function attachActionListeners() {
            // This function will be called after DataTable redraws
            console.log('Action listeners attached');
        }
        
        // Update statistics
        function updateStats() {
            const totalParticipants = <?= count($participants) ?>;
            const attendedCount = <?= count(array_filter($participants, function($p) { return $p['status'] === 'hadir'; })) ?>;
            const capacity = <?= $event['kapasitas'] ?? 0 ?>;
            
            console.log(`Stats: ${totalParticipants} total, ${attendedCount} attended, ${capacity} capacity`);
        }
        
        // Participant management functions
        function viewParticipantDetail(participantId) {
            // Find participant data
            const participants = <?= json_encode($participants) ?>;
            const participant = participants.find(p => p.id == participantId);
            
            if (!participant) {
                showToast('Data peserta tidak ditemukan', 'error');
                return;
            }
            
            selectedParticipant = participant;
            
            // Build modal content
            const content = `
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="participant-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 24px;">
                            ${participant.nama.substring(0, 2).toUpperCase()}
                        </div>
                        <h5>${participant.nama}</h5>
                        <p class="text-muted">${participant.nim}</p>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Fakultas:</strong></td>
                                <td>${participant.fakultas}</td>
                            </tr>
                            <tr>
                                <td><strong>Jurusan:</strong></td>
                                <td>${participant.jurusan}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>${participant.email}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="attendance-badge attendance-${participant.status}">
                                        ${getStatusText(participant.status)}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Daftar:</strong></td>
                                <td>${formatDateTime(participant.tanggal_daftar)}</td>
                            </tr>
                            ${participant.tanggal_hadir ? `
                            <tr>
                                <td><strong>Tanggal Hadir:</strong></td>
                                <td>${formatDateTime(participant.tanggal_hadir)}</td>
                            </tr>
                            ` : ''}
                        </table>
                    </div>
                </div>
            `;
            
            document.getElementById('participantDetailContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('participantDetailModal')).show();
        }
        
        function markAttendance(participantId, participantName) {
            if (confirm(`Tandai ${participantName} sebagai hadir?`)) {
                showToast('Menandai kehadiran...', 'info');
                
                // Simulate API call
                setTimeout(() => {
                    // Update UI
                    const row = document.querySelector(`tr[data-participant-id="${participantId}"]`);
                    if (row) {
                        const statusCell = row.querySelector('.attendance-badge');
                        statusCell.className = 'attendance-badge attendance-hadir';
                        statusCell.innerHTML = '<i class="fas fa-check"></i> Hadir';
                        
                        // Hide attendance button
                        const checkBtn = row.querySelector('.btn-check');
                        if (checkBtn) {
                            checkBtn.style.display = 'none';
                        }
                    }
                    
                    showToast(`${participantName} berhasil ditandai hadir`, 'success');
                    updateStats();
                }, 1000);
            }
        }
        
        function markAttendanceFromModal() {
            if (selectedParticipant) {
                markAttendance(selectedParticipant.id, selectedParticipant.nama);
                bootstrap.Modal.getInstance(document.getElementById('participantDetailModal')).hide();
            }
        }
        
        function markAllAttendance() {
            const totalParticipants = <?= count($participants) ?>;
            
            if (confirm(`Tandai semua ${totalParticipants} peserta sebagai hadir?`)) {
                showToast('Menandai semua peserta hadir...', 'info');
                
                // Simulate API call
                setTimeout(() => {
                    // Update all attendance badges
                    document.querySelectorAll('.attendance-badge').forEach(badge => {
                        badge.className = 'attendance-badge attendance-hadir';
                        badge.innerHTML = '<i class="fas fa-check"></i> Hadir';
                    });
                    
                    // Hide all check buttons
                    document.querySelectorAll('.btn-check').forEach(btn => {
                        btn.style.display = 'none';
                    });
                    
                    showToast('Semua peserta berhasil ditandai hadir', 'success');
                    updateStats();
                }, 1500);
            }
        }
        
        function sendMessage(participantId) {
            showToast('Fitur kirim pesan sedang dalam pengembangan', 'info');
        }
        
        // Bulk actions
        function exportParticipants() {
            showToast('Mengekspor data peserta...', 'info');
            
            // Simulate export
            setTimeout(() => {
                showToast('Data peserta berhasil diekspor', 'success');
                
                // Create download link (simulation)
                const link = document.createElement('a');
                link.href = '#';
                link.download = 'peserta-event.xlsx';
                link.click();
            }, 2000);
        }
        
        function executeBulkAction() {
            const action = document.getElementById('bulkActionType').value;
            
            if (!action) {
                showToast('Pilih aksi terlebih dahulu', 'warning');
                return;
            }
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('bulkActionModal'));
            
            switch(action) {
                case 'mark_attended':
                    markAllAttendance();
                    modal.hide();
                    break;
                case 'mark_absent':
                    markAllAbsent();
                    modal.hide();
                    break;
                case 'send_email':
                    sendBulkEmail();
                    modal.hide();
                    break;
                case 'export_data':
                    exportParticipants();
                    modal.hide();
                    break;
            }
        }
        
        function markAllAbsent() {
            showToast('Menandai peserta tidak hadir...', 'info');
            
            setTimeout(() => {
                showToast('Status kehadiran berhasil diperbarui', 'success');
            }, 1000);
        }
        
        function sendBulkEmail() {
            const template = document.getElementById('emailTemplate').value;
            const customMessage = document.getElementById('customMessage').value;
            
            showToast('Mengirim email ke semua peserta...', 'info');
            
            setTimeout(() => {
                showToast('Email berhasil dikirim ke semua peserta', 'success');
            }, 2000);
        }
        
        // Quick actions
        function generateCertificates() {
            const attendedCount = <?= count(array_filter($participants, function($p) { return $p['status'] === 'hadir'; })) ?>;
            
            if (attendedCount === 0) {
                showToast('Tidak ada peserta yang hadir untuk dibuatkan sertifikat', 'warning');
                return;
            }
            
            showToast(`Membuat ${attendedCount} sertifikat...`, 'info');
            
            setTimeout(() => {
                showToast('Sertifikat berhasil dibuat dan dikirim ke peserta', 'success');
            }, 3000);
        }
        
        function printAttendanceList() {
            showToast('Menyiapkan daftar hadir...', 'info');
            
            setTimeout(() => {
                window.print();
            }, 1000);
        }
        
        function viewAnalytics() {
            window.location.href = `${BASE_URL}organisasi/analytics`;
        }
        
        function shareEvent() {
            showToast('Fitur berbagi event sedang dalam pengembangan', 'info');
        }
        
        function promoteEvent() {
            showToast('Fitur promosi event sedang dalam pengembangan', 'info');
        }
        
        // Utility functions
        function formatDateTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function getStatusText(status) {
            switch(status) {
                case 'hadir': return '<i class="fas fa-check"></i> Hadir';
                case 'tidak_hadir': return '<i class="fas fa-times"></i> Tidak Hadir';
                default: return '<i class="fas fa-clock"></i> Terdaftar';
            }
        }
        
        function showToast(message, type = 'info') {
            // Create toast element
            const toastId = 'toast-' + Date.now();
            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            
            const icon = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-triangle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            }[type] || 'fa-info-circle';
            
            toast.innerHTML = `
                <i class="fas ${icon}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(toast);
            
            // Auto remove after 4 seconds
            setTimeout(() => {
                const element = document.getElementById(toastId);
                if (element) {
                    element.remove();
                }
            }, 4000);
        }
        
        // Add to table rows for easier identification
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('#participantsTable tbody tr');
            rows.forEach((row, index) => {
                const participantId = <?= json_encode(array_column($participants, 'id')) ?>[index];
                if (participantId) {
                    row.setAttribute('data-participant-id', participantId);
                }
            });
        });
        
        console.log('Participants page loaded successfully!');
        console.log('Event:', <?= json_encode($event ?? null) ?>);
        console.log('Participants count:', <?= count($participants) ?>);
    </script>
</body>
</html>