<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../includes/config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Inicia sesión para dejar una reseña']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$calificacion = $data['calificacion'] ?? 0;
$comentario = trim($data['comentario'] ?? '');
$usuario_id = $_SESSION['usuario_id'];

if ($calificacion < 1 || $calificacion > 5) {
    echo json_encode(['success' => false, 'message' => 'Calificación no válida']);
    exit();
}

if (strlen($comentario) < 10) {
    echo json_encode(['success' => false, 'message' => 'El comentario debe tener al menos 10 caracteres']);
    exit();
}

// Verificar si la tabla existe
$check_table = $conn->query("SHOW TABLES LIKE 'resenas'");
if ($check_table->num_rows == 0) {
    // Crear la tabla si no existe
    $conn->query("
        CREATE TABLE IF NOT EXISTS resenas (
            id_resena INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NOT NULL,
            calificacion INT NOT NULL,
            comentario TEXT NOT NULL,
            fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
            estado VARCHAR(20) DEFAULT 'aprobado'
        )
    ");
}
$stmt = $conn->prepare("INSERT INTO resenas (id_usuario, calificacion, comentario, fecha, estado) VALUES (?, ?, ?, NOW(), 'aprobado')");
// $stmt = $conn->prepare("INSERT INTO resenas (id_usuario, calificacion, comentario, fecha, estado) VALUES (?, ?, ?, NOW(), 'aprobado')");
$stmt->bind_param("iis", $usuario_id, $calificacion, $comentario);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '¡Gracias por tu reseña!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
}
$stmt->close();
?>