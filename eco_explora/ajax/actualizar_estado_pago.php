<?php
require_once '../includes/config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$tour_id = $data['id'] ?? 0;
$metodo = $data['metodo'] ?? '';

if ($tour_id) {
    $stmt = $conn->prepare("UPDATE tours SET estado = 'confirmado', metodo_pago = ? WHERE id_tour = ?");
    $stmt->bind_param("si", $metodo, $tour_id);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => true]);
?>