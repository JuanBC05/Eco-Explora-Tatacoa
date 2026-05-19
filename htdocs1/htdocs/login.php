<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/config.php';

// Si ya está logueado, ir al inicio
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, complete todos los campos';
    } else {
        // Buscar usuario
        $stmt = $conn->prepare("SELECT Id_usuario, Nombre, Correo, Contrasena, rol, activo FROM usuarios WHERE Correo = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            
            // Verificar contraseña
            if (password_verify($password, $usuario['Contrasena'])) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $usuario['Id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['Nombre'];
                $_SESSION['usuario_email'] = $usuario['Correo'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                
                // Redirigir al inicio
                header('Location: index.php');
                exit();
            } else {
                $error = 'Contraseña incorrecta';
            }
        } else {
            $error = 'El email no está registrado';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Eco Explora Tatacoa</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/Favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <img src="img/Logo.png" alt="Logo" class="auth-logo">
                <h2>Eco Explora Tatacoa</h2>
                <p>Inicia sesión para continuar</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="ejemplo@correo.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required 
                               placeholder="Ingresa tu contraseña">
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">👁️</button>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Iniciar Sesión</button>
            </form>
            
            <div class="auth-footer">
                <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
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