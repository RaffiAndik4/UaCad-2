<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UACAD System</title>
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
            --danger-color: #f56565;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 25px;
            box-shadow: var(--shadow);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 600px;
        }

        .login-left {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 50px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .login-left h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .login-left p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .feature-list {
            list-style: none;
            text-align: left;
        }

        .feature-list li {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
        }

        .feature-list i {
            margin-right: 15px;
            font-size: 1.3rem;
        }

        .login-right {
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            color: var(--text-dark);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .role-selector {
            margin-bottom: 2rem;
        }

        .role-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 1rem;
        }

        .role-option {
            position: relative;
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .role-option label {
            display: block;
            padding: 15px 8px;
            background: var(--bg-light);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .role-option label:hover {
            border-color: var(--primary-color);
            background: #f0f4ff;
            transform: translateY(-2px);
        }

        .role-option input[type="radio"]:checked + label {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .role-option i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .input-group .form-control {
            padding-left: 50px;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: none;
            display: flex;
            align-items: center;
        }

        .alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
            color: #c53030;
        }

        .alert-success {
            background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
            color: #2f855a;
        }

        .forgot-password {
            text-align: center;
            margin-top: 1rem;
        }

        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .register-section {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #f1f5f9;
        }

        .register-section h4 {
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .register-section p {
            color: var(--text-light);
            margin-bottom: 1.5rem;
        }

        .btn-register {
            display: inline-block;
            padding: 12px 30px;
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-register:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 450px;
                margin: 10px;
            }

            .login-left {
                padding: 30px 20px;
            }

            .login-left h1 {
                font-size: 2.2rem;
            }

            .login-right {
                padding: 30px 20px;
            }

            .role-options {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div>
                <h1><i class="fas fa-graduation-cap"></i> UACAD</h1>
                <p>Sistem Informasi Akademik Universitas</p>
                <ul class="feature-list">
                    <li><i class="fas fa-check-circle"></i> Manajemen Akademik</li>
                    <li><i class="fas fa-check-circle"></i> Kegiatan Mahasiswa</li>
                    <li><i class="fas fa-check-circle"></i> Organisasi Kampus</li>
                    <li><i class="fas fa-check-circle"></i> Laporan & Statistik</li>
                </ul>
            </div>
        </div>
        
        <div class="login-right">
            <div class="login-header">
                <h2>Selamat Datang Kembali</h2>
                <p>Silakan login untuk mengakses sistem</p>
            </div>

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
                <?php endif; ?>
            </div>

            <form method="POST" action="<?= BASE_URL ?>auth/login" id="loginForm">
                <div class="role-selector">
                    <label class="form-label">Pilih Role:</label>
                    <div class="role-options">
                        <div class="role-option">
                            <input type="radio" name="role" id="mahasiswa" value="mahasiswa" <?= (isset($_POST['role']) && $_POST['role'] == 'mahasiswa') ? 'checked' : '' ?>>
                            <label for="mahasiswa">
                                <i class="fas fa-user-graduate"></i>
                                Mahasiswa
                            </label>
                        </div>
                        <div class="role-option">
                            <input type="radio" name="role" id="organisasi" value="organisasi" <?= (isset($_POST['role']) && $_POST['role'] == 'organisasi') ? 'checked' : '' ?>>
                            <label for="organisasi">
                                <i class="fas fa-users"></i>
                                Organisasi
                            </label>
                        </div>
                        <div class="role-option">
                            <input type="radio" name="role" id="staff" value="staff" <?= (isset($_POST['role']) && $_POST['role'] == 'staff') ? 'checked' : '' ?>>
                            <label for="staff">
                                <i class="fas fa-user-tie"></i>
                                Staff
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Username atau Email</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username atau email" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginButton">
                    <span id="loginText">Login</span>
                    <div id="loginLoading" class="loading" style="display: none;"></div>
                </button>
            </form>

            <div class="forgot-password">
                <a href="<?= BASE_URL ?>auth/forgot-password">Lupa Password?</a>
            </div>

            <div class="register-section">
                <h4>Belum Punya Akun?</h4>
                <p>Daftar sekarang untuk mengakses sistem akademik universitas</p>
                <a href="<?= BASE_URL ?>auth/register" class="btn-register">
                    <span><i class="fas fa-user-plus"></i> Daftar Sekarang</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Auto-select role based on username prefix
        document.querySelector('input[name="username"]').addEventListener('input', function() {
            const username = this.value.toLowerCase();
            if (username.startsWith('mahasiswa') || username.includes('mhs') || /^\d+$/.test(username)) {
                document.getElementById('mahasiswa').checked = true;
            } else if (username.startsWith('bem') || username.includes('org') || username.includes('osis')) {
                document.getElementById('organisasi').checked = true;
            } else if (username.startsWith('admin') || username.includes('staff') || username.startsWith('nip')) {
                document.getElementById('staff').checked = true;
            }
        });

        // Handle form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            console.log('Login form submitted');
            
            // Check if role is selected
            const selectedRole = document.querySelector('input[name="role"]:checked');
            if (!selectedRole) {
                e.preventDefault();
                alert('Silakan pilih role terlebih dahulu!');
                return false;
            }
            
            // Check if username and password are filled
            const username = document.querySelector('input[name="username"]').value.trim();
            const password = document.querySelector('input[name="password"]').value;
            
            if (!username) {
                e.preventDefault();
                alert('Username atau email harus diisi!');
                return false;
            }
            
            if (!password) {
                e.preventDefault();
                alert('Password harus diisi!');
                return false;
            }
            
            // Show loading state
            const loginButton = document.getElementById('loginButton');
            const loginText = document.getElementById('loginText');
            const loginLoading = document.getElementById('loginLoading');
            
            loginButton.disabled = true;
            loginText.style.display = 'none';
            loginLoading.style.display = 'inline-block';
            
            // Allow form to submit normally
            return true;
        });

        // Enable click on role labels
        document.querySelectorAll('.role-option label').forEach(label => {
            label.addEventListener('click', function() {
                const input = this.previousElementSibling;
                if (input && input.type === 'radio') {
                    input.checked = true;
                }
            });
        });

        // Debug: Log when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Login page loaded');
            console.log('Form action:', document.getElementById('loginForm').action);
        });
    </script>
</body>
</html>