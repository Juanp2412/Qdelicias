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
$categorias = $conn->query("
    SELECT * 
    FROM categorias 
    WHERE estado = 1
    ORDER BY nombre ASC
");
$categoriasData = [];
$categoriasNombrePorId = [];
$categoriaChipTemas = [];
$temasChip = [
    ['bg' => '#eef9ee', 'color' => '#4f8b51', 'border' => '#d7edd8'],
    ['bg' => '#eaf4ff', 'color' => '#3b6ea8', 'border' => '#d2e6ff'],
    ['bg' => '#fff2ea', 'color' => '#c05c2d', 'border' => '#ffd8c2'],
    ['bg' => '#f4edff', 'color' => '#6e54a9', 'border' => '#e1d4ff'],
    ['bg' => '#fff8e8', 'color' => '#9d7b2c', 'border' => '#ffe6b5']
];
$saboresPorProducto = [];

while ($cat = $categorias->fetch_assoc()) {
    $catId = (int)$cat['id'];
    $temIndex = $catId % count($temasChip);

    $categoriasData[] = $cat;
    $categoriasNombrePorId[$catId] = $cat['nombre'];
    $categoriaChipTemas[$catId] = $temasChip[$temIndex];
}

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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/ventas.css">
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

            <?php if ($_SESSION['rol'] == 'admin') { ?>

                <a href="dashboard.php" class="btn btn-light btn-sm">Dashboard</a>

            <?php } else { ?>

                <a href="reporte_ventas.php" class="btn btn-info btn-sm">Ver Reportes</a>

            <?php } ?>

            <a href="../controllers/logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>

        </div>

    </div>

    <div class="layout-grid">

        <aside class="panel-card side-panel">

            <h5 class="section-title">Categorías</h5>

            <div class="mb-2"></div>

            <button class="btn btn-dark btn-categoria" onclick="filtrarCategoria(0, this)">
                <span>Todas</span>
                <span>›</span>
            </button>

            <?php foreach ($categoriasData as $cat) { ?>

                <button
                    type="button"
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
                    placeholder="Buscar en productos..."
                    oninput="buscarProductos()"
                >

            </div>

            <div class="productos-grid">

                <?php
                mysqli_data_seek($productos, 0);

                while ($p = $productos->fetch_assoc()) {
                    $imagenProducto = isset($p['imagen']) ? trim($p['imagen']) : '';
                    $inicial = substr($p['nombre'], 0, 1);
                    $categoriaId = (int)$p['categoria_id'];
                    $categoriaNombre = $categoriasNombrePorId[$categoriaId] ?? 'Sin categoria';
                    $categoriaTema = $categoriaChipTemas[$categoriaId] ?? ['bg' => '#f3f4f6', 'color' => '#4b5563', 'border' => '#d1d5db'];
                ?>

                    <div
                        class="producto-item"
                        data-categoria="<?php echo (int)$p['categoria_id']; ?>"
                        data-nombre="<?php echo strtolower(htmlspecialchars($p['nombre'])); ?>"
                    >

                        <button
                            type="button"
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

                                <div class="producto-nombre">
                                    <?php echo htmlspecialchars($p['nombre']); ?>
                                </div>

                                <div class="producto-meta">

                                    <span
                                        class="tipo-chip"
                                        style="background:<?php echo htmlspecialchars($categoriaTema['bg']); ?>;color:<?php echo htmlspecialchars($categoriaTema['color']); ?>;border:1px solid <?php echo htmlspecialchars($categoriaTema['border']); ?>"
                                    >
                                        <?php echo htmlspecialchars($categoriaNombre); ?>
                                    </span>

                                    <span class="producto-precio" aria-label="Precio $ <?php echo number_format($p['precio'], 0, ',', '.'); ?>">
                                        <span class="precio-text">$ <?php echo number_format($p['precio'], 0, ',', '.'); ?></span>
                                    </span>

                                </div>

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

                <div class="d-flex gap-2">
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        onclick="abrirConfirmacionVenta()"
                    >
                        Abrir carrito grande
                    </button>
                </div>

            </div>

            <div class="table-responsive">

                <table class="table table-bordered align-middle" id="tabla">

                    <caption class="visually-hidden">Resumen de productos del carrito actual</caption>

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

            <div class="cart-footer-sticky">

            <div class="total-box mb-2">
                Total: $ <span id="total">0</span>
            </div>

            <div id="paymentDock">

            <div class="payment-card mb-2">

                <div class="payment-card-head d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-bold mb-0">Pagos</label>
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-help">Rápido o mixto</span>
                        <button
                            type="button"
                            id="btnTogglePagoPanel"
                            class="btn btn-outline-secondary btn-sm payment-toggle-btn"
                            onclick="togglePagoPanel()"
                        >
                            Ver pagos
                        </button>
                    </div>
                </div>

                <div id="paymentMiniSummary" class="payment-mini-summary mb-2">
                    <div class="payment-mini-item">
                        <span>Pagado</span>
                        <strong id="mostrarTotalPagadoMini">$ 0</strong>
                    </div>
                    <div class="payment-mini-item">
                        <span>Diferencia</span>
                        <strong id="mostrarDiferenciaMini">$ 0</strong>
                    </div>
                </div>

                <div id="paymentPanelBody" class="payment-panel-body d-none">

                <div class="quick-pay-grid-top mb-2">
                    <button
                        type="button"
                        class="btn btn-outline-success btn-pago-rapido btn-pago-metodo"
                        aria-pressed="false"
                        onclick="seleccionarPagoSimple('efectivo', this)"
                    >
                        <img src="../assets/img/pagos/efectivo.png" alt="Efectivo" class="pago-imagen">
                        <span class="pago-nombre">Efectivo</span>
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-primary btn-pago-rapido btn-pago-metodo"
                        aria-pressed="false"
                        onclick="seleccionarPagoSimple('nequi', this)"
                    >
                        <img src="../assets/img/pagos/nequi.png" alt="Nequi" class="pago-imagen">
                        <span class="pago-nombre">Nequi</span>
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-info btn-pago-rapido btn-pago-metodo"
                        aria-pressed="false"
                        onclick="seleccionarPagoSimple('daviplata', this)"
                    >
                        <img src="../assets/img/pagos/Daviplata.png" alt="Daviplata" class="pago-imagen">
                        <span class="pago-nombre">Daviplata</span>
                    </button>
                </div>

                <div class="quick-pay-grid-bottom mb-2">
                    <button
                        type="button"
                        class="btn btn-outline-secondary btn-pago-rapido btn-pago-metodo"
                        aria-pressed="false"
                        onclick="seleccionarPagoSimple('transferencia', this)"
                    >
                        <span class="pago-icon">🏦</span>
                        <span class="pago-nombre">Transferencia</span>
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-dark btn-pago-rapido btn-pago-metodo"
                        aria-pressed="false"
                        onclick="activarPagoMixto(this)"
                    >
                        <span class="pago-icon">➕</span>
                        <span class="pago-nombre">Mixto</span>
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

                <div id="boxCambioEfectivo" class="cambio-efectivo-box mt-2 d-none">
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

            </div>

            </div>

            <div class="acciones-finales">

                <button
                    class="btn btn-success w-100"
                    id="btnGuardarVenta"
                    onclick="abrirConfirmacionVenta()"
                >
                    ✔ Verificar pedido
                </button>

            </div>

            </div>

            </div>

        </section>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* Datos inyectados desde PHP — deben cargarse antes de ventas.js */
const reglasProductos   = <?php echo json_encode($reglasProductos,  JSON_UNESCAPED_UNICODE); ?>;
const extrasCatalogo    = <?php echo json_encode($extrasData,        JSON_UNESCAPED_UNICODE); ?>;
const saboresPorProducto = <?php echo json_encode($saboresPorProducto, JSON_UNESCAPED_UNICODE); ?>;
</script>
<script src="../assets/js/ventas.js"></script>

<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-producto-dialog">

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

                    <div class="modal-product-header-left">
                        <h4 id="modalProductoNombre" class="mb-1 fw-bold"></h4>
                        <p class="text-muted mb-0">
                            Precio base:
                            <strong id="modalProductoPrecio"></strong>
                        </p>
                    </div>

                    <div class="modal-product-header-right">
                        <div class="d-flex justify-content-between gap-3">
                            <span class="text-muted">Precio base:</span>
                            <strong id="modalPrecioBase">$ 0</strong>
                        </div>
                        <div class="d-flex justify-content-between gap-3">
                            <span class="text-muted">Extras cobrados:</span>
                            <strong id="modalExtrasCobrados">$ 0</strong>
                        </div>
                        <hr class="my-1">
                        <div class="d-flex justify-content-between gap-3 fs-6">
                            <span class="fw-bold">Total:</span>
                            <strong id="modalTotalProducto" class="text-success">$ 0</strong>
                        </div>
                    </div>

                </div>

                <div id="modalReglasProducto" class="mb-3"></div>

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
                    id="btnConfirmarModalProducto"
                    onclick="confirmarProductoSeleccionado()"
                >
                    Agregar al carrito
                </button>

            </div>

        </div>

    </div>

</div>

<div class="modal fade modal-checkout-left" id="modalResumenPedido" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-scrollable modal-resumen-dialog">

        <div class="modal-content resumen-modal">

            <div class="modal-header resumen-header">

                <h5 class="modal-title fw-bold fs-5 mb-0">🧾 Confirmar pedido</h5>

                <div class="resumen-header-actions">
                    <div id="resumenLecturaHeader"></div>
                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Close"
                        onclick="cerrarCheckout()"
                    ></button>
                </div>

            </div>

            <div class="modal-body">
                <div id="resumenPedidoContenido"></div>
            </div>

            <div class="modal-footer justify-content-between">
                <div class="resumen-total-modal">
                    Total: <strong id="resumenPedidoTotal">$ 0</strong>
                </div>

                <div class="d-flex gap-2">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        onclick="vaciarCarrito()"
                    >
                        Vaciar carrito
                    </button>

                    <button
                        type="button"
                        class="btn btn-primary"
                        data-bs-dismiss="modal"
                    >
                        Continuar venta
                    </button>
                </div>
            </div>

        </div>

    </div>

</div>

<div class="modal fade modal-checkout-right" id="modalPagoPedido" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-pago-side-dialog">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Confirmar cobro <span id="pagoMinimizadoTotal" class="pago-minimizado-total"></span></h5>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary btn-min-pago"
                    id="btnMinimizarPagoCheckout"
                    aria-pressed="false"
                    title="Minimizar cobro"
                    onclick="toggleMinimizarPagoCheckout()"
                >
                    -
                </button>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    onclick="cerrarCheckout()"
                ></button>
            </div>

            <div class="modal-body p-0">
                <div id="paymentDockTarget"></div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-success w-100"
                    id="btnConfirmarGuardarPago"
                    onclick="guardarVenta('checkout')"
                    disabled
                >
                    ✔ Confirmar y guardar
                </button>
            </div>

        </div>

    </div>

</div>

<script src="../assets/js/app.js"></script>
</body>
</html>
