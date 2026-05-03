/* ============================================================
   ventas.js — Lógica del módulo POS (QDelicias)
   Depende de: reglasProductos, extrasCatalogo, saboresPorProducto
   (inyectados como variables inline desde ventas.php)
   ============================================================ */

/* ─── Estado global ──────────────────────────────────────── */
let carrito = [];
let total = 0;
let productoSeleccionado = null;
let pagoAutoSeleccionado = false;
let cantidadesSabores = {};
let cantidadesExtras = {};

// Inicializar contadores de extras
extrasCatalogo.forEach(extra => {
    cantidadesExtras[extra.id] = 0;
});

/* ─── Categorías ─────────────────────────────────────────── */
function filtrarCategoria(categoriaId, boton = null) {
    document.querySelectorAll('.producto-item').forEach(producto => {
        const categoriaProducto = parseInt(producto.getAttribute('data-categoria'));
        producto.style.display =
            (categoriaId === 0 || categoriaProducto === categoriaId) ? 'block' : 'none';
    });

    document.querySelectorAll('.btn-categoria').forEach(btn => {
        btn.classList.remove('btn-dark');
        btn.classList.add('btn-outline-primary');
    });

    if (boton) {
        boton.classList.remove('btn-outline-primary');
        boton.classList.add('btn-dark');
    }
}

/* ─── Búsqueda de productos ──────────────────────────────── */
function buscarProductos() {
    const t = document.getElementById('buscadorProductos').value.toLowerCase();

    document.querySelectorAll('.producto-item').forEach(p => {
        const n = (p.dataset.nombre || '').toLowerCase();
        p.style.display = n.includes(t) ? 'block' : 'none';
    });
}

/* ─── Modal de producto ──────────────────────────────────── */
function abrirModalProducto(id, nombre, precio, tipo) {
    productoSeleccionado = { id, nombre, precio, tipo };

    if (tipo === 'simple') {
        limpiarExtrasSeleccionados();
        limpiarSaboresSeleccionados();
        agregarProductoConExtras(id, nombre, precio);
        return;
    }

    limpiarExtrasSeleccionados();
    limpiarSaboresSeleccionados();

    document.getElementById('modalProductoNombre').innerText = nombre;
    document.getElementById('modalProductoPrecio').innerText = '$ ' + formatearPeso(precio);
    actualizarSubtotalModal();

    if (tipo === 'sabores') {
        renderSaboresEnModal(id);
    } else if (tipo === 'extras') {
        renderExtrasEnModal(id);
    } else {
        document.getElementById('modalReglasProducto').innerHTML = '';
        document.getElementById('modalExtrasContenido').innerHTML =
            "<p class='text-muted'>Este producto no requiere configuración.</p>";
    }

    new bootstrap.Modal(document.getElementById('modalProducto')).show();
}

/* ─── Render extras en modal ─────────────────────────────── */
function renderExtrasEnModal(productoId) {
    const contenedor = document.getElementById('modalExtrasContenido');
    const reglasBox = document.getElementById('modalReglasProducto');

    const reglas = reglasProductos[productoId] || {};

    reglasBox.innerHTML = Object.keys(reglas).length
        ? Object.entries(reglas).map(([tipo, cantidad]) =>
            `<span class="badge bg-success me-2 mb-2">${tipo}: ${cantidad} incluido(s)</span>`
        ).join('')
        : "<span class='text-muted'>Este producto no tiene extras incluidos configurados.</span>";

    const extrasPorTipo = {};
    extrasCatalogo.forEach(extra => {
        if (!extrasPorTipo[extra.tipo]) extrasPorTipo[extra.tipo] = [];
        extrasPorTipo[extra.tipo].push(extra);
    });

    let html = '';
    Object.keys(extrasPorTipo).forEach(tipo => {
        html += `<div class="mb-4"><h6 class="modal-extra-title">${tipo}</h6><div class="row">`;

        extrasPorTipo[tipo].forEach(extra => {
            const cantidad = cantidadesExtras[extra.id] || 0;
            html += `
                <div class="col-md-6 col-12">
                    <div class="extra-item h-100">
                        <div>
                            <strong>${extra.nombre}</strong><br>
                            <small class="text-muted">$ ${formatearPeso(extra.precio)}</small>
                        </div>
                        <div class="qty-controls">
                            <button type="button" class="qty-btn minus" onclick="cambiarCantidadExtra(${extra.id}, -1)">-</button>
                            <span class="qty-value" id="modal_cantidad_extra_${extra.id}">${cantidad}</span>
                            <button type="button" class="qty-btn plus"  onclick="cambiarCantidadExtra(${extra.id}, 1)">+</button>
                        </div>
                    </div>
                </div>`;
        });

        html += '</div></div>';
    });

    contenedor.innerHTML = html;
}

