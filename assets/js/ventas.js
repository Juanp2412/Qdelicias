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
let categoriaActivaId = 0;
let edicionCarrito = { activa: false, index: null };
let modalResumenPedidoInstance = null;
let modalPagoPedidoInstance = null;
let filtroModalOpciones = '';
let editarDesdeResumen = false;
let resumenModoLectura = false;
let resumenConfigExpandida = {};
let pagoDockOrigen = null;
let pagoDockSiguiente = null;
let modoPagoActual = 'ninguno';
let metodoPagoSimpleActual = null;
let pagoCheckoutMinimizado = false;

// Inicializar contadores de extras
extrasCatalogo.forEach(extra => {
    cantidadesExtras[extra.id] = 0;
});

function calcularExtrasAplicados(productoId, extrasSeleccionados) {
    const reglas = reglasProductos[productoId] || {};
    const usadosPorTipo = {};
    let totalExtrasCobrados = 0;

    const extrasAjustados = extrasSeleccionados.map(extra => {
        const tipo = extra.tipo;
        const limiteGratis = parseInt(reglas[tipo] || 0, 10);
        if (!usadosPorTipo[tipo]) usadosPorTipo[tipo] = 0;

        const disponiblesGratis = Math.max(limiteGratis - usadosPorTipo[tipo], 0);
        const cantidadIncluida = Math.min(extra.cantidad, disponiblesGratis);
        const cantidadCobrada = extra.cantidad - cantidadIncluida;

        usadosPorTipo[tipo] += cantidadIncluida;
        totalExtrasCobrados += cantidadCobrada * extra.precio;

        return {
            ...extra,
            cantidad_incluida: cantidadIncluida,
            cantidad_cobrada: cantidadCobrada
        };
    });

    return {
        extrasAjustados,
        totalExtrasCobrados
    };
}

function recalcularTotalCarrito() {
    total = carrito.reduce((acc, item) => acc + (parseFloat(item.subtotal) || 0), 0);
}

/* ─── Categorías ─────────────────────────────────────────── */
function filtrarCategoria(categoriaId, boton = null) {
    categoriaActivaId = categoriaId;

    const textoBusqueda = (document.getElementById('buscadorProductos')?.value || '').toLowerCase();

    document.querySelectorAll('.producto-item').forEach(producto => {
        const categoriaProducto = parseInt(producto.getAttribute('data-categoria'));
        const nombre = (producto.dataset.nombre || '').toLowerCase();
        const pasaCategoria = (categoriaId === 0 || categoriaProducto === categoriaId);
        const pasaBusqueda = nombre.includes(textoBusqueda);
        producto.style.display = (pasaCategoria && pasaBusqueda) ? 'block' : 'none';
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
        const categoriaProducto = parseInt(p.getAttribute('data-categoria'));
        const pasaCategoria = (categoriaActivaId === 0 || categoriaProducto === categoriaActivaId);
        p.style.display = (n.includes(t) && pasaCategoria) ? 'block' : 'none';
    });
}

function inferirTipoConfiguracion(item) {
    if (item && item.tipo_configuracion) return item.tipo_configuracion;
    if (item.sabores && item.sabores.length > 0) return 'sabores';
    if (item.extras && item.extras.length > 0) return 'extras';
    return 'simple';
}

function productoSoportaExtras(item) {
    return inferirTipoConfiguracion(item) === 'extras';
}

function productoSoportaSabores(item) {
    return inferirTipoConfiguracion(item) === 'sabores';
}

function actualizarBotonConfirmarModal(texto) {
    const btn = document.getElementById('btnConfirmarModalProducto');
    if (btn) btn.textContent = texto;
}

function limpiarModoEdicionCarrito() {
    edicionCarrito = { activa: false, index: null };
    actualizarBotonConfirmarModal('Agregar al carrito');
}

