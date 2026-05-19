<?php
require_once '../includes/config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Inicia sesión para guardar tu tour']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$usuario_id = $_SESSION['usuario_id'];
$hotel_id = $data['hotel_id'] ?? null;
$guia_id = $data['id_guia'] ?? null;
$fecha_llegada = $data['fecha_llegada'] ?? null;
$fecha_salida = $data['fecha_salida'] ?? null;
$noches = $data['noches'] ?? 0;
$precio_total = $data['precio_total'] ?? 0;
$lugares = $data['lugares'] ?? [];

// Validaciones
if (!$hotel_id) {
    echo json_encode(['success' => false, 'message' => 'Selecciona un hotel']);
    exit();
}

if (!$fecha_llegada || !$fecha_salida) {
    echo json_encode(['success' => false, 'message' => 'Selecciona las fechas de tu viaje']);
    exit();
}

if ($noches <= 0) {
    echo json_encode(['success' => false, 'message' => 'Las fechas seleccionadas no son válidas']);
    exit();
}

// Guardar tour
$stmt = $conn->prepare("
    INSERT INTO tours (id_usuario, id_hotel, id_guia, fecha_llegada, fecha_salida, noches, precio_total, fecha_creacion, estado) 
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'pendiente')
");
$stmt->bind_param("iiissiii", $usuario_id, $hotel_id, $guia_id, $fecha_llegada, $fecha_salida, $noches, $precio_total);

if ($stmt->execute()) {
    $tour_id = $stmt->insert_id;
    
    // Guardar lugares
    if (!empty($lugares)) {
        $stmt_lugar = $conn->prepare("INSERT INTO tour_lugares (id_tour, id_lugar) VALUES (?, ?)");
        foreach ($lugares as $lugar_id) {
            $stmt_lugar->bind_param("ii", $tour_id, $lugar_id);
            $stmt_lugar->execute();
        }
        $stmt_lugar->close();
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Tour guardado exitosamente',
        'tour_id' => $tour_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $conn->error]);
}
$stmt->close();
?>