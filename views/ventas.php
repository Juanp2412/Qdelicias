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

$productos = $conn->query("SELECT * FROM productos ORDER BY nombre ASC");
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
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
<style>
:root {
    --bg:#f3f4f6;
    --panel:#ffffff;
    --panel-soft:#f8fafc;
    --line:#e5e7eb;
    --text:#111827;
    --muted:#6b7280;
    --primary:#000;
    --primary-strong:#ea580c;
    --primary-soft:#fff7ed;
    --success:#16a34a
}

body {
    background:
        radial-gradient(circle at 0% 0%,rgba(249,115,22,.08),transparent 30%),
        radial-gradient(circle at 100% 100%,rgba(59,130,246,.07),transparent 26%),
        var(--bg);
    font-family:"Plus Jakarta Sans", "Segoe UI", sans-serif;
    color:var(--text);
    font-size:14px
}

.pos-shell {
    padding:16px
}

.top-bar {
    background:var(--panel);
    color:var(--text);
    padding:14px 16px;
    border-radius:14px;
    margin-bottom:14px;
    border:1px solid var(--line);
    box-shadow:0 8px 24px rgba(15,23,42,.05)
}

.brand-title {
    font-size:22px;
    font-weight:800;
    letter-spacing:-.2px
}

.brand-subtitle {
    color:var(--muted);
    font-size:12px
}

.layout-grid {
    display:grid;
    grid-template-columns:170px minmax(0,1fr) clamp(280px,30vw,390px);
    gap:14px;
    align-items:start
}

.panel-card {
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:14px;
    box-shadow:0 10px 26px rgba(15,23,42,.05)
}

.side-panel {
    padding:12px;
    position:sticky;
    top:16px;
    background:var(--panel-soft);
    min-height:calc(100vh - 110px)
}

.products-panel {
    padding:14px;
    min-height:calc(100vh - 110px);
    min-width:0
}

.cart-panel {
    padding:12px;
    position:sticky;
    top:16px;
    max-height:calc(100vh - 28px);
    overflow:auto;
    min-width:0
}

.section-title {
    font-size:17px;
    font-weight:800;
    margin:0;
    color:var(--text)
}

.section-help {
    color:var(--muted);
    font-size:12px
}

.btn-categoria {
    width:100%;
    border-radius:10px;
    padding:9px 10px;
    margin-bottom:6px;
    font-weight:700;
    font-size:12px;
    text-align:left;
    display:flex;
    align-items:center;
    justify-content:space-between;
    border-color:#d1d5db;
    color:#374151;
    background:#fff
}

.btn-categoria.btn-dark {
    background:var(--primary);
    border-color:var(--primary);
    color:#fff
}

.toolbar {
    display:flex;
    gap:10px;
    align-items:center;
    justify-content:space-between;
    margin-bottom:14px
}

.search-box {
    max-width:340px;
    border-radius:12px;
    padding:10px 13px;
    border:1px solid #d1d5db
}

.productos-grid {
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:12px
}

.producto-btn {
    width:100%;
    min-height:208px;
    border-radius:12px;
    border:1px solid #e5e7eb;
    background:#fff;
    padding:10px;
    text-align:left;
    overflow:hidden;
    box-shadow:0 6px 16px rgba(17,24,39,.05);
    transition:.16s ease;
    display:flex;
    flex-direction:column;
    gap:10px
}

.producto-btn:hover {
    transform:translateY(-2px);
    box-shadow:0 12px 20px rgba(17,24,39,.10);
    border-color:#fdba74
}

.producto-img {
    height:114px;
    background:#f3f4f6;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#9ca3af;
    font-size:32px;
    font-weight:800;
    border-radius:12px;
    border:1px solid #edf0f4;
    overflow:hidden;
    padding:0
}

.producto-img img {
    width:100%;
    height:100%;
    object-fit:cover;
    border-radius:0;
    border:0;
    box-shadow:none;
    padding:0;
    display:block
}

.producto-body {
    padding:0 2px 2px
}

.producto-nombre {
    font-size:14px;
    font-weight:800;
    color:var(--text);
    line-height:1.3;
    min-height:34px;
    display:-webkit-box;
    -webkit-box-orient:vertical;
    -webkit-line-clamp:2;
    overflow:hidden
}

