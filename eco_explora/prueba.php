<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

echo "✅ Conexión exitosa!";
echo "<br>Base de datos: " . DB_NAME;
echo "<br>Usuario conectado: " . $conn->host_info;
?>