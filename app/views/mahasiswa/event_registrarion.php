<?php
// app/views/mahasiswa/event_registration.php
// Form pendaftaran event untuk mahasiswa

$event = $event ?? null;
$mahasiswa_data = $mahasiswa_data ?? ['nama_lengkap' => 'Mahasiswa', 'nim' => '', 'fakultas' => '', 'jurusan' => ''];

if (!$event) {
    header('Location: ' . BASE_URL . 'mahasiswa/kegiatan');
    exit();
}

// Check if already registered
$already_registered = false;
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $check_query = "SELECT id FROM event_participants ep 
                    JOIN mahasiswa m ON ep.user_id = m.user_id 
                    WHERE ep.event_id = ? AND m.user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $event['id'], $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $already_registered = $result->num_rows > 0;
} catch (Exception $e) {
    // Handle error
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Event - <?= htmlspecialchars($event['nama_event']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #10b981;
            --secondary-color: #059669;
            --accent-color: #f0fdf4;
            --text-dark: #065f46;
            --text-light: #6b7280;
            --danger-color: #dc2626;
            --warning-color: #f59e0b;
        }

        body {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            padding: 20px 0;
        }

        .container {
            max-width: 1000px;
        }

        .registration-header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.1);
            border: 1px solid #d1fae5;
        }

        .event-banner {
            position: relative;
            height: 200px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 16px;
            margin-bottom: 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .event-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .event-banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.8) 0%, rgba(5, 150, 105, 0.8) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .event-banner-content {
            text-align: center;
            z-index: 2;
        }

        .event-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .event-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .event-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f0fdf4;
            border-radius: 12px;
            border: 1px solid #bbf7d0;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 12px;
            font-size: 16px;
        }

        .info-content h6 {
            margin: 0;
            font-size: 12px;
            color: var(--text-light);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .info-content p {
            margin: 0;
            font-size: 14px;
            color: var(--text-dark);
            font-weight: 600;
        }

        .registration-form {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.1);
            border: 1px solid #d1fae5;
            margin-bottom: 30px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #f0fdf4;
        }

        .section-title h4 {
            margin: 0;
            color: var(--text-dark);
            font-weight: 700;
            font-size: 20px;
        }

        .section-title i {
            margin-right: 12px;
            color: var(--primary-color);
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }

        .form-label.required::after {
            content: " *";
            color: var(--danger-color);
            font-weight: bold;
        }

        .form-control {
            border: 2px solid #d1fae5;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
            background: white;
        }

        .form-control:disabled {
            background: #f3f4f6;
            color: #6b7280;
        }

        .form-text {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 6px;
        }

        .file-upload-area {
            border: 2px dashed #d1fae5;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .file-upload-area:hover {
            border-color: var(--primary-color);
            background: #f0fdf4;
        }

        .file-upload-area.dragover {
            border-color: var(--primary-color);
            background: #f0fdf4;
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .upload-text {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .upload-subtext {
            color: var(--text-light);
            font-size: 12px;
        }

        .file-preview {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: #f0fdf4;
            border-radius: 8px;
            border: 1px solid #bbf7d0;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .btn-secondary-custom {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            color: #6b7280;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-secondary-custom:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
            color: #374151;
            text-decoration: none;
        }

        .alert-custom {
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: none;
            display: flex;
            align-items: center;
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .alert-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        .alert-custom i {
            font-size: 20px;
            margin-right: 12px;
        }

        .registration-summary {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.1);
            border: 1px solid #d1fae5;
            margin-bottom: 30px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0fdf4;
        }

        .summary-item:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 18px;
            color: var(--text-dark);
        }

        .summary-label {
            color: var(--text-light);
            font-weight: 500;
        }

        .summary-value {
            color: var(--text-dark);
            font-weight: 600;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-spinner {
            text-align: center;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .capacity-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .capacity-warning i {
            font-size: 24px;
            color: #f59e0b;
            margin-right: 15px;
        }

        .already-registered {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 2px solid #dc2626;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin-bottom: 25px;
        }

        .already-registered i {
            font-size: 4rem;
            color: #dc2626;
            margin-bottom: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .registration-header,
            .registration-form,
            .registration-summary {
                padding: 20px;
                border-radius: 16px;
            }
            
            .event-title {
                font-size: 1.8rem;
            }
            
            .event-info-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .btn-primary-custom,
            .btn-secondary-custom {
                width: 100%;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="<?= BASE_URL ?>mahasiswa/kegiatan" class="btn-secondary-custom">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Event
            </a>
        </div>

        <!-- Event Header -->
        <div class="registration-header">
            <div class="event-banner">
                <?php if (!empty($event['poster'])): ?>
                    <img src="<?= BASE_URL ?>uploads/posters/<?= htmlspecialchars($event['poster']) ?>" alt="Event Poster">
                <?php endif; ?>
                <div class="event-banner-overlay">
                    <div class="event-banner-content">
                        <h1 class="event-title"><?= htmlspecialchars($event['nama_event']) ?></h1>
                        <p class="event-subtitle">by <?= htmlspecialchars($event['nama_organisasi'] ?? 'Organisasi') ?></p>
                    </div>
                </div>
            </div>

            <div class="event-info-grid">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="info-content">
                        <h6>Tanggal & Waktu</h6>
                        <p><?= date('d M Y, H:i', strtotime($event['tanggal_mulai'])) ?> WIB</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h6>Lokasi</h6>
                        <p><?= htmlspecialchars($event['lokasi']) ?></p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="info-content">
                        <h6>Kapasitas</h6>
                        <p><?= $event['remaining_slots'] ?? $event['kapasitas'] ?> slot tersisa</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div class="info-content">
                        <h6>Kategori</h6>
                        <p><?= ucfirst($event['kategori']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($already_registered): ?>
            <!-- Already Registered Message -->
            <div class="already-registered">
                <i class="fas fa-check-circle"></i>
                <h3>Anda Sudah Terdaftar!</h3>
                <p class="mb-3">Anda sudah terdaftar untuk event ini. Status pendaftaran Anda sedang dalam proses verifikasi oleh organisasi.</p>
                <a href="<?= BASE_URL ?>mahasiswa/kegiatan" class="btn-primary-custom">
                    <i class="fas fa-list"></i> Lihat Event Saya
                </a>
            </div>
            
        <?php else: ?>
            <!-- Check capacity -->
            <?php 
            $remaining_slots = $event['remaining_slots'] ?? $event['kapasitas'];
            if ($remaining_slots <= 0): 
            ?>
                <div class="already-registered">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Event Penuh!</h3>
                    <p class="mb-3">Maaf, event ini sudah mencapai kapasitas maksimum. Silakan cari event lain yang tersedia.</p>
                    <a href="<?= BASE_URL ?>mahasiswa/kegiatan" class="btn-primary-custom">
                        <i class="fas fa-search"></i> Cari Event Lain
                    </a>
                </div>
                
            <?php elseif ($remaining_slots <= 5): ?>
                <!-- Low capacity warning -->
                <div class="capacity-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <h5 style="margin: 0; color: #92400e;">Slot Terbatas!</h5>
                        <p style="margin: 0; color: #92400e;">Hanya tersisa <?= $remaining_slots ?> slot. Daftar sekarang sebelum kehabisan!</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($remaining_slots > 0): ?>
                <!-- Registration Form -->
                <div class="registration-form">
                    <div id="alertContainer"></div>
                    
                    <form id="registrationForm" enctype="multipart/form-data">
                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                        
                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-user"></i>
                                <h4>Data Pribadi</h4>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label required">Nama Lengkap</label>
                                        <input type="text" class="form-control" name="nama_lengkap" 
                                               value="<?= htmlspecialchars($mahasiswa_data['nama_lengkap']) ?>" disabled>
                                        <div class="form-text">Data diambil dari profil Anda</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label required">NIM</label>
                                        <input type="text" class="form-control" name="nim" 
                                               value="<?= htmlspecialchars($mahasiswa_data['nim']) ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label required">Fakultas</label>
                                        <input type="text" class="form-control" name="fakultas" 
                                               value="<?= htmlspecialchars($mahasiswa_data['fakultas']) ?>" disabled>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label required">Jurusan</label>
                                        <input type="text" class="form-control" name="jurusan" 
                                               value="<?= htmlspecialchars($mahasiswa_data['jurusan']) ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label required">Nomor WhatsApp</label>
                                        <input type="tel" class="form-control" name="no_whatsapp" 
                                               placeholder="08xxxxxxxxxx" pattern="[0-9]{10,13}" required>
                                        <div class="form-text">Nomor yang dapat dihubungi untuk konfirmasi event</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label required">Email</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" 
                                               placeholder="email@student.university.ac.id" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Event Specific Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-clipboard-list"></i>
                                <h4>Informasi Pendaftaran</h4>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Alasan Mengikuti Event</label>
                                <textarea class="form-control" name="alasan_ikut" rows="4" 
                                          placeholder="Jelaskan mengapa Anda tertarik mengikuti event ini dan apa yang Anda harapkan..." 
                                          required></textarea>
                                <div class="form-text">Minimal 50 karakter</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Pengalaman Terkait</label>
                                        <select class="form-control" name="pengalaman">
                                            <option value="">Pilih pengalaman Anda</option>
                                            <option value="pemula">Pemula (belum pernah mengikuti event serupa)</option>
                                            <option value="menengah">Menengah (sudah beberapa kali mengikuti)</option>
                                            <option value="berpengalaman">Berpengalaman (sering mengikuti event serupa)</option>
                                            <option value="expert">Expert (pernah menjadi panitia/speaker)</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Harapan Setelah Event</label>
                                        <select class="form-control" name="harapan">
                                            <option value="">Pilih harapan Anda</option>
                                            <option value="ilmu_baru">Mendapat ilmu/skill baru</option>
                                            <option value="networking">Memperluas networking</option>
                                            <option value="sertifikat">Mendapat sertifikat</option>
                                            <option value="prestasi">Meraih prestasi/juara</option>
                                            <option value="pengembangan_diri">Pengembangan diri</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Pertanyaan untuk Organisator</label>
                                <textarea class="form-control" name="pertanyaan" rows="3" 
                                          placeholder="Ada pertanyaan khusus tentang event ini? (opsional)"></textarea>
                            </div>
                        </div>

                        <!-- File Upload Section -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-paperclip"></i>
                                <h4>Dokumen Pendukung</h4>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Upload CV/Portfolio (Opsional)</label>
                                <div class="file-upload-area" onclick="document.getElementById('cv_file').click()">
                                    <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx" style="display: none;">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <div class="upload-text">Klik untuk upload CV/Portfolio</div>
                                    <div class="upload-subtext">PDF, DOC, DOCX (Max: 2MB)</div>
                                </div>
                                <div id="cv-preview" class="file-preview"></div>
                            </div>
                            
                            <?php if (strtolower($event['kategori']) === 'kompetisi'): ?>
                            <div class="form-group">
                                <label class="form-label required">Bukti Pembayaran/Karya</label>
                                <div class="file-upload-area" onclick="document.getElementById('bukti_file').click()">
                                    <input type="file" id="bukti_file" name="bukti_file" accept=".pdf,.jpg,.jpeg,.png" style="display: none;" required>
                                    <div class="upload-icon">
                                        <i class="fas fa-file-upload"></i>
                                    </div>
                                    <div class="upload-text">Upload bukti pembayaran atau karya awal</div>
                                    <div class="upload-subtext">PDF, JPG, PNG (Max: 5MB)</div>
                                </div>
                                <div id="bukti-preview" class="file-preview"></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-file-contract"></i>
                                <h4>Syarat & Ketentuan</h4>
                            </div>
                            
                            <div class="alert-custom alert-info">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    <strong>Perhatian:</strong> Pendaftaran Anda akan diverifikasi oleh organisasi penyelenggara. 
                                    Pastikan semua data yang dimasukkan benar dan lengkap.
                                </div>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms_agreement" name="terms_agreement" required>
                                <label class="form-check-label" for="terms_agreement">
                                    Saya menyetujui <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">syarat dan ketentuan</a> 
                                    event ini dan bersedia mengikuti seluruh rangkaian acara.
                                </label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="data_agreement" name="data_agreement" required>
                                <label class="form-check-label" for="data_agreement">
                                    Saya memberikan persetujuan untuk penggunaan data pribadi sesuai kebijakan privasi universitas.
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="btn-primary-custom" id="submitBtn">
                                <i class="fas fa-paper-plane"></i> Daftar Event Sekarang
                            </button>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt"></i> 
                                    Data Anda aman dan akan diverifikasi oleh organisasi
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Registration Summary (Hidden by default) -->
        <div class="registration-summary" id="registrationSummary" style="display: none;">
            <div class="section-title">
                <i class="fas fa-check-circle"></i>
                <h4>Ringkasan Pendaftaran</h4>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Event:</span>
                <span class="summary-value"><?= htmlspecialchars($event['nama_event']) ?></span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Tanggal:</span>
                <span class="summary-value"><?= date('d M Y, H:i', strtotime($event['tanggal_mulai'])) ?> WIB</span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Lokasi:</span>
                <span class="summary-value"><?= htmlspecialchars($event['lokasi']) ?></span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Status Pendaftaran:</span>
                <span class="summary-value" style="color: var(--warning-color);">
                    <i class="fas fa-clock"></i> Menunggu Verifikasi
                </span>
            </div>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: white;">
                    <h5 class="modal-title" id="termsModalLabel">
                        <i class="fas fa-file-contract"></i> Syarat dan Ketentuan Event
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Ketentuan Umum</h6>
                    <ul>
                        <li>Peserta wajib menghadiri seluruh rangkaian acara</li>
                        <li>Peserta wajib mematuhi protokol kesehatan yang berlaku</li>
                        <li>Peserta bertanggung jawab atas keamanan barang bawaan pribadi</li>
                    </ul>
                    
                    <h6>2. Pendaftaran</h6>
                    <ul>
                        <li>Pendaftaran akan diverifikasi oleh panitia dalam 1x24 jam</li>
                        <li>Panitia berhak menolak pendaftaran tanpa memberikan alasan</li>
                        <li>Data yang diberikan harus benar dan dapat dipertanggungjawabkan</li>
                    </ul>
                    
                    <h6>3. Pembatalan</h6>
                    <ul>
                        <li>Peserta dapat membatalkan pendaftaran maksimal 24 jam sebelum acara</li>
                        <li>Panitia berhak membatalkan acara karena force majeure</li>
                        <li>Jika acara dibatalkan, peserta akan mendapat pemberitahuan via email/WhatsApp</li>
                    </ul>
                    
                    <h6>4. Sertifikat</h6>
                    <ul>
                        <li>Sertifikat hanya diberikan kepada peserta yang hadir minimal 80% dari total acara</li>
                        <li>Sertifikat akan dikirim via email dalam 7 hari kerja setelah acara</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <h5>Memproses Pendaftaran...</h5>
            <p>Mohon tunggu, jangan tutup halaman ini</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
        const CV_MAX_SIZE = 2 * 1024 * 1024; // 2MB
        const BASE_URL = '<?= BASE_URL ?>';
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            setupFileUploads();
            setupFormValidation();
            setupCharacterCounters();
        });
        
        // Setup file upload handlers
        function setupFileUploads() {
            // CV file upload
            const cvFile = document.getElementById('cv_file');
            const cvPreview = document.getElementById('cv-preview');
            
            if (cvFile) {
                cvFile.addEventListener('change', function(e) {
                    handleFileUpload(e, cvPreview, CV_MAX_SIZE, 'CV/Portfolio');
                });
            }
            
            // Bukti file upload (for competitions)
            const buktiFile = document.getElementById('bukti_file');
            const buktiPreview = document.getElementById('bukti-preview');
            
            if (buktiFile) {
                buktiFile.addEventListener('change', function(e) {
                    handleFileUpload(e, buktiPreview, MAX_FILE_SIZE, 'Bukti Pembayaran/Karya');
                });
            }
            
            // Drag and drop functionality
            setupDragAndDrop();
        }
        
        // Handle file upload preview and validation
        function handleFileUpload(event, previewContainer, maxSize, fileType) {
            const file = event.target.files[0];
            
            if (!file) {
                previewContainer.style.display = 'none';
                return;
            }
            
            // Validate file size
            if (file.size > maxSize) {
                showAlert('error', `Ukuran file ${fileType} terlalu besar! Maksimal ${formatFileSize(maxSize)}`);
                event.target.value = '';
                previewContainer.style.display = 'none';
                return;
            }
            
            // Validate file type
            const allowedTypes = fileType.includes('CV') ? 
                ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'] :
                ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                
            if (!allowedTypes.includes(file.type)) {
                showAlert('error', `Format file ${fileType} tidak valid!`);
                event.target.value = '';
                previewContainer.style.display = 'none';
                return;
            }
            
            // Show preview
            previewContainer.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center;">
                        <i class="fas fa-file${getFileIcon(file.type)} text-success" style="font-size: 1.5rem; margin-right: 10px;"></i>
                        <div>
                            <strong>${fileType}:</strong><br>
                            <span style="font-size: 0.9rem; color: #666;">${file.name}</span><br>
                            <span style="font-size: 0.8rem; color: #888;">${formatFileSize(file.size)}</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile('${event.target.id}', '${previewContainer.id}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            previewContainer.style.display = 'block';
        }
        
        // Remove uploaded file
        function removeFile(inputId, previewId) {
            document.getElementById(inputId).value = '';
            document.getElementById(previewId).style.display = 'none';
        }
        
        // Setup drag and drop
        function setupDragAndDrop() {
            const uploadAreas = document.querySelectorAll('.file-upload-area');
            
            uploadAreas.forEach(area => {
                area.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                });
                
                area.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                });
                
                area.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        const input = this.querySelector('input[type="file"]');
                        if (input) {
                            input.files = files;
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                });
            });
        }
        
        // Setup form validation
        function setupFormValidation() {
            const form = document.getElementById('registrationForm');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (validateForm()) {
                    submitRegistration();
                }
            });
            
            // Real-time validation
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.addEventListener('blur', function() {
                    validateField(this);
                });
                
                field.addEventListener('input', function() {
                    clearFieldError(this);
                });
            });
            
            // Phone number validation
            const phoneField = form.querySelector('[name="no_whatsapp"]');
            if (phoneField) {
                phoneField.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '');
                    validatePhoneNumber(this);
                });
            }
        }
        
        // Setup character counters
        function setupCharacterCounters() {
            const textareas = document.querySelectorAll('textarea[name="alasan_ikut"]');
            textareas.forEach(textarea => {
                const counter = document.createElement('div');
                counter.className = 'form-text text-end';
                counter.style.marginTop = '5px';
                textarea.parentNode.appendChild(counter);
                
                function updateCounter() {
                    const length = textarea.value.length;
                    const minLength = 50;
                    counter.textContent = `${length} karakter`;
                    
                    if (length < minLength) {
                        counter.style.color = '#dc2626';
                        counter.textContent += ` (minimal ${minLength} karakter)`;
                    } else {
                        counter.style.color = '#10b981';
                    }
                }
                
                textarea.addEventListener('input', updateCounter);
                updateCounter();
            });
        }
        
        // Validate entire form
        function validateForm() {
            let isValid = true;
            const form = document.getElementById('registrationForm');
            
            // Clear previous alerts
            document.getElementById('alertContainer').innerHTML = '';
            
            // Validate required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });
            
            // Validate phone number
            const phoneField = form.querySelector('[name="no_whatsapp"]');
            if (phoneField && !validatePhoneNumber(phoneField)) {
                isValid = false;
            }
            
            // Validate reason length
            const reasonField = form.querySelector('[name="alasan_ikut"]');
            if (reasonField && reasonField.value.length < 50) {
                showFieldError(reasonField, 'Alasan mengikuti event minimal 50 karakter');
                isValid = false;
            }
            
            // Validate checkboxes
            const checkboxes = form.querySelectorAll('input[type="checkbox"][required]');
            checkboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    showAlert('error', 'Anda harus menyetujui syarat dan ketentuan untuk melanjutkan');
                    isValid = false;
                }
            });
            
            return isValid;
        }
        
        // Validate individual field
        function validateField(field) {
            const value = field.value.trim();
            
            if (field.hasAttribute('required') && !value) {
                showFieldError(field, 'Field ini wajib diisi');
                return false;
            }
            
            if (field.type === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    showFieldError(field, 'Format email tidak valid');
                    return false;
                }
            }
            
            clearFieldError(field);
            return true;
        }
        
        // Validate phone number
        function validatePhoneNumber(field) {
            const value = field.value;
            
            if (value.length < 10 || value.length > 13) {
                showFieldError(field, 'Nomor WhatsApp harus 10-13 digit');
                return false;
            }
            
            if (!value.startsWith('08')) {
                showFieldError(field, 'Nomor WhatsApp harus dimulai dengan 08');
                return false;
            }
            
            clearFieldError(field);
            return true;
        }
        
        // Submit registration
        function submitRegistration() {
            const form = document.getElementById('registrationForm');
            const formData = new FormData(form);
            const submitBtn = document.getElementById('submitBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            // Show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            loadingOverlay.style.display = 'flex';
            
            // Submit to server
            fetch('<?= BASE_URL ?>mahasiswa/event_registration', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loadingOverlay.style.display = 'none';
                
                if (data.success) {
                    showSuccessRegistration(data);
                } else {
                    showAlert('error', data.message || 'Terjadi kesalahan saat mendaftar');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Daftar Event Sekarang';
                }
            })
            .catch(error => {
                console.error('Registration error:', error);
                loadingOverlay.style.display = 'none';
                showAlert('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Daftar Event Sekarang';
            });
        }
        
        // Show success registration
        function showSuccessRegistration(data) {
            const alertContainer = document.getElementById('alertContainer');
            
            alertContainer.innerHTML = `
                <div class="alert-custom alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <h5 style="margin: 0 0 10px 0;">Pendaftaran Berhasil!</h5>
                        <p style="margin: 0;">
                            ${data.message || 'Pendaftaran Anda telah dikirim dan sedang menunggu verifikasi dari organisasi. 
                            Anda akan mendapat notifikasi melalui email dan WhatsApp.'}
                        </p>
                        <div style="margin-top: 15px;">
                            <a href="${BASE_URL}mahasiswa/kegiatan" class="btn-primary-custom btn-sm">
                                <i class="fas fa-list"></i> Lihat Status Pendaftaran
                            </a>
                        </div>
                    </div>
                </div>
            `;
            
            // Hide form and show summary
            document.querySelector('.registration-form').style.display = 'none';
            document.getElementById('registrationSummary').style.display = 'block';
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // Utility functions
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = `alert-${type}`;
            const icon = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-triangle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            }[type] || 'fa-info-circle';
            
            alertContainer.innerHTML = `
                <div class="alert-custom ${alertClass}">
                    <i class="fas ${icon}"></i>
                    <div>${message}</div>
                </div>
            `;
            
            // Auto remove after 5 seconds for non-error alerts
            if (type !== 'error') {
                setTimeout(() => {
                    alertContainer.innerHTML = '';
                }, 5000);
            }
        }
        
        function showFieldError(field, message) {
            clearFieldError(field);
            
            field.style.borderColor = '#dc2626';
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.style.color = '#dc2626';
            errorDiv.style.fontSize = '12px';
            errorDiv.style.marginTop = '5px';
            errorDiv.textContent = message;
            
            field.parentNode.appendChild(errorDiv);
        }
        
        function clearFieldError(field) {
            field.style.borderColor = '#d1fae5';
            const errorDiv = field.parentNode.querySelector('.field-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function getFileIcon(mimeType) {
            if (mimeType.includes('pdf')) return '-pdf';
            if (mimeType.includes('word')) return '-word';
            if (mimeType.includes('image')) return '-image';
            return '';
        }
        
        // Auto-format phone number
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.querySelector('[name="no_whatsapp"]');
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    let value = this.value.replace(/\D/g, '');
                    
                    // Add formatting for better UX
                    if (value.length > 4 && value.length <= 8) {
                        value = value.replace(/(\d{4})(\d{1,4})/, '$1-$2');
                    } else if (value.length > 8) {
                        value = value.replace(/(\d{4})(\d{4})(\d{1,5})/, '$1-$2-$3');
                    }
                    
                    // Remove formatting for validation (keep only numbers)
                    this.value = value.replace(/-/g, '');
                });
            }
        });
        
        console.log('Event registration form loaded successfully!');
    </script>
</body>
</html>