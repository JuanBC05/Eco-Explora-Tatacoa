<?php
require_once '../includes/config.php';
session_start();

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$tour_id = $data['id_tour'] ?? 0;
$hotel_id = $data['hotel_id'] ?? null;
$guia_id = $data['id_guia'] ?? null;
$fecha_llegada = $data['fecha_llegada'] ?? null;
$fecha_salida = $data['fecha_salida'] ?? null;
$noches = $data['noches'] ?? 0;
$precio_total = $data['precio_total'] ?? 0;
$lugares = $data['lugares'] ?? [];

$usuario_id = $_SESSION['usuario_id'];

// Verificar que el tour pertenece al usuario
$check = $conn->prepare("SELECT id_tour FROM tours WHERE id_tour = ? AND id_usuario = ?");
$check->bind_param("ii", $tour_id, $usuario_id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Tour no encontrado']);
    exit();
}
$check->close();

// Actualizar tour
$update = $conn->prepare("
    UPDATE tours 
    SET id_hotel = ?, id_guia = ?, fecha_llegada = ?, fecha_salida = ?, noches = ?, precio_total = ? 
    WHERE id_tour = ?
");
$update->bind_param("iissiii", $hotel_id, $guia_id, $fecha_llegada, $fecha_salida, $noches, $precio_total, $tour_id);

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
    $delete->close();
    
    echo json_encode(['success' => true, 'message' => 'Tour actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $conn->error]);
}
$update->close();
?>