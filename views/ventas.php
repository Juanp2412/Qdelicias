<?php
/*
|--------------------------------------------------------------------------
| Archivo: ventas.php
|--------------------------------------------------------------------------
| Propósito:
| Vista principal del módulo de ventas tipo caja registradora. Permite
| seleccionar extras, aplicar reglas de inclusión por producto, agregar
| productos al carrito, calcular totales y enviar la venta al backend.
|
| Funcionalidades principales:
| - Valida sesión del usuario.
| - Carga productos, extras y reglas desde la base de datos.
| - Permite seleccionar cantidades de extras antes de elegir un producto.
| - Calcula extras incluidos y extras cobrados según reglas del producto.
| - Agrupa líneas iguales en el carrito usando producto + combinación de extras.
| - Permite modificar cantidades y eliminar productos del carrito.
| - Calcula el total general de la venta.
| - Envía la venta al controlador mediante fetch en formato JSON.
|
| Observación:
| Este archivo concentra la lógica visual y parte de la lógica dinámica
| del carrito. Es la base operativa del POS y representa la pantalla de
| venta rápida del sistema.
|--------------------------------------------------------------------------
*/
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

verificarLogin();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$productos = $conn->query("
    SELECT * 
    FROM productos 
    WHERE estado = 1
    ORDER BY nombre ASC
");
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
$saboresPorProducto = [];

$sqlSabores = "SELECT ps.producto_id, s.id, s.nombre
               FROM producto_sabores ps
               INNER JOIN sabores s ON s.id = ps.sabor_id
               WHERE s.activo = 1
               ORDER BY s.nombre ASC";

$resultSabores = $conn->query($sqlSabores);

while ($filaSabor = $resultSabores->fetch_assoc()) {
    $productoId = $filaSabor['producto_id'];

    if (!isset($saboresPorProducto[$productoId])) {
        $saboresPorProducto[$productoId] = [];
    }

    $saboresPorProducto[$productoId][] = [
        'id' => (int)$filaSabor['id'],
        'nombre' => $filaSabor['nombre']
    ];
}
$extras = $conn->query("SELECT * FROM extras ORDER BY nombre ASC");
$reglasDB = $conn->query("SELECT producto_id, tipo_extra, cantidad_incluida FROM producto_reglas_extras");
$reglasProductos = [];

while ($r = $reglasDB->fetch_assoc()) {
    $productoId = $r['producto_id'];
    $tipoExtra = $r['tipo_extra'];
    $cantidadIncluida = (int)$r['cantidad_incluida'];

    if (!isset($reglasProductos[$productoId])) {
        $reglasProductos[$productoId] = [];
    }

    $reglasProductos[$productoId][$tipoExtra] = $cantidadIncluida;
}
$extrasData = [];

while ($e = $extras->fetch_assoc()) {
    $extrasData[] = $e;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ventas - QDelicias POS</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
:root {
    --dark:#17191d;
    --purple:#7c3aed;
    --pink:#ec4899;
    --green:#15803d;
    --bg:#f6f2fb;
    --border:#e5e7eb;
    --muted:#6b7280
}

body {
    background:radial-gradient(circle at top left,#fff7ed 0,#f7f2ff 35%,#f8fafc 80%);
    font-size:14px
}

.pos-shell {
    padding:14px
}

.top-bar {
    background:linear-gradient(135deg,#111827,#20242b);
    color:white;
    padding:14px 16px;
    border-radius:16px;
    margin-bottom:14px;
    box-shadow:0 10px 28px rgba(17,24,39,.18)
}

.brand-title {
    font-size:22px;
    font-weight:900
}

.brand-subtitle {
    color:#d1d5db;
    font-size:12px
}

.layout-grid {
    display:grid;
    grid-template-columns:210px minmax(430px,1fr) 550px;
    gap:14px;
    align-items:start
}

.panel-card {
    background:rgba(255,255,255,.95);
    border:1px solid var(--border);
    border-radius:18px;
    box-shadow:0 12px 30px rgba(15,23,42,.08)
}

.side-panel {
    padding:14px;
    position:sticky;
    top:14px;
    min-height:calc(100vh - 110px)
}

.products-panel {
    padding:14px;
    min-height:calc(100vh - 110px)
}

.cart-panel {
    padding:12px;
    position:sticky;
    top:14px;
    max-height:calc(100vh - 28px);
    overflow:auto
}

.section-title {
    font-size:18px;
    font-weight:900;
    margin:0;
    color:#111827
}

.section-help {
    color:var(--muted);
    font-size:12px
}

.btn-categoria {
    width:100%;
    border-radius:12px;
    padding:10px 11px;
    margin-bottom:8px;
    font-weight:700;
    text-align:left;
    display:flex;
    align-items:center;
    justify-content:space-between
}

.btn-categoria.btn-dark {
    background:#111827;
    border-color:#111827
}

.toolbar {
    display:flex;
    gap:10px;
    align-items:center;
    justify-content:space-between;
    margin-bottom:14px
}

.search-box {
    max-width:360px;
    border-radius:12px;
    padding:10px 13px
}

.productos-grid {
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:12px
}

.producto-btn {
    width:100%;
    min-height:150px;
    border-radius:18px;
    border:1px solid #dbeafe;
    background:#fff;
    padding:0;
    text-align:left;
    overflow:hidden;
    box-shadow:0 8px 18px rgba(37,99,235,.08);
    transition:.12s
}

.producto-btn:hover {
    transform:translateY(-2px);
    box-shadow:0 14px 26px rgba(124,58,237,.16);
    border-color:var(--purple)
}

.producto-img {
    height:82px;
    background:linear-gradient(135deg,#fde68a,#f0abfc,#c4b5fd);
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-size:34px;
    font-weight:900
}

.producto-img img {
    width:100%;
    height:100%;
    object-fit:cover;
    display:block
}

.producto-body {
    padding:11px 12px 12px
}

.producto-nombre {
    font-size:14px;
    font-weight:850;
    color:#111827;
    line-height:1.2;
    min-height:34px
}

.precio-text {
    display:block;
    color:var(--purple);
    font-size:15px;
    font-weight:900;
    margin-top:6px
}

.tipo-chip {
    font-size:10px;
    padding:3px 7px;
    border-radius:999px;
    background:#f3e8ff;
    color:#6d28d9;
    font-weight:800
}

.table {
    font-size:12px;
    margin-bottom:10px
}

.table thead th {
    background:#15191f!important;
    color:#fff;
    border-color:#15191f!important
}

.detalle-extra {
    font-size:11px;
    color:#6b7280;
    display:block;
    margin-top:4px;
    line-height:1.3
}

.total-box {
    background:linear-gradient(135deg,#16a34a,#15803d);
    color:white;
    border-radius:18px;
    padding:18px;
    text-align:center;
    font-size:28px;
    font-weight:900;
    box-shadow:0 12px 24px rgba(22,163,74,.20)
}

.payment-card,.payment-summary {
    border:1px solid var(--border);
    border-radius:16px;
    padding:12px;
    background:#fff
}

.payment-summary {
    background:#f8fafc;
    font-size:13px
}

.quick-pay-grid {
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:6px
}

.quick-pay-grid .btn,.acciones-finales .btn {
    border-radius:12px;
    padding:7px 6px;
    font-weight:900;
    font-size:12px;
    min-height:36px
}



.quick-pay-grid .btn.active-pay {
    background:#111827!important;
    color:#fff!important;
    border-color:#111827!important
}

.payment-inputs {
    display:none;
    margin-top:9px
}

.payment-inputs.show {
    display:block
}

.payment-inputs .form-label {
    font-size:11px;
    margin-bottom:3px
}
.cambio-efectivo-box {
    border: 1px solid #bbf7d0;
    border-radius: 15px;
    padding: 10px;
    background: #f0fdf4;
}

.vueltas-box {
    background: #d1fae5;
    border: 1px solid #86efac;
    border-radius: 14px;
    padding: 11px 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.vueltas-box span {
    color: #166534;
    font-weight: 800;
    font-size: 12px;
}

.vueltas-box strong {
    color: #14532d;
    font-size: 22px;
    font-weight: 900;
}

.form-control {
    border-radius:12px
}

.acciones-finales {
    display:flex;
    gap:10px;
    flex-wrap:wrap
}

.modal-content {
    border-radius:20px;
    border:0;
    overflow:hidden
}

.modal-product-header {
    background:linear-gradient(135deg,#faf5ff,#fff7ed);
    border:1px solid var(--border);
    border-radius:16px;
    padding:14px
}

.extra-item {
    border:1px solid #dee2e6;
    border-radius:12px;
    padding:10px;
    margin-bottom:10px;
    background:#fff
}

.extra-cantidad {
    min-width:32px;
    text-align:center;
    font-weight:bold
}

@media(max-width:1200px) {
    .layout-grid {
        grid-template-columns:190px 1fr
    }

    .cart-panel {
        grid-column:1/-1;
        position:relative;
        max-height:none
    }

}

@media(max-width:768px) {
    .layout-grid {
        display:block
    }

    .side-panel,.products-panel,.cart-panel {
        position:relative;
        min-height:auto;
        max-height:none;
        margin-bottom:12px
    }

    .productos-grid {
        grid-template-columns:repeat(2,minmax(0,1fr))
    }

    .toolbar {
        align-items:stretch;
        flex-direction:column
    }

    .search-box {
        max-width:100%
    }

    .quick-pay-grid {
        grid-template-columns:1fr
    }

}
</style>
</head>
<body>

<div class="container-fluid pos-shell">

    <div class="top-bar d-flex justify-content-between align-items-center flex-wrap gap-2">

        <div>
            <div class="brand-title">QDelicias POS</div>

            <div class="brand-subtitle">
                Usuario: <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                |
                Rol: <?php echo htmlspecialchars($_SESSION['rol']); ?>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
            <a href="../controllers/logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
        </div>

    </div>

    <div class="layout-grid">

        <aside class="panel-card side-panel">

            <h5 class="section-title">Categorías</h5>

            <div class="section-help mb-3">
                Filtra rápido la venta
            </div>

            <button class="btn btn-dark btn-categoria" onclick="filtrarCategoria(0, this)">
                <span>Todas</span>
                <span>›</span>
            </button>

            <?php while ($cat = $categorias->fetch_assoc()) { ?>

                <button
                    class="btn btn-outline-primary btn-categoria"
                    onclick="filtrarCategoria(<?php echo (int)$cat['id']; ?>, this)"
                >
                    <span><?php echo htmlspecialchars($cat['nombre']); ?></span>
                    <span>›</span>
                </button>

            <?php } ?>

        </aside>

        <main class="panel-card products-panel">

            <div class="toolbar">

                <div>
                    <h4 class="section-title">Productos</h4>

                    <div class="section-help">
                        Toca un producto para configurarlo o agregarlo
                    </div>
                </div>

                <input
                    type="text"
                    id="buscadorProductos"
                    class="form-control search-box"
                    placeholder="Buscar producto..."
                    oninput="buscarProductos()"
                >

            </div>

            <div class="productos-grid">

                <?php
                mysqli_data_seek($productos, 0);

                while ($p = $productos->fetch_assoc()) {
                    $imagenProducto = isset($p['imagen']) ? trim($p['imagen']) : '';
                    $inicial = mb_substr($p['nombre'], 0, 1, 'UTF-8');
                ?>

                    <div
                        class="producto-item"
                        data-categoria="<?php echo (int)$p['categoria_id']; ?>"
                        data-nombre="<?php echo strtolower(htmlspecialchars($p['nombre'])); ?>"
                    >

                        <button
                            class="producto-btn"
                            onclick="abrirModalProducto(
                                <?php echo (int)$p['id']; ?>,
                                '<?php echo addslashes($p['nombre']); ?>',
                                <?php echo (float)$p['precio']; ?>,
                                '<?php echo addslashes($p['tipo_configuracion']); ?>'
                            )"
                        >

                            <div class="producto-img">
                                <?php if ($imagenProducto !== '') { ?>

                                    <img
                                        src="../<?php echo htmlspecialchars($imagenProducto); ?>"
                                        alt="<?php echo htmlspecialchars($p['nombre']); ?>"
                                    >

                                <?php } else { ?>

                                    <span><?php echo htmlspecialchars($inicial); ?></span>

                                <?php } ?>

                            </div>

                            <div class="producto-body">

                                <div class="d-flex justify-content-between gap-2 align-items-start">

                                    <div class="producto-nombre">
                                        <?php echo htmlspecialchars($p['nombre']); ?>
                                    </div>

                                    <span class="tipo-chip">
                                        <?php echo htmlspecialchars($p['tipo_configuracion']); ?>
                                    </span>

                                </div>

                                <span class="precio-text">
                                    $ <?php echo number_format($p['precio'], 0, ',', '.'); ?>
                                </span>

                            </div>

                        </button>

                    </div>

                <?php } ?>

            </div>

        </main>

        <section class="panel-card cart-panel">

            <div class="d-flex justify-content-between align-items-start mb-2">

                <div>
                    <h4 class="section-title">Carrito</h4>

                    <div class="section-help">
                        Resumen de la venta actual
                    </div>
                </div>

                <button class="btn btn-outline-secondary btn-sm" onclick="vaciarCarrito()">
                    Vaciar
                </button>

            </div>

            <div class="table-responsive">

                <table class="table table-bordered align-middle" id="tabla">

                    <thead>
                        <tr>
                            <th>Detalle</th>
                            <th width="74">Cant.</th>
                            <th width="100">Subtotal</th>
                            <th width="58">Acción</th>
                        </tr>
                    </thead>

                    <tbody></tbody>

                </table>

            </div>

            <div class="total-box mb-2">
                Total: $ <span id="total">0</span>
            </div>

            <div class="payment-card mb-2">

                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-bold mb-0">Pagos</label>
                    <span class="section-help">Rápido o mixto</span>
                </div>

                <div class="quick-pay-grid mb-2">

                    <button
                        type="button"
                        class="btn btn-outline-success btn-pago-rapido"
                        onclick="seleccionarPagoSimple('efectivo', this)"
                    >
                        💵 Efectivo
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-primary btn-pago-rapido"
                        onclick="seleccionarPagoSimple('nequi', this)"
                    >
                        📱 Nequi
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-info btn-pago-rapido"
                        onclick="seleccionarPagoSimple('daviplata', this)"
                    >
                        🟣 Daviplata
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-secondary btn-pago-rapido"
                        onclick="seleccionarPagoSimple('transferencia', this)"
                    >
                        🏦 Transf.
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-dark btn-pago-rapido"
                        onclick="activarPagoMixto(this)"
                    >
                        🔀 Mixto
                    </button>

                </div>

                <div class="payment-inputs" id="paymentInputs">

                    <div class="row g-2">

                        <div class="col-6">
                            <label class="form-label">Efectivo</label>

                            <input
                                type="number"
                                class="form-control pago-input"
                                id="pago_efectivo"
                                min="0"
                                step="0.01"
                                value="0"
                            >
                        </div>

                        <div class="col-6">
                            <label class="form-label">Nequi</label>

                            <input
                                type="number"
                                class="form-control pago-input"
                                id="pago_nequi"
                                min="0"
                                step="0.01"
                                value="0"
                            >
                        </div>

                        <div class="col-6">
                            <label class="form-label">Daviplata</label>

                            <input
                                type="number"
                                class="form-control pago-input"
                                id="pago_daviplata"
                                min="0"
                                step="0.01"
                                value="0"
                            >
                        </div>

                        <div class="col-6">
                            <label class="form-label">Transferencia</label>

                            <input
                                type="number"
                                class="form-control pago-input"
                                id="pago_transferencia"
                                min="0"
                                step="0.01"
                                value="0"
                            >
                        </div>

                    </div>

                </div>

                <div id="boxCambioEfectivo" class="cambio-efectivo-box mt-2">
                    <label class="form-label fw-bold mb-1">
                        ¿Con cuánto paga el cliente?
                    </label>

                    <input
                        type="number"
                        class="form-control pago-input"
                        id="efectivo_recibido"
                        min="0"
                        step="0.01"
                        value="0"
                        placeholder="Ej: 60000"
                    >
                    <button
                        type="button"
                        id="btnCompletarEfectivo"
                        class="btn btn-sm btn-success w-100 mt-2"
                        onclick="completarConEfectivo()"
                    >
                        Completar con efectivo
                    </button>

                    <div class="vueltas-box mt-2">
                        <span>Vueltas a entregar</span>
                        <strong id="mostrarVueltas">$ 0</strong>
                    </div>
                </div>

                <div class="payment-summary mt-2">
                    <div class="d-flex justify-content-between">
                        <span>Total venta:</span>
                        <strong id="mostrarTotalVenta">$ 0</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Total pagado:</span>
                        <strong id="mostrarTotalPagado">$ 0</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Diferencia:</span>
                        <strong id="mostrarDiferencia">$ 0</strong>
                    </div>
                </div>

            </div>

            <div class="acciones-finales">

                <button
                    class="btn btn-success flex-fill"
                    id="btnGuardarVenta"
                    onclick="guardarVenta()"
                    disabled
                >
                    Guardar venta
                </button>

                <button class="btn btn-secondary flex-fill" onclick="vaciarCarrito()">
                    Vaciar carrito
                </button>

            </div>

        </section>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let carrito = [];
let total = 0;
let productoSeleccionado = null;
function filtrarCategoria(categoriaId, boton = null) {
    const productos = document.querySelectorAll(".producto-item");

    productos.forEach(producto => {
        const categoriaProducto = parseInt(producto.getAttribute("data-categoria"));

        if (categoriaId === 0 || categoriaProducto === categoriaId) {
            producto.style.display = "block";
        } else {
            producto.style.display = "none";
        }
    });

    // Quitar activo de todos
    document.querySelectorAll(".btn-categoria").forEach(btn => {
        btn.classList.remove("btn-dark");
        btn.classList.add("btn-outline-primary");
    });

    // Activar el actual
    if (boton) {
        boton.classList.remove("btn-outline-primary");
        boton.classList.add("btn-dark");
    }
}
function buscarProductos() {
    const t = document.getElementById('buscadorProductos').value.toLowerCase();

    document.querySelectorAll('.producto-item').forEach(p => {
        const n = (p.dataset.nombre || '').toLowerCase();
        p.style.display = n.includes(t) ? 'block' : 'none';
    });
}
function abrirModalProducto(id, nombre, precio, tipo) {
    productoSeleccionado = { id, nombre, precio, tipo };

    limpiarExtrasSeleccionados();
    limpiarSaboresSeleccionados();

    document.getElementById("modalProductoNombre").innerText = nombre;
    document.getElementById("modalProductoPrecio").innerText = "$ " + formatearPeso(precio);
    actualizarSubtotalModal();

    if (tipo === 'sabores') {
        renderSaboresEnModal(id);
    } else if (tipo === 'extras') {
        renderExtrasEnModal(id);
    } else {
        document.getElementById("modalReglasProducto").innerHTML = "";
        document.getElementById("modalExtrasContenido").innerHTML =
            "<p class='text-muted'>Este producto no requiere configuración.</p>";
    }

    let modal = new bootstrap.Modal(document.getElementById('modalProducto'));
    modal.show();
}
function renderSaboresEnModal(productoId) {
    const contenedor = document.getElementById("modalExtrasContenido");

    contenedor.innerHTML = `
        <div class="alert alert-info">
            Aquí podrás repartir cantidades por sabor (ej: 20 BBQ, 10 Miel Mostaza)
        </div>
    `;
}
function renderExtrasEnModal(productoId) {
    const contenedor = document.getElementById("modalExtrasContenido");
    const reglasBox = document.getElementById("modalReglasProducto");

    let reglas = reglasProductos[productoId] || {};

    let textoReglas = Object.keys(reglas).length
        ? Object.entries(reglas).map(([tipo, cantidad]) =>
            `<span class="badge bg-success me-2 mb-2">${tipo}: ${cantidad} incluido(s)</span>`
        ).join("")
        : "<span class='text-muted'>Este producto no tiene extras incluidos configurados.</span>";

    reglasBox.innerHTML = textoReglas;

    let extrasPorTipo = {};

    extrasCatalogo.forEach(extra => {
        if (!extrasPorTipo[extra.tipo]) {
            extrasPorTipo[extra.tipo] = [];
        }
        extrasPorTipo[extra.tipo].push(extra);
    });

    let html = "";

    Object.keys(extrasPorTipo).forEach(tipo => {
        html += `
            <div class="mb-4">
                <h6 class="fw-bold border-bottom pb-2">${tipo}</h6>
                <div class="row">
        `;

        extrasPorTipo[tipo].forEach(extra => {
            let cantidad = cantidadesExtras[extra.id] || 0;

            html += `
                <div class="col-md-6 mb-3">
                    <div class="border rounded p-2 h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${extra.nombre}</strong><br>
                                <small class="text-muted">$ ${formatearPeso(extra.precio)}</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="cambiarCantidadExtra(${extra.id}, -1)">-</button>
                                <span id="modal_cantidad_extra_${extra.id}">${cantidad}</span>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="cambiarCantidadExtra(${extra.id}, 1)">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;
    });

    contenedor.innerHTML = html;
}
function renderSaboresEnModal(productoId) {
    const contenedor = document.getElementById("modalExtrasContenido");
    const reglasBox = document.getElementById("modalReglasProducto");

    reglasBox.innerHTML = `
        <div class="alert alert-info py-2 mb-3">
            Distribuye la cantidad del producto entre los sabores.
        </div>
    `;

    const sabores = saboresPorProducto[productoId] || [];

    if (sabores.length === 0) {
        contenedor.innerHTML = "<p class='text-muted'>Este producto no tiene sabores configurados.</p>";
        return;
    }

    let html = `<div class="row">`;

    sabores.forEach(sabor => {
        let cantidad = cantidadesSabores[sabor.id] || 0;

        html += `
            <div class="col-md-6 mb-3">
                <div class="border rounded p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>${sabor.nombre}</strong>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="cambiarCantidadSabor(${sabor.id}, -1)">-</button>
                            <span id="cantidad_sabor_${sabor.id}">${cantidad}</span>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="cambiarCantidadSabor(${sabor.id}, 1)">+</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    html += `</div>`;
    contenedor.innerHTML = html;
}
function cambiarCantidadExtra(extraId, cambio) {
    let actual = parseInt(cantidadesExtras[extraId]) || 0;
    actual += cambio;

    if (actual < 0) {
        actual = 0;
    }

    cantidadesExtras[extraId] = actual;

    const span = document.getElementById("modal_cantidad_extra_" + extraId);
    if (span) {
        span.textContent = actual;
    }
    actualizarSubtotalModal();
}
function actualizarSubtotalModal() {
    if (!productoSeleccionado) return;

    let precioBase = parseFloat(productoSeleccionado.precio) || 0;
    let totalExtrasCobrados = 0;

    if (productoSeleccionado.tipo === 'extras') {
        let reglas = reglasProductos[productoSeleccionado.id] || {};

        let usadosPorTipo = {};

        extrasCatalogo.forEach(extra => {
            let cantidad = parseInt(cantidadesExtras[extra.id]) || 0;

            if (cantidad > 0) {
                let tipo = extra.tipo;
                let incluidosPermitidos = reglas[tipo] || 0;

                if (!usadosPorTipo[tipo]) {
                    usadosPorTipo[tipo] = 0;
                }

                for (let i = 1; i <= cantidad; i++) {
                    usadosPorTipo[tipo]++;

                    if (usadosPorTipo[tipo] > incluidosPermitidos) {
                        totalExtrasCobrados += parseFloat(extra.precio);
                    }
                }
            }
        });
    }

    let totalProducto = precioBase + totalExtrasCobrados;

    document.getElementById("modalPrecioBase").innerText = "$ " + formatearPeso(precioBase);
    document.getElementById("modalExtrasCobrados").innerText = "$ " + formatearPeso(totalExtrasCobrados);
    document.getElementById("modalTotalProducto").innerText = "$ " + formatearPeso(totalProducto);
}
function cambiarCantidadSabor(saborId, cambio) {
    let actual = parseInt(cantidadesSabores[saborId]) || 0;
    actual += cambio;

    if (actual < 0) {
        actual = 0;
    }

    cantidadesSabores[saborId] = actual;

    const span = document.getElementById("cantidad_sabor_" + saborId);
    if (span) {
        span.textContent = actual;
    }
    actualizarSubtotalModal();
}
function totalSaboresSeleccionados() {
    let total = 0;

    Object.values(cantidadesSabores).forEach(c => {
        total += parseInt(c) || 0;
    });

    return total;
}
function obtenerSaboresSeleccionados() {
    const sabores = [];
    const saboresProducto = saboresPorProducto[productoSeleccionado.id] || [];

    saboresProducto.forEach(sabor => {
        const cantidad = parseInt(cantidadesSabores[sabor.id]) || 0;

        if (cantidad > 0) {
            sabores.push({
                id: sabor.id,
                nombre: sabor.nombre,
                cantidad: cantidad
            });
        }
    });

    return sabores;
}
// Ajusta esto con los IDs reales de tus productos
const reglasProductos = <?php echo json_encode($reglasProductos, JSON_UNESCAPED_UNICODE); ?>;
const extrasCatalogo = <?php echo json_encode($extrasData, JSON_UNESCAPED_UNICODE); ?>;
const saboresPorProducto = <?php echo json_encode($saboresPorProducto, JSON_UNESCAPED_UNICODE); ?>;
let cantidadesSabores = {};
let cantidadesExtras = {};

extrasCatalogo.forEach(extra => {
    cantidadesExtras[extra.id] = 0;
});
function obtenerMetodoPago() {
    let seleccionado = document.querySelector('input[name="metodo_pago"]:checked');
    return seleccionado ? seleccionado.value : 'efectivo';
}


function obtenerExtrasSeleccionados() {
    let extrasSeleccionados = [];

    extrasCatalogo.forEach(extra => {
        let cantidad = cantidadesExtras[extra.id] || 0;

        if (cantidad > 0) {
            extrasSeleccionados.push({
                id: parseInt(extra.id),
                nombre: extra.nombre,
                precio: parseFloat(extra.precio),
                tipo: extra.tipo,
                cantidad: cantidad,
                cantidad_incluida: 0,
                cantidad_cobrada: 0
            });
        }
    });

    return extrasSeleccionados;
}
function obtenerPagos() {
    return {
        efectivo: parseFloat(document.getElementById('pago_efectivo').value) || 0,
        nequi: parseFloat(document.getElementById('pago_nequi').value) || 0,
        daviplata: parseFloat(document.getElementById('pago_daviplata').value) || 0,
        transferencia: parseFloat(document.getElementById('pago_transferencia').value) || 0
    };
}

function calcularTotalPagado() {
    const pagos = obtenerPagos();
    return pagos.efectivo + pagos.nequi + pagos.daviplata + pagos.transferencia;
}
function marcarPagoActivo(boton) {
    document.querySelectorAll('.btn-pago-rapido').forEach(btn => {
        btn.classList.remove('active-pay');
    });

    if (boton) {
        boton.classList.add('active-pay');
    }
}

function ocultarInputsPago() {
    const box = document.getElementById('paymentInputs');

    if (box) {
        box.classList.remove('show');
    }
}

function mostrarInputsPago() {
    const box = document.getElementById('paymentInputs');

    if (box) {
        box.classList.add('show');
    }
}
function mostrarCambioEfectivo(mostrar) {
    const contenedor = document.getElementById("boxCambioEfectivo");

    if (!contenedor) return;

    if (mostrar) {
        contenedor.classList.remove("d-none");
    } else {
        contenedor.classList.add("d-none");
    }
}
function actualizarBotonEfectivo() {
    const pagos = obtenerPagos();

    const pagadoSinEfectivo =
        pagos.nequi +
        pagos.daviplata +
        pagos.transferencia;

    const faltante = total - pagadoSinEfectivo;

    const btn = document.getElementById("btnCompletarEfectivo");

    if (!btn) return;

    if (faltante > 0) {
        btn.textContent = "Faltan $" + faltante.toLocaleString();
    } else {
        btn.textContent = "Pago completo";
    }
}

function seleccionarPagoSimple(metodo, boton) {
    marcarPagoActivo(boton);
    ocultarInputsPago();

    document.getElementById("pago_efectivo").value = 0;
    document.getElementById("pago_nequi").value = 0;
    document.getElementById("pago_daviplata").value = 0;
    document.getElementById("pago_transferencia").value = 0;

    document.getElementById("pago_" + metodo).value = total;

    if (metodo === "efectivo") {
        document.getElementById("efectivo_recibido").value = total;
    } else {
        document.getElementById("efectivo_recibido").value = 0;
    }

    actualizarResumenPagos();
}

function activarPagoMixto(boton) {
    marcarPagoActivo(boton);
    mostrarInputsPago();

    document.getElementById("pago_efectivo").value = 0;
    document.getElementById("pago_nequi").value = 0;
    document.getElementById("pago_daviplata").value = 0;
    document.getElementById("pago_transferencia").value = 0;
    document.getElementById("efectivo_recibido").value = 0;

    actualizarResumenPagos();
}
function completarConEfectivo() {
    const pagos = obtenerPagos();

    const pagadoSinEfectivo =
        pagos.nequi +
        pagos.daviplata +
        pagos.transferencia;

    const faltante = total - pagadoSinEfectivo;

    // Si ya está pago o se pasaron, no hacer nada
    if (faltante <= 0) return;

    // Completa el efectivo necesario
    document.getElementById("pago_efectivo").value = faltante;

    // Si paga exacto → también llenar recibido
    document.getElementById("efectivo_recibido").value = faltante;

    actualizarResumenPagos();
}
function pagoRapido(metodo) {
    const metodos = ["efectivo", "nequi", "daviplata", "transferencia"];

    metodos.forEach(m => {
        const input = document.getElementById("pago_" + m);
        input.value = 0;
        input.dispatchEvent(new Event("input"));
    });

    const inputSeleccionado = document.getElementById("pago_" + metodo);
    inputSeleccionado.value = total;
    inputSeleccionado.dispatchEvent(new Event("input"));

    actualizarResumenPagos();
    validarPagoParaGuardar();
}

function obtenerEfectivoRecibido() {
    const input = document.getElementById("efectivo_recibido");

    if (!input) return 0;

    return parseFloat(input.value) || 0;
}

function calcularVueltas() {
    const pagos = obtenerPagos();
    const efectivoRecibido = obtenerEfectivoRecibido();

    if (pagos.efectivo <= 0) return 0;

    if (efectivoRecibido > pagos.efectivo) {
        return efectivoRecibido - pagos.efectivo;
    }

    return 0;
}
function controlarCambioPorEfectivo() {
    const pagos = obtenerPagos();

    if (pagos.efectivo > 0) {
        mostrarCambioEfectivo(true);
    } else {
        mostrarCambioEfectivo(false);
    }

    actualizarResumenPagos();
}

function actualizarResumenPagos() {
    const totalPagado = calcularTotalPagado();
    const diferencia = total - totalPagado;
    const vueltas = calcularVueltas();

    document.getElementById('mostrarTotalVenta').textContent = `$ ${total.toLocaleString('es-CO')}`;
    document.getElementById('mostrarTotalPagado').textContent = `$ ${totalPagado.toLocaleString('es-CO')}`;
    document.getElementById('mostrarDiferencia').textContent = `$ ${diferencia.toLocaleString('es-CO')}`;

    const mostrarVueltas = document.getElementById("mostrarVueltas");

    if (mostrarVueltas) {
        mostrarVueltas.textContent = `$ ${vueltas.toLocaleString('es-CO')}`;
    }

    validarPagoParaGuardar();
    actualizarBotonEfectivo();
}
function validarPagoParaGuardar() {
    const btn = document.getElementById("btnGuardarVenta");
    if (!btn) return;

    const pagos = obtenerPagos();
    const totalPagado = calcularTotalPagado();
    const efectivoRecibido = obtenerEfectivoRecibido();

    const pagoCuadra = Math.abs(totalPagado - total) < 0.01;
    const efectivoCorrecto = pagos.efectivo <= 0 || efectivoRecibido >= pagos.efectivo;

    btn.disabled = !(
        carrito.length > 0 &&
        total > 0 &&
        pagoCuadra &&
        efectivoCorrecto
    );
}
function limpiarExtrasSeleccionados() {
    extrasCatalogo.forEach(extra => {
        cantidadesExtras[extra.id] = 0;

        const span = document.getElementById("modal_cantidad_extra_" + extra.id);
        if (span) {
            span.textContent = 0;
        }
    });
}
function limpiarSaboresSeleccionados() {
    cantidadesSabores = {};
}
function seleccionarProducto(id, nombre, precio) {
    productoSeleccionado = {
        id: id,
        nombre: nombre,
        precio: precio
    };

    document.getElementById("productoSeleccionadoTexto").innerText = nombre + " - $ " + formatearPeso(precio);
    document.getElementById("productoSeleccionadoBox").classList.remove("d-none");
}

function confirmarProductoSeleccionado() {
    if (!productoSeleccionado) {
        alert("Primero selecciona un producto");
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
            alert("Debes seleccionar al menos 1 sabor");
            return;
        }

        if (totalSabores > 1) {
            alert("Este producto solo permite seleccionar 1 sabor");
            return;
        }
    }
}

    agregarProductoConExtras(
        productoSeleccionado.id,
        productoSeleccionado.nombre,
        productoSeleccionado.precio
    );

    const modalElement = document.getElementById('modalProducto');
    const modalInstance = bootstrap.Modal.getInstance(modalElement);
    if (modalInstance) {
        modalInstance.hide();
    }
    limpiarSaboresSeleccionados();

    productoSeleccionado = null;
}
function obtenerCantidadProducto(nombre) {
    nombre = nombre.toLowerCase();

    if (nombre.includes("personal")) return 4;
    if (nombre.includes("pareja")) return 10;
    if (nombre.includes("familiar")) return 30;

    if (nombre.includes("30")) return 30;
    if (nombre.includes("20")) return 20;
    if (nombre.includes("10")) return 10;
    if (nombre.includes("4")) return 4;

    return 0;
}
function generarClaveLinea(productoId, extras) {
    let extrasClave = extras
        .map(e => `${e.id}:${e.cantidad}`)
        .sort()
        .join("|");

    return productoId + "|" + extrasClave;
}

function agregarProductoConExtras(id, nombre, precio) {
    let extrasSeleccionados = obtenerExtrasSeleccionados();
    let saboresSeleccionados = [];

    if (productoSeleccionado && productoSeleccionado.tipo === 'sabores') {
        saboresSeleccionados = obtenerSaboresSeleccionados();
    }
    let reglas = reglasProductos[id] || {};
    let totalExtrasCobrados = 0;

    // Lleva control acumulado por tipo
    let usadosPorTipo = {};

    extrasSeleccionados.forEach(extra => {
        let tipo = extra.tipo;
        let limiteGratis = parseInt(reglas[tipo] || 0);

        if (!usadosPorTipo[tipo]) {
            usadosPorTipo[tipo] = 0;
        }

        let disponiblesGratis = limiteGratis - usadosPorTipo[tipo];
        if (disponiblesGratis < 0) {
            disponiblesGratis = 0;
        }

        let cantidadIncluida = Math.min(extra.cantidad, disponiblesGratis);
        let cantidadCobrada = extra.cantidad - cantidadIncluida;

        extra.cantidad_incluida = cantidadIncluida;
        extra.cantidad_cobrada = cantidadCobrada;

        usadosPorTipo[tipo] += cantidadIncluida;
        totalExtrasCobrados += cantidadCobrada * extra.precio;
    });

    let precioUnitarioLinea = precio + totalExtrasCobrados;
    let clave = generarClaveLinea(id, extrasSeleccionados);

    let productoExistente = carrito.find(item => item.clave === clave);

    if (productoExistente) {
        productoExistente.cantidad++;
        productoExistente.subtotal += precioUnitarioLinea;
    } else {
        carrito.push({
            clave: clave,
            id: id,
            nombre: nombre,
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
}

function disminuirCantidad(index) {
    let valorUnitario = carrito[index].subtotal / carrito[index].cantidad;

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
    let valorUnitario = carrito[index].subtotal / carrito[index].cantidad;

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

    document.getElementById("pago_efectivo").value = 0;
    document.getElementById("pago_nequi").value = 0;
    document.getElementById("pago_daviplata").value = 0;
    document.getElementById("pago_transferencia").value = 0;

    const efectivoRecibidoInput = document.getElementById("efectivo_recibido");

    if (efectivoRecibidoInput) {
        efectivoRecibidoInput.value = 0;
    }


    renderCarrito();
    actualizarResumenPagos();
    limpiarExtrasSeleccionados();
}

function formatearPeso(valor) {
    return new Intl.NumberFormat('es-CO').format(Math.round(valor));
}

function renderCarrito() {
    let tabla = document.querySelector("#tabla tbody");
    tabla.innerHTML = "";

    if (carrito.length === 0) {
        tabla.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted">No hay productos en el carrito</td>
            </tr>
        `;
    } else {
        carrito.forEach((item, index) => {
            let extrasTexto = item.extras.length > 0
                ? item.extras.map(extra => {
                    let partes = [];

                    if (extra.cantidad_incluida > 0) {
                        partes.push(`${extra.cantidad_incluida} incluido`);
                    }

                    if (extra.cantidad_cobrada > 0) {
                        partes.push(`${extra.cantidad_cobrada} cobrado (+$${formatearPeso(extra.cantidad_cobrada * extra.precio)})`);
                    }

                    return `${extra.nombre} x${extra.cantidad} [${partes.join(", ")}]`;
                }).join(", ")
                : "";

            let saboresTexto = "";

            if (item.sabores && item.sabores.length > 0) {
                saboresTexto = item.sabores.map(sabor =>
                    `${sabor.nombre} x${sabor.cantidad}`
                ).join(", ");
            }

            tabla.innerHTML += `
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
                </tr>
            `;
        });
    }

    document.getElementById("total").innerText = formatearPeso(total);
    actualizarResumenPagos();
}
function abrirConfiguracionProducto(id, nombre, precio) {
    productoSeleccionado = { id, nombre, precio };
    limpiarExtras();
    cargarReglasProducto(id);
    mostrarModalProducto();
}
function guardarVenta() {
    const btn = document.getElementById("btnGuardarVenta");

    if (btn.disabled) return;

    // bloquear botón
    btn.disabled = true;
    btn.innerText = "Guardando...";
    if (carrito.length === 0) {
        alert("El carrito está vacío");
        btn.disabled = false;
        btn.innerText = "Guardar venta";
        return;
    }

    const pagos = obtenerPagos();
    const totalPagado = calcularTotalPagado();

    if (totalPagado <= 0) {
        alert("Debes ingresar al menos un valor de pago");
        btn.disabled = false;
        btn.innerText = "Guardar venta";
        return;
    }

    if (Math.abs(totalPagado - total) > 0.01) {
        alert("La suma de los pagos debe ser igual al total de la venta");
        btn.disabled = false;
        btn.innerText = "Guardar venta";
        return;
    }
    const efectivoRecibido = obtenerEfectivoRecibido();

    if (pagos.efectivo > 0 && efectivoRecibido < pagos.efectivo) {
        alert("El dinero recibido en efectivo no alcanza para cubrir el pago en efectivo");
        btn.disabled = false;
        btn.innerText = "Guardar venta";
        return;
    }

    fetch("../controllers/ventascontroller.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            carrito,
            total,
            pagos
        })
    })
    .then(response => response.text())
    .then(data => {
        alert(data);

        if (data.toLowerCase().includes("correctamente")) {
            carrito = [];
            total = 0;

            document.getElementById("pago_efectivo").value = 0;
            document.getElementById("pago_nequi").value = 0;
            document.getElementById("pago_daviplata").value = 0;
            document.getElementById("pago_transferencia").value = 0;
            const efectivoRecibidoInput = document.getElementById("efectivo_recibido");

            if (efectivoRecibidoInput) {
                efectivoRecibidoInput.value = 0;
            }


            limpiarExtrasSeleccionados();
            limpiarSaboresSeleccionados();

            renderCarrito();
            actualizarResumenPagos();

            const btn = document.getElementById("btnGuardarVenta");
            btn.innerText = "Guardar venta";
            btn.disabled = true;
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error al guardar la venta");

        btn.disabled = false;
        btn.innerText = "Guardar venta";
    });
}

renderCarrito();
document.querySelectorAll('.pago-input').forEach(input => {
    input.addEventListener('input', function () {
        if (input.id === "pago_efectivo") {
            const pagos = obtenerPagos();

            if (pagos.efectivo > 0) {
                mostrarCambioEfectivo(true);
            } else {
                mostrarCambioEfectivo(false);
            }
        }

        actualizarResumenPagos();
    });
});
const inputEfectivoRecibido = document.getElementById("efectivo_recibido");

if (inputEfectivoRecibido) {
    inputEfectivoRecibido.addEventListener("input", actualizarResumenPagos);
}
</script>

<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title fw-bold">
                    Configurar producto
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>

            <div class="modal-body">

                <div class="modal-product-header mb-3">

                    <h4 id="modalProductoNombre" class="mb-1 fw-bold"></h4>

                    <p class="text-muted mb-0">
                        Precio base:
                        <strong id="modalProductoPrecio"></strong>
                    </p>

                </div>

                <div id="modalReglasProducto" class="mb-3"></div>

                <div class="payment-summary mb-3">

                    <div class="d-flex justify-content-between">
                        <span>Precio base:</span>
                        <strong id="modalPrecioBase">$ 0</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Extras cobrados:</span>
                        <strong id="modalExtrasCobrados">$ 0</strong>
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between fs-5">
                        <span>Total producto:</span>
                        <strong id="modalTotalProducto">$ 0</strong>
                    </div>

                </div>

                <div id="modalExtrasContenido"></div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    class="btn btn-success"
                    onclick="confirmarProductoSeleccionado()"
                >
                    Agregar al carrito
                </button>

            </div>

        </div>

    </div>

</div>

</body>
</html>
