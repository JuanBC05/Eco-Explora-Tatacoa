<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========== CONFIGURACIÓN PARA LOCAL (XAMPP) ==========
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eco explora tatacoa');  // Cambia por el nombre de tu BD local
define('SITE_NAME', 'Eco Explora Tatacoa');
define('BASE_URL', 'http://localhost/eco_explora/');

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