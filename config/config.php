<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application settings
define('APP_NAME', 'Zeely');
define('BASE_URL', 'http://localhost/projectb2');
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Security settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('REMEMBER_TOKEN_LIFETIME', 30 * 24 * 3600); // 30 days
define('CSRF_TOKEN_NAME', 'csrf_token');

// Base de données 
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'projetb2');
define('DB_USER', 'projetb2');
define('DB_PASS', 'password');

// Autoload des classes
spl_autoload_register(function ($class_name) {
    $directories = ['models/', 'controllers/', 'utils/','config/'];
    
    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Fonctions de sécurité et session
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

function handleError($message, $redirect = null) {
    $_SESSION['error'] = $message;
    if ($redirect) {
        header("Location: $redirect");
        exit();
    }
}

function handleSuccess($message, $redirect = null) {
    $_SESSION['success'] = $message;
    if ($redirect) {
        header("Location: $redirect");
        exit();
    }
}
