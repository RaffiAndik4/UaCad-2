<?php
// app/views/mahasiswa/dashboard.php

$mahasiswa_data = $mahasiswa_data ?? ['nama_lengkap' => 'Mahasiswa', 'nim' => 'N/A', 'fakultas' => 'N/A', 'jurusan' => 'N/A'];
$stats = $stats ?? ['total_events_registered' => 0, 'events_attended' => 0, 'upcoming_events' => 0, 'certificates_earned' => 0];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - UACAD</title>
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
            color: rgba(255, 255, 255, 0.85);
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
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(4px);
            text-decoration: none;
        }

        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.25);
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

        .header-card {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stats-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .stats-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 16px;
        }

        .stats-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .stats-card .label {
            color: #64748b;
            font-size: 14px;
        }

        .content-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i> UACAD
        </div>

       <a href="<?= BASE_URL ?>mahasiswa/landing" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="<?= BASE_URL ?>mahasiswa/dashboard" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="<?= BASE_URL ?>mahasiswa/kegiatan" class="nav-link"><i class="fas fa-calendar-check"></i> Kegiatan</a>
            <a href="<?= BASE_URL ?>mahasiswa/jadwal" class="nav-link "><i class="fas fa-calendar-alt"></i> Jadwal</a>
            <a href="<?= BASE_URL ?>mahasiswa/aspirasi" class="nav-link"><i class="fas fa-lightbulb"></i> Aspirasi</a>
            <a href="<?= BASE_URL ?>mahasiswa/profile" class="nav-link"><i class="fas fa-user"></i> Profil</a>
            <a href="<?= BASE_URL ?>auth/logout" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header-card">
            <h1>Selamat Datang, <?= htmlspecialchars($mahasiswa_data['nama_lengkap']) ?>!</h1>
            <p>NIM: <?= htmlspecialchars($mahasiswa_data['nim']) ?> | Fakultas:
                <?= htmlspecialchars($mahasiswa_data['fakultas']) ?></p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="value"><?= $stats['total_events_registered'] ?></div>
                <div class="label">Event Terdaftar</div>
            </div>
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="value"><?= $stats['events_attended'] ?></div>
                <div class="label">Event Dihadiri</div>
            </div>
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="value"><?= $stats['upcoming_events'] ?></div>
                <div class="label">Event Mendatang</div>
            </div>
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="value"><?= $stats['certificates_earned'] ?></div>
                <div class="label">Sertifikat</div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="content-card">
            <h5><i class="fas fa-clock text-primary me-2"></i>Aktivitas Terbaru</h5>
            <?php if (!empty($recent_events)): ?>
                <?php foreach ($recent_events as $event): ?>
                    <div class="border-start border-success ps-3 mb-3">
                        <h6><?= htmlspecialchars($event['nama_event']) ?></h6>
                        <small class="text-muted">
                            <?= htmlspecialchars($event['nama_organisasi']) ?> •
                            <?= date('d M Y', strtotime($event['tanggal_mulai'])) ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">Belum ada aktivitas terbaru</p>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="content-card">
            <h5><i class="fas fa-bolt text-warning me-2"></i>Aksi Cepat</h5>
            <div class="row">
                <div class="col-md-3">
                    <a href="<?= BASE_URL ?>mahasiswa/kegiatan" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-plus"></i> Daftar Event
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= BASE_URL ?>mahasiswa/jadwal" class="btn btn-info w-100 mb-2">
                        <i class="fas fa-calendar"></i> Lihat Jadwal
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= BASE_URL ?>mahasiswa/prestasi" class="btn btn-warning w-100 mb-2">
                        <i class="fas fa-trophy"></i> Input Prestasi
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= BASE_URL ?>mahasiswa/profile" class="btn btn-secondary w-100 mb-2">
                        <i class="fas fa-user"></i> Edit Profil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>