<?php
// app/views/mahasiswa/jadwal.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal - UACAD</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: 260px; background: linear-gradient(180deg, #10b981 0%, #059669 100%); z-index: 1000; }
        .sidebar .logo { text-align: center; color: white; font-size: 24px; font-weight: 700; margin: 24px 0 40px 0; }
        .sidebar .nav-link { color: rgba(255,255,255,0.85); padding: 14px 24px; text-decoration: none; display: flex; align-items: center; transition: all 0.2s ease; margin: 2px 12px 2px 0; border-radius: 0 12px 12px 0; font-weight: 500; font-size: 14px; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.15); color: white; transform: translateX(4px); text-decoration: none; }
        .sidebar .nav-link.active { background: rgba(255,255,255,0.25); color: white; font-weight: 600; }
        .sidebar .nav-link i { margin-right: 12px; width: 18px; text-align: center; }
        .main-content { margin-left: 260px; padding: 24px; min-height: 100vh; }
        .page-header { background: white; padding: 24px; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 24px; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo"><i class="fas fa-graduation-cap"></i> UACAD</div>
        <nav class="nav flex-column">
            <a href="<?= BASE_URL ?>mahasiswa/dashboard" class="nav-link"><i class="fas fa-home"></i> Dashboard</a>
            <a href="<?= BASE_URL ?>mahasiswa/kegiatan" class="nav-link"><i class="fas fa-calendar-check"></i> Kegiatan</a>
            <a href="<?= BASE_URL ?>mahasiswa/jadwal" class="nav-link active"><i class="fas fa-calendar-alt"></i> Jadwal</a>
            <a href="<?= BASE_URL ?>mahasiswa/organisasi" class="nav-link"><i class="fas fa-users"></i> Organisasi</a>
            <a href="<?= BASE_URL ?>mahasiswa/prestasi" class="nav-link"><i class="fas fa-trophy"></i> Prestasi</a>
            <a href="<?= BASE_URL ?>mahasiswa/profile" class="nav-link"><i class="fas fa-user"></i> Profil</a>
            <a href="<?= BASE_URL ?>auth/logout" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>Jadwal Saya</h2>
            <p class="text-muted">Kelola jadwal kuliah dan event Anda</p>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-alt text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">Fitur Jadwal</h4>
                            <p class="text-muted">Fitur jadwal akan segera tersedia</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

