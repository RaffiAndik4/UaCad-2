<?php
// app/views/mahasiswa/aspirasi.php
$mahasiswa_data = $mahasiswa_data ?? ['nama_lengkap' => 'Mahasiswa'];
$aspirasi_list = $aspirasi_list ?? [];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aspirasi Event - UACAD</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
            z-index: 1000;
        }

        .sidebar .logo {
            text-align: center;
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin: 24px 0 40px 0;
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

        .page-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 32px;
            border-radius: 16px;
            margin-bottom: 24px;
        }

        .aspirasi-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .vote-btn {
            border: none;
            background: none;
            color: #64748b;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .vote-btn:hover {
            background: #f1f5f9;
            color: #ef4444;
        }

        .vote-btn.voted {
            background: #fef2f2;
            color: #ef4444;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo"><i class="fas fa-graduation-cap"></i> UACAD</div>
        <nav class="nav flex-column">
            <a href="<?= BASE_URL ?>mahasiswa/landing" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="<?= BASE_URL ?>mahasiswa/dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="<?= BASE_URL ?>mahasiswa/kegiatan" class="nav-link"><i class="fas fa-calendar-check"></i> Kegiatan</a>
            <a href="<?= BASE_URL ?>mahasiswa/jadwal" class="nav-link"><i class="fas fa-calendar-alt"></i> Jadwal</a>
            <a href="<?= BASE_URL ?>mahasiswa/aspirasi" class="nav-link active"><i class="fas fa-lightbulb"></i> Aspirasi</a>
            <a href="<?= BASE_URL ?>mahasiswa/profile" class="nav-link"><i class="fas fa-user"></i> Profil</a>
            <a href="<?= BASE_URL ?>auth/logout" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-lightbulb me-2"></i>Aspirasi Event</h1>
                    <p class="mb-0 opacity-90">Sampaikan ide event yang Anda inginkan</p>
                </div>
                <a href="<?= BASE_URL ?>mahasiswa/createAspirai" class="btn btn-light">
                    <i class="fas fa-plus"></i> Buat Aspirasi
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Aspirasi List -->
        <div class="row">
            <div class="col-12">
                <?php if (!empty($aspirasi_list)): ?>
                    <?php foreach ($aspirasi_list as $aspirasi): ?>
                        <div class="aspirasi-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex gap-2 mb-2">
                                        <span class="badge bg-primary"><?= ucfirst($aspirasi['kategori_event']) ?></span>
                                        <span
                                            class="badge bg-<?= $aspirasi['status'] === 'pending' ? 'warning' : ($aspirasi['status'] === 'approved' ? 'success' : 'secondary') ?>">
                                            <?= ucfirst($aspirasi['status']) ?>
                                        </span>
                                        <span
                                            class="badge bg-info"><?= ucfirst(str_replace('_', ' ', $aspirasi['urgency'])) ?></span>
                                    </div>
                                    <h5><?= htmlspecialchars($aspirasi['judul']) ?></h5>
                                    <p class="text-muted"><?= htmlspecialchars(substr($aspirasi['deskripsi'], 0, 150)) ?>...</p>
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> <?= htmlspecialchars($aspirasi['pengusul_nama']) ?> •
                                        <i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($aspirasi['created_at'])) ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <button class="vote-btn <?= $aspirasi['has_voted'] ? 'voted' : '' ?>"
                                        onclick="voteAspirai(<?= $aspirasi['id'] ?>)">
                                        <i class="fas fa-heart"></i>
                                        <span class="vote-count"><?= $aspirasi['vote_count'] ?? 0 ?></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-lightbulb text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Belum Ada Aspirasi</h4>
                        <p class="text-muted">Jadilah yang pertama membuat aspirasi event!</p>
                        <a href="<?= BASE_URL ?>mahasiswa/createAspirai" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Buat Aspirasi Pertama
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function voteAspirai(aspirasiId) {
            fetch('<?= BASE_URL ?>mahasiswa/aspirasi', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=vote&aspirasi_id=${aspirasiId}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const voteBtn = document.querySelector(`[onclick="voteAspirai(${aspirasiId})"]`);
                        const voteCount = voteBtn.querySelector('.vote-count');

                        if (data.action === 'voted') {
                            voteBtn.classList.add('voted');
                        } else {
                            voteBtn.classList.remove('voted');
                        }

                        voteCount.textContent = data.vote_count;

                        // Show alert
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show';
                        alertDiv.innerHTML = `<i class="fas fa-check-circle me-2"></i>${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                        document.querySelector('.main-content').insertBefore(alertDiv, document.querySelector('.row'));
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan sistem');
                });
        }
    </script>
</body>

</html>