document.addEventListener('DOMContentLoaded', function() {
    // Selección de método de pago
    const metodos = document.querySelectorAll('.metodo');
    
    metodos.forEach(metodo => {
        metodo.addEventListener('click', function() {
            // Quitar selección de todos
            metodos.forEach(m => m.classList.remove('seleccionado'));
            
            // Seleccionar este
            this.classList.add('seleccionado');
            
            // Ocultar todos los datos
            document.getElementById('datos-transferencia').style.display = 'none';
            document.getElementById('datos-nequi').style.display = 'none';
            document.getElementById('datos-efectivo').style.display = 'none';
            
            // Mostrar los datos correspondientes
            const metodoSeleccionado = this.dataset.metodo;
            if (metodoSeleccionado === 'transferencia') {
                document.getElementById('datos-transferencia').style.display = 'block';
            } else if (metodoSeleccionado === 'nequi') {
                document.getElementById('datos-nequi').style.display = 'block';
            } else if (metodoSeleccionado === 'efectivo') {
                document.getElementById('datos-efectivo').style.display = 'block';
            }
        });
    });
});

console.log('✅ pago.js cargado');