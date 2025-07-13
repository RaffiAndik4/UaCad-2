<?php
// app/core/Controller.php

class Controller {
    
    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }
    
    public function view($view, $data = []) {
        // Extract data to variables
        extract($data);
        
        require_once '../app/views/' . $view . '.php';
    }
    
    public function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }
    
    public function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
    }
    
    public function checkRole($allowedRoles) {
        $this->checkAuth();
        if (!in_array($_SESSION['role'], $allowedRoles)) {
            $this->redirect('dashboard');
        }
    }
    
    public function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    public function upload($file, $destination, $allowedTypes = [], $maxSize = 5242880) {
        // Create directory if not exists
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        // Default allowed types
        if (empty($allowedTypes)) {
            $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
        }
        
        // Validate file
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error');
        }
        
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Check file size
        if ($fileSize > $maxSize) {
            throw new Exception('File size too large');
        }
        
        // Check file type
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Invalid file type');
        }
        
        // Generate unique filename
        $newFileName = uniqid() . '_' . time() . '.' . $fileType;
        $uploadPath = $destination . '/' . $newFileName;
        
        // Move uploaded file
        if (move_uploaded_file($fileTmp, $uploadPath)) {
            return $newFileName;
        } else {
            throw new Exception('Failed to upload file');
        }
    }
    
    public function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            // Required validation
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            // Skip other validations if field is empty and not required
            if (empty($value)) {
                continue;
            }
            
            // Email validation
            if (isset($rule['email']) && $rule['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = 'Invalid email format';
            }
            
            // Minimum length validation
            if (isset($rule['min']) && strlen($value) < $rule['min']) {
                $errors[$field] = ucfirst($field) . ' must be at least ' . $rule['min'] . ' characters';
            }
            
            // Maximum length validation
            if (isset($rule['max']) && strlen($value) > $rule['max']) {
                $errors[$field] = ucfirst($field) . ' must not exceed ' . $rule['max'] . ' characters';
            }
            
            // Pattern validation
            if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                $errors[$field] = isset($rule['message']) ? $rule['message'] : 'Invalid format';
            }
        }
        
        return $errors;
    }
    
    public function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    public function logError($message, $context = []) {
        $log = date('Y-m-d H:i:s') . ' - ' . $message;
        if (!empty($context)) {
            $log .= ' - Context: ' . json_encode($context);
        }
        $log .= PHP_EOL;
        
        file_put_contents('../logs/error.log', $log, FILE_APPEND | LOCK_EX);
    }
}
?>