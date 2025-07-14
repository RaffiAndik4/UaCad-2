<?php
// app/views/layouts/organisasi_sidebar.php
// Shared sidebar untuk semua halaman organisasi

$current_page = $current_page ?? 'dashboard';
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
?>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 260px;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    z-index: 1000;
    transition: all 0.3s ease;
    box-shadow: 4px 0 20px rgba(102, 126, 234, 0.15);
    overflow-y: auto;
}

.sidebar .logo {
    text-align: center;
    color: white;
    font-size: 24px;
    font-weight: 700;
    margin: 24px 0 30px 0;
    padding: 0 20px;
    letter-spacing: 1px;
}

.sidebar .org-info {
    padding: 0 20px;
    margin-bottom: 20px;
    text-align: center;
}

.sidebar .org-avatar {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    color: white;
    font-weight: bold;
    font-size: 18px;
}

.sidebar .org-name {
    color: rgba(255,255,255,0.9);
    font-size: 12px;
    line-height: 1.3;
    margin-bottom: 4px;
    font-weight: 500;
}

.sidebar .org-type {
    color: rgba(255,255,255,0.7);
    font-size: 11px;
}

.sidebar .nav {
    padding: 0;
    margin: 0;
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
    border: none;
    background: none;
    width: calc(100% - 12px);
    text-align: left;
    cursor: pointer;
    position: relative;
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
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.sidebar .nav-link i {
    margin-right: 12px;
    width: 18px;
    text-align: center;
    font-size: 16px;
}

.sidebar .nav-link .badge {
    background: rgba(255,255,255,0.2);
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: auto;
}

.sidebar .sidebar-footer {
    position: absolute;
    bottom: 20px;
    left: 20px;
    right: 20px;
    text-align: center;
    color: rgba(255,255,255,0.6);
    font-size: 11px;
}

.sidebar .user-info {
    color: rgba(255,255,255,0.8);
    font-weight: 600;
}

.sidebar hr {
    border-color: rgba(255,255,255,0.2);
    margin: 10px 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
}
</style>

<!-- Sidebar -->
<div class="sidebar">
    <div class="logo">
        <i class="fas fa-university"></i> UACAD
    </div>
    
    <!-- Organization Info -->
    <div class="org-info">
        <div class="org-avatar">
            <?= $org_initials ?>
        </div>
        <div class="org-name">
            <?= htmlspecialchars(substr($org_data['nama_organisasi'] ?? 'Organisasi', 0, 20)) ?>
            <?= strlen($org_data['nama_organisasi'] ?? '') > 20 ? '...' : '' ?>
        </div>
        <div class="org-type">
            <?= htmlspecialchars($org_data['jenis_organisasi'] ?? '') ?>
        </div>
    </div>
    
    <nav class="nav flex-column">
        <a href="<?= BASE_URL ?>organisasi/dashboard" class="nav-link <?= $current_page === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Dashboard
            <?php if (isset($stats) && ($stats['aktif'] ?? 0) > 0): ?>
                <span class="badge"><?= $stats['aktif'] ?></span>
            <?php endif; ?>
        </a>
        
        <a href="<?= BASE_URL ?>organisasi/events" class="nav-link <?= $current_page === 'events' ? 'active' : '' ?>">
            <i class="fas fa-calendar-check"></i> Kelola Event
            <?php if (isset($all_events) && count($all_events) > 0): ?>
                
            <?php endif; ?>
        </a>
        
        <a href="<?= BASE_URL ?>organisasi/participants" class="nav-link <?= $current_page === 'participants' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Peserta
        </a>
        
        <a href="<?= BASE_URL ?>organisasi/analytics" class="nav-link <?= $current_page === 'analytics' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> Analitik
        </a>
        
        <a href="<?= BASE_URL ?>organisasi/reports" class="nav-link <?= $current_page === 'reports' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> Laporan
            <?php if (isset($stats) && ($stats['selesai'] ?? 0) > 0): ?>
                <span class="badge"><?= $stats['selesai'] ?></span>
            <?php endif; ?>
        </a>
        
        <hr>
        
        <a href="<?= BASE_URL ?>organisasi/profile" class="nav-link <?= $current_page === 'profile' ? 'active' : '' ?>">
            <i class="fas fa-building"></i> Profil Organisasi
        </a>
        
        <a href="<?= BASE_URL ?>auth/logout" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
    
    <!-- Footer -->
    <div class="sidebar-footer">
        <div>Logged in as:</div>
        <div class="user-info">
            <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
        </div>
    </div>
</div>