.precio-text {
    display:inline-flex;
    align-items:center;
    color:#1f2937;
    font-size:16px;
    font-weight:800;
    line-height:1.1;
    letter-spacing:0;
    padding:3px 8px;
    border-radius:8px;
    background:#f8fafc;
    border:1px solid #e5e7eb
}

.tipo-chip {
    font-size:11px;
    padding:4px 9px;
    border-radius:8px;
    font-weight:700
}

.producto-meta {
    margin-top:6px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px
}

.producto-precio {
    display:flex;
    align-items:center;
    font-weight:800;
    color:#111827
}

.table {
    font-size:12px;
    margin-bottom:10px;
    border-color:#eceff3
}

.table thead th {
    background:#f8fafc!important;
    color:#374151;
    border-color:#eceff3!important;
    font-weight:700
}

.table tbody td {
    border-color:#eceff3
}

.detalle-extra {
    font-size:11px;
    color:var(--muted);
    display:block;
    margin-top:4px;
    line-height:1.3
}

.total-box {
    background:linear-gradient(145deg,#fff,#fff7ed);
    color:var(--text);
    border:1px solid #fed7aa;
    border-radius:14px;
    padding:14px;
    text-align:center;
    font-size:30px;
    font-weight:800;
    box-shadow:0 10px 24px rgba(249,115,22,.12)
}

.payment-card,.payment-summary {
    border:1px solid var(--line);
    border-radius:14px;
    padding:14px;
    background:#fff
}

.payment-summary {
    background:#f8fafc;
    font-size:15px
}

.quick-pay-grid-top,.quick-pay-grid-bottom {
    display:grid;
    gap:15px
}

.quick-pay-grid-top {
    grid-template-columns:repeat(3,1fr)
}

.quick-pay-grid-bottom {
    grid-template-columns:repeat(2,1fr)
}

.btn-pago-metodo {
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:6px;
    border-radius:10px;
    padding:8px 6px;
    font-weight:700;
    font-size:12px;
    min-height:78px;
    border:none!important;
    background:linear-gradient(180deg,rgba(255,255,255,.96),rgba(248,250,252,.94))!important;
    box-shadow:inset 0 0 0 1px rgba(148,163,184,.24),0 3px 10px rgba(15,23,42,.06);
    transition:background-color .2s ease,border-color .2s ease,color .2s ease,box-shadow .2s ease,transform .2s ease
}

.btn-pago-metodo:hover {
    background:#0f766e!important;
    color:#fff!important;
    box-shadow:0 8px 18px rgba(15,118,110,.24);
    transform:translateY(-1px)
}

.btn-pago-metodo .pago-icon {
    font-size:24px;
    line-height:1
}

.btn-pago-metodo .pago-imagen {
    height:32px;
    width:auto;
    max-width:100%;
    object-fit:contain;
    line-height:1
}

.btn-pago-metodo .pago-nombre {
    font-size:11px
}

.cart-panel .table-responsive {
    margin-bottom:12px
}

.cart-panel .total-box {
    margin-bottom:12px!important
}

.cart-panel .payment-card {
    margin-bottom:12px!important
}

.payment-card .payment-summary {
    margin-top:10px
}

.acciones-finales .btn {
    border-radius:10px;
    padding:7px 6px;
    font-weight:700;
    font-size:12px;
    min-height:36px
}

.btn-pago-metodo.active-pay {
    background:var(--primary)!important;
    color:#fff!important;
    box-shadow:0 8px 18px rgba(15,23,42,.22)
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
    border-radius:10px;
    border-color:#d1d5db
}

.btn-primary,
.btn-success,
.btn-warning,
.btn-outline-primary:hover {
    border-color:var(--primary-strong)
}

.btn-success {
    background:var(--success);
    border-color:var(--success)
}

.btn-success:hover {
    background:#15803d;
    border-color:#15803d
}

.btn:focus-visible,
.form-control:focus-visible,
.producto-btn:focus-visible,
.btn-categoria:focus-visible {
    outline:none;
    box-shadow:0 0 0 3px rgba(249,115,22,.28)!important;
    border-color:#fb923c!important
}

.acciones-finales {
    display:flex;
    gap:10px;
    flex-wrap:wrap
}

.modal-content {
    border-radius:14px;
    border:1px solid var(--line);
    overflow:hidden
}

#modalProducto .modal-header {
    position:sticky;
    top:0;
    z-index:3;
    background:#fff;
    border-bottom:1px solid var(--line)
}