function abrirModalEditarCarrito(index) {
    const item = carrito[index];
    if (!item) return;
    filtroModalOpciones = '';

    const tipo = inferirTipoConfiguracion(item);
    productoSeleccionado = {
        id: item.id,
        nombre: item.nombre,
        precio: item.precio_base,
        tipo
    };

    limpiarExtrasSeleccionados();
    limpiarSaboresSeleccionados();

    (item.extras || []).forEach(extra => {
        cantidadesExtras[extra.id] = parseInt(extra.cantidad, 10) || 0;
    });

    (item.sabores || []).forEach(sabor => {
        cantidadesSabores[sabor.id] = parseInt(sabor.cantidad, 10) || 0;
    });

    edicionCarrito = { activa: true, index };

    document.getElementById('modalProductoNombre').innerText = `Editar: ${item.nombre}`;
    document.getElementById('modalProductoPrecio').innerText = '$ ' + formatearPeso(item.precio_base);
    actualizarBotonConfirmarModal('Guardar cambios');

    if (tipo === 'sabores') {
        renderSaboresEnModal(item.id);
    } else if (tipo === 'extras') {
        renderExtrasEnModal(item.id);
    } else {
        document.getElementById('modalReglasProducto').innerHTML = '';
        document.getElementById('modalExtrasContenido').innerHTML =
            "<p class='text-muted mb-0'>Este producto no tiene extras o sabores para editar.</p>";
    }

    actualizarSubtotalModal();

    const elResumen = document.getElementById('modalResumenPedido');
    const resumenAbierto = elResumen && elResumen.classList.contains('show');

    editarDesdeResumen = resumenAbierto;

    const abrirProducto = () => {
        new bootstrap.Modal(document.getElementById('modalProducto')).show();
    };

    if (resumenAbierto && modalResumenPedidoInstance) {
        elResumen.addEventListener('hidden.bs.modal', abrirProducto, { once: true });
        modalResumenPedidoInstance.hide();
    } else {
        abrirProducto();
    }
}