/* ─── Render sabores en modal ────────────────────────────── */
function renderSaboresEnModal(productoId) {
    const contenedor = document.getElementById('modalExtrasContenido');
    const reglasBox = document.getElementById('modalReglasProducto');

    reglasBox.innerHTML = `
        <div class="alert alert-info py-2 mb-3">
            Distribuye la cantidad del producto entre los sabores.
        </div>`;

    const sabores = saboresPorProducto[productoId] || [];

    if (sabores.length === 0) {
        contenedor.innerHTML = "<p class='text-muted'>Este producto no tiene sabores configurados.</p>";
        return;
    }

    let html = '<div class="row">';

    sabores.forEach(sabor => {
        const cantidad = cantidadesSabores[sabor.id] || 0;
        html += `
            <div class="col-md-6 col-12">
                <div class="extra-item h-100">
                    <strong>${sabor.nombre}</strong>
                    <div class="qty-controls">
                        <button type="button" class="qty-btn minus" onclick="cambiarCantidadSabor(${sabor.id}, -1)">-</button>
                        <span class="qty-value" id="cantidad_sabor_${sabor.id}">${cantidad}</span>
                        <button type="button" class="qty-btn plus"  onclick="cambiarCantidadSabor(${sabor.id}, 1)">+</button>
                    </div>
                </div>
            </div>`;
    });

    html += '</div>';
    contenedor.innerHTML = html;
}

/* ─── Cantidad extras ────────────────────────────────────── */
function cambiarCantidadExtra(extraId, cambio) {
    let actual = (parseInt(cantidadesExtras[extraId]) || 0) + cambio;
    if (actual < 0) actual = 0;
    cantidadesExtras[extraId] = actual;

    const span = document.getElementById('modal_cantidad_extra_' + extraId);
    if (span) span.textContent = actual;

    actualizarSubtotalModal();
}

/* ─── Cantidad sabores ───────────────────────────────────── */
function cambiarCantidadSabor(saborId, cambio) {
    let actual = (parseInt(cantidadesSabores[saborId]) || 0) + cambio;
    if (actual < 0) actual = 0;
    cantidadesSabores[saborId] = actual;

    const span = document.getElementById('cantidad_sabor_' + saborId);
    if (span) span.textContent = actual;

    actualizarSubtotalModal();
}

/* ─── Subtotal en modal ──────────────────────────────────── */
function actualizarSubtotalModal() {
    if (!productoSeleccionado) return;

    const precioBase = parseFloat(productoSeleccionado.precio) || 0;
    let totalExtrasCobrados = 0;

    if (productoSeleccionado.tipo === 'extras') {
        const reglas = reglasProductos[productoSeleccionado.id] || {};
        const usadosPorTipo = {};

        extrasCatalogo.forEach(extra => {
            const cantidad = parseInt(cantidadesExtras[extra.id]) || 0;
            if (cantidad <= 0) return;

            const tipo = extra.tipo;
            const incluidosPermitidos = reglas[tipo] || 0;
            if (!usadosPorTipo[tipo]) usadosPorTipo[tipo] = 0;

            for (let i = 1; i <= cantidad; i++) {
                usadosPorTipo[tipo]++;
                if (usadosPorTipo[tipo] > incluidosPermitidos) {
                    totalExtrasCobrados += parseFloat(extra.precio);
                }
            }
        });
    }

    document.getElementById('modalPrecioBase').innerText = '$ ' + formatearPeso(precioBase);
    document.getElementById('modalExtrasCobrados').innerText = '$ ' + formatearPeso(totalExtrasCobrados);
    document.getElementById('modalTotalProducto').innerText = '$ ' + formatearPeso(precioBase + totalExtrasCobrados);
}

