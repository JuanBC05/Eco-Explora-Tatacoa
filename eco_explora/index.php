
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/config.php';

// ========== EDITAR TOUR ==========
if (isset($_GET['editar_tour']) && is_numeric($_GET['editar_tour']) && isLoggedIn()) {
    $tour_id = $_GET['editar_tour'];
    $usuario_id = $_SESSION['usuario_id'];
    
    // Obtener datos del tour
    $stmt = $conn->prepare("
        SELECT t.*, h.Nombre as hotel_nombre, h.Id_hotel as hotel_id, h.precio_por_persona,
               g.nombre as guia_nombre, g.id_guia as guia_id
        FROM tours t 
        LEFT JOIN hoteles h ON t.id_hotel = h.Id_hotel 
        LEFT JOIN guias g ON t.id_guia = g.id_guia
        WHERE t.id_tour = ? AND t.id_usuario = ?
    ");
    $stmt->bind_param("ii", $tour_id, $usuario_id);
    $stmt->execute();
    $tour_editando = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($tour_editando) {
        // Obtener lugares del tour
        $stmt = $conn->prepare("SELECT id_lugar FROM tour_lugares WHERE id_tour = ?");
        $stmt->bind_param("i", $tour_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $lugares_editando = [];
        while ($row = $result->fetch_assoc()) {
            $lugares_editando[] = $row['id_lugar'];
        }
        $stmt->close();
        
        // Guardar en sesión
        $_SESSION['tour_editando_id'] = $tour_id;
        $_SESSION['tour_editando_datos'] = $tour_editando;
        $_SESSION['tour_editando_lugares'] = $lugares_editando;
        
        // Redirigir al index
        echo "<script>window.location.href = 'index.php#hoteles';</script>";
        exit();
    }
}
?>

<?php include 'includes/header.php'; ?>

<!-- Variables para edición de tour -->
<script>
    var tourEditandoData = <?php echo isset($_SESSION['tour_editando_datos']) && $_SESSION['tour_editando_datos'] ? json_encode($_SESSION['tour_editando_datos']) : 'null'; ?>;
    var tourEditandoLugares = <?php echo isset($_SESSION['tour_editando_lugares']) ? json_encode($_SESSION['tour_editando_lugares']) : '[]'; ?>;
    var tourEditandoId = <?php echo isset($_SESSION['tour_editando_id']) ? $_SESSION['tour_editando_id'] : 'null'; ?>;

    <?php 
    unset($_SESSION['tour_editando_datos']);
    unset($_SESSION['tour_editando_lugares']);
    unset($_SESSION['tour_editando_id']);
    ?>
</script>

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<link rel="stylesheet" href="css/paginicio.css">

<!-- ========== Seccion principal ========== -->
<section id="inicio" class="hero" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('img/Tatacoa1.jpg');">
    <div class="hero-content">
        <h1>Descubre el Mágico Desierto de la Tatacoa</h1>
        <p>Explora paisajes únicos, observa las estrellas y vive una experiencia inolvidable</p>
        <a href="#hoteles" class="btn-hero">Armar mi Tour →</a>
    </div>
</section>

<!-- ========== BARRA DE RESUMEN ========== -->
<div class="progress-bar" id="progressBar">
    <h4>📋 Resumen de mi Tour</h4>
    <div class="progress-step" id="progress-tiempo">
        <span>⏱️ Tiempo total: <span id="tiempo_total">0</span> horas</span>
    </div>
    <div class="progress-step" id="progress-hotel">
        <span class="check" id="hotel-check">⬜</span>
        <span>🏨 Hotel: <span id="selected_hotel_name">Ninguno</span></span>
        <span class="precio" id="hotel-precio">$0</span>
    </div>
    <div class="progress-step" id="progress-lugares">
        <span class="check" id="lugares-check">⬜</span>
        <span>🏜️ Lugares: <span id="selected_lugares_count">0</span></span>
        <span class="precio" id="lugares-precio">$0</span>
    </div>
    <!-- <div class="progress-step" id="progress-tiempo">
        <span>⏱️ Tiempo total: <span id="tiempo_total">0</span> horas</span>
    </div> -->
    <div class="progress-step" id="progress-guia">
        <span class="check" id="guia-check">⬜</span>
        <span>🧑‍🌾 Guía: <span id="selected_guia_name">Ninguno</span></span>
        <span class="precio" id="guia-precio">$0</span>
    </div>
    <div class="progress-step total">
        <strong>💰 TOTAL: <span id="total_final">$0</span> COP</strong>
    </div>
    <button class="btn-finalizar" id="btnFinalizar" style="display: none;">✅ Finalizar Tour</button>
</div>

<!-- ========== Bienvenida ========== -->
<section>
    <div class="section-title">Bienvenidos al Desierto de la Tatacoa</div>
    <div class="cards-container">
        <div class="card">
            <img src="img/Tatacoa1.jpg" alt="Paisaje">
            <div class="card-content">
                <h3>🌵 Paisajes Únicos</h3>
                <p>El segundo desierto más árido de Colombia, con formaciones rocosas impresionantes.</p>
            </div>
        </div>
        <div class="card">
            <img src="img/Tatacoa3.jpg" alt="Estrellas">
            <div class="card-content">
                <h3>✨ Observación de Estrellas</h3>
                <p>Uno de los mejores lugares del mundo para observar el cielo nocturno.</p>
            </div>
        </div>
        <div class="card">
            <img src="img/Tatacoa2.jpg" alt="Aventura">
            <div class="card-content">
                <h3>🏜️ Aventura y Naturaleza</h3>
                <p>Caminatas, avistamiento de fauna y mucho más en este paraíso del Huila.</p>
            </div>
        </div>
    </div>
</section>

<!-- ========== HOTELES ========== -->
<section id="hoteles">
    <div class="section-title">🏨 Elige tu Hotel</div>
    <div class="cards-container">
        <?php
        $hoteles_query = $conn->query("SELECT Id_hotel, Nombre, Descripcion, precio_por_persona, imagen, ubicacion, servicios, mapa_iframe FROM hoteles");
        if ($hoteles_query && $hoteles_query->num_rows > 0):
            while($hotel = $hoteles_query->fetch_assoc()):
        ?>
        <div class="card hotel-card" 
        data-id="<?php echo $hotel['Id_hotel']; ?>" 
        data-nombre="<?php echo htmlspecialchars($hotel['Nombre']); ?>" 
        data-descripcion="<?php echo htmlspecialchars($hotel['Descripcion']); ?>" 
        data-precio="<?php echo $hotel['precio_por_persona']; ?>" 
        data-imagen="<?php echo $hotel['imagen']; ?>" 
        data-servicios="<?php echo htmlspecialchars($hotel['servicios'] ?? 'Wifi, Estacionamiento, Desayuno incluido'); ?>" 
        data-mapa="<?php echo htmlspecialchars($hotel['mapa_iframe']); ?>">
            <img src="img/hoteles/<?php echo $hotel['imagen']; ?>" onerror="this.src='img/hoteles/default.jpg'" alt="<?php echo $hotel['Nombre']; ?>">
            <div class="card-content">
                <h3>🏨 <?php echo htmlspecialchars($hotel['Nombre']); ?></h3>
                <p><?php echo htmlspecialchars(substr($hotel['Descripcion'], 0, 80)); ?>...</p>
                <p><strong>$<?php echo number_format($hotel['precio_por_persona'], 0, ',', '.'); ?> COP</strong> / persona / noche</p>
                
                <div class="hotel-actions">
                    <label>👥 Personas:</label>
                    <input type="number" class="personas-input" data-id="<?php echo $hotel['Id_hotel']; ?>" data-precio="<?php echo $hotel['precio_por_persona']; ?>" data-nombre="<?php echo $hotel['Nombre']; ?>" min="1" max="20" value="1" style="width: 80px; padding: 5px; margin: 10px 0; border: 1px solid var(--sand); border-radius: 8px;">
                </div>

                <div class="fechas-container">
                    <div class="form-group">
                        <label>📅 Selecciona tu rango de fechas:</label>
                        <input type="text" class="fecha-rango" data-id="<?php echo $hotel['Id_hotel']; ?>" placeholder="Selecciona fecha de inicio y fin" autocomplete="off">
                        <small style="display: block; margin-top: 5px; color: #7a5a4a;">Haz clic para elegir fecha de llegada y salida</small>
                    </div>
                </div>
                
                <?php if (isLoggedIn()): ?>
                    <button class="btn-seleccionar-hotel" data-id="<?php echo $hotel['Id_hotel']; ?>" data-nombre="<?php echo $hotel['Nombre']; ?>" data-precio="<?php echo $hotel['precio_por_persona']; ?>">
                        Seleccionar Hotel
                    </button>
                <?php else: ?>
                    <button class="btn-seleccionar-hotel" onclick="location.href='login.php'">Inicia sesión para seleccionar</button>
                <?php endif; ?>
            </div>
        </div>
        <?php 
            endwhile;
        endif;
        ?>
    </div>
</section>

<!-- ========== LUGARES TURÍSTICOS ========== -->
<section id="lugares">
    <div class="section-title">🏜️ Elige los Lugares para Visitar</div>
    <div class="cards-container">
        <?php
        $lugares_query = $conn->query("SELECT id_lugar, nombre, descripcion, tiempo_recorrido, precio_lugar, imagen, dificultad, recomendaciones, mapa_iframe FROM lugares_turisticos");
        if ($lugares_query && $lugares_query->num_rows > 0):
            while($lugar = $lugares_query->fetch_assoc()):
        ?>
        <div class="card lugar-card" data-id="<?php echo $lugar['id_lugar']; ?>" data-nombre="<?php echo htmlspecialchars($lugar['nombre']); ?>" data-descripcion="<?php echo htmlspecialchars($lugar['descripcion']); ?>" data-tiempo="<?php echo $lugar['tiempo_recorrido']; ?>" data-precio="<?php echo $lugar['precio_lugar']; ?>" data-imagen="<?php echo $lugar['imagen']; ?>" data-dificultad="<?php echo $lugar['dificultad'] ?? 'Media'; ?>" data-recomendaciones="<?php echo $lugar['recomendaciones'] ?? 'Llevar agua, protector solar, sombrero y zapatos cómodos'; ?>" data-mapa="<?php echo htmlspecialchars($lugar['mapa_iframe']); ?>">
            <img src="img/lugares/<?php echo $lugar['imagen']; ?>" onerror="this.src='img/lugares/default.jpg'" alt="<?php echo $lugar['nombre']; ?>">
            <div class="card-content">
                <h3>🏜️ <?php echo htmlspecialchars($lugar['nombre']); ?></h3>
                <p><?php echo htmlspecialchars(substr($lugar['descripcion'], 0, 80)); ?>...</p>
                <p>⏱️ Duración: <strong><?php echo $lugar['tiempo_recorrido']; ?> horas</strong></p>
                <p>💰 Precio: <strong>$<?php echo number_format($lugar['precio_lugar'], 0, ',', '.'); ?> COP</strong></p>
                <?php if (isLoggedIn()): ?>
                    <button class="btn-seleccionar-lugar" data-id="<?php echo $lugar['id_lugar']; ?>" data-nombre="<?php echo $lugar['nombre']; ?>" data-tiempo="<?php echo $lugar['tiempo_recorrido']; ?>" data-precio="<?php echo $lugar['precio_lugar']; ?>">
                        + Agregar a mi tour
                    </button>
                <?php else: ?>
                    <button class="btn-seleccionar-lugar" onclick="location.href='login.php'">Inicia sesión para agregar</button>
                <?php endif; ?>
            </div>
        </div>
        <?php 
            endwhile;
        endif;
        ?>
    </div>
</section>

<!-- ========== GUÍAS ========== -->
<section id="guias">
    <div class="section-title">🧑‍🌾 Elige tu Guía</div>
    <div class="cards-container">
        <?php
        $guias_query = $conn->query("SELECT id_guia, nombre, experiencia, precio_por_hora, telefono, idiomas, imagen FROM guias");
        if ($guias_query && $guias_query->num_rows > 0):
            while($guia = $guias_query->fetch_assoc()):
        ?>
        <div class="card">
            <img src="img/guias/<?php echo $guia['imagen']; ?>" onerror="this.src='img/guias/default.jpg'" alt="<?php echo $guia['nombre']; ?>">
            <div class="card-content">
                <h3>🧑‍🌾 <?php echo htmlspecialchars($guia['nombre']); ?></h3>
                <p>⭐ <?php echo htmlspecialchars($guia['experiencia']); ?> de experiencia</p>
                <p>💰 <strong>$<?php echo number_format($guia['precio_por_hora'], 0, ',', '.'); ?> COP / hora</strong></p>
                <p>📞 <?php echo htmlspecialchars($guia['telefono']); ?></p>
                <p>🗣️ <?php echo htmlspecialchars($guia['idiomas']); ?></p>
                <?php if (isLoggedIn()): ?>
                    <button class="btn-seleccionar-guia" data-id="<?php echo $guia['id_guia']; ?>" data-nombre="<?php echo $guia['nombre']; ?>" data-precio_hora="<?php echo $guia['precio_por_hora']; ?>">
                        Seleccionar Guía
                    </button>
                <?php else: ?>
                    <button class="btn-seleccionar-guia" onclick="location.href='login.php'">Inicia sesión para seleccionar</button>
                <?php endif; ?>
            </div>
        </div>
        <?php 
            endwhile;
        else:
        ?>
        <div class="card">
            <div class="card-content">
                <h3>No hay guías disponibles</h3>
                <p>Agrega guías desde el panel de administración</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ========== Testimonios ========== -->
<section id="testimonios" class="testimonios">
    <div class="section-title">Lo que dicen nuestros viajeros</div>
    <div class="testimonios-grid">
        <div class="testimonio">
            <img src="https://randomuser.me/api/portraits/women/1.jpg" alt="Cliente">
            <div class="stars">★★★★★</div>
            <p>"Una experiencia increíble. El desierto de la Tatacoa es mágico. ¡Volveré!"</p>
            <h4>- María González</h4>
        </div>
        <div class="testimonio">
            <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="Cliente">
            <div class="stars">★★★★★</div>
            <p>"Los guías son excelentes. La observación de estrellas es imperdible."</p>
            <h4>- Carlos Rodríguez</h4>
        </div>
        <div class="testimonio">
            <img src="https://randomuser.me/api/portraits/women/2.jpg" alt="Cliente">
            <div class="stars">★★★★★</div>
            <p>"El mejor viaje de mi vida. Los paisajes parecen de otro planeta."</p>
            <h4>- Laura Martínez</h4>
        </div>
    </div>
</section>

<!-- ========== SECCIÓN DE RESEÑAS ========== -->
<section id="reseñas" class="seccion-resenas">
    <div class="section-title">💬 Lo que opinan nuestros viajeros</div>
    
    <!-- Mostrar reseñas existentes -->
    <div class="resenas-container" id="resenasContainer">
        <?php
        // Obtener reseñas aprobadas
        $resenas_query = $conn->query("
        SELECT r.*, u.Nombre as usuario_nombre 
        FROM resenas r 
        JOIN usuarios u ON r.id_usuario = u.Id_usuario 
        ORDER BY r.fecha DESC 
        LIMIT 10
        ");
        
        if ($resenas_query && $resenas_query->num_rows > 0):
            while($resena = $resenas_query->fetch_assoc()):
        ?>
        <div class="resena-item">
            <div class="resena-header">
                <div class="resena-usuario">
                    <div class="resena-avatar">
                        <?php echo strtoupper(substr($resena['usuario_nombre'], 0, 1)); ?>
                    </div>
                    <div>
                        <div class="resena-nombre"><?php echo htmlspecialchars($resena['usuario_nombre']); ?></div>
                        <div class="resena-fecha"><?php echo date('d/m/Y', strtotime($resena['fecha'])); ?></div>
                    </div>
                </div>
                <div class="resena-estrellas">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <span class="estrella <?php echo $i <= $resena['calificacion'] ? 'llena' : ''; ?>">★</span>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="resena-comentario">
                <?php echo nl2br(htmlspecialchars($resena['comentario'])); ?>
            </div>
        </div>
        <?php 
            endwhile;
        else:
        ?>
        <div class="resena-vacio">
            <p>🌟 Sé el primero en dejar tu opinión sobre el desierto de la Tatacoa</p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Formulario para dejar reseña (solo usuarios logueados) -->
    <?php if (isLoggedIn()): ?>
    <div class="form-resena">
        <h3>✍️ Deja tu experiencia</h3>
        <form id="formDejarResena">
            <div class="form-group">
                <label>Tu calificación:</label>
                <div class="selector-estrellas" id="selectorEstrellas">
                    <span data-rating="1">☆</span>
                    <span data-rating="2">☆</span>
                    <span data-rating="3">☆</span>
                    <span data-rating="4">☆</span>
                    <span data-rating="5">☆</span>
                </div>
                <input type="hidden" id="calificacionInput" value="0">
            </div>
            
            <div class="form-group">
                <label>Tu comentario:</label>
                <textarea id="comentarioResena" rows="3" placeholder="Cuéntanos cómo fue tu experiencia en el desierto..." required></textarea>
            </div>
            
            <button type="submit" class="btn-enviar-resena">📝 Enviar reseña</button>
        </form>
        <div id="mensajeResena" style="display: none; margin-top: 15px;"></div>
    </div>
    <?php else: ?>
    <div class="form-resena-login">
        <p>🔐 <a href="login.php">Inicia sesión</a> para dejar tu opinión sobre el desierto de la Tatacoa</p>
    </div>
    <?php endif; ?>
</section>

<!-- ========== Newsletter ========== -->
<section class="newsletter">
    <h2>¿Quieres más información?</h2>
    <p>Suscríbete y recibe ofertas exclusivas del desierto de la Tatacoa</p>
    <form class="newsletter-form">
        <input type="email" placeholder="Tu correo electrónico">
        <button type="submit">Suscribirse</button>
    </form>
</section>

<!-- ========== Footer ========== -->
<footer id="contacto">
    <div class="footer-content">
        <div class="footer-section">
            <h3>Eco Explora Tatacoa</h3>
            <p>Tu mejor opción para explorar el desierto de la Tatacoa.</p>
        </div>
        <div class="footer-section">
            <h3>Enlaces Rápidos</h3>
            <a href="#inicio">Inicio</a>
            <a href="#hoteles">Hoteles</a>
            <a href="#lugares">Lugares</a>
            <a href="#guias">Guías</a>
        </div>
        <div class="footer-section">
            <h3>Contacto</h3>
            <p>📍 Villavieja, Huila, Colombia</p>
            <p>📞 +57 123 456 7890</p>
            <p>✉️ info@ecoexploratatacoa.com</p>
        </div>
        <div class="footer-section">
            <h3>Síguenos</h3>
            <a href="#">📘 Facebook</a>
            <a href="#">📸 Instagram</a>
            <a href="#">🐦 Twitter</a>
        </div>
    </div>
    <div class="copyright">
        <p>&copy; 2026 Eco Explora Tatacoa - Todos los derechos reservados</p>
    </div>
</footer>

<!-- ========== MODAL HOTEL ========== -->
<div id="hotelModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalHotelNombre">Hotel</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <img id="modalHotelImg" src="" alt="Hotel">
            <div class="modal-info">
                <div class="info-row">
                    <span class="label">🏷️ Nombre:</span>
                    <span class="value" id="modalHotelNombreValue">-</span>
                </div>
                <div class="info-row">
                    <span class="label">📝 Descripción:</span>
                    <span class="value" id="modalHotelDescripcion">-</span>
                </div>
                <div class="info-row">
                    <span class="label">💰 Precio:</span>
                    <span class="value" id="modalHotelPrecio">-</span>
                </div>
                <div class="info-row">
                    <span class="label">👥 Personas:</span>
                    <span class="value">
                        <input type="number" id="modalPersonas" min="1" max="20" value="1">
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">📍 Ubicación:</span>
                    <span class="value" id="modalHotelUbicacion">Desierto de la Tatacoa</span>
                </div>
                <div class="info-row">
                    <span class="label">⭐ Servicios:</span>
                    <span class="value" id="modalHotelServicios">-</span>
                </div>
            </div>
            <div class="mapa-container">
                <iframe id="mapaHotelIframe" width="100%" height="250" style="border:0; border-radius:15px;" allowfullscreen loading="lazy"></iframe>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-modal" id="modalSeleccionarBtn">Seleccionar Hotel</button>
        </div>
    </div>
</div>

<!-- ========== MODAL LUGAR ========== -->
<div id="lugarModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalLugarNombre">Lugar Turístico</h2>
            <span class="close-modal-lugar">&times;</span>
        </div>
        <div class="modal-body">
            <img id="modalLugarImg" src="" alt="Lugar">
            <div class="modal-info">
                <div class="info-row">
                    <span class="label">🏷️ Nombre:</span>
                    <span class="value" id="modalLugarNombreValue">-</span>
                </div>
                <div class="info-row">
                    <span class="label">📝 Descripción:</span>
                    <span class="value" id="modalLugarDescripcion">-</span>
                </div>
                <div class="info-row">
                    <span class="label">⏱️ Duración:</span>
                    <span class="value" id="modalLugarTiempo">-</span>
                </div>
                <div class="info-row">
                    <span class="label">💰 Precio:</span>
                    <span class="value" id="modalLugarPrecio">-</span>
                </div>
                <div class="info-row">
                    <span class="label">🏜️ Dificultad:</span>
                    <span class="value" id="modalLugarDificultad">Media</span>
                </div>
                <div class="info-row">
                    <span class="label">⭐ Recomendaciones:</span>
                    <span class="value" id="modalLugarRecomendaciones">Llevar agua, protector solar, sombrero y zapatos cómodos</span>
                </div>
            </div>
            <div class="mapa-container">
                <iframe id="mapaLugarIframe" width="100%" height="250" style="border:0; border-radius:15px;" allowfullscreen loading="lazy"></iframe>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-modal" id="modalAgregarLugarBtn">+ Agregar a mi tour</button>
        </div>
    </div>
</div>

<script src="js/tour.js"></script>
<script>
// Selector de estrellas para reseñas
const estrellas = document.querySelectorAll('.selector-estrellas span');
let calificacionSeleccionada = 0;

if (estrellas.length > 0) {
    estrellas.forEach(estrella => {
        estrella.addEventListener('click', function() {
            calificacionSeleccionada = parseInt(this.dataset.rating);
            document.getElementById('calificacionInput').value = calificacionSeleccionada;
            
            estrellas.forEach((s, index) => {
                if (index < calificacionSeleccionada) {
                    s.innerHTML = '★';
                    s.classList.add('seleccionada');
                } else {
                    s.innerHTML = '☆';
                    s.classList.remove('seleccionada');
                }
            });
        });
    });
}

// Enviar reseña
const formResena = document.getElementById('formDejarResena');
if (formResena) {
    formResena.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const calificacion = document.getElementById('calificacionInput').value;
        const comentario = document.getElementById('comentarioResena').value;
        
        if (calificacion == 0) {
            mostrarMensajeResena('❌ Por favor selecciona una calificación', 'error');
            return;
        }
        
        if (comentario.trim().length < 10) {
            mostrarMensajeResena('❌ El comentario debe tener al menos 10 caracteres', 'error');
            return;
        }
        
        const btn = formResena.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Enviando...';
        
        try {
            const response = await fetch('ajax/guardar_resena.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ calificacion: calificacion, comentario: comentario })
            });
            
            const result = await response.json();
            
            if (result.success) {
                mostrarMensajeResena('✅ ' + result.message, 'success');
                formResena.reset();
                calificacionSeleccionada = 0;
                document.getElementById('calificacionInput').value = 0;
                estrellas.forEach(s => {
                    s.innerHTML = '☆';
                    s.classList.remove('seleccionada');
                });
                
                setTimeout(() => location.reload(), 2000);
            } else {
                mostrarMensajeResena('❌ ' + result.message, 'error');
            }
        } catch (error) {
            mostrarMensajeResena('❌ Error al conectar con el servidor', 'error');
        }
        
        btn.disabled = false;
        btn.textContent = '📝 Enviar reseña';
    });
}

function mostrarMensajeResena(mensaje, tipo) {
    const div = document.getElementById('mensajeResena');
    div.style.display = 'block';
    div.style.padding = '12px';
    div.style.borderRadius = '10px';
    div.style.marginTop = '15px';
    div.style.backgroundColor = tipo === 'success' ? '#d4edda' : '#f8d7da';
    div.style.color = tipo === 'success' ? '#155724' : '#721c24';
    div.innerHTML = mensaje;
    
    setTimeout(() => {
        div.style.display = 'none';
    }, 5000);
}
</script>

</main>
</body>
</html>