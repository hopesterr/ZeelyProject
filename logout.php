<?php
require_once 'config/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy session
session_destroy();

// Start new session for flash message
session_start();
$_SESSION['success'] = 'Vous êtes déconnecté(e) avec succès !';

// Redirect to home page
header('Location: index.php');
exit();
?>