/* ─── Sabores helpers ────────────────────────────────────── */
function totalSaboresSeleccionados() {
    return Object.values(cantidadesSabores).reduce((acc, c) => acc + (parseInt(c) || 0), 0);
}

function obtenerSaboresSeleccionados() {
    const saboresProducto = saboresPorProducto[productoSeleccionado.id] || [];
    return saboresProducto
        .filter(sabor => (parseInt(cantidadesSabores[sabor.id]) || 0) > 0)
        .map(sabor => ({ id: sabor.id, nombre: sabor.nombre, cantidad: parseInt(cantidadesSabores[sabor.id]) }));
}

/* ─── Confirmar producto del modal ───────────────────────── */
function confirmarProductoSeleccionado() {
    if (!productoSeleccionado) {
        alert('Primero selecciona un producto');
        return;
    }

    if (productoSeleccionado.tipo === 'sabores') {
        const totalSabores = totalSaboresSeleccionados();
        const piezas = obtenerCantidadProducto(productoSeleccionado.nombre);

        if (piezas > 0) {
            if (totalSabores !== piezas) {
                alert(`Debes asignar exactamente ${piezas} piezas en sabores`);
                return;
            }
        } else {
            if (totalSabores < 1) {
                alert('Debes seleccionar al menos 1 sabor');
                return;
            }
            if (totalSabores > 1) {
                alert('Este producto solo permite seleccionar 1 sabor');
                return;
            }
        }
    }

    agregarProductoConExtras(
        productoSeleccionado.id,
        productoSeleccionado.nombre,
        productoSeleccionado.precio
    );

    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalProducto'));
    if (modalInstance) modalInstance.hide();

    limpiarSaboresSeleccionados();
    productoSeleccionado = null;
}

/* ─── Cantidad por nombre de producto ───────────────────── */
function obtenerCantidadProducto(nombre) {
    nombre = nombre.toLowerCase();
    if (nombre.includes('personal')) return 4;
    if (nombre.includes('pareja')) return 10;
    if (nombre.includes('familiar')) return 30;
    if (nombre.includes('30')) return 30;
    if (nombre.includes('20')) return 20;
    if (nombre.includes('10')) return 10;
    if (nombre.includes('4')) return 4;
    return 0;
}

/* ─── Extras seleccionados ───────────────────────────────── */
function obtenerExtrasSeleccionados() {
    return extrasCatalogo
        .filter(extra => (cantidadesExtras[extra.id] || 0) > 0)
        .map(extra => ({
            id: parseInt(extra.id),
            nombre: extra.nombre,
            precio: parseFloat(extra.precio),
            tipo: extra.tipo,
            cantidad: cantidadesExtras[extra.id],
            cantidad_incluida: 0,
            cantidad_cobrada: 0
        }));
}

/* ─── Limpiar selecciones ────────────────────────────────── */
function limpiarExtrasSeleccionados() {
    extrasCatalogo.forEach(extra => {
        cantidadesExtras[extra.id] = 0;
        const span = document.getElementById('modal_cantidad_extra_' + extra.id);
        if (span) span.textContent = 0;
    });
}

function limpiarSaboresSeleccionados() {
    cantidadesSabores = {};
}

/* ─── Clave de línea del carrito ─────────────────────────── */
function generarClaveLinea(productoId, extras) {
    const extrasClave = extras.map(e => `${e.id}:${e.cantidad}`).sort().join('|');
    return productoId + '|' + extrasClave;
}

