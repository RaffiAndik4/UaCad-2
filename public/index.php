<?php
// public/index.php

// Start session hanya jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once '../config/config.php';
require_once '../config/database.php';

// Load core files
require_once '../app/core/Controller.php';

// Simple routing - default ke auth/login
$url = $_GET['url'] ?? 'auth/login';
$url = rtrim($url, '/');
$url = explode('/', $url);

$controllerName = ucfirst($url[0] ?? 'Auth') . 'Controller';
$method = $url[1] ?? 'login';
$params = array_slice($url, 2);

// Check if controller file exists
$controllerFile = '../app/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        
        if (method_exists($controller, $method)) {
            call_user_func_array([$controller, $method], $params);
        } else {
            // Method not found, try index
            if (method_exists($controller, 'index')) {
                $controller->index();
            } else {
                show404();
            }
        }
    } else {
        show404();
    }
} else {
    show404();
}

function show404() {
    http_response_code(404);
    echo '<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #333; }
        a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you are looking for does not exist.</p>
    <a href="' . BASE_URL . '">Go to Homepage</a>
</body>
</html>';
}
?>