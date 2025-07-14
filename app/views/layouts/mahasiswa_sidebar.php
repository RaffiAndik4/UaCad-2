<?php
// Ambil data mahasiswa dari variabel yang dikirim controller
$nama = $data['mahasiswa']['nama_lengkap'] ?? 'Nama Mahasiswa';
$nim = $data['mahasiswa']['nim'] ?? 'NIM Mahasiswa';
$inisial = strtoupper(substr($nama, 0, 1));
?>

<div class="sidebar">
    <div class="logo">
        <i class="fas fa-university me-2"></i>
        <span>UACAD</span>
    </div>

    <div class="profile-card">
        <div class="profile-avatar"><?= htmlspecialchars($inisial) ?></div>
        <div class="profile-info">
            <div class="profile-name"><?= htmlspecialchars($nama) ?></div>
            <div class="profile-role">Mahasiswa</div>
        </div>
    </div>

    <nav class="nav-menu">
        <p class="nav-title">MENU</p>
        <a href="<?= BASE_URL ?>mahasiswa/dashboard" class="nav-link active">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?= BASE_URL ?>mahasiswa/event" class="nav-link">
            <i class="fas fa-calendar-star"></i>
            <span>Event Kampus</span>
        </a>
        <a href="#" class="nav-link">
            <i class="fas fa-book-open"></i>
            <span>Jadwal Kuliah</span>
        </a>
        <a href="#" class="nav-link">
            <i class="fas fa-user-graduate"></i>
            <span>Profil Saya</span>
        </a>

        <p class="nav-title mt-4">AKUN</p>
        <a href="<?= BASE_URL ?>auth/logout" class="nav-link">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>
</div>