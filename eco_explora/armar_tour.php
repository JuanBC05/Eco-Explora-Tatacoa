<?php
require_once 'includes/config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Armar mi Tour - Eco Explora Tatacoa</title>
    <link rel="stylesheet" href="css/armar_tour.css">
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
        <a href="logout.php" class="btn-nav">Cerrar Sesión</a>
    </div>
</nav>

<div class="tour-builder">
    <div class="form-card">
        <h1>🏜️ Arma tu Tour</h1>
        <p>Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></p>
        
        <form method="POST" action="confirmar_tour.php">
            <div class="form-group">
                <label>Hotel</label>
                <select name="hotel" required>
                    <option value="1">Hotel Tatacoa - $150,000</option>
                    <option value="2">Ecohotel Cactus - $120,000</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Fecha</label>
                <input type="date" name="fecha" required>
            </div>
            
            <div class="form-group">
                <label>Personas</label>
                <input type="number" name="personas" min="1" value="1" required>
            </div>
            
            <button type="submit" class="btn-submit">Continuar →</button>
        </form>
        
        <a href="index.php" class="btn-back">← Volver</a>
    </div>
</div>

</body>
</html>