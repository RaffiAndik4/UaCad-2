<?php
// config/config.php

// Application Configuration
define('APP_NAME', 'UACAD System');
define('APP_VERSION', '1.0.0');

// URL Configuration
define('BASE_URL', 'http://localhost/uacad_final/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Path Configuration
define('ROOT_PATH', dirname(__DIR__) . '/');
define('APP_PATH', ROOT_PATH . 'app/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('PUBLIC_PATH', ROOT_PATH . 'public/');
define('UPLOAD_PATH', PUBLIC_PATH . 'uploads/');

// Database Configuration (you can move this to separate file for security)
define('DB_HOST', 'localhost');
define('DB_NAME', 'uacad_final');
define('DB_USER', 'root');
define('DB_PASS', '');

// Security Configuration
define('CSRF_TOKEN_NAME', '_token');
define('SESSION_LIFETIME', 7200); // 2 hours

// File Upload Configuration
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png']);

// Error Reporting (change to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Helper Functions
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    return date($format, strtotime($datetime));
}

function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . BASE_URL . $url);
    exit;
}

function asset($path) {
    return ASSETS_URL . $path;
}

function upload($path) {
    return UPLOAD_URL . $path;
}

// Auto-create required directories
$requiredDirs = [
    UPLOAD_PATH,
    UPLOAD_PATH . 'mahasiswa/',
    UPLOAD_PATH . 'organisasi/',
    UPLOAD_PATH . 'staff/',
    ROOT_PATH . 'logs/'
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Create .htaccess for uploads directory
$htaccessContent = "# Deny direct access to uploaded files\n<Files ~ \"\\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$\">\n    Require all denied\n</Files>\n\n# Allow only specific file types\n<FilesMatch \"\\.(jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|ppt|pptx)$\">\n    Require all granted\n</FilesMatch>";

$htaccessFile = UPLOAD_PATH . '.htaccess';
if (!file_exists($htaccessFile)) {
    file_put_contents($htaccessFile, $htaccessContent);
}
?>