/* ─── Agregar producto con extras ────────────────────────── */
function agregarProductoConExtras(id, nombre, precio) {
    const extrasSeleccionados = obtenerExtrasSeleccionados();
    const saboresSeleccionados = (productoSeleccionado && productoSeleccionado.tipo === 'sabores')
        ? obtenerSaboresSeleccionados()
        : [];

    const reglas = reglasProductos[id] || {};
    const usadosPorTipo = {};
    let totalExtrasCobrados = 0;

    extrasSeleccionados.forEach(extra => {
        const tipo = extra.tipo;
        const limiteGratis = parseInt(reglas[tipo] || 0);
        if (!usadosPorTipo[tipo]) usadosPorTipo[tipo] = 0;

        const disponiblesGratis = Math.max(limiteGratis - usadosPorTipo[tipo], 0);
        const cantidadIncluida = Math.min(extra.cantidad, disponiblesGratis);
        const cantidadCobrada = extra.cantidad - cantidadIncluida;

        extra.cantidad_incluida = cantidadIncluida;
        extra.cantidad_cobrada = cantidadCobrada;

        usadosPorTipo[tipo] += cantidadIncluida;
        totalExtrasCobrados += cantidadCobrada * extra.precio;
    });

    const precioUnitarioLinea = precio + totalExtrasCobrados;
    const clave = generarClaveLinea(id, extrasSeleccionados);
    const productoExistente = carrito.find(item => item.clave === clave);

    if (productoExistente) {
        productoExistente.cantidad++;
        productoExistente.subtotal += precioUnitarioLinea;
    } else {
        carrito.push({
            clave,
            id,
            nombre,
            precio_base: precio,
            cantidad: 1,
            extras: extrasSeleccionados,
            sabores: saboresSeleccionados,
            subtotal: precioUnitarioLinea
        });
    }

    total += precioUnitarioLinea;
    renderCarrito();
    limpiarExtrasSeleccionados();

    if (carrito.length === 1 && !pagoAutoSeleccionado) {
        pagoAutoSeleccionado = true;
        const btnEfectivo = document.querySelector('button.btn-pago-rapido');
        if (btnEfectivo) seleccionarPagoSimple('efectivo', btnEfectivo);
    }
}

/* ─── Modificar cantidad en carrito ─────────────────────── */
function disminuirCantidad(index) {
    const valorUnitario = carrito[index].subtotal / carrito[index].cantidad;
    if (carrito[index].cantidad > 1) {
        carrito[index].cantidad--;
        carrito[index].subtotal -= valorUnitario;
        total -= valorUnitario;
    } else {
        total -= carrito[index].subtotal;
        carrito.splice(index, 1);
    }
    renderCarrito();
}

function aumentarCantidad(index) {
    const valorUnitario = carrito[index].subtotal / carrito[index].cantidad;
    carrito[index].cantidad++;
    carrito[index].subtotal += valorUnitario;
    total += valorUnitario;
    renderCarrito();
}

function eliminar(index) {
    total -= carrito[index].subtotal;
    carrito.splice(index, 1);
    renderCarrito();
}

function vaciarCarrito() {
    carrito = [];
    total = 0;
    pagoAutoSeleccionado = false;

    ['pago_efectivo', 'pago_nequi', 'pago_daviplata', 'pago_transferencia'].forEach(id => {
        document.getElementById(id).value = 0;
    });

    const efectivoRecibidoInput = document.getElementById('efectivo_recibido');
    if (efectivoRecibidoInput) efectivoRecibidoInput.value = 0;

    renderCarrito();
    actualizarResumenPagos();
    limpiarExtrasSeleccionados();
}

/* ─── Render carrito ─────────────────────────────────────── */
function formatearPeso(valor) {
    return new Intl.NumberFormat('es-CO').format(Math.round(valor));
}

function renderCarrito() {
    const tbody = document.querySelector('#tabla tbody');
    tbody.innerHTML = '';

    if (carrito.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">No hay productos en el carrito</td></tr>`;
    } else {
        carrito.forEach((item, index) => {
            const extrasTexto = item.extras.length > 0
                ? item.extras.map(extra => {
                    const partes = [];
                    if (extra.cantidad_incluida > 0) partes.push(`${extra.cantidad_incluida} incluido`);
                    if (extra.cantidad_cobrada > 0) partes.push(`${extra.cantidad_cobrada} cobrado (+$${formatearPeso(extra.cantidad_cobrada * extra.precio)})`);
                    return `${extra.nombre} x${extra.cantidad} [${partes.join(', ')}]`;
                }).join(', ')
                : '';

            const saboresTexto = (item.sabores && item.sabores.length > 0)
                ? item.sabores.map(s => `${s.nombre} x${s.cantidad}`).join(', ')
                : '';

            tbody.innerHTML += `
                <tr>
                    <td>
                        <strong>${item.nombre}</strong>
                        <span class="detalle-extra">
                            ${saboresTexto ? 'Sabores: ' + saboresTexto : ''}
                            ${saboresTexto && extrasTexto ? '<br>' : ''}
                            ${extrasTexto || (!saboresTexto ? 'Sin extras' : '')}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <button class="btn btn-warning btn-sm" onclick="disminuirCantidad(${index})">-</button>
                            <span class="fw-bold">${item.cantidad}</span>
                            <button class="btn btn-success btn-sm" onclick="aumentarCantidad(${index})">+</button>
                        </div>
                    </td>
                    <td>$ ${formatearPeso(item.subtotal)}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="eliminar(${index})">X</button>
                    </td>
                </tr>`;
        });
    }

    document.getElementById('total').innerText = formatearPeso(total);
    actualizarResumenPagos();
}

