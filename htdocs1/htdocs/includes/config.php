<?php
error_reporting(0);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========== CONFIGURACIÓN PARA INFINITYFREE ==========
define('DB_HOST', 'sql200.infinityfree.com');
define('DB_USER', 'if0_41877507');
define('DB_PASS', 'ecoexplora0905');
define('DB_NAME', 'if0_41877507_ecoexplorafinal');
define('SITE_NAME', 'Eco Explora Tatacoa');
define('BASE_URL', 'https://ecoexploratatacoa.page.gd/');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Error de conexión local: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

date_default_timezone_set('America/Bogota');

function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function isAdmin() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

function isGuia() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'guia';
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
        die('Acceso denegado');
    }
}
?>