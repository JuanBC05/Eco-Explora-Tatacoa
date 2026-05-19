<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$tour_id = $_GET['id'] ?? 0; //obtiene el toru desde la URL

if ($tour_id == 0) {
    header('Location: dashboard.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del tour
$stmt = $conn->prepare("
    SELECT t.*, 
           h.Nombre as hotel_nombre,
           g.nombre as guia_nombre
    FROM tours t 
    LEFT JOIN hoteles h ON t.id_hotel = h.Id_hotel 
    LEFT JOIN guias g ON t.id_guia = g.id_guia
    WHERE t.id_tour = ? AND t.id_usuario = ?
");
$stmt->bind_param("ii", $tour_id, $usuario_id);
$stmt->execute();
$tour = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tour) {
    header('Location: dashboard.php');
    exit();
}

// Obtener lugares
$lugares = [];
$precio_lugares = 0;
$stmt = $conn->prepare("
    SELECT l.nombre, l.precio_lugar, l.tiempo_recorrido 
    FROM tour_lugares tl 
    JOIN lugares_turisticos l ON tl.id_lugar = l.id_lugar 
    WHERE tl.id_tour = ?
");
$stmt->bind_param("i", $tour_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $lugares[] = $row;
    $precio_lugares += $row['precio_lugar'];
}
$stmt->close();
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="css/pago.css">

<div class="pago-container">
    <div class="pago-header">
        <h1>💰 Finalizar Pago</h1>
        <p>Completa tu reserva en el Desierto de la Tatacoa</p>
    </div>
        <!--  BARRA DE RESUMEN -->
    <div class="progress-bar">
        <h4>📋 Resumen detallado de tu Tour</h4>
        
        <!-- Hotel -->
        <div class="progress-step">
            <span class="check">✅</span>
            <span>🏨 <strong>Hotel:</strong> <?php echo htmlspecialchars($tour['hotel_nombre'] ?? 'No especificado'); ?></span>
            <span class="precio">$<?php echo number_format($tour['precio_total'] ?? 0, 0, ',', '.'); ?></span>
        </div>
        
        <!-- Fechas -->
        <!-- <div class="progress-step">
            <span class="check">📅</span>
            <span><strong>Fechas:</strong> 
                <?php 
                if ($tour['fecha_llegada'] && $tour['fecha_salida']) {
                    echo date('d/m/Y', strtotime($tour['fecha_llegada'])) . ' hasta ' . date('d/m/Y', strtotime($tour['fecha_salida']));
                } else {
                    echo 'No especificadas';
                }
                ?>
            </span>
            <span class="precio"><?php echo $tour['noches'] ?? 0; ?> noches</span>
        </div> -->
        
        <!-- Personas
        <div class="progress-step">
            <span class="check">👥</span>
            <span><strong>Personas:</strong> <?php echo $tour['personas'] ?? 1; ?> adulto(s)</span>
            <span class="precio"></span>
        </div> -->
        
        <!-- Lugares (detallando) -->
        <div class="progress-step lugares-header">
            <span class="check">🏜️</span>
            <span><strong>Lugares a visitar:</strong></span>
            <span class="precio">Subtotal</span>
        </div>
        
        <?php foreach($lugares as $lugar): ?>
        <div class="progress-step lugar-item">
            <span></span>
            <span>• <?php echo htmlspecialchars($lugar['nombre']); ?> (<?php echo $lugar['tiempo_recorrido']; ?> horas)</span>
            <span class="precio">$<?php echo number_format($lugar['precio_lugar'], 0, ',', '.'); ?></span>
        </div>
        <?php endforeach; ?>
        
        <!-- Subtotal lugares -->
        <div class="progress-step subtotal">
            <span></span>
            <span><strong>Subtotal lugares:</strong></span>
            <span class="precio">$<?php echo number_format($precio_lugares, 0, ',', '.'); ?></span>
        </div>
        
        <!-- Guía -->
        <?php if (!empty($tour['guia_nombre'])): ?>
        <div class="progress-step">
            <span class="check">🧑‍🌾</span>
            <span><strong>Guía turístico:</strong> <?php echo htmlspecialchars($tour['guia_nombre']); ?></span>
            <span class="precio">Incluido</span>
        </div>
        <?php endif; ?>
        
        <!-- Línea separadora -->
        <div class="progress-step divider">
            <span></span>
            <span></span>
            <span></span>
        </div>
        
        <!-- TOTAL -->
        <div class="progress-step total">
            <strong>💰 TOTAL A PAGAR:</strong>
            <strong class="precio-final">$<?php echo number_format($tour['precio_total'] ?? 0, 0, ',', '.'); ?> COP</strong>
        </div>
        
        <!-- Nota -->
        <!-- <div class="nota-pago">
            <p>🔒 Pago 100% seguro. Recibirás un comprobante por correo.</p>
        </div> -->
    </div>

    <div class="pago-grid">
        <div class="formulario-pago" style="width: 100%;">
            <h2>💳 Selecciona tu método de pago</h2>
            
            <div class="metodos-pago">
                <div class="metodo" data-metodo="transferencia">
                    <div class="metodo-icono">🏦</div>
                    <div class="metodo-info">
                        <div class="metodo-nombre">Transferencia Bancaria</div>
                        <div class="metodo-descripcion">Paga desde cualquier banco</div>
                    </div>
                </div>
                
                <div class="metodo" data-metodo="nequi">
                    <div class="metodo-icono">📱</div>
                    <div class="metodo-info">
                        <div class="metodo-nombre">Nequi / Daviplata</div>
                        <div class="metodo-descripcion">Paga desde tu celular</div>
                    </div>
                </div>
                
                <div class="metodo" data-metodo="efectivo">
                    <div class="metodo-icono">💵</div>
                    <div class="metodo-info">
                        <div class="metodo-nombre">Pagar en Efectivo</div>
                        <div class="metodo-descripcion">Paga al llegar al hotel</div>
                    </div>
                </div>
            </div>
            
            <div id="datos-transferencia" class="datos-pago" style="display: none;">
                <h3>🏦 Datos Bancarios</h3>
                <div class="dato-row">
                    <span class="dato-label">Banco:</span>
                    <span class="dato-value">Bancolombia</span>
                </div>
                <div class="dato-row">
                    <span class="dato-label">Número:</span>
                    <span class="dato-value">123-456-789</span>
                </div>
                <div class="dato-row">
                    <span class="dato-label">Titular:</span>
                    <span class="dato-value">Eco Explora Tatacoa</span>
                </div>
                <div class="dato-row">
                    <span class="dato-label">Referencia:</span>
                    <span class="dato-value">TOUR-<?php echo str_pad($tour_id, 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="dato-row">
                    <span class="dato-label">Valor:</span>
                    <span class="dato-value">$<?php echo number_format($tour['precio_total'] ?? 0, 0, ',', '.'); ?> COP</span>
                </div>
                <button class="btn-confirmar" onclick="confirmarPago('transferencia')">✅ Ya hice la transferencia</button>
            </div>
            
            <div id="datos-nequi" class="datos-pago" style="display: none;">
                <h3>📱 Nequi / Daviplata</h3>
                <div class="dato-row">
                    <span class="dato-label">Número:</span>
                    <span class="dato-value">321 654 9870</span>
                </div>
                <div class="dato-row">
                    <span class="dato-label">Referencia:</span>
                    <span class="dato-value">TOUR-<?php echo str_pad($tour_id, 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="dato-row">
                    <span class="dato-label">Valor:</span>
                    <span class="dato-value">$<?php echo number_format($tour['precio_total'] ?? 0, 0, ',', '.'); ?> COP</span>
                </div>
                <button class="btn-confirmar" onclick="confirmarPago('nequi')">✅ Ya hice el pago</button>
            </div>
            
            <div id="datos-efectivo" class="datos-pago" style="display: none;">
                <h3>💵 Pago en Efectivo</h3>
                <p>Puedes pagar directamente al llegar al hotel. Tu reserva está garantizada.</p>
                <button class="btn-confirmar" onclick="confirmarPago('efectivo')">✅ Confirmar reserva</button>
            </div>
        </div>
    </div>
</div>

<script src="js/pago.js"></script>

<script>
const tourId = <?php echo $tour_id; ?>;

function confirmarPago(metodo) {
    if (confirm('¿Confirmas que has realizado el pago?')) {
        // Primero actualizar el estado del pago en la base de datos
        fetch('ajax/actualizar_estado_pago.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: tourId, metodo: metodo })
        });
        
        // Mostrar mensaje de éxito
        mostrarMensajeExito(metodo);
    }
}

function mostrarMensajeExito(metodo) {
    let metodoTexto = '';
    let metodoIcono = '';
    
    if (metodo === 'transferencia') {
        metodoTexto = 'transferencia bancaria';
        metodoIcono = '🏦';
    }
    if (metodo === 'nequi') {
        metodoTexto = 'Nequi / Daviplata';
        metodoIcono = '📱';
    }
    if (metodo === 'efectivo') {
        metodoTexto = 'pago en efectivo';
        metodoIcono = '💵';
    }
    
    // Crear el modal
    const modalOverlay = document.createElement('div');
    modalOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9998;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    const modalContent = document.createElement('div');
    modalContent.style.cssText = `
        background: white;
        padding: 35px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        text-align: center;
        max-width: 450px;
        width: 90%;
        animation: slideIn 0.3s ease;
    `;
    
    modalContent.innerHTML = `
        <div style="font-size: 70px; margin-bottom: 10px;">${metodoIcono}</div>
        <div style="font-size: 60px; margin-bottom: 10px;">✅</div>
        <h2 style="color: #28a745; margin-bottom: 15px;">¡Pago Confirmado!</h2>
        <p style="color: #333; margin-bottom: 10px;">Hemos registrado tu pago por <strong>${metodoTexto}</strong>.</p>
        <p style="color: #333; margin-bottom: 10px;">Tu reserva ha sido confirmada exitosamente.</p>
        
        <button onclick="cerrarMensaje(this)" style="
            background: #5a7d3c;
            color: white;
            padding: 10px 35px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s;
        " onmouseover="this.style.background='#3d5a1e'" onmouseout="this.style.background='#5a7d3c'">
            Aceptar
        </button>
    `;
    
    // Agregar estilos de animación
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
    
    modalOverlay.appendChild(modalContent);
    document.body.appendChild(modalOverlay);
}

function cerrarMensaje(btn) {
    const modal = btn.closest('div').parentElement;
    modal.remove();
    
    // Recargar la página para mostrar el estado actualizado
    location.reload();
}
</script>

</main>
</body>
</html>