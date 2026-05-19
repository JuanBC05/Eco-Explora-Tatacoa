

// ========== VARIABLES ==========
let hotelSeleccionado = null;
let guiaSeleccionado = null;
let lugaresSeleccionados = [];
let personas = 1;

// Variables para precios y tiempos
let precioHotel = 0;
let precioLugares = 0;
let tiempoTotal = 0;
let precioGuia = 0;

// ========== FUNCIÓN PARA LIMPIAR URL DEL MAPA ==========
function limpiarUrlMapa(datoMapa) {
    if (!datoMapa) return null;
    const match = datoMapa.match(/src=["'](https:\/\/www\.google\.com\/maps\/embed\/[^"']+)["']/);
    if (match) {
        return match[1];
    }
    return datoMapa;
}

//  ACTUALIZAR BARRA DE RESUMEN 
function actualizarResumen() {
    document.getElementById('hotel-precio').textContent = '$' + precioHotel.toLocaleString('es-CO');
    document.getElementById('lugares-precio').textContent = '$' + precioLugares.toLocaleString('es-CO');
    document.getElementById('tiempo_total').textContent = tiempoTotal;
    
    if (guiaSeleccionado && tiempoTotal > 0) {
        precioGuia = guiaSeleccionado.precio_hora * tiempoTotal;
        document.getElementById('guia-precio').textContent = '$' + precioGuia.toLocaleString('es-CO');
    } else {
        precioGuia = 0;
        document.getElementById('guia-precio').textContent = '$0';
    }
    
    const totalFinal = precioHotel + precioLugares + precioGuia;
    document.getElementById('total_final').textContent = '$' + totalFinal.toLocaleString('es-CO');
    
    const btn = document.getElementById('btnFinalizar');
    if (hotelSeleccionado && lugaresSeleccionados.length > 0) {
        btn.style.display = 'block';
    } else {
        btn.style.display = 'none';
    }
}

// FUNCIONES PARA CALENDARIO DE RANGO 
function inicializarCalendarios() {
    document.querySelectorAll('.fecha-rango').forEach(input => {
        flatpickr(input, {
            mode: "range",
            locale: "es",
            dateFormat: "Y-m-d",
            minDate: "today",
            onClose: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    const fechaInicio = selectedDates[0];
                    const fechaFin = selectedDates[1];
                    const noches = Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24));
                    
                    input.dataset.fechaLlegada = fechaInicio.toISOString().split('T')[0];
                    input.dataset.fechaSalida = fechaFin.toISOString().split('T')[0];
                    input.dataset.noches = noches;
                    
                    if (hotelSeleccionado && hotelSeleccionado.id == input.dataset.id) {
                        actualizarPrecioHotelPorRango();
                    }
                }
            }
        });
    });
}

function actualizarPrecioHotelPorRango() {
    if (!hotelSeleccionado) return;
    
    const card = document.querySelector(`.hotel-card[data-id="${hotelSeleccionado.id}"]`);
    if (card) {
        const fechaInput = card.querySelector('.fecha-rango');
        const personasInput = card.querySelector('.personas-input');
        
        if (fechaInput && fechaInput.dataset.noches) {
            const noches = parseInt(fechaInput.dataset.noches);
            const personas = parseInt(personasInput.value) || 1;
            
            hotelSeleccionado.noches = noches;
            hotelSeleccionado.personas = personas;
            hotelSeleccionado.fecha_llegada = fechaInput.dataset.fechaLlegada;
            hotelSeleccionado.fecha_salida = fechaInput.dataset.fechaSalida;
            
            precioHotel = hotelSeleccionado.precio_base * personas * noches;
            
            document.getElementById('selected_hotel_name').textContent = hotelSeleccionado.nombre + ' (' + personas + ' pers., ' + noches + ' noches)';
            document.getElementById('hotel-precio').textContent = '$' + precioHotel.toLocaleString('es-CO');
            
            actualizarResumen();
        }
    }
}

//  MODAL Y MAPA PARA HOTELES 
const modal = document.getElementById('hotelModal');
const closeModal = document.querySelector('.close-modal');
let hotelActual = null;

