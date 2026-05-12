<?php
require_once 'includes/config.php';
requireLogin();

$usuario_id = $_SESSION['usuario_id'];

$seleccion = $_POST['seleccion'] ?? '';
if (empty($seleccion)) {
    $seleccion = $_SESSION['tour_final'] ?? '';
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

$stmt = $conn->prepare("INSERT INTO tours (id_usuario, id_hotel, id_guia, precio_total, fecha_creacion) VALUES (?, ?, ?, ?, NOW())");

$id_hotel = $tour['hotel']['id'];
$id_guia = $tour['guia']['id'] ?? 0;
$total = $tour['total'];

$stmt->bind_param("iiid", $usuario_id, $id_hotel, $id_guia, $total);

if ($stmt->execute()) {
    $tour_id = $stmt->insert_id;
    
    if (!empty($tour['lugares'])) {
        $stmt2 = $conn->prepare("INSERT INTO tour_lugares (id_tour, id_lugar) VALUES (?, ?)");
        foreach ($tour['lugares'] as $lugar) {
            $stmt2->bind_param("ii", $tour_id, $lugar['id']);
            $stmt2->execute();
        }
        $stmt2->close();
    }
    
    $stmt->close();
    unset($_SESSION['tour_final']);
    unset($_SESSION['tour_seleccion']);
    
    header('Location: tour_exitoso.php');
    exit();
} else {
    $error = "Error al guardar: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/paginicio.css">
    <link rel="stylesheet" href="css/error.css">
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

<div class="error-container">
    <div class="error-card">
        <div class="emoji">❌</div>
        <h1>Error</h1>
        <p><?php echo $error; ?></p>
        <a href="index.php" class="btn-volver">Volver al inicio</a>
    </div>
</div>

</body>
</html>