/* ─── Pagos ──────────────────────────────────────────────── */
function obtenerPagos() {
    return {
        efectivo: parseFloat(document.getElementById('pago_efectivo').value) || 0,
        nequi: parseFloat(document.getElementById('pago_nequi').value) || 0,
        daviplata: parseFloat(document.getElementById('pago_daviplata').value) || 0,
        transferencia: parseFloat(document.getElementById('pago_transferencia').value) || 0
    };
}

function calcularTotalPagado() {
    const p = obtenerPagos();
    return p.efectivo + p.nequi + p.daviplata + p.transferencia;
}

function marcarPagoActivo(boton) {
    document.querySelectorAll('.btn-pago-rapido').forEach(btn => {
        btn.classList.remove('active-pay');
        btn.setAttribute('aria-pressed', 'false');
    });
    if (boton) {
        boton.classList.add('active-pay');
        boton.setAttribute('aria-pressed', 'true');
    }
}

function ocultarInputsPago() {
    document.getElementById('paymentInputs')?.classList.remove('show');
}

function mostrarInputsPago() {
    document.getElementById('paymentInputs')?.classList.add('show');
}

function mostrarCambioEfectivo(mostrar) {
    const contenedor = document.getElementById('boxCambioEfectivo');
    if (!contenedor) return;
    contenedor.classList.toggle('d-none', !mostrar);
}

function actualizarBotonEfectivo() {
    const pagos = obtenerPagos();
    const faltante = total - (pagos.nequi + pagos.daviplata + pagos.transferencia);
    const btn = document.getElementById('btnCompletarEfectivo');
    if (!btn) return;
    btn.textContent = faltante > 0 ? `Faltan $${faltante.toLocaleString()}` : 'Pago completo';
}

function seleccionarPagoSimple(metodo, boton) {
    marcarPagoActivo(boton);
    ocultarInputsPago();

    ['pago_efectivo', 'pago_nequi', 'pago_daviplata', 'pago_transferencia'].forEach(id => {
        document.getElementById(id).value = 0;
    });

    document.getElementById('pago_' + metodo).value = total;
    document.getElementById('efectivo_recibido').value = (metodo === 'efectivo') ? total : 0;

    actualizarResumenPagos();
}

function activarPagoMixto(boton) {
    marcarPagoActivo(boton);
    mostrarInputsPago();

    ['pago_efectivo', 'pago_nequi', 'pago_daviplata', 'pago_transferencia', 'efectivo_recibido'].forEach(id => {
        document.getElementById(id).value = 0;
    });

    actualizarResumenPagos();
}

function completarConEfectivo() {
    const pagos = obtenerPagos();
    const faltante = total - (pagos.nequi + pagos.daviplata + pagos.transferencia);
    if (faltante <= 0) return;

    document.getElementById('pago_efectivo').value = faltante;
    document.getElementById('efectivo_recibido').value = faltante;

    actualizarResumenPagos();
}

function obtenerEfectivoRecibido() {
    return parseFloat(document.getElementById('efectivo_recibido')?.value) || 0;
}

function calcularVueltas() {
    const pagos = obtenerPagos();
    if (pagos.efectivo <= 0) return 0;
    const efectivoRecibido = obtenerEfectivoRecibido();
    return efectivoRecibido > pagos.efectivo ? efectivoRecibido - pagos.efectivo : 0;
}