/* ─── Modal de producto ──────────────────────────────────── */
function abrirModalProducto(id, nombre, precio, tipo) {
    limpiarModoEdicionCarrito();
    filtroModalOpciones = '';
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

function renderToolbarOpcionesModal() {
    return `
        <div class="modal-opciones-toolbar mb-3">
            <input
                type="text"
                class="form-control form-control-sm"
                placeholder="Buscar extra o sabor..."
                value="${filtroModalOpciones}"
                oninput="filtrarOpcionesModal(this.value)"
            >
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="limpiarFiltroOpcionesModal()">
                Limpiar
            </button>
        </div>`;
}

function filtrarOpcionesModal(texto) {
    filtroModalOpciones = (texto || '').toLowerCase().trim();

    const opciones = document.querySelectorAll('#modalExtrasContenido .modal-opcion-wrap');
    opciones.forEach(opcion => {
        const nombre = (opcion.dataset.nombre || '').toLowerCase();
        opcion.style.display = (!filtroModalOpciones || nombre.includes(filtroModalOpciones)) ? '' : 'none';
    });

    const grupos = document.querySelectorAll('#modalExtrasContenido .modal-tipo-group');
    grupos.forEach(grupo => {
        const visibles = Array.from(grupo.querySelectorAll('.modal-opcion-wrap')).some(el => el.style.display !== 'none');
        grupo.style.display = visibles ? '' : 'none';
    });
}

function limpiarFiltroOpcionesModal() {
    filtroModalOpciones = '';
    const input = document.querySelector('#modalExtrasContenido .modal-opciones-toolbar input');
    if (input) input.value = '';
    filtrarOpcionesModal('');
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

    let html = renderToolbarOpcionesModal();
    Object.keys(extrasPorTipo).forEach(tipo => {
        html += `<div class="mb-3 modal-tipo-group"><h6 class="modal-extra-title">${tipo}</h6><div class="row">`;

        extrasPorTipo[tipo].forEach(extra => {
            const cantidad = cantidadesExtras[extra.id] || 0;
            html += `
                <div class="col-lg-4 col-md-6 col-12 modal-opcion-wrap" data-nombre="${(extra.nombre || '').toLowerCase()}">
                    <div class="extra-item h-100">
                        <div>
                            <strong>${extra.nombre}</strong><br>
                            <small class="text-muted">$ ${formatearPeso(extra.precio)}</small>
                        </div>
                        <div class="qty-controls">
                            <button type="button" class="qty-btn minus" onclick="cambiarCantidadExtra(${extra.id}, -1)">-</button>
                            <input
                                type="number"
                                min="0"
                                step="1"
                                class="form-control form-control-sm text-center"
                                style="width: 70px;"
                                id="modal_cantidad_extra_${extra.id}"
                                value="${cantidad}"
                                oninput="establecerCantidadExtra(${extra.id}, this.value)"
                            >
                            <button type="button" class="qty-btn plus"  onclick="cambiarCantidadExtra(${extra.id}, 1)">+</button>
                        </div>
                    </div>
                </div>`;
        });

        html += '</div></div>';
    });

    contenedor.innerHTML = html;
    filtrarOpcionesModal(filtroModalOpciones);
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

    let html = `${renderToolbarOpcionesModal()}<div class="modal-tipo-group"><div class="row">`;

    sabores.forEach(sabor => {
        const cantidad = cantidadesSabores[sabor.id] || 0;
        html += `
            <div class="col-lg-4 col-md-6 col-12 modal-opcion-wrap" data-nombre="${(sabor.nombre || '').toLowerCase()}">
                <div class="extra-item h-100">
                    <strong>${sabor.nombre}</strong>
                    <div class="qty-controls">
                        <button type="button" class="qty-btn minus" onclick="cambiarCantidadSabor(${sabor.id}, -1)">-</button>
                        <input
                            type="number"
                            min="0"
                            step="1"
                            class="form-control form-control-sm text-center"
                            style="width: 70px;"
                            id="cantidad_sabor_${sabor.id}"
                            value="${cantidad}"
                            oninput="establecerCantidadSabor(${sabor.id}, this.value)"
                        >
                        <button type="button" class="qty-btn plus"  onclick="cambiarCantidadSabor(${sabor.id}, 1)">+</button>
                    </div>
                </div>
            </div>`;
    });

    html += '</div></div>';
    contenedor.innerHTML = html;
    filtrarOpcionesModal(filtroModalOpciones);
}

/* ─── Cantidad extras ────────────────────────────────────── */
function cambiarCantidadExtra(extraId, cambio) {
    let actual = (parseInt(cantidadesExtras[extraId]) || 0) + cambio;
    if (actual < 0) actual = 0;
    cantidadesExtras[extraId] = actual;

    const input = document.getElementById('modal_cantidad_extra_' + extraId);
    if (input) input.value = actual;

    actualizarSubtotalModal();
}

function establecerCantidadExtra(extraId, valor) {
    let actual = parseInt(valor, 10);
    if (Number.isNaN(actual) || actual < 0) actual = 0;
    cantidadesExtras[extraId] = actual;

    const input = document.getElementById('modal_cantidad_extra_' + extraId);
    if (input && parseInt(input.value, 10) !== actual) input.value = actual;

    actualizarSubtotalModal();
}

/* ─── Cantidad sabores ───────────────────────────────────── */
function cambiarCantidadSabor(saborId, cambio) {
    let actual = (parseInt(cantidadesSabores[saborId]) || 0) + cambio;
    if (actual < 0) actual = 0;
    cantidadesSabores[saborId] = actual;

    const input = document.getElementById('cantidad_sabor_' + saborId);
    if (input) input.value = actual;

    actualizarSubtotalModal();
}

function establecerCantidadSabor(saborId, valor) {
    let actual = parseInt(valor, 10);
    if (Number.isNaN(actual) || actual < 0) actual = 0;
    cantidadesSabores[saborId] = actual;

    const input = document.getElementById('cantidad_sabor_' + saborId);
    if (input && parseInt(input.value, 10) !== actual) input.value = actual;

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

    if (edicionCarrito.activa) {
        actualizarLineaCarritoDesdeModal();
    } else {
        agregarProductoConExtras(
            productoSeleccionado.id,
            productoSeleccionado.nombre,
            productoSeleccionado.precio
        );
    }

    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalProducto'));
    if (modalInstance) modalInstance.hide();

    limpiarSaboresSeleccionados();
    productoSeleccionado = null;
    limpiarModoEdicionCarrito();
}

function actualizarLineaCarritoDesdeModal() {
    const index = edicionCarrito.index;
    const lineaActual = carrito[index];
    if (!lineaActual || !productoSeleccionado) return;

    const extrasSeleccionados = obtenerExtrasSeleccionados();
    const saboresSeleccionados = (productoSeleccionado.tipo === 'sabores')
        ? obtenerSaboresSeleccionados()
        : [];

    const calculoExtras = calcularExtrasAplicados(productoSeleccionado.id, extrasSeleccionados);
    const precioUnitario = (parseFloat(productoSeleccionado.precio) || 0) + calculoExtras.totalExtrasCobrados;

    const nuevaLinea = {
        ...lineaActual,
        clave: generarClaveLinea(productoSeleccionado.id, calculoExtras.extrasAjustados),
        precio_base: parseFloat(productoSeleccionado.precio) || 0,
        tipo_configuracion: productoSeleccionado.tipo || inferirTipoConfiguracion(lineaActual),
        extras: calculoExtras.extrasAjustados,
        sabores: saboresSeleccionados,
        subtotal: precioUnitario * lineaActual.cantidad
    };

    carrito[index] = nuevaLinea;

    const indiceDuplicado = carrito.findIndex((item, i) => i !== index && item.clave === nuevaLinea.clave);
    if (indiceDuplicado >= 0) {
        carrito[indiceDuplicado].cantidad += nuevaLinea.cantidad;
        carrito[indiceDuplicado].subtotal += nuevaLinea.subtotal;
        carrito.splice(index, 1);
    }

    recalcularTotalCarrito();
    renderCarrito();
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
        const input = document.getElementById('modal_cantidad_extra_' + extra.id);
        if (input) input.value = 0;
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

    const calculoExtras = calcularExtrasAplicados(id, extrasSeleccionados);
    const precioUnitarioLinea = precio + calculoExtras.totalExtrasCobrados;
    const clave = generarClaveLinea(id, calculoExtras.extrasAjustados);
    const productoExistente = carrito.find(item => item.clave === clave);

    if (productoExistente) {
        productoExistente.cantidad++;
        productoExistente.subtotal += precioUnitarioLinea;
        if (!productoExistente.tipo_configuracion) {
            productoExistente.tipo_configuracion = (productoSeleccionado && productoSeleccionado.tipo)
                ? productoSeleccionado.tipo
                : inferirTipoConfiguracion(productoExistente);
        }
    } else {
        carrito.push({
            clave,
            id,
            nombre,
            precio_base: precio,
            tipo_configuracion: (productoSeleccionado && productoSeleccionado.tipo) ? productoSeleccionado.tipo : 'simple',
            cantidad: 1,
            extras: calculoExtras.extrasAjustados,
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

function actualizarCantidad(index, nuevaCantidad) {
    if (!carrito[index]) return;

    const cantidadNormalizada = Math.max(1, parseInt(nuevaCantidad, 10) || 1);
    const valorUnitario = carrito[index].subtotal / carrito[index].cantidad;
    const subtotalAnterior = carrito[index].subtotal;

    carrito[index].cantidad = cantidadNormalizada;
    carrito[index].subtotal = valorUnitario * cantidadNormalizada;
    total += carrito[index].subtotal - subtotalAnterior;

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
    modoPagoActual = 'ninguno';
    metodoPagoSimpleActual = null;

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
            const mostrarSabores = productoSoportaSabores(item);
            const mostrarExtras = productoSoportaExtras(item);

            const cantidadSabores = (item.sabores || []).reduce(
                (acc, s) => acc + (parseInt(s.cantidad, 10) || 0),
                0
            );

            const cantidadExtras = (item.extras || []).reduce(
                (acc, e) => acc + (parseInt(e.cantidad, 10) || 0),
                0
            );

            const detalleLineas = [];
            if (mostrarSabores && cantidadSabores > 0) detalleLineas.push(`Sabores: ${cantidadSabores}`);
            if (mostrarExtras && cantidadExtras > 0) detalleLineas.push(`Extras: ${cantidadExtras}`);

            tbody.innerHTML += `
                <tr>
                    <td>
                        <strong>${item.nombre}</strong>
                        ${detalleLineas.length > 0
                    ? `<span class="detalle-extra">
                                ${detalleLineas.map(linea => `<span class="detalle-linea">${linea}</span>`).join('')}
                            </span>`
                    : ''}
                    </td>
                    <td>
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2" onclick="actualizarCantidad(${index}, ${item.cantidad - 1})">−</button>
                            <input
                                type="number"
                                min="1"
                                step="1"
                                class="form-control form-control-sm text-center"
                                style="width: 52px;"
                                value="${item.cantidad}"
                                oninput="actualizarCantidad(${index}, this.value)"
                            >
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2" onclick="actualizarCantidad(${index}, ${item.cantidad + 1})">+</button>
                        </div>
                    </td>
                    <td>$ ${formatearPeso(item.subtotal)}</td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <button class="btn btn-outline-primary btn-sm" onclick="abrirModalEditarCarrito(${index})">Editar</button>
                            <button class="btn btn-danger btn-sm" onclick="eliminar(${index})">X</button>
                        </div>
                    </td>
                </tr>`;
        });
    }

    document.getElementById('total').innerText = formatearPeso(total);
    actualizarResumenPagos();

    const modalResumen = document.getElementById('modalResumenPedido');
    if (modalResumen && modalResumen.classList.contains('show')) {
        renderResumenPedido();
    }
}

function abrirResumenPedido() {
    renderResumenPedido();

    if (!modalResumenPedidoInstance) {
        modalResumenPedidoInstance = new bootstrap.Modal(document.getElementById('modalResumenPedido'), {
            focus: false
        });
    }

    modalResumenPedidoInstance.show();
}

function moverPagoAlModalCheckout() {
    const dock = document.getElementById('paymentDock');
    const target = document.getElementById('paymentDockTarget');
    if (!dock || !target) return;

    if (!pagoDockOrigen) {
        pagoDockOrigen = dock.parentElement;
        pagoDockSiguiente = dock.nextElementSibling;
    }

    target.appendChild(dock);
    togglePagoPanel(true);
}

function devolverPagoAlLateral() {
    const dock = document.getElementById('paymentDock');
    if (!dock || !pagoDockOrigen) return;

    if (pagoDockSiguiente && pagoDockSiguiente.parentElement === pagoDockOrigen) {
        pagoDockOrigen.insertBefore(dock, pagoDockSiguiente);
    } else {
        pagoDockOrigen.appendChild(dock);
    }
}

function abrirConfirmacionVenta() {
    if (carrito.length === 0) {
        alert('Primero agrega productos al carrito');
        return;
    }

    moverPagoAlModalCheckout();
    abrirResumenPedido();
    abrirModalPagoPedido();
}

function abrirModalPagoPedido() {
    toggleMinimizarPagoCheckout(false);

    if (!modalPagoPedidoInstance) {
        modalPagoPedidoInstance = new bootstrap.Modal(document.getElementById('modalPagoPedido'), {
            backdrop: false,
            focus: false
        });
    }

    modalPagoPedidoInstance.show();
    validarPagoParaGuardar();

    // Forzar foco en el input de efectivo para permitir escritura inmediata.
    const inputEfectivo = document.getElementById('efectivo_recibido');
    if (inputEfectivo && !document.getElementById('boxCambioEfectivo')?.classList.contains('d-none')) {
        setTimeout(() => {
            inputEfectivo.focus();
            inputEfectivo.select();
        }, 80);
    }
}

function aplicarEstadoPagoMinimizado() {
    const modalPagoEl = document.getElementById('modalPagoPedido');
    const btnMin = document.getElementById('btnMinimizarPagoCheckout');
    const totalEl = document.getElementById('pagoMinimizadoTotal');
    if (!modalPagoEl) return;

    modalPagoEl.classList.toggle('is-minimized', pagoCheckoutMinimizado);

    if (btnMin) {
        btnMin.innerText = pagoCheckoutMinimizado ? '▲' : '−';
        btnMin.setAttribute('aria-pressed', pagoCheckoutMinimizado ? 'true' : 'false');
        btnMin.setAttribute('title', pagoCheckoutMinimizado ? 'Expandir cobro' : 'Minimizar cobro');
    }

    if (totalEl) {
        totalEl.textContent = pagoCheckoutMinimizado ? `Total: $ ${formatearPeso(total)}` : '';
    }

    const header = modalPagoEl.querySelector('.modal-header');
    if (header) {
        if (pagoCheckoutMinimizado) {
            header.addEventListener('click', _onHeaderMinimizadoClick);
        } else {
            header.removeEventListener('click', _onHeaderMinimizadoClick);
        }
    }
}

function _onHeaderMinimizadoClick(e) {
    if (e.target.closest('.btn-close') || e.target.closest('.btn-min-pago')) return;
    toggleMinimizarPagoCheckout(false);
}

function toggleMinimizarPagoCheckout(forzarEstado) {
    if (typeof forzarEstado === 'boolean') {
        pagoCheckoutMinimizado = forzarEstado;
    } else {
        pagoCheckoutMinimizado = !pagoCheckoutMinimizado;
    }

    aplicarEstadoPagoMinimizado();
}

function toggleResumenLectura(activado) {
    resumenModoLectura = !!activado;
    renderResumenPedido();
}

function toggleResumenConfig(index) {
    resumenConfigExpandida[index] = !resumenConfigExpandida[index];
    renderResumenPedido();
}

function renderResumenPedido() {
    const contenedor = document.getElementById('resumenPedidoContenido');
    const totalResumen = document.getElementById('resumenPedidoTotal');
    const hostLectura = document.getElementById('resumenLecturaHeader');
    if (!contenedor || !totalResumen) return;

    if (hostLectura) {
        hostLectura.innerHTML = `
            <label class="rc-toggle-read">
                <input type="checkbox" ${resumenModoLectura ? 'checked' : ''} onchange="toggleResumenLectura(this.checked)">
                <span>Modo lectura al cliente</span>
            </label>`;
    }

    totalResumen.textContent = `$ ${formatearPeso(total)}`;

    if (carrito.length === 0) {
        contenedor.innerHTML = `
            <div class="alert alert-light border text-center mb-0">
                No hay productos en el carrito.
            </div>`;
        return;
    }

    const totalItems = carrito.reduce((acc, item) => acc + (parseInt(item.cantidad, 10) || 0), 0);

    const tarjetas = carrito.map((item, index) => {
        const cantidad = parseInt(item.cantidad, 10) || 1;
        const valorUnitario = cantidad > 0 ? (item.subtotal / cantidad) : item.subtotal;
        const mostrarSabores = productoSoportaSabores(item);
        const mostrarExtras = productoSoportaExtras(item);

        // Construir chips de configuración con modo colapsable por fila.
        const chipsConfig = [];

        if (mostrarSabores && item.sabores && item.sabores.length > 0) {
            item.sabores.forEach(s => {
                chipsConfig.push(`<span class="rc-chip rc-chip--sabor">${s.nombre} × ${s.cantidad}</span>`);
            });
        }

        if (mostrarExtras && item.extras && item.extras.length > 0) {
            item.extras.forEach(extra => {
                const plus = extra.cantidad_cobrada > 0
                    ? ` <span class="rc-chip-price">+$${formatearPeso(extra.cantidad_cobrada * extra.precio)}</span>`
                    : '';
                chipsConfig.push(`<span class="rc-chip rc-chip--extra">${extra.nombre} × ${extra.cantidad}${plus}</span>`);
            });
        }

        const configRow = chipsConfig.length > 0
            ? `<div class="rc-config">${chipsConfig.join('')}</div>`
            : '';

        const tipoCard = mostrarExtras ? 'extras' : (mostrarSabores ? 'sabores' : 'simple');
        const claseSimple = chipsConfig.length === 0 ? ' rc-row--simple' : '';

        return `
            <div class="rc-row${claseSimple}" data-tipo="${tipoCard}">
                <div class="rc-num">${index + 1}</div>
                <div class="rc-body">
                    <div class="rc-main">
                        <span class="rc-nombre">${item.nombre}</span>
                    </div>
                    ${configRow}
                </div>
                <div class="rc-qty">
                    ${resumenModoLectura
                ? `<span class="rc-qty-read">x${cantidad}</span>`
                : `<input
                            type="number"
                            min="1"
                            step="1"
                            class="rc-qty-input"
                            value="${cantidad}"
                            oninput="actualizarCantidad(${index}, this.value)"
                        >`}
                </div>
                <div class="rc-price">$&thinsp;${formatearPeso(valorUnitario)}</div>
                <div class="rc-total-cell">
                    <div class="rc-total">$&thinsp;${formatearPeso(item.subtotal)}</div>
                    <div class="rc-actions">
                        <button class="rc-btn rc-btn--edit" onclick="abrirModalEditarCarrito(${index})">Editar</button>
                        <button class="rc-btn rc-btn--del" onclick="eliminar(${index})">Quitar</button>
                    </div>
                </div>
            </div>`;
    }).join('');

    contenedor.innerHTML = `
        <div class="rc-summary-bar">
            <div class="rc-summary-item">
                <span class="rc-summary-label">Productos</span>
                <strong class="rc-summary-val">${totalItems}</strong>
            </div>
            <div class="rc-summary-item">
                <span class="rc-summary-label">Líneas</span>
                <strong class="rc-summary-val">${carrito.length}</strong>
            </div>
            <div class="rc-summary-item rc-summary-item--total">
                <span class="rc-summary-label">Total del pedido</span>
                <strong class="rc-summary-val">$ ${formatearPeso(total)}</strong>
            </div>
        </div>
        <div class="rc-table${resumenModoLectura ? ' rc-table--reading' : ''}">
            <div class="rc-header">
                <div class="rc-num">#</div>
                <div class="rc-body">Producto</div>
                <div class="rc-qty">Cant.</div>
                <div class="rc-price">Precio</div>
                <div class="rc-total">Subtotal</div>
            </div>
            <div class="rc-rows">${tarjetas}</div>
        </div>
    `;
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

function togglePagoPanel(forzarAbierto = null) {
    const panel = document.getElementById('paymentPanelBody');
    const boton = document.getElementById('btnTogglePagoPanel');
    const mini = document.getElementById('paymentMiniSummary');
    if (!panel || !boton) return;

    const abrir = (forzarAbierto === null)
        ? panel.classList.contains('d-none')
        : !!forzarAbierto;

    panel.classList.toggle('d-none', !abrir);
    if (mini) mini.classList.toggle('d-none', abrir);
    boton.textContent = abrir ? 'Ocultar pagos' : 'Ver pagos';
}



function seleccionarPagoSimple(metodo, boton) {
    togglePagoPanel(true);
    marcarPagoActivo(boton);
    ocultarInputsPago();
    modoPagoActual = 'simple';
    metodoPagoSimpleActual = metodo;

    ['pago_efectivo', 'pago_nequi', 'pago_daviplata', 'pago_transferencia'].forEach(id => {
        document.getElementById(id).value = 0;
    });

    document.getElementById('pago_' + metodo).value = total;
    document.getElementById('efectivo_recibido').value = (metodo === 'efectivo') ? total : 0;

    mostrarCambioEfectivo(metodo === 'efectivo');
    actualizarResumenPagos();
}

function activarPagoMixto(boton) {
    togglePagoPanel(true);
    marcarPagoActivo(boton);
    mostrarInputsPago();
    modoPagoActual = 'mixto';
    metodoPagoSimpleActual = null;

    ['pago_efectivo', 'pago_nequi', 'pago_daviplata', 'pago_transferencia', 'efectivo_recibido'].forEach(id => {
        document.getElementById(id).value = 0;
    });

    actualizarResumenPagos();
}



function obtenerEfectivoRecibido() {
    return parseFloat(document.getElementById('efectivo_recibido')?.value) || 0;
}

function sincronizarPagoRapidoConTotal() {
    if (modoPagoActual !== 'simple' || !metodoPagoSimpleActual) return;

    const ids = ['pago_efectivo', 'pago_nequi', 'pago_daviplata', 'pago_transferencia'];
    ids.forEach(id => {
        const input = document.getElementById(id);
        if (input) input.value = 0;
    });

    const inputMetodo = document.getElementById('pago_' + metodoPagoSimpleActual);
    if (inputMetodo) inputMetodo.value = total;

    mostrarCambioEfectivo(metodoPagoSimpleActual === 'efectivo');
}

function calcularVueltas() {
    const pagos = obtenerPagos();
    if (pagos.efectivo <= 0) return 0;
    const efectivoRecibido = obtenerEfectivoRecibido();
    return efectivoRecibido > pagos.efectivo ? efectivoRecibido - pagos.efectivo : 0;
}

function actualizarResumenPagos() {
    sincronizarPagoRapidoConTotal();

    const totalPagado = calcularTotalPagado();
    const diferencia = total - totalPagado;
    const vueltas = calcularVueltas();

    document.getElementById('mostrarTotalVenta').textContent = `$ ${total.toLocaleString('es-CO')}`;
    document.getElementById('mostrarTotalPagado').textContent = `$ ${totalPagado.toLocaleString('es-CO')}`;
    document.getElementById('mostrarDiferencia').textContent = `$ ${diferencia.toLocaleString('es-CO')}`;

    const totalPagadoMini = document.getElementById('mostrarTotalPagadoMini');
    if (totalPagadoMini) totalPagadoMini.textContent = `$ ${totalPagado.toLocaleString('es-CO')}`;

    const diferenciaMini = document.getElementById('mostrarDiferenciaMini');
    if (diferenciaMini) {
        diferenciaMini.textContent = `$ ${diferencia.toLocaleString('es-CO')}`;
        diferenciaMini.classList.toggle('text-success', Math.abs(diferencia) < 0.01);
        diferenciaMini.classList.toggle('text-danger', diferencia > 0.009);
    }

    const mostrarVueltasEl = document.getElementById('mostrarVueltas');
    if (mostrarVueltasEl) mostrarVueltasEl.textContent = `$ ${vueltas.toLocaleString('es-CO')}`;

    validarPagoParaGuardar();
}

function validarPagoParaGuardar() {
    const btnPago = document.getElementById('btnConfirmarGuardarPago');
    if (!btnPago) return;

    const pagos = obtenerPagos();
    const totalPagado = calcularTotalPagado();
    const efectivoRecibido = obtenerEfectivoRecibido();

    const puedeConfirmar = (
        carrito.length > 0 &&
        total > 0 &&
        Math.abs(totalPagado - total) < 0.01 &&
        (pagos.efectivo <= 0 || efectivoRecibido >= pagos.efectivo)
    );

    btnPago.disabled = !puedeConfirmar;
}

/* ─── Guardar venta ──────────────────────────────────────── */
function guardarVenta(origen = 'principal') {
    const btnVerificar = document.getElementById('btnGuardarVenta');
    const btnPago = document.getElementById('btnConfirmarGuardarPago');
    const btnAccion = (origen === 'checkout') ? btnPago : btnVerificar;
    if (!btnAccion) return;

    if (btnVerificar) btnVerificar.disabled = true;
    if (btnPago) btnPago.disabled = true;
    btnAccion.innerText = 'Guardando...';

    if (carrito.length === 0) {
        alert('El carrito está vacío');
        if (btnVerificar) btnVerificar.disabled = false;
        if (btnPago) {
            btnPago.innerText = '✔ Confirmar y guardar';
            btnPago.disabled = false;
        }
        return;
    }

    const pagos = obtenerPagos();
    const totalPagado = calcularTotalPagado();

    if (totalPagado <= 0) {
        alert('Debes ingresar al menos un valor de pago');
        if (btnVerificar) btnVerificar.disabled = false;
        if (btnPago) {
            btnPago.innerText = '✔ Confirmar y guardar';
            btnPago.disabled = false;
        }
        return;
    }

    if (Math.abs(totalPagado - total) > 0.01) {
        alert('La suma de los pagos debe ser igual al total de la venta');
        if (btnVerificar) btnVerificar.disabled = false;
        if (btnPago) {
            btnPago.innerText = '✔ Confirmar y guardar';
            btnPago.disabled = false;
        }
        return;
    }

    const efectivoRecibido = obtenerEfectivoRecibido();
    if (pagos.efectivo > 0 && efectivoRecibido < pagos.efectivo) {
        alert('El dinero recibido en efectivo no alcanza para cubrir el pago en efectivo');
        if (btnVerificar) btnVerificar.disabled = false;
        if (btnPago) {
            btnPago.innerText = '✔ Confirmar y guardar';
            btnPago.disabled = false;
        }
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

                const modalResumenEl = document.getElementById('modalResumenPedido');
                const modalResumen = modalResumenEl ? bootstrap.Modal.getInstance(modalResumenEl) : null;
                if (modalResumen) modalResumen.hide();

                const modalPagoEl = document.getElementById('modalPagoPedido');
                const modalPago = modalPagoEl ? bootstrap.Modal.getInstance(modalPagoEl) : null;
                if (modalPago) modalPago.hide();
                devolverPagoAlLateral();

                if (btnVerificar) btnVerificar.disabled = false;
                if (btnPago) {
                    btnPago.innerText = '✔ Confirmar y guardar';
                    btnPago.disabled = true;
                }
            } else {
                if (btnVerificar) btnVerificar.disabled = false;
                if (btnPago) {
                    btnPago.innerText = '✔ Confirmar y guardar';
                    btnPago.disabled = false;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar la venta');
            if (btnVerificar) btnVerificar.disabled = false;
            if (btnPago) {
                btnPago.innerText = '✔ Confirmar y guardar';
                btnPago.disabled = false;
            }
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

const modalProductoEl = document.getElementById('modalProducto');
if (modalProductoEl) {
    modalProductoEl.addEventListener('hidden.bs.modal', () => {
        const volverResumen = editarDesdeResumen;
        editarDesdeResumen = false;
        limpiarModoEdicionCarrito();
        productoSeleccionado = null;
        limpiarSaboresSeleccionados();
        limpiarExtrasSeleccionados();
        if (volverResumen) {
            abrirResumenPedido();
        }
    });
}

function cerrarCheckout() {
    const modalResumenEl = document.getElementById('modalResumenPedido');
    const modalPagoEl = document.getElementById('modalPagoPedido');

    const modalResumen = modalResumenEl ? (modalResumenPedidoInstance || bootstrap.Modal.getInstance(modalResumenEl)) : null;
    const modalPago = modalPagoEl ? (modalPagoPedidoInstance || bootstrap.Modal.getInstance(modalPagoEl)) : null;

    if (modalPago) modalPago.hide();
    if (modalResumen) modalResumen.hide();
    devolverPagoAlLateral();
    toggleMinimizarPagoCheckout(false);
}

const modalPagoCheckoutEl = document.getElementById('modalPagoPedido');
if (modalPagoCheckoutEl) {
    modalPagoCheckoutEl.addEventListener('hidden.bs.modal', () => {
        devolverPagoAlLateral();
        toggleMinimizarPagoCheckout(false);
    });
}