#modalProducto .modal-body {
    padding:14px
}

#modalProducto .modal-footer {
    position:sticky;
    bottom:0;
    z-index:3;
    background:#fff;
    border-top:1px solid var(--line)
}

#modalExtrasContenido .row {
    --bs-gutter-x:.75rem;
    --bs-gutter-y:.75rem
}

.modal-product-header {
    background:linear-gradient(135deg,#fff,#fff7ed);
    border:1px solid var(--line);
    border-radius:12px;
    padding:14px
}

.modal-extra-title {
    font-size:20px;
    font-weight:800;
    color:#1f2937;
    margin-bottom:10px;
    border-bottom:1px solid #e5e7eb;
    padding-bottom:8px
}

.extra-item {
    border:1px solid #e2e8f0;
    border-radius:12px;
    padding:10px 12px;
    margin-bottom:0;
    background:linear-gradient(180deg,#fff,#f8fafc);
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px
}

.extra-cantidad {
    min-width:32px;
    text-align:center;
    font-weight:bold
}

.qty-controls {
    display:flex;
    align-items:center;
    gap:6px
}

.qty-btn {
    width:28px;
    height:28px;
    border-radius:8px;
    border:none;
    font-weight:800;
    line-height:1;
    display:inline-flex;
    align-items:center;
    justify-content:center
}

.qty-btn.minus {
    background:#fee2e2;
    color:#b91c1c
}

.qty-btn.plus {
    background:#dcfce7;
    color:#15803d
}

.qty-value {
    min-width:22px;
    text-align:center;
    font-weight:800;
    color:#111827
}

@media(min-width:1600px) {
    .productos-grid {
        grid-template-columns:repeat(5,minmax(0,1fr))
    }
}

@media(max-width:1360px) {
    .productos-grid {
        grid-template-columns:repeat(3,minmax(0,1fr))
    }
}

@media(max-width:1200px) {
    .layout-grid {
        grid-template-columns:160px minmax(0,1fr) 320px
    }

    .productos-grid {
        grid-template-columns:repeat(3,minmax(0,1fr))
    }
}

@media(max-width:1100px) {
    .layout-grid {
        grid-template-columns:140px minmax(0,1fr) 300px
    }

    .productos-grid {
        grid-template-columns:repeat(auto-fit,minmax(215px,1fr))
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

    .side-panel {
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(150px,1fr));
        gap:8px;
        padding:10px;
        margin-bottom:18px
    }

    .side-panel .section-title {
        grid-column:1/-1;
        margin-bottom:2px
    }

    .side-panel .btn-categoria {
        margin-bottom:0;
        min-height:42px;
        padding:8px 10px;
        font-size:12px
    }

    .productos-grid {
        grid-template-columns:repeat(2,minmax(0,1fr))
    }

    .producto-btn {
        min-height:194px
    }

    .producto-img {
        height:102px
    }

    .precio-text {
        font-size:14px
    }

    .toolbar {
        align-items:stretch;
        flex-direction:column
    }

    .search-box {
        max-width:100%
    }

    .cart-panel {
        padding:10px
    }

    .cart-panel .section-title {
        font-size:15px
    }

    .cart-panel .section-help {
        font-size:11px
    }

    .cart-panel .table {
        font-size:11px;
        margin-bottom:8px
    }

    .cart-panel .table th,
    .cart-panel .table td {
        padding:.42rem .35rem
    }

    .cart-panel .total-box {
        font-size:24px;
        padding:12px
    }

    .payment-card,
    .payment-summary {
        padding:10px
    }

    .quick-pay-grid-top,.quick-pay-grid-bottom {
        gap:8px
    }

    .quick-pay-grid-top {
        grid-template-columns:repeat(3,1fr)
    }

    .quick-pay-grid-bottom {
        grid-template-columns:repeat(2,1fr)
    }

    .btn-pago-metodo {
        min-height:54px;
        gap:2px;
        padding:5px 3px
    }

    .btn-pago-metodo .pago-imagen {
        height:20px
    }

    .btn-pago-metodo .pago-icon {
        font-size:17px
    }

    .btn-pago-metodo .pago-nombre {
        font-size:9px
    }

    #modalProducto .modal-dialog {
        margin:.4rem
    }

    #modalProducto .modal-body {
        padding:10px
    }

    .modal-extra-title {
        font-size:15px;
        margin-bottom:8px
    }

    .extra-item {
        padding:9px 10px
    }

    .qty-btn {
        width:30px;
        height:30px
    }

}

