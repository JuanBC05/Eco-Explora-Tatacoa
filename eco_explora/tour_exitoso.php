<?php
require_once 'includes/config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Confirmado - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/paginicio.css">
    <link rel="stylesheet" href="css/exito.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <div class="logo">
        <img src="img/Logo.png" alt="Logo">
        <h1>Eco Explora Tatacoa</h1>
    </div>
    <div class="nav-links">
        <a href="index.php">Inicio</a>
        <a href="logout.php" class="btn-nav">Salir</a>
    </div>
</nav>

<div class="success-container">
    <div class="success-card">
        <div class="emoji">🏜️✨</div>
        <h1>¡Tour Confirmado!</h1>
        <p>Tu aventura por el desierto de la Tatacoa está lista.</p>
        <p>Te enviaremos los detalles a tu correo electrónico.</p>
        <a href="dashboard.php" class="btn-dashboard">Ver mis Tours</a>
        <br><br>
        <a href="index.php" style="color: #7a5a4a;">← Volver al inicio</a>
    </div>
</div>

</body>
</html>