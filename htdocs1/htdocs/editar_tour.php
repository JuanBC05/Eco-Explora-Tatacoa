<?php
require_once 'includes/config.php';
requireLogin();

$usuario_id = $_SESSION['usuario_id'];
$tour_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Verificar que el tour pertenece al usuario
$check = $conn->prepare("SELECT * FROM tours WHERE id_tour = ? AND id_usuario = ?");
$check->bind_param("ii", $tour_id, $usuario_id);
$check->execute();
$tour = $check->get_result()->fetch_assoc();

if (!$tour) {
    header('Location: dashboard.php');
    exit();
}

// Obtener hoteles para el select
$hoteles = $conn->query("SELECT Id_hotel, Nombre FROM hoteles");

// Obtener lugares seleccionados actualmente
$lugares_seleccionados = [];
$lugares_query = $conn->prepare("SELECT id_lugar FROM tour_lugares WHERE id_tour = ?");
$lugares_query->bind_param("i", $tour_id);
$lugares_query->execute();
$result = $lugares_query->get_result();
while ($row = $result->fetch_assoc()) {
    $lugares_seleccionados[] = $row['id_lugar'];
}
$lugares_query->close();

// Obtener todos los lugares disponibles
$todos_lugares = $conn->query("SELECT id_lugar, nombre FROM lugares_turisticos");

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hotel_id = $_POST['hotel_id'] ?? null;
    $precio_total = $_POST['precio_total'] ?? 0;
    $lugares = $_POST['lugares'] ?? [];
    
    // Actualizar tour
    $update = $conn->prepare("UPDATE tours SET id_hotel = ?, precio_total = ? WHERE id_tour = ?");
    $update->bind_param("idi", $hotel_id, $precio_total, $tour_id);
    
    if ($update->execute()) {
        // Eliminar lugares antiguos
        $delete = $conn->prepare("DELETE FROM tour_lugares WHERE id_tour = ?");
        $delete->bind_param("i", $tour_id);
        $delete->execute();
        
        // Insertar nuevos lugares
        if (!empty($lugares)) {
            $insert = $conn->prepare("INSERT INTO tour_lugares (id_tour, id_lugar) VALUES (?, ?)");
            foreach ($lugares as $lugar_id) {
                $insert->bind_param("ii", $tour_id, $lugar_id);
                $insert->execute();
            }
            $insert->close();
        }
        
        $success = "Tour actualizado correctamente";
        echo '<meta http-equiv="refresh" content="2;url=index.php">';
    } else {
        $error = "Error al actualizar el tour";
    }
    $update->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Tour - Eco Explora Tatacoa</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/editar_tour.css">
    <link rel="icon" href="img/Favicon.ico">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="editar-container">
        <h1>✏️ Editar Tour</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?php echo $success; ?> Redirigiendo...</div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>🏨 Hotel</label>
                <select name="hotel_id" required>
                    <option value="">Selecciona un hotel</option>
                    <?php while($hotel = $hoteles->fetch_assoc()): ?>
                        <option value="<?php echo $hotel['Id_hotel']; ?>" <?php echo ($tour['id_hotel'] == $hotel['Id_hotel']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($hotel['Nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>🏜️ Lugares a visitar</label>
                <select name="lugares[]" multiple size="5">
                    <?php while($lugar = $todos_lugares->fetch_assoc()): ?>
                        <option value="<?php echo $lugar['id_lugar']; ?>" 
                            <?php echo in_array($lugar['id_lugar'], $lugares_seleccionados) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lugar['nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <small>Mantén presionada la tecla Ctrl (Windows) o Cmd (Mac) para seleccionar múltiples lugares</small>
            </div>
            
            <div class="form-group">
                <label>💰 Precio total</label>
                <input type="number" name="precio_total" value="<?php echo $tour['precio_total']; ?>" required step="1000">
            </div>
            
            <div class="botones">
                <button type="submit" class="btn-guardar">💾 Guardar cambios</button>
                <a href="index.php" class="btn-cancelar">❌ Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>