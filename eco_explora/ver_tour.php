<?php
require_once 'includes/config.php';
requireLogin();

$tour_id = $_GET['id'] ?? 0;
$usuario_id = $_SESSION['usuario_id'];

// Obtener el tour y verificar que pertenece al usuario
$tour_query = $conn->prepare("
    SELECT t.*, h.Nombre as hotel_nombre, h.Descripcion as hotel_descripcion
    FROM tours t 
    LEFT JOIN hoteles h ON t.id_hotel = h.Id_hotel 
    WHERE t.id_tour = ? AND t.id_usuario = ?
");
$tour_query->bind_param("ii", $tour_id, $usuario_id);
$tour_query->execute();
$tour = $tour_query->get_result()->fetch_assoc();

if (!$tour) {
    header('Location: dashboard.php');
    exit();
}

// Obtener lugares del tour
$lugares_query = $conn->prepare("
    SELECT l.nombre, l.descripcion, l.tiempo_recorrido, l.precio_lugar
    FROM tour_lugares tl 
    JOIN lugares_turisticos l ON tl.id_lugar = l.id_lugar 
    WHERE tl.id_tour = ?
");
$lugares_query->bind_param("i", $tour_id);
$lugares_query->execute();
$lugares = $lugares_query->get_result();

// Obtener guía si tiene
$guia = null;
if ($tour['id_guia'] > 0) {
    $guia_query = $conn->prepare("SELECT nombre, experiencia, precio_por_hora FROM guias WHERE id_guia = ?");
    $guia_query->bind_param("i", $tour['id_guia']);
    $guia_query->execute();
    $guia = $guia_query->get_result()->fetch_assoc();
    $guia_query->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Tour - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/paginicio.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .detail-container {
            max-width: 800px;
            margin: 100px auto 50px;
            padding: 20px;
        }
        .detail-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .detail-card h1 {
            color: #c26b4a;
            margin-bottom: 20px;
        }
        .detail-section {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e8d5a3;
        }
        .detail-section h3 {
            color: #5a7d3c;
            margin-bottom: 10px;
        }
        .btn-volver {
            background: #ccc;
            color: #3e2a21;
            padding: 10px 25px;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">
        <img src="img/Logo.png" alt="Logo">
        <h1>Eco Explora Tatacoa</h1>
    </div>
    <div class="nav-links">
        <a href="index.php">Inicio</a>
        <a href="dashboard.php">Mis Tours</a>
        <a href="logout.php" class="btn-nav">Salir</a>
    </div>
</nav>

<div class="detail-container">
    <div class="detail-card">
        <h1>🏜️ Detalle del Tour #<?php echo $tour['id_tour']; ?></h1>
        
        <div class="detail-section">
            <h3>🏨 Hotel</h3>
            <p><strong><?php echo htmlspecialchars($tour['hotel_nombre']); ?></strong></p>
            <p><?php echo htmlspecialchars($tour['hotel_descripcion']); ?></p>
        </div>
        
        <div class="detail-section">
            <h3>🏜️ Lugares a visitar</h3>
            <?php while($lugar = $lugares->fetch_assoc()): ?>
                <div style="margin-bottom: 15px;">
                    <strong><?php echo htmlspecialchars($lugar['nombre']); ?></strong>
                    <p><?php echo htmlspecialchars($lugar['descripcion']); ?></p>
                    <small>⏱️ <?php echo $lugar['tiempo_recorrido']; ?> horas - $<?php echo number_format($lugar['precio_lugar'], 0, ',', '.'); ?> COP</small>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php if ($guia): ?>
        <div class="detail-section">
            <h3>🧑‍🌾 Guía</h3>
            <p><strong><?php echo htmlspecialchars($guia['nombre']); ?></strong></p>
            <p>⭐ <?php echo $guia['experiencia']; ?> de experiencia</p>
            <p>💰 $<?php echo number_format($guia['precio_por_hora'], 0, ',', '.'); ?> COP / hora</p>
        </div>
        <?php endif; ?>
        
        <div class="detail-section">
            <h3>💰 Resumen de pago</h3>
            <p><strong>Total pagado:</strong> $<?php echo number_format($tour['precio_total'], 0, ',', '.'); ?> COP</p>
            <p><strong>Fecha de reserva:</strong> <?php echo date('d/m/Y H:i', strtotime($tour['fecha_creacion'])); ?></p>
        </div>
        
        <a href="dashboard.php" class="btn-volver">← Volver a mis tours</a>
    </div>
</div>

</body>
</html>