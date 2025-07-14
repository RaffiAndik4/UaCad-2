<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Organisasi - UACAD</title>
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
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(102, 126, 234, 0.15);
        }
        
        .main-content {
            margin-left: 260px;
            padding: 24px;
            min-height: 100vh;
        }
        
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
        }
        
        .org-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0 auto 20px;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php 
    $current_page = 'profile';
    include '../app/views/layouts/organisasi_sidebar.php'; 
    ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="profile-card">
            <h2>Profil Organisasi</h2>
            <p class="text-muted">Kelola informasi dan pengaturan organisasi Anda</p>
        </div>

        <div class="row">
            <!-- Profile Info -->
            <div class="col-md-4">
                <div class="profile-card text-center">
                    <?php
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
                    if (empty($org_initials)) $org_initials = 'ORG';
                    ?>
                    <div class="org-avatar"><?= $org_initials ?></div>
                    <h4><?= htmlspecialchars($org_data['nama_organisasi'] ?? 'Organisasi') ?></h4>
                    <p class="text-muted"><?= htmlspecialchars($org_data['jenis_organisasi'] ?? '') ?></p>
                    
                    <div class="mt-4">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="fw-bold text-primary"><?= count($all_events) ?></div>
                                <small class="text-muted">Total Event</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-success"><?= $stats['aktif'] ?></div>
                                <small class="text-muted">Event Aktif</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-info"><?= $stats['selesai'] ?></div>
                                <small class="text-muted">Selesai</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <span class="badge bg-<?= ($org_data['status_verifikasi'] ?? 'pending') === 'verified' ? 'success' : 'warning' ?> fs-6">
                            <?= ucfirst($org_data['status_verifikasi'] ?? 'pending') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="col-md-8">
                <div class="profile-card">
                    <h5><i class="fas fa-edit text-primary me-2"></i>Edit Profil Organisasi</h5>
                    
                    <form id="profileForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nama Organisasi</label>
                                <input type="text" class="form-control" name="nama_organisasi" 
                                       value="<?= htmlspecialchars($org_data['nama_organisasi'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jenis Organisasi</label>
                                <select class="form-select" name="jenis_organisasi" required>
                                    <option value="">Pilih Jenis</option>
                                    <option value="BEM" <?= ($org_data['jenis_organisasi'] ?? '') === 'BEM' ? 'selected' : '' ?>>Badan Eksekutif Mahasiswa</option>
                                    <option value="DPM" <?= ($org_data['jenis_organisasi'] ?? '') === 'DPM' ? 'selected' : '' ?>>Dewan Perwakilan Mahasiswa</option>
                                    <option value="Himpunan" <?= ($org_data['jenis_organisasi'] ?? '') === 'Himpunan' ? 'selected' : '' ?>>Himpunan Mahasiswa</option>
                                    <option value="UKM" <?= ($org_data['jenis_organisasi'] ?? '') === 'UKM' ? 'selected' : '' ?>>Unit Kegiatan Mahasiswa</option>
                                    <option value="Komunitas" <?= ($org_data['jenis_organisasi'] ?? '') === 'Komunitas' ? 'selected' : '' ?>>Komunitas</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi Organisasi</label>
                            <textarea class="form-control" name="deskripsi" rows="4" 
                                      placeholder="Deskripsikan organisasi Anda..."><?= htmlspecialchars($org_data['deskripsi'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?= htmlspecialchars($org_data['email'] ?? '') ?>" required>
                            </div>
                            <div