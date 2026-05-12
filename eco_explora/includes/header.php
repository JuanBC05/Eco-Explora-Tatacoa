<?php
if (!isset($conn)) {
    require_once 'config.php';
}

$usuario_logueado = isLoggedIn();
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuario_rol = $_SESSION['usuario_rol'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" href="img/Favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- ========== Navbar ========== -->
<nav class="navbar">
    <div class="logo">
        <img src="img/Logo.png" alt="Logo">
        <h1>Eco Explora Tatacoa</h1>
    </div>
    <div class="nav-links">
        <a href="index.php">Inicio</a>
        <a href="#hoteles">Hoteles</a>
        <a href="#lugares">Lugares</a>
        <a href="#guias">Guías</a>
        <a href="#contacto">Contacto</a>
        <?php if ($usuario_logueado): ?>
            <div class="user-dropdown">
                <span class="user-name">🌵 <?php echo htmlspecialchars($usuario_nombre); ?> ▼</span>
                <div class="dropdown-menu">
                    <a href="perfil.php">👤 Mi Perfil</a>
                    <a href="dashboard.php">📊 Dashboard</a>
                    <!-- <a href="mis_reservas.php">🎒 Mis Reservas</a> -->
                    <a href="logout.php" class="logout-link">🔓 Cerrar Sesión</a>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php" class="btn-nav">Iniciar Sesión</a>
            <a href="registro.php" class="btn-nav">Registrarse</a>
        <?php endif; ?>
    </div>
</nav>

<main>