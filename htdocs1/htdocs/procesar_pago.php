<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$tour_id = $_GET['id'] ?? 0;
$metodo = $_GET['metodo'] ?? '';
$usuario_id = $_SESSION['usuario_id'];

if (!$tour_id || !$metodo) {
    header('Location: dashboard.php');
    exit();
}

// Actualizar el tour como pagado
$stmt = $conn->prepare("UPDATE tours SET estado = 'confirmado', metodo_pago = ?, fecha_pago = NOW() WHERE id_tour = ? AND id_usuario = ?");
$stmt->bind_param("sii", $metodo, $tour_id, $usuario_id);

if ($stmt->execute()) {
    $mensaje = "✅ ¡Pago confirmado! Tu tour ha sido reservado exitosamente.";
    $tipo = "success";
} else {
    $mensaje = "❌ Error al procesar el pago. Por favor contacta con nosotros.";
    $tipo = "error";
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Procesando Pago - Eco Explora Tatacoa</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pago.css">
    <meta http-equiv="refresh" content="3;url=dashboard.php">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="pago-container" style="text-align: center;">
        <div class="resumen-tour" style="max-width: 500px; margin: 0 auto;">
            <?php if ($tipo === 'success'): ?>
                <div style="font-size: 80px;">🎉</div>
                <h1 style="color: #28a745;">¡Pago Exitoso!</h1>
                <p><?php echo $mensaje; ?></p>
                <p>Serás redirigido a tu dashboard en 3 segundos...</p>
            <?php else: ?>
                <div style="font-size: 80px;">❌</div>
                <h1 style="color: #dc3545;">Error en el Pago</h1>
                <p><?php echo $mensaje; ?></p>
                <p>Serás redirigido a tu dashboard en 3 segundos...</p>
            <?php endif; ?>
            
            <a href="dashboard.php" class="btn-confirmar" style="display: inline-block; text-decoration: none; margin-top: 20px;">Volver a mi dashboard</a>
        </div>
    </div>
</body>
</html>