function abrirModalHotel(hotel) {
    document.getElementById('modalHotelNombre').textContent = hotel.nombre;
    document.getElementById('modalHotelNombreValue').textContent = hotel.nombre;
    document.getElementById('modalHotelDescripcion').textContent = hotel.descripcion;
    document.getElementById('modalHotelPrecio').textContent = '$' + parseInt(hotel.precio).toLocaleString('es-CO') + ' COP / persona / noche';
    document.getElementById('modalHotelServicios').textContent = hotel.servicios;
    document.getElementById('modalHotelImg').src = 'img/hoteles/' + hotel.imagen;
    document.getElementById('modalHotelImg').onerror = function() { this.src = 'img/hoteles/default.jpg'; };
    document.getElementById('modalPersonas').value = 1;
    
    const iframe = document.getElementById('mapaHotelIframe');
    let urlHotel = limpiarUrlMapa(hotel.mapa_iframe);
    iframe.src = urlHotel || 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15905!2d-75.1645!3d3.2345!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0!2sDesierto%20de%20la%20Tatacoa!5e0!3m2!1ses!2sco!4v1234567890';
    
    hotelActual = hotel;
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function cerrarModal() {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

if (closeModal) {
    closeModal.onclick = function() {
        cerrarModal();
    }
}

window.onclick = function(event) {
    if (event.target == modal) {
        cerrarModal();
    }
}

//  MODAL Y MAPA PARA LUGARES 
const modalLugar = document.getElementById('lugarModal');
const closeModalLugar = document.querySelector('.close-modal-lugar');
let lugarActual = null;

function abrirModalLugar(lugar) {
    document.getElementById('modalLugarNombre').textContent = lugar.nombre;
    document.getElementById('modalLugarNombreValue').textContent = lugar.nombre;
    document.getElementById('modalLugarDescripcion').textContent = lugar.descripcion;
    document.getElementById('modalLugarTiempo').textContent = lugar.tiempo + ' horas';
    document.getElementById('modalLugarPrecio').textContent = '$' + parseInt(lugar.precio).toLocaleString('es-CO') + ' COP';
    document.getElementById('modalLugarDificultad').textContent = lugar.dificultad;
    document.getElementById('modalLugarRecomendaciones').textContent = lugar.recomendaciones;
    document.getElementById('modalLugarImg').src = 'img/lugares/' + lugar.imagen;
    document.getElementById('modalLugarImg').onerror = function() { this.src = 'img/lugares/default.jpg'; };
    
    const iframe = document.getElementById('mapaLugarIframe');
    let urlLugar = limpiarUrlMapa(lugar.mapa_iframe);
    iframe.src = urlLugar || 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15905!2d-75.1645!3d3.2345!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0!2sDesierto%20de%20la%20Tatacoa!5e0!3m2!1ses!2sco!4v1234567890';
    
    lugarActual = lugar;
    modalLugar.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function cerrarModalLugar() {
    modalLugar.style.display = 'none';
    document.body.style.overflow = 'auto';
}

if (closeModalLugar) {
    closeModalLugar.onclick = function() {
        cerrarModalLugar();
    }
}

window.onclick = function(event) {
    if (event.target == modalLugar) {
        cerrarModalLugar();
    }
}

//  CLICK EN TARJETAS DE HOTEL 
document.querySelectorAll('.hotel-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (e.target.tagName === 'BUTTON' || e.target.closest('button') || e.target.closest('input')) {
            return;
        }
        
        const hotel = {
            id: this.dataset.id,
            nombre: this.dataset.nombre,
            descripcion: this.dataset.descripcion,
            precio: this.dataset.precio,
            imagen: this.dataset.imagen,
            servicios: this.dataset.servicios,
            mapa_iframe: this.dataset.mapa
        };
        abrirModalHotel(hotel);
    });
});

//  CLICK EN TARJETAS DE LUGAR 
document.querySelectorAll('.lugar-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
            return;
        }
        
        const lugar = {
            id: this.dataset.id,
            nombre: this.dataset.nombre,
            descripcion: this.dataset.descripcion,
            tiempo: this.dataset.tiempo,
            precio: this.dataset.precio,
            imagen: this.dataset.imagen,
            dificultad: this.dataset.dificultad,
            recomendaciones: this.dataset.recomendaciones,
            mapa_iframe: this.dataset.mapa
        };
        abrirModalLugar(lugar);
    });
});

