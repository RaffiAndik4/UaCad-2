<?php
// app/views/mahasiswa/kegiatan.php

$mahasiswa_data = $mahasiswa_data ?? ['nama_lengkap' => 'Mahasiswa'];
$available_events = $available_events ?? [];
$registered_events = $registered_events ?? [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kegiatan & Event - UACAD</title>
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
            padding: 0 20px;
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
        
        .btn-register {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
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
            <a href="<?= BASE_URL ?>mahasiswa/dashboard" class="nav-link">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="<?= BASE_URL ?>mahasiswa/kegiatan" class="nav-link active">
                <i class="fas fa-calendar-check"></i> Kegiatan
            </a>
            <a href="<?= BASE_URL ?>mahasiswa/jadwal" class="nav-link">
                <i class="fas fa-calendar-alt"></i> Jadwal
            </a>
            <a href="<?= BASE_URL ?>mahasiswa/organisasi" class="nav-link">
                <i class="fas fa-users"></i> Organisasi
            </a>
            <a href="<?= BASE_URL ?>mahasiswa/prestasi" class="nav-link">
                <i class="fas fa-trophy"></i> Prestasi
            </a>
            <a href="<?= BASE_URL ?>mahasiswa/profile" class="nav-link">
                <i class="fas fa-user"></i> Profil
            </a>
            <a href="<?= BASE_URL ?>auth/logout" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <h2>Kegiatan & Event</h2>
            <p class="text-muted">Temukan dan daftar event menarik di kampus</p>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="eventTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="available-tab" data-bs-toggle="tab" data-bs-target="#available-events" type="button">
                    <i class="fas fa-calendar-plus me-2"></i>Event Tersedia
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="registered-tab" data-bs-toggle="tab" data-bs-target="#registered-events" type="button">
                    <i class="fas fa-check-circle me-2"></i>Event Terdaftar
                </button>
            </li>
        </ul>

        <div class="tab-content" id="eventTabContent">
            <!-- Available Events -->
            <div class="tab-pane fade show active" id="available-events" role="tabpanel">
                <div class="mt-3">
                    <?php if (!empty($available_events)): ?>
                        <?php foreach ($available_events as $event): ?>
                        <div class="event-card">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5><?= htmlspecialchars($event['nama_event']) ?></h5>
                                    <p class="text-muted mb-2"><?= htmlspecialchars($event['deskripsi']) ?></p>
                                    <div class="d-flex gap-3 text-sm">
                                        <span><i class="fas fa-building text-primary"></i> <?= htmlspecialchars($event['nama_organisasi']) ?></span>
                                        <span><i class="fas fa-calendar text-success"></i> <?= date('d M Y', strtotime($event['tanggal_mulai'])) ?></span>
                                        <span><i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($event['lokasi']) ?></span>
                                        <span><i class="fas fa-users text-warning"></i> <?= $event['remaining_slots'] ?? $event['kapasitas'] ?> slot tersisa</span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge bg-success mb-2"><?= ucfirst($event['kategori']) ?></span><br>
                                    <button class="btn btn-register" onclick="registerEvent(<?= $event['id'] ?>)">
                                        <i class="fas fa-plus"></i> Daftar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Belum Ada Event Tersedia</h5>
                            <p class="text-muted">Event baru akan muncul di sini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Registered Events -->
            <div class="tab-pane fade" id="registered-events" role="tabpanel">
                <div class="mt-3">
                    <?php if (!empty($registered_events)): ?>
                        <?php foreach ($registered_events as $event): ?>
                        <div class="event-card">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5><?= htmlspecialchars($event['nama_event']) ?></h5>
                                    <p class="text-muted mb-2"><?= htmlspecialchars($event['deskripsi']) ?></p>
                                    <div class="d-flex gap-3 text-sm">
                                        <span><i class="fas fa-building text-primary"></i> <?= htmlspecialchars($event['nama_organisasi']) ?></span>
                                        <span><i class="fas fa-calendar text-success"></i> <?= date('d M Y', strtotime($event['tanggal_mulai'])) ?></span>
                                        <span><i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($event['lokasi']) ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge bg-success mb-2"><?= ucfirst($event['participation_status'] ?? 'terdaftar') ?></span><br>
                                    <button class="btn btn-outline-danger btn-sm" onclick="cancelRegistration(<?= $event['id'] ?>)">
                                        <i class="fas fa-times"></i> Batal
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Belum Terdaftar Event</h5>
                            <p class="text-muted">Event yang Anda daftari akan muncul di sini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function registerEvent(eventId) {
            if (confirm('Daftar event ini?')) {
                fetch('<?= BASE_URL ?>mahasiswa/kegiatan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=register_event&event_id=${eventId}`
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan sistem!');
                });
            }
        }
        
        function cancelRegistration(eventId) {
            if (confirm('Batalkan pendaftaran event ini?')) {
                fetch('<?= BASE_URL ?>mahasiswa/kegiatan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=cancel_registration&event_id=${eventId}`
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan sistem!');
                });
            }
        }
    </script>
</body>
</html>