<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - UACAD System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --text-dark: #2d3748;
            --text-light: #718096;
            --bg-light: #f7fafc;
            --success-color: #48bb78;
            --warning-color: #ed8936;
            --danger-color: #f56565;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .register-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 25px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .register-header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .register-header p {
            font-size: 1.2rem;
            opacity: 0.95;
        }

        .register-body {
            padding: 50px;
        }

        .role-selector {
            margin-bottom: 30px;
        }

        .role-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .role-card {
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .role-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
        }

        .role-card.active {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }

        .role-card input[type="radio"] {
            display: none;
        }

        .role-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .role-card.active .role-icon {
            color: white;
        }

        .role-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .role-description {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .form-tab {
            position: absolute;
            width: 100%;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .form-tab.active {
            position: relative;
            visibility: visible;
            opacity: 1;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-label.required::after {
            content: " *";
            color: var(--danger-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .file-upload {
            border: 2px dashed #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        .file-upload:hover {
            border-color: var(--primary-color);
            background: #f0f4ff;
        }

        .file-upload input[type="file"] {
            display: none;
        }

        .file-preview {
            margin-top: 15px;
            padding: 15px;
            background: #f0f9ff;
            border-radius: 10px;
            border: 2px solid #3b82f6;
            display: none;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: none;
            display: flex;
            align-items: center;
        }

        .alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .alert-danger {
            background: #fed7d7;
            color: #c53030;
        }

        .alert-success {
            background: #c6f6d5;
            color: #2f855a;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .role-options {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .register-container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .register-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1><i class="fas fa-graduation-cap"></i> UACAD</h1>
            <p>Daftar akun baru untuk mengakses sistem akademik</p>
        </div>
        
        <div class="register-body">
            <div id="alert-container">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                    <script>
                        setTimeout(() => {
                            window.location.href = '<?= BASE_URL ?>auth/login';
                        }, 3000);
                    </script>
                <?php endif; ?>
            </div>

            <form id="registerForm" method="POST" action="<?= BASE_URL ?>auth/register" enctype="multipart/form-data">
                <!-- Step 1: Role Selection -->
                <div class="form-section active" id="roleSection">
                    <div class="role-selector">
                        <h3 class="mb-3">Pilih jenis akun:</h3>
                        <div class="role-options">
                            <div class="role-card <?= (isset($_POST['role']) && $_POST['role'] == 'mahasiswa') ? 'active' : '' ?>" onclick="selectRole('mahasiswa')">
                                <input type="radio" name="role" value="mahasiswa" id="role_mahasiswa" <?= (isset($_POST['role']) && $_POST['role'] == 'mahasiswa') ? 'checked' : '' ?>>
                                <div class="role-icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="role-title">Mahasiswa</div>
                                <div class="role-description">
                                    Daftar sebagai mahasiswa untuk akses jadwal kuliah dan kegiatan kampus
                                </div>
                            </div>

                            <div class="role-card <?= (isset($_POST['role']) && $_POST['role'] == 'organisasi') ? 'active' : '' ?>" onclick="selectRole('organisasi')">
                                <input type="radio" name="role" value="organisasi" id="role_organisasi" <?= (isset($_POST['role']) && $_POST['role'] == 'organisasi') ? 'checked' : '' ?>>
                                <div class="role-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="role-title">Organisasi</div>
                                <div class="role-description">
                                    Daftar sebagai organisasi untuk mengelola kegiatan dan member
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary" onclick="nextStep()" id="nextBtn" disabled>
                            Lanjutkan <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Form Data -->
                <div class="form-section" id="dataSection">
                    <!-- Form Mahasiswa -->
                    <div id="mahasiswaForm" class="form-tab">
                        <h3 class="mb-4">Data Mahasiswa</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap" value="<?= isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : '' ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">NIM</label>
                                <input type="text" name="nim" class="form-control" placeholder="Masukkan NIM" value="<?= isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : '' ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Fakultas</label>
                                <select name="fakultas" class="form-control" required>
                                    <option value="">Pilih Fakultas</option>
                                    <option value="Teknik" <?= (isset($_POST['fakultas']) && $_POST['fakultas'] == 'Teknik') ? 'selected' : '' ?>>Fakultas Teknik</option>
                                    <option value="Ekonomi" <?= (isset($_POST['fakultas']) && $_POST['fakultas'] == 'Ekonomi') ? 'selected' : '' ?>>Fakultas Ekonomi dan Bisnis</option>
                                    <option value="Hukum" <?= (isset($_POST['fakultas']) && $_POST['fakultas'] == 'Hukum') ? 'selected' : '' ?>>Fakultas Hukum</option>
                                    <option value="Kedokteran" <?= (isset($_POST['fakultas']) && $_POST['fakultas'] == 'Kedokteran') ? 'selected' : '' ?>>Fakultas Kedokteran</option>
                                    <option value="FISIP" <?= (isset($_POST['fakultas']) && $_POST['fakultas'] == 'FISIP') ? 'selected' : '' ?>>Fakultas Ilmu Sosial dan Politik</option>
                                    <option value="Pertanian" <?= (isset($_POST['fakultas']) && $_POST['fakultas'] == 'Pertanian') ? 'selected' : '' ?>>Fakultas Pertanian</option>
                                    <option value="MIPA" <?= (isset($_POST['fakultas']) && $_POST['fakultas'] == 'MIPA') ? 'selected' : '' ?>>Fakultas MIPA</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Jurusan</label>
                                <select name="jurusan" class="form-control" required>
                                    <option value="">Pilih Jurusan</option>
                                    <?php if (isset($_POST['jurusan'])): ?>
                                        <option value="<?= htmlspecialchars($_POST['jurusan']) ?>" selected><?= htmlspecialchars($_POST['jurusan']) ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Angkatan</label>
                                <select name="angkatan" class="form-control" required>
                                    <option value="">Pilih Angkatan</option>
                                    <?php 
                                    $currentYear = date('Y');
                                    for ($year = $currentYear; $year >= $currentYear - 10; $year--): 
                                    ?>
                                        <option value="<?= $year ?>" <?= (isset($_POST['angkatan']) && $_POST['angkatan'] == $year) ? 'selected' : '' ?>><?= $year ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Minat/Konsentrasi</label>
                                <input type="text" name="minat" class="form-control" placeholder="Contoh: Sistem Informasi" value="<?= isset($_POST['minat']) ? htmlspecialchars($_POST['minat']) : '' ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Upload Jadwal Kuliah</label>
                            <div class="file-upload" onclick="document.getElementById('jadwal_kuliah').click()">
                                <input type="file" name="jadwal_kuliah" id="jadwal_kuliah" accept=".pdf,.jpg,.jpeg,.png" required>
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 10px;"></i>
                                <p>Klik untuk upload jadwal kuliah</p>
                                <small>Format: PDF, JPG, PNG (Max: 5MB)</small>
                            </div>
                            <div id="jadwal-preview" class="file-preview"></div>
                        </div>
                    </div>

                    <!-- Form Organisasi -->
                    <div id="organisasiForm" class="form-tab">
                        <h3 class="mb-4">Data Organisasi</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Nama Organisasi</label>
                                <input type="text" name="nama_organisasi" class="form-control" placeholder="Masukkan nama organisasi" value="<?= isset($_POST['nama_organisasi']) ? htmlspecialchars($_POST['nama_organisasi']) : '' ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Jenis Organisasi</label>
                                <select name="jenis_organisasi" class="form-control" required>
                                    <option value="">Pilih Jenis Organisasi</option>
                                    <option value="BEM" <?= (isset($_POST['jenis_organisasi']) && $_POST['jenis_organisasi'] == 'BEM') ? 'selected' : '' ?>>Badan Eksekutif Mahasiswa</option>
                                    <option value="DPM" <?= (isset($_POST['jenis_organisasi']) && $_POST['jenis_organisasi'] == 'DPM') ? 'selected' : '' ?>>Dewan Perwakilan Mahasiswa</option>
                                    <option value="Himpunan" <?= (isset($_POST['jenis_organisasi']) && $_POST['jenis_organisasi'] == 'Himpunan') ? 'selected' : '' ?>>Himpunan Mahasiswa Jurusan</option>
                                    <option value="UKM" <?= (isset($_POST['jenis_organisasi']) && $_POST['jenis_organisasi'] == 'UKM') ? 'selected' : '' ?>>Unit Kegiatan Mahasiswa</option>
                                    <option value="Komunitas" <?= (isset($_POST['jenis_organisasi']) && $_POST['jenis_organisasi'] == 'Komunitas') ? 'selected' : '' ?>>Komunitas Mahasiswa</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Email Organisasi</label>
                                <input type="email" name="email_organisasi" class="form-control" placeholder="organisasi@email.com" value="<?= isset($_POST['email_organisasi']) ? htmlspecialchars($_POST['email_organisasi']) : '' ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Password</label>
                                <input type="password" name="password_organisasi" class="form-control" placeholder="Minimal 6 karakter" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Deskripsi Organisasi</label>
                            <textarea name="deskripsi_organisasi" class="form-control" rows="4" placeholder="Deskripsikan organisasi Anda..."><?= isset($_POST['deskripsi_organisasi']) ? htmlspecialchars($_POST['deskripsi_organisasi']) : '' ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Upload Surat Pengesahan</label>
                            <div class="file-upload" onclick="document.getElementById('surat_pengesahan').click()">
                                <input type="file" name="surat_pengesahan" id="surat_pengesahan" accept=".pdf,.jpg,.jpeg,.png" required>
                                <i class="fas fa-certificate" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 10px;"></i>
                                <p>Klik untuk upload surat pengesahan</p>
                                <small>Format: PDF, JPG, PNG (Max: 5MB)</small>
                            </div>
                            <div id="surat-preview" class="file-preview"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Daftar Sekarang <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
            </form>

            <div class="text-center mt-4">
                <p>Sudah punya akun? <a href="<?= BASE_URL ?>auth/login" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Login di sini</a></p>
            </div>
        </div>
    </div>

    <script>
        let selectedRole = '<?= isset($_POST['role']) ? $_POST['role'] : '' ?>';
        
        // Data fakultas dan jurusan
        const fakultasJurusan = {
            'Teknik': ['Teknik Informatika', 'Teknik Sipil', 'Teknik Mesin', 'Teknik Elektro', 'Teknik Industri'],
            'Ekonomi': ['Manajemen', 'Akuntansi', 'Ekonomi Pembangunan', 'Ekonomi Islam'],
            'Hukum': ['Ilmu Hukum'],
            'Kedokteran': ['Pendidikan Dokter', 'Keperawatan', 'Kebidanan'],
            'FISIP': ['Ilmu Komunikasi', 'Administrasi Publik', 'Hubungan Internasional', 'Sosiologi'],
            'Pertanian': ['Agroteknologi', 'Agribisnis', 'Teknologi Pangan'],
            'MIPA': ['Matematika', 'Fisika', 'Kimia', 'Biologi', 'Statistika']
        };

        // Initialize if there's form data from PHP (validation error)
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_POST['role']) && !isset($success)): ?>
                selectRole('<?= $_POST['role'] ?>');
                nextStep();
            <?php else: ?>
                // Initialize default state - semua form hidden dengan no required
                enableRequiredFields('mahasiswaForm', false);
                enableRequiredFields('organisasiForm', false);
            <?php endif; ?>
        });

        function selectRole(role) {
            selectedRole = role;
            
            // Reset semua card
            document.querySelectorAll('.role-card').forEach(card => {
                card.classList.remove('active');
            });
            
            // Aktifkan card yang dipilih
            const selectedCard = document.querySelector(`#role_${role}`).closest('.role-card');
            selectedCard.classList.add('active');
            document.getElementById(`role_${role}`).checked = true;
            
            // Enable next button
            document.getElementById('nextBtn').disabled = false;
        }

        function nextStep() {
            if (!selectedRole) {
                alert('Pilih jenis akun terlebih dahulu!');
                return;
            }
            
            // Hide role section, show data section
            document.getElementById('roleSection').classList.remove('active');
            document.getElementById('dataSection').classList.add('active');
            
            // Show appropriate form and disable/enable required fields
            if (selectedRole === 'mahasiswa') {
                document.getElementById('mahasiswaForm').classList.add('active');
                document.getElementById('organisasiForm').classList.remove('active');
                
                // Enable required untuk mahasiswa, disable untuk organisasi
                enableRequiredFields('mahasiswaForm', true);
                enableRequiredFields('organisasiForm', false);
                
            } else {
                document.getElementById('mahasiswaForm').classList.remove('active');
                document.getElementById('organisasiForm').classList.add('active');
                
                // Enable required untuk organisasi, disable untuk mahasiswa
                enableRequiredFields('organisasiForm', true);
                enableRequiredFields('mahasiswaForm', false);
            }
        }

        function enableRequiredFields(formId, enable) {
            const form = document.getElementById(formId);
            if (form) {
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (enable) {
                        field.setAttribute('required', 'required');
                    } else {
                        field.removeAttribute('required');
                    }
                });
            }
        }

        function prevStep() {
            document.getElementById('dataSection').classList.remove('active');
            document.getElementById('roleSection').classList.add('active');
        }

        // Handle fakultas change
        document.querySelector('[name="fakultas"]').addEventListener('change', function() {
            const jurusanSelect = document.querySelector('[name="jurusan"]');
            const selectedFakultas = this.value;
            
            // Clear jurusan options
            jurusanSelect.innerHTML = '<option value="">Pilih Jurusan</option>';
            
            if (selectedFakultas && fakultasJurusan[selectedFakultas]) {
                fakultasJurusan[selectedFakultas].forEach(jurusan => {
                    const option = document.createElement('option');
                    option.value = jurusan;
                    option.textContent = jurusan;
                    jurusanSelect.appendChild(option);
                });
            }
        });

        // File upload preview
        document.getElementById('jadwal_kuliah').addEventListener('change', function(e) {
            showFilePreview(e, 'jadwal-preview', 'Jadwal Kuliah');
        });

        document.getElementById('surat_pengesahan').addEventListener('change', function(e) {
            showFilePreview(e, 'surat-preview', 'Surat Pengesahan');
        });

        function showFilePreview(event, previewId, fileType) {
            const file = event.target.files[0];
            const preview = document.getElementById(previewId);
            
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const fileName = file.name;
                
                preview.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center;">
                            <i class="fas fa-file" style="color: var(--primary-color); font-size: 1.5rem; margin-right: 10px;"></i>
                            <div>
                                <strong>${fileType}:</strong><br>
                                <span style="font-size: 0.9rem; color: #666;">${fileName}</span><br>
                                <span style="font-size: 0.8rem; color: #888;">${fileSize} MB</span>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-check-circle" style="color: var(--success-color); font-size: 1.2rem;"></i>
                        </div>
                    </div>
                `;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>