//  SELECCIONAR HOTEL 
document.querySelectorAll('.btn-seleccionar-hotel').forEach(btn => {
    const card = btn.closest('.card');
    const inputPersonas = card.querySelector('.personas-input');
    const fechaInput = card.querySelector('.fecha-rango');
    
    if (inputPersonas) {
        inputPersonas.addEventListener('change', function() {
            if (hotelSeleccionado && hotelSeleccionado.id == btn.dataset.id) {
                const personas = parseInt(this.value) || 1;
                hotelSeleccionado.personas = personas;
                precioHotel = hotelSeleccionado.precio_base * personas * (hotelSeleccionado.noches || 1);
                document.getElementById('selected_hotel_name').textContent = hotelSeleccionado.nombre + ' (' + personas + ' pers., ' + (hotelSeleccionado.noches || 1) + ' noches)';
                document.getElementById('hotel-precio').textContent = '$' + precioHotel.toLocaleString('es-CO');
                actualizarResumen();
            }
        });
    }
    
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nombre = this.dataset.nombre;
        const precioBase = parseInt(this.dataset.precio);
        
        const personas = inputPersonas ? parseInt(inputPersonas.value) : 1;
        
        let llegada = new Date().toISOString().split('T')[0];
        let salida = new Date(Date.now() + 2*24*60*60*1000).toISOString().split('T')[0];
        let noches = 1;
        
        if (fechaInput && fechaInput.dataset.fechaLlegada && fechaInput.dataset.fechaSalida) {
            llegada = fechaInput.dataset.fechaLlegada;
            salida = fechaInput.dataset.fechaSalida;
            noches = parseInt(fechaInput.dataset.noches);
        }
        
        document.querySelectorAll('.btn-seleccionar-hotel').forEach(b => {
            b.textContent = 'Seleccionar Hotel';
            b.style.background = '#5a7d3c';
        });
        
        this.textContent = '✓ Hotel Seleccionado';
        this.style.background = '#3d5a1e';
        
        hotelSeleccionado = { 
            id: id, 
            nombre: nombre, 
            precio_base: precioBase, 
            personas: personas, 
            noches: noches,
            fecha_llegada: llegada,
            fecha_salida: salida
        };
        
        precioHotel = precioBase * personas * noches;
        
        document.getElementById('selected_hotel_name').textContent = nombre + ' (' + personas + ' pers., ' + noches + ' noches)';
        document.getElementById('hotel-precio').textContent = '$' + precioHotel.toLocaleString('es-CO');
        document.getElementById('hotel-check').textContent = '✅';
        
        actualizarResumen();
    });
});

//  SELECCIONAR LUGAR 
document.querySelectorAll('.btn-seleccionar-lugar').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nombre = this.dataset.nombre;
        const tiempo = parseInt(this.dataset.tiempo);
        const precio = parseInt(this.dataset.precio);
        
        const index = lugaresSeleccionados.findIndex(l => l.id == id);
        
        if (index === -1) {
            lugaresSeleccionados.push({ id, nombre, tiempo, precio });
            this.textContent = '✓ Agregado';
            this.style.background = '#3d5a1e';
        } else {
            lugaresSeleccionados.splice(index, 1);
            this.textContent = '+ Agregar a mi tour';
            this.style.background = '#5a7d3c';
        }
        
        tiempoTotal = lugaresSeleccionados.reduce((sum, l) => sum + l.tiempo, 0);
        precioLugares = lugaresSeleccionados.reduce((sum, l) => sum + l.precio, 0);
        
        document.getElementById('selected_lugares_count').textContent = lugaresSeleccionados.length;
        document.getElementById('lugares-check').textContent = lugaresSeleccionados.length > 0 ? '✅' : '⬜';
        
        actualizarResumen();
    });
});

//  SELECCIONAR GUÍA 
document.querySelectorAll('.btn-seleccionar-guia').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nombre = this.dataset.nombre;
        const precioHora = parseInt(this.dataset.precio_hora);
        
        document.querySelectorAll('.btn-seleccionar-guia').forEach(b => {
            b.textContent = 'Seleccionar Guía';
            b.style.background = '#5a7d3c';
        });
        
        this.textContent = '✓ Guía Seleccionado';
        this.style.background = '#3d5a1e';
        
        guiaSeleccionado = { id, nombre, precio_hora: precioHora };
        document.getElementById('selected_guia_name').textContent = nombre;
        document.getElementById('guia-check').textContent = '✅';
        
        actualizarResumen();
    });
});

// SELECCIONAR DESDE MODAL 
const modalSeleccionarBtn = document.getElementById('modalSeleccionarBtn');
if (modalSeleccionarBtn) {
    modalSeleccionarBtn.addEventListener('click', function() {
        if (hotelActual) {
            const personas = document.getElementById('modalPersonas').value;
            
            const card = document.querySelector(`.hotel-card[data-id="${hotelActual.id}"]`);
            if (card) {
                const inputPersonas = card.querySelector('.personas-input');
                if (inputPersonas) {
                    inputPersonas.value = personas;
                    const event = new Event('change');
                    inputPersonas.dispatchEvent(event);
                }
                
                const botonSeleccionar = card.querySelector('.btn-seleccionar-hotel');
                if (botonSeleccionar) {
                    botonSeleccionar.click();
                }
            }
            
            cerrarModal();
        }
    });
}

