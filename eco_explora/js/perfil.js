// Variables globales
let reservaIdActual = null;
let calificacionActual = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Navegación entre tabs
    const navBtns = document.querySelectorAll('.nav-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    navBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.getAttribute('data-tab');
            
            navBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            tabContents.forEach(tab => {
                tab.classList.remove('active');
                if (tab.id === tabId) {
                    tab.classList.add('active');
                }
            });
            
            localStorage.setItem('activeTab', tabId);
        });
    });
    
    const activeTab = localStorage.getItem('activeTab');
    if (activeTab) {
        const tabToActivate = document.querySelector(`[data-tab="${activeTab}"]`);
        if (tabToActivate) {
            tabToActivate.click();
        }
    }
    
    // Cancelar reserva
    const modalCancelar = document.getElementById('modal-cancelar');
    const confirmCancelBtn = document.querySelector('.confirm-cancel-btn');
    
    document.querySelectorAll('.btn-cancelar').forEach(btn => {
        btn.addEventListener('click', () => {
            reservaIdActual = btn.getAttribute('data-reserva-id');
            modalCancelar.style.display = 'block';
        });
    });
    
    if (confirmCancelBtn) {
        confirmCancelBtn.addEventListener('click', async () => {
            const motivo = document.getElementById('motivo-cancelacion').value;
            
            try {
                const response = await fetch('ajax/cancelar-reserva.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        reserva_id: reservaIdActual,
                        motivo: motivo 
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification(result.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error al cancelar la reserva', 'error');
            }
            
            modalCancelar.style.display = 'none';
            document.getElementById('motivo-cancelacion').value = '';
        });
    }
    
    // Sistema de reseñas
    const modalReseña = document.getElementById('modal-reseña');
    const stars = document.querySelectorAll('.stars span');
    
    document.querySelectorAll('.btn-reseñar').forEach(btn => {
        btn.addEventListener('click', () => {
            reservaIdActual = btn.getAttribute('data-reserva-id');
            const tourNombre = btn.getAttribute('data-tour-nombre');
            document.getElementById('tour-nombre').textContent = tourNombre;
            modalReseña.style.display = 'block';
            
            calificacionActual = 0;
            document.getElementById('calificacion').value = 0;
            document.getElementById('comentario').value = '';
            stars.forEach(star => {
                star.classList.remove('active');
            });
        });
    });
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            calificacionActual = rating;
            document.getElementById('calificacion').value = rating;
            
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.style.color = '#ffd700';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
        
        star.addEventListener('mouseleave', function() {
            stars.forEach(s => {
                if (s.classList.contains('active')) {
                    s.style.color = '#ffd700';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
    });
    
    const guardarReseñaBtn = document.querySelector('.guardar-reseña-btn');
    if (guardarReseñaBtn) {
        guardarReseñaBtn.addEventListener('click', async () => {
            const calificacion = document.getElementById('calificacion').value;
            const comentario = document.getElementById('comentario').value;
            
            if (calificacion == 0) {
                showNotification('Por favor, selecciona una calificación', 'error');
                return;
            }
            
            if (comentario.length < 10) {
                showNotification('El comentario debe tener al menos 10 caracteres', 'error');
                return;
            }
            
            try {
                const response = await fetch('ajax/guardar-reseña.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        reserva_id: reservaIdActual,
                        calificacion: calificacion,
                        comentario: comentario
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification(result.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error al guardar la reseña', 'error');
            }
            
            modalReseña.style.display = 'none';
        });
    }
    
    // Eliminar favorito
    document.querySelectorAll('.btn-remove-fav').forEach(btn => {
        btn.addEventListener('click', async () => {
            const tourId = btn.getAttribute('data-tour-id');
            
            if (confirm('¿Eliminar este tour de favoritos?')) {
                try {
                    const response = await fetch('ajax/eliminar-favorito.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ tour_id: tourId })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        btn.closest('.tour-card').remove();
                        showNotification('Eliminado de favoritos', 'success');
                    } else {
                        showNotification('Error al eliminar', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        });
    });
    
    // Reservar desde favoritos
    document.querySelectorAll('.btn-reservar').forEach(btn => {
        btn.addEventListener('click', () => {
            const tourId = btn.getAttribute('data-tour-id');
            window.location.href = `reservar.php?id=${tourId}`;
        });
    });
    
    // Cerrar modales
    const modals = document.querySelectorAll('.modal');
    const closeBtns = document.querySelectorAll('.modal-close, .modal-cancel-btn');
    
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
        });
    });
    
    window.addEventListener('click', (e) => {
        modals.forEach(modal => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
});

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#28a745' : '#dc3545'};
        color: white;
        border-radius: 5px;
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);