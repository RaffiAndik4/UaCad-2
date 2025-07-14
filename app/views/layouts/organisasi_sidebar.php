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

<!-- Sidebar -->
<div class="sidebar">
    <div class="logo">
        <i class="fas fa-university"></i> UACAD
    </div>
    
    <!-- Organization Info -->
    <div class="org-info text-center mb-3" style="padding: 0 20px;">
        <div class="org-avatar mb-2" style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; color: white; font-weight: bold;">
            <?= $org_initials ?>
        </div>
        <div style="color: rgba(255,255,255,0.9); font-size: 12px; line-height: 1.3;">
            <?= htmlspecialchars(substr($org_data['nama_organisasi'] ?? 'Organisasi', 0, 20)) ?>
            <?= strlen($org_data['nama_organisasi'] ?? '') > 20 ? '...' : '' ?>
        </div>
        <div style="color: rgba(255,255,255,0.7); font-size: 11px;">
            <?= htmlspecialchars($org_data['jenis_organisasi'] ?? '') ?>
        </div>
    </div>
    
    <nav class="nav flex-column">
        <a href="<?= BASE_URL ?>organisasi/dashboard" class="nav-link <?= $current_page === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Dashboard
            <?php if (isset($stats) && $stats['aktif'] > 0): ?>
                <span class="badge badge-light ms-auto" style="background: rgba(255,255,255,0.2); color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px;">
                    <?= $stats['aktif'] ?>
                </span>
            <?php endif; ?>
        </a>
        
        <a href="<?= BASE_URL ?>organisasi/events" class="nav-link <?= $current_page === 'events' ? 'active' : '' ?>">
            <i class="fas fa-calendar-check"></i> Kelola Event
            <?php if (isset($all_events) && count($all_events) > 0): ?>
                <span class="badge badge-light ms-auto" style="background: rgba(255,255,255,0.2); color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px;">
                    <?= count($all_events) ?>
                </span>
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
            <?php if (isset($stats) && $stats['selesai'] > 0): ?>
                <span class="badge badge-light ms-auto" style="background: rgba(255,255,255,0.2); color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px;">
                    <?= $stats['selesai'] ?>
                </span>
            <?php endif; ?>
        </a>
        
        <hr style="border-color: rgba(255,255,255,0.2); margin: 10px 20px;">
        
        <a href="<?= BASE_URL ?>organisasi/profile" class="nav-link <?= $current_page === 'profile' ? 'active' : '' ?>">
            <i class="fas fa-building"></i> Profil Organisasi
        </a>
        
        <a href="<?= BASE_URL ?>auth/logout" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
    
    <!-- Footer -->
    <div style="position: absolute; bottom: 20px; left: 20px; right: 20px; text-align: center; color: rgba(255,255,255,0.6); font-size: 11px;">
        <div>Logged in as:</div>
        <div style="font-weight: 600; color: rgba(255,255,255,0.8);">
            <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
        </div>
    </div>
</div>