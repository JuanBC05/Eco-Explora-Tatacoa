<?php
require_once 'includes/config.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['usuario_id'] ?? 0;

if ($user_id == 0) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $nombre = trim($_POST['nombre'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        
        if (strlen($nombre) < 3) {
            $error = 'El nombre debe tener al menos 3 caracteres';
        } else {
            $stmt = $conn->prepare("UPDATE usuarios SET Nombre = ?, telefono = ? WHERE Id_usuario = ?");
            $stmt->bind_param("ssi", $nombre, $telefono, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['usuario_nombre'] = $nombre;
                $success = 'Perfil actualizado exitosamente';
            } else {
                $error = 'Error al actualizar el perfil';
            }
            $stmt->close();
        }
    }
    
    if ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $stmt = $conn->prepare("SELECT Contrasena FROM usuarios WHERE Id_usuario = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if (!password_verify($current_password, $user['Contrasena'])) {
            $error = 'Contraseña actual incorrecta';
        } elseif (strlen($new_password) < 6) {
            $error = 'La nueva contraseña debe tener al menos 6 caracteres';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Las contraseñas nuevas no coinciden';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET Contrasena = ? WHERE Id_usuario = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = 'Contraseña actualizada exitosamente';
            } else {
                $error = 'Error al actualizar la contraseña';
            }
            $stmt->close();
        }
    }
}

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT Nombre, Correo, telefono, fecha_registro FROM usuarios WHERE Id_usuario = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();
?>

<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="/css/perfil.css">

<div class="perfil-wrapper">
    <div class="perfil-sidebar">
        <div class="avatar">
            <?php echo strtoupper(substr($user_data['Nombre'], 0, 2)); ?>
        </div>
        <h3><?php echo htmlspecialchars($user_data['Nombre']); ?></h3>
        <p><?php echo htmlspecialchars($user_data['Correo']); ?></p>
        
        <button class="nav-btn active" onclick="showTab('resumen')">📊 Resumen</button>
        <button class="nav-btn" onclick="showTab('configuracion')">⚙️ Configuración</button>
    </div>
    
    <div class="perfil-content">
        <div id="resumen" class="tab-content active">
            <h2>Mi Resumen</h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">1</div>
                    <p>Total Tours</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <p>Tours Completados</p>
                </div>
            </div>
            
            <div class="config-section">
                <h3>Información de la cuenta</h3>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($user_data['Nombre']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['Correo']); ?></p>
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($user_data['telefono'] ?: 'No registrado'); ?></p>
                <!-- <p><strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($user_data['fecha_registro'])); ?></p> -->
                <p><strong>Miembro desde:</strong> <?php echo $user_data['fecha_registro'] ? date('d/m/Y', strtotime($user_data['fecha_registro'])) : 'No disponible'; ?></p>
            </div>
        </div>
        
        <div id="configuracion" class="tab-content">
            <h2>Configuración</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="config-section">
                <h3>Editar información</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($user_data['Nombre']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="tel" name="telefono" value="<?php echo htmlspecialchars($user_data['telefono']); ?>">
                    </div>
                    
                    <button type="submit" class="btn-primary">Actualizar</button>
                </form>
            </div>
            
            <div class="config-section">
                <h3>Cambiar contraseña</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label>Contraseña actual</label>
                        <input type="password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nueva contraseña</label>
                        <input type="password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirmar contraseña</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn-primary">Cambiar contraseña</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.getElementById(tabId).classList.add('active');
        
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
    }
</script>

</main>
</body>
</html>