function actualizarResumenPagos() {
    const totalPagado = calcularTotalPagado();
    const diferencia = total - totalPagado;
    const vueltas = calcularVueltas();

    document.getElementById('mostrarTotalVenta').textContent = `$ ${total.toLocaleString('es-CO')}`;
    document.getElementById('mostrarTotalPagado').textContent = `$ ${totalPagado.toLocaleString('es-CO')}`;
    document.getElementById('mostrarDiferencia').textContent = `$ ${diferencia.toLocaleString('es-CO')}`;

    const mostrarVueltasEl = document.getElementById('mostrarVueltas');
    if (mostrarVueltasEl) mostrarVueltasEl.textContent = `$ ${vueltas.toLocaleString('es-CO')}`;

    validarPagoParaGuardar();
    actualizarBotonEfectivo();
}

function validarPagoParaGuardar() {
    const btn = document.getElementById('btnGuardarVenta');
    if (!btn) return;

    const pagos = obtenerPagos();
    const totalPagado = calcularTotalPagado();
    const efectivoRecibido = obtenerEfectivoRecibido();

    btn.disabled = !(
        carrito.length > 0 &&
        total > 0 &&
        Math.abs(totalPagado - total) < 0.01 &&
        (pagos.efectivo <= 0 || efectivoRecibido >= pagos.efectivo)
    );
}

/* ─── Guardar venta ──────────────────────────────────────── */
function guardarVenta() {
    const btn = document.getElementById('btnGuardarVenta');
    if (btn.disabled) return;

    btn.disabled = true;
    btn.innerText = 'Guardando...';

    if (carrito.length === 0) {
        alert('El carrito está vacío');
        btn.disabled = false;
        btn.innerText = 'Guardar venta';
        return;
    }

    const pagos = obtenerPagos();
    const totalPagado = calcularTotalPagado();

    if (totalPagado <= 0) {
        alert('Debes ingresar al menos un valor de pago');
        btn.disabled = false;
        btn.innerText = 'Guardar venta';
        return;
    }

    if (Math.abs(totalPagado - total) > 0.01) {
        alert('La suma de los pagos debe ser igual al total de la venta');
        btn.disabled = false;
        btn.innerText = 'Guardar venta';
        return;
    }

    const efectivoRecibido = obtenerEfectivoRecibido();
    if (pagos.efectivo > 0 && efectivoRecibido < pagos.efectivo) {
        alert('El dinero recibido en efectivo no alcanza para cubrir el pago en efectivo');
        btn.disabled = false;
        btn.innerText = 'Guardar venta';
        return;
    }

    fetch('../controllers/ventascontroller.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ carrito, total, pagos })
    })
        .then(response => response.text())
        .then(data => {
            alert(data);

            if (data.toLowerCase().includes('correctamente')) {
                carrito = [];
                total = 0;

                ['pago_efectivo', 'pago_nequi', 'pago_daviplata', 'pago_transferencia'].forEach(id => {
                    document.getElementById(id).value = 0;
                });

                const efectivoRecibidoInput = document.getElementById('efectivo_recibido');
                if (efectivoRecibidoInput) efectivoRecibidoInput.value = 0;

                limpiarExtrasSeleccionados();
                limpiarSaboresSeleccionados();
                renderCarrito();
                actualizarResumenPagos();

                btn.innerText = 'Guardar venta';
                btn.disabled = true;
            } else {
                btn.disabled = false;
                btn.innerText = 'Guardar venta';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar la venta');
            btn.disabled = false;
            btn.innerText = 'Guardar venta';
        });
}

/* ─── Inicialización ─────────────────────────────────────── */
renderCarrito();

document.querySelectorAll('.pago-input').forEach(input => {
    input.addEventListener('input', function () {
        if (input.id === 'pago_efectivo') {
            mostrarCambioEfectivo(obtenerPagos().efectivo > 0);
        }
        actualizarResumenPagos();
    });
});

const inputEfectivoRecibido = document.getElementById('efectivo_recibido');
if (inputEfectivoRecibido) {
    inputEfectivoRecibido.addEventListener('input', actualizarResumenPagos);
}
