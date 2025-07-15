<?php
// app/views/mahasiswa/create_aspirasi.php
$mahasiswa_data = $mahasiswa_data ?? ['nama_lengkap' => 'Mahasiswa'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Aspirasi - UACAD</title>
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
        .page-header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 32px; border-radius: 16px; margin-bottom: 24px; }
        .form-card { background: white; border-radius: 16px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
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
                    <h1><i class="fas fa-plus-circle me-2"></i>Buat Aspirasi Event</h1>
                    <p class="mb-0 opacity-90">Sampaikan ide event yang Anda inginkan</p>
                </div>
                <a href="<?= BASE_URL ?>mahasiswa/aspirasi" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Form -->
        <div class="form-card">
            <form method="POST" action="<?= BASE_URL ?>mahasiswa/createAspirai">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Judul Aspirasi <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" 
                                   placeholder="Contoh: Workshop Digital Marketing" 
                                   value="<?= $_POST['judul'] ?? '' ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori_event" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <option value="seminar" <?= ($_POST['kategori_event'] ?? '') === 'seminar' ? 'selected' : '' ?>>Seminar</option>
                                <option value="workshop" <?= ($_POST['kategori_event'] ?? '') === 'workshop' ? 'selected' : '' ?>>Workshop</option>
                                <option value="kompetisi" <?= ($_POST['kategori_event'] ?? '') === 'kompetisi' ? 'selected' : '' ?>>Kompetisi</option>
                                <option value="pelatihan" <?= ($_POST['kategori_event'] ?? '') === 'pelatihan' ? 'selected' : '' ?>>Pelatihan</option>
                                <option value="lainnya" <?= ($_POST['kategori_event'] ?? '') === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                    <textarea name="deskripsi" class="form-control" rows="5" 
                              placeholder="Jelaskan detail event yang Anda inginkan..." required><?= $_POST['deskripsi'] ?? '' ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Sasaran Peserta</label>
                            <input type="text" name="sasaran_peserta" class="form-control" 
                                   placeholder="Contoh: Mahasiswa IT" 
                                   value="<?= $_POST['sasaran_peserta'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Urgensi <span class="text-danger">*</span></label>
                            <select name="urgency" class="form-select" required>
                                <option value="">Pilih Urgensi</option>
                                <option value="rendah" <?= ($_POST['urgency'] ?? '') === 'rendah' ? 'selected' : '' ?>>Rendah</option>
                                <option value="sedang" <?= ($_POST['urgency'] ?? '') === 'sedang' ? 'selected' : '' ?>>Sedang</option>
                                <option value="tinggi" <?= ($_POST['urgency'] ?? '') === 'tinggi' ? 'selected' : '' ?>>Tinggi</option>
                                <option value="sangat_tinggi" <?= ($_POST['urgency'] ?? '') === 'sangat_tinggi' ? 'selected' : '' ?>>Sangat Tinggi</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Kirim Aspirasi
                    </button>
                    <a href="<?= BASE_URL ?>mahasiswa/aspirasi" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>