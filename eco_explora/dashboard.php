<?php
require_once 'includes/config.php';
requireLogin();

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];
$usuario_rol = $_SESSION['usuario_rol'];

$mensaje = '';
$error = '';

// Procesar eliminación de tour
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $tour_id = $_GET['eliminar'];
    
    $check = $conn->prepare("SELECT id_tour FROM tours WHERE id_tour = ? AND id_usuario = ?");
    $check->bind_param("ii", $tour_id, $usuario_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $delete_lugares = $conn->prepare("DELETE FROM tour_lugares WHERE id_tour = ?");
        $delete_lugares->bind_param("i", $tour_id);
        $delete_lugares->execute();
        $delete_lugares->close();
        
        $delete_tour = $conn->prepare("DELETE FROM tours WHERE id_tour = ?");
        $delete_tour->bind_param("i", $tour_id);
        
        if ($delete_tour->execute()) {
            $mensaje = "Tour eliminado correctamente";
        } else {
            $error = "Error al eliminar el tour";
        }
        $delete_tour->close();
    } else {
        $error = "No tienes permiso para eliminar este tour";
    }
    $check->close();
}

// Obtener los tours del usuario
$tours_query = $conn->prepare("
    SELECT t.*, h.Nombre as hotel_nombre 
    FROM tours t 
    LEFT JOIN hoteles h ON t.id_hotel = h.Id_hotel 
    WHERE t.id_usuario = ? 
    ORDER BY t.fecha_creacion DESC
");
$tours_query->bind_param("i", $usuario_id);
$tours_query->execute();
$tours = $tours_query->get_result();

$total_tours = $tours->num_rows;
$total_gastado = 0;
$tours_data = [];

while ($tour = $tours->fetch_assoc()) {
    $total_gastado += $tour['precio_total'];
    
    $lugares_query = $conn->prepare("
        SELECT l.nombre 
        FROM tour_lugares tl 
        JOIN lugares_turisticos l ON tl.id_lugar = l.id_lugar 
        WHERE tl.id_tour = ?
    ");
    $lugares_query->bind_param("i", $tour['id_tour']);
    $lugares_query->execute();
    $lugares = $lugares_query->get_result();
    
    $tour['lugares'] = [];
    while ($lugar = $lugares->fetch_assoc()) {
        $tour['lugares'][] = $lugar['nombre'];
    }
    $lugares_query->close();
    
    $tours_data[] = $tour;
}
$tours_query->close();
?>

<?php include 'includes/header.php'; ?>


<link rel="stylesheet" href="css/dashboard.css">

<div class="dashboard-container">
    <?php if ($mensaje): ?>
        <div class="alert alert-success">✅ <?php echo $mensaje; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="welcome-card">
        <h1>¡Bienvenido, <?php echo htmlspecialchars($usuario_nombre); ?>!</h1>
        <p>Gestiona tus aventuras en el desierto de la Tatacoa</p>
        <span class="badge"><?php echo $usuario_rol === 'admin' ? '👑 Administrador' : '🌵 Viajero'; ?></span>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_tours; ?></div>
            <div class="stat-label">Tours realizados</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">$<?php echo number_format($total_gastado, 0, ',', '.'); ?></div>
            <div class="stat-label">Total invertido</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">🏜️</div>
            <div class="stat-label">Desierto Tatacoa</div>
        </div>
    </div>

    <div class="section-card">
        <h2>Mis Tours</h2>
        
        <?php if (count($tours_data) > 0): ?>
            <div class="table-responsive">
                <table class="tours-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hotel</th>
                            <th>Lugares</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tours_data as $tour): ?>
                        <tr>
                            <td>#<?php echo $tour['id_tour']; ?></td>
                            <td><?php echo htmlspecialchars($tour['hotel_nombre']); ?></td>
                            <td>
                                <ul class="lugares-list">
                                    <?php foreach($tour['lugares'] as $lugar): ?>
                                        <li><?php echo htmlspecialchars($lugar); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($tour['fecha_creacion'])); ?></td>
                            <td>$<?php echo number_format($tour['precio_total'], 0, ',', '.'); ?></td>
                            <td><span class="status-confirmado">✅ Confirmado</span></td>
                            <td class="acciones">
                                <?php if ($tour['estado'] == 'pendiente'): ?>
                                <a href="pago.php?id=<?php echo $tour['id_tour']; ?>" class="btn-pagar">💰 Pagar</a>
                                <?php endif; ?>
                                <!-- <a href="index.php?editar_tour=<?php echo $tour['id_tour']; ?>" class="btn-editar">✏️ Editar</a> -->
                                <button class="btn-eliminar" onclick="mostrarModalEliminar(<?php echo $tour['id_tour']; ?>)">🗑️ Eliminar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-tours">
                <div class="emoji">🏜️</div>
                <p>Todavía no tienes tours reservados</p>
                <a href="index.php" class="btn-explorar">Explorar Tours</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div id="modalEliminar" class="modal-eliminar">
    <div class="modal-contenido">
        <h3>🗑️ Eliminar Tour</h3>
        <p>¿Estás seguro de que deseas eliminar este tour?</p>
        <p style="font-size: 14px; color: var(--terracota);">Esta acción no se puede deshacer.</p>
        <div class="modal-buttons">
            <button class="btn-cancelar-modal" onclick="cerrarModal()">Cancelar</button>
            <a href="#" id="confirmarEliminar" class="btn-confirmar">Eliminar</a>
        </div>
    </div>
</div>

<script>
function mostrarModalEliminar(tourId) {
    const modal = document.getElementById('modalEliminar');
    const confirmarLink = document.getElementById('confirmarEliminar');
    confirmarLink.href = '?eliminar=' + tourId;
    modal.style.display = 'flex';
}

function cerrarModal() {
    const modal = document.getElementById('modalEliminar');
    modal.style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('modalEliminar');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

</main>
</body>
</html>