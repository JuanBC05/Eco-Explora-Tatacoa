<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Paso 1: Iniciando...<br>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Paso 2: Sesión iniciada<br>";

require_once 'includes/config.php';
echo "Paso 3: Config cargado<br>";

$usuario_logueado = isset($_SESSION['usuario_id']);
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
echo "Paso 4: Variables de usuario<br>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Header</title>
</head>
<body>
    <h1>Test</h1>
    <p>Usuario logueado: <?php echo $usuario_logueado ? 'SI' : 'NO'; ?></p>
    <p>Nombre: <?php echo $usuario_nombre; ?></p>
    <p>Paso 5: Hasta aquí bien</p>
</body>
</html>