@media(max-width:520px) {
    .productos-grid {
        grid-template-columns:1fr
    }

    .side-panel {
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:7px;
        padding:9px;
        margin-bottom:16px
    }

    .side-panel .btn-categoria {
        min-height:40px;
        padding:7px 8px;
        font-size:11px
    }

    .quick-pay-grid-top,.quick-pay-grid-bottom {
        gap:6px
    }

    .quick-pay-grid-top {
        grid-template-columns:repeat(3,1fr)
    }

    .quick-pay-grid-bottom {
        grid-template-columns:repeat(2,1fr)
    }

    .btn-pago-metodo {
        min-height:50px;
        padding:4px 3px
    }

    .btn-pago-metodo .pago-imagen {
        height:18px
    }

    .btn-pago-metodo .pago-icon {
        font-size:15px
    }

    .btn-pago-metodo .pago-nombre {
        font-size:8px
    }

    .cart-panel .table-responsive {
        overflow-x:auto
    }
}

@media(prefers-reduced-motion:reduce) {
    *,
    *::before,
    *::after {
        transition:none!important;
        animation:none!important
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

                <button class="btn btn-outline-secondary btn-sm" onclick="vaciarCarrito()">
                    Vaciar
                </button>

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

            <div class="total-box mb-2">
                Total: $ <span id="total">0</span>
            </div>

            <div class="payment-card mb-2">

                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-bold mb-0">Pagos</label>
                    <span class="section-help">Rápido o mixto</span>
                </div>

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
                    class="btn btn-success w-100"
                    id="btnGuardarVenta"
                    onclick="guardarVenta()"
                    disabled
                >
                    ✔ Guardar venta
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
let pagoAutoSeleccionado = false;
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

    // Productos simples: agregar directamente sin abrir modal
    if (tipo === 'simple') {
        limpiarExtrasSeleccionados();
        limpiarSaboresSeleccionados();
        agregarProductoConExtras(id, nombre, precio);
        return;
    }

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
                <h6 class="modal-extra-title">${tipo}</h6>
                <div class="row">
        `;

        extrasPorTipo[tipo].forEach(extra => {
            let cantidad = cantidadesExtras[extra.id] || 0;

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
                            <button type="button" class="qty-btn plus" onclick="cambiarCantidadExtra(${extra.id}, 1)">+</button>
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
            <div class="col-md-6 col-12">
                <div class="extra-item h-100">
                    <strong>${sabor.nombre}</strong>
                    <div class="qty-controls">
                        <button type="button" class="qty-btn minus" onclick="cambiarCantidadSabor(${sabor.id}, -1)">-</button>
                        <span class="qty-value" id="cantidad_sabor_${sabor.id}">${cantidad}</span>
                        <button type="button" class="qty-btn plus" onclick="cambiarCantidadSabor(${sabor.id}, 1)">+</button>
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
        btn.setAttribute('aria-pressed', 'false');
    });

    if (boton) {
        boton.classList.add('active-pay');
        boton.setAttribute('aria-pressed', 'true');
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

    // Auto-seleccionar efectivo al agregar el primer producto
    if (carrito.length === 1 && !pagoAutoSeleccionado) {
        pagoAutoSeleccionado = true;
        const btnEfectivo = document.querySelector('button.btn-pago-rapido');
        if (btnEfectivo) seleccionarPagoSimple('efectivo', btnEfectivo);
    }
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
    pagoAutoSeleccionado = false;

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