const modalAgregarLugarBtn = document.getElementById('modalAgregarLugarBtn');
if (modalAgregarLugarBtn) {
    modalAgregarLugarBtn.addEventListener('click', function() {
        if (lugarActual) {
            const card = document.querySelector(`.lugar-card[data-id="${lugarActual.id}"]`);
            if (card) {
                const botonAgregar = card.querySelector('.btn-seleccionar-lugar');
                if (botonAgregar) {
                    botonAgregar.click();
                }
            }
            cerrarModalLugar();
        }
    });
}

//  INICIAR CALENDARIOS 
document.addEventListener('DOMContentLoaded', function() {
    inicializarCalendarios();
});

//  FINALIZAR TOUR 
function finalizarTour() {
    if (!hotelSeleccionado) {
        alert('❌ Por favor selecciona un hotel primero');
        return;
    }
    
    if (lugaresSeleccionados.length === 0) {
        alert('❌ Por favor selecciona al menos un lugar turístico');
        return;
    }
    
    const totalFinal = precioHotel + precioLugares + precioGuia;
    
    const seleccion = {
        hotel: hotelSeleccionado,
        lugares: lugaresSeleccionados,
        guia: guiaSeleccionado,
        personas: hotelSeleccionado.personas,
        noches: hotelSeleccionado.noches || 1,
        fecha_llegada: hotelSeleccionado.fecha_llegada || new Date().toISOString().split('T')[0],
        fecha_salida: hotelSeleccionado.fecha_salida || new Date(Date.now() + 2*24*60*60*1000).toISOString().split('T')[0],
        tiempo_total: tiempoTotal,
        precio_hotel: precioHotel,
        precio_lugares: precioLugares,
        precio_guia: precioGuia,
        total: totalFinal
    };
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'confirmar_tour.php';
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'seleccion';
    input.value = JSON.stringify(seleccion);
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

//  EVENTO DEL BOTÓN FINALIZAR 
const btnFinalizar = document.getElementById('btnFinalizar');
if (btnFinalizar) {
    btnFinalizar.addEventListener('click', finalizarTour);
}

console.log('✅ tour.js cargado correctamente');

// ========== GUARDAR TOUR NUEVO ==========
async function guardarTour() {
    // Verificar hotel
    if (!window.hotelActual || !window.hotelActual.id) {
        alert('❌ Por favor selecciona un hotel');
        return false;
    }
    
    // Verificar fechas
    if (!window.fechasSeleccionadas || !window.fechasSeleccionadas.inicio || !window.fechasSeleccionadas.fin) {
        alert('❌ Por favor selecciona las fechas de tu viaje');
        return false;
    }
    
    // Verificar lugares
    if (!window.lugaresSeleccionados || window.lugaresSeleccionados.length === 0) {
        alert('❌ Por favor selecciona al menos un lugar turístico');
        return false;
    }
    
    // Preparar lugares IDs
    var lugaresIds = [];
    for (var i = 0; i < window.lugaresSeleccionados.length; i++) {
        if (window.lugaresSeleccionados[i].id) {
            lugaresIds.push(window.lugaresSeleccionados[i].id);
        }
    }
    
    // Calcular total
    var total = 0;
    if (document.getElementById('total_final')) {
        total = parseInt(document.getElementById('total_final').innerText.replace(/[^0-9]/g, '')) || 0;
    }
    
    var datosTour = {
        hotel_id: window.hotelActual.id,
        id_guia: window.guiaActual ? window.guiaActual.id : null,
        fecha_llegada: window.fechasSeleccionadas.inicio,
        fecha_salida: window.fechasSeleccionadas.fin,
        noches: window.fechasSeleccionadas.noches || 1,
        lugares: lugaresIds,
        precio_total: total
    };
    
    console.log('Guardando tour:', datosTour);
    
    try {
        const response = await fetch('ajax/guardar_tour.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosTour)
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.href = 'pago.php?id=' + result.tour_id;
            return true;
        } else {
            alert('❌ ' + result.message);
            return false;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error al conectar con el servidor');
        return false;
    }
}
