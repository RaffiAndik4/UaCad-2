<?php
// app/config/config.php

// --- KONFIGURASI INTI (JANGAN DIUBAH) ---
// Path absolut ke folder 'app'. Ini adalah kunci untuk semua 'require'.
define('APPROOT', dirname(dirname(__FILE__)));
// URL dasar untuk seluruh proyek.
define('BASE_URL', 'http://localhost/uacad_final/');


// --- KONFIGURASI URL & PATH ---
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOAD_URL', BASE_URL . 'uploads/');
define('UPLOAD_PATH', dirname(APPROOT) . '/public/uploads/'); // Path server untuk upload file


// --- KONFIGURASI DATABASE ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'system_kampus'); // Pastikan nama DB ini benar
define('DB_USER', 'root');
define('DB_PASS', '');


// --- KONFIGURASI APLIKASI ---
define('APP_NAME', 'UACAD System');
date_default_timezone_set('Asia/Jakarta');


// --- PENGATURAN ERROR (Untuk Development) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);