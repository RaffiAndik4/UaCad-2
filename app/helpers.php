<?php
// app/helpers.php

// Berisi semua fungsi bantuan untuk aplikasi Anda.

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

function redirect($url) {
    header('Location: ' . BASE_URL . $url);
    exit;
}

function asset($path) {
    return ASSETS_URL . $path;
}

function upload_url($path) { // Ganti nama 'upload' agar tidak bentrok
    return UPLOAD_URL . $path;
}

// Anda bisa menambahkan fungsi lain dari config lama jika diperlukan (seperti CSRF, sanitize, dll.)