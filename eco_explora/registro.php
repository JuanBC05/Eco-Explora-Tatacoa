<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/config.php';

// Si ya está logueado, redirigir
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    
    // Validaciones
    $errores = [];
    
    if (strlen($nombre) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Email no válido';
    }
    
    if (strlen($password) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if ($password !== $confirm_password) {
        $errores[] = 'Las contraseñas no coinciden';
    }
    
    // Verificar si el email ya existe
    if (empty($errores)) {
        $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE Correo = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errores[] = 'Este email ya está registrado';
        }
        $stmt->close();
    }
    
    // Si no hay errores, registrar usuario
    if (empty($errores)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO usuarios (Nombre, Correo, Contrasena, telefono, rol, fecha_registro) VALUES (?, ?, ?, ?, 'usuario', NOW())");
        $stmt->bind_param("ssss", $nombre, $email, $hashed_password, $telefono);
        
        if ($stmt->execute()) {
            $success = 'Registro exitoso. Ahora puedes iniciar sesión.';
            // Limpiar campos
            $nombre = $email = $telefono = '';
        } else {
            $errores[] = 'Error al registrar: ' . $conn->error;
        }
        $stmt->close();
    }
    
    if (!empty($errores)) {
        $error = implode('<br>', $errores);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/Favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <img src="img/Logo.png" alt="Logo" class="auth-logo">
                <h2>Crear Cuenta</h2>
                <p>Únete a Eco Explora Tatacoa</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <br><a href="login.php" style="color: white; text-decoration: underline;">Iniciar sesión ahora</a>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="nombre">Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" required 
                           value="<?php echo htmlspecialchars($nombre ?? ''); ?>"
                           placeholder="Tu nombre completo">
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>"
                           placeholder="ejemplo@correo.com">
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" 
                           value="<?php echo htmlspecialchars($telefono ?? ''); ?>"
                           placeholder="Opcional">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required 
                               placeholder="Mínimo 6 caracteres">
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">👁️</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña *</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Repite tu contraseña">
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">👁️</button>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Registrarse</button>
            </form>
            
            <div class="auth-footer">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            </div>
        </div>
    </div>
    
    <script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
        field.setAttribute('type', type);
    }
    </script>
</body>
</html>