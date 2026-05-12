<?php
require_once 'includes/config.php';
requireLogin();

// Obtener la selección del localStorage (viene del index)
$seleccion = $_POST['seleccion'] ?? '';
if (empty($seleccion)) {
    $seleccion = $_SESSION['tour_seleccion'] ?? '';
}

if (empty($seleccion)) {
    header('Location: index.php');
    exit();
}

if (is_string($seleccion)) {
    $tour = json_decode($seleccion, true);
} else {
    $tour = $seleccion;
}

if (!$tour) {
    header('Location: index.php');
    exit();
}

$_SESSION['tour_final'] = $tour;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Tour - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/paginicio.css">
    <link rel="stylesheet" href="css/confirmar.css">
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

<div class="confirm-container">
    <div class="confirm-card">
        <h1>Confirma tu Tour</h1>
        <p>Revisa los detalles de tu experiencia en el desierto</p>

        <!-- HOTEL -->
        <div class="seccion-resumen">
            <h3>🏨 Hotel</h3>
            <div class="resumen-item">
                <span class="label">Nombre:</span>
                <span class="value"><?php echo htmlspecialchars($tour['hotel']['nombre']); ?></span>
            </div>
            <div class="resumen-item">
                <span class="label">Personas:</span>
                <span class="value"><?php echo $tour['personas']; ?> persona(s)</span>
            </div>
            <div class="resumen-item">
                <span class="label">Precio:</span>
                <span class="value">$<?php echo number_format($tour['precio_hotel'], 0, ',', '.'); ?> COP</span>
            </div>
        </div>

        <!-- LUGARES -->
        <div class="seccion-resumen">
            <h3>🏜️ Lugares a visitar</h3>
            <?php foreach($tour['lugares'] as $lugar): ?>
            <div class="resumen-item">
                <span class="label"><?php echo htmlspecialchars($lugar['nombre']); ?>:</span>
                <span class="value"><?php echo $lugar['tiempo']; ?> horas - $<?php echo number_format($lugar['precio'], 0, ',', '.'); ?> COP</span>
            </div>
            <?php endforeach; ?>
            <div class="resumen-item">
                <span class="label">⏱️ Tiempo total:</span>
                <span class="value"><?php echo $tour['tiempo_total']; ?> horas</span>
            </div>
            <div class="resumen-item">
                <span class="label">💰 Precio lugares:</span>
                <span class="value">$<?php echo number_format($tour['precio_lugares'], 0, ',', '.'); ?> COP</span>
            </div>
        </div>

        <!-- GUÍA (si seleccionó) -->
        <?php if ($tour['guia']): ?>
        <div class="seccion-resumen">
            <h3>🧑‍🌾 Guía</h3>
            <div class="resumen-item">
                <span class="label">Nombre:</span>
                <span class="value"><?php echo htmlspecialchars($tour['guia']['nombre']); ?></span>
            </div>
            <div class="resumen-item">
                <span class="label">Precio por hora:</span>
                <span class="value">$<?php echo number_format($tour['guia']['precio_hora'], 0, ',', '.'); ?> COP</span>
            </div>
            <div class="resumen-item">
                <span class="label">Total guía:</span>
                <span class="value">$<?php echo number_format($tour['precio_guia'], 0, ',', '.'); ?> COP</span>
            </div>
        </div>
        <?php endif; ?>

        <!-- TOTAL -->
        <div class="total-final">
            <span>💰 TOTAL A PAGAR:</span>
            <span>$<?php echo number_format($tour['total'], 0, ',', '.'); ?> COP</span>
        </div>

        <!-- BOTONES -->
        <div class="btn-group">
            <a href="index.php" class="btn-volver">← Volver y modificar</a>
            <form method="POST" action="guardar_tour.php" style="flex: 1;">
                <input type="hidden" name="seleccion" value='<?php echo json_encode($tour); ?>'>
                <button type="submit" class="btn-confirmar">✅ Confirmar Reserva</button>
            </form>
        </div>
        
</div>
    </div>
</div>

</body>
</html>