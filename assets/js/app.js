/* ============================================================
   app.js — Configuración global de modales (QDelicias)
   - Deshabilita cierre con click fuera del modal (backdrop static)
   - Deshabilita cierre con tecla Escape
   - Centra verticalmente todos los modales en cualquier pantalla
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    var esVentas = window.location.pathname.indexOf('ventas.php') !== -1;

    document.querySelectorAll('.modal').forEach(function (el) {
        // Deshabilitar cierre por backdrop y teclado en todos los modales
        if (el.getAttribute('data-bs-backdrop') !== 'false') {
            el.setAttribute('data-bs-backdrop', 'static');
        }
        el.setAttribute('data-bs-keyboard', 'false');
    });

    // Centrar verticalmente solo en páginas del dashboard (no ventas)
    if (!esVentas) {
        document.querySelectorAll('.modal-dialog').forEach(function (el) {
            if (!el.classList.contains('modal-dialog-centered')) {
                el.classList.add('modal-dialog-centered');
            }
        });
    }

});
