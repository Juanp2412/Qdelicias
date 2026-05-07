<?php
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

verificarLogin();
noCache();
validarUsuarioActivo();

$rol = $_SESSION['rol'] ?? 'vendedor';

if ($rol != 'admin') {
    header("Location: ventas.php");
    exit();
}

$filtro = $_GET['filtro'] ?? 'hoy';
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';

if (!in_array($filtro, ['hoy', 'semana', 'mes', 'año', 'personalizado'])) {
    $filtro = 'hoy';
}

if ($filtro == 'personalizado' && (empty($fechaInicio) || empty($fechaFin))) {
    $filtro = 'hoy';
}

if ($filtro == "hoy") {
    $where = "DATE(fecha) = CURDATE()";
    $tituloPeriodo = "Hoy";
    $selectGrafica = "HOUR(fecha) AS grupo, CONCAT(HOUR(fecha), ':00') AS etiqueta";
    $groupGrafica = "HOUR(fecha)";
}

if ($filtro == "semana") {
    $where = "YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1)";
    $tituloPeriodo = "Esta semana";
    $selectGrafica = "DATE(fecha) AS grupo, DATE_FORMAT(fecha, '%d/%m') AS etiqueta";
    $groupGrafica = "DATE(fecha)";
}

if ($filtro == "mes") {
    $where = "MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
    $tituloPeriodo = "Este mes";
    $selectGrafica = "DATE(fecha) AS grupo, DATE_FORMAT(fecha, '%d/%m') AS etiqueta";
    $groupGrafica = "DATE(fecha)";
}

if ($filtro == "año") {
    $where = "YEAR(fecha) = YEAR(CURDATE())";
    $tituloPeriodo = "Este año";
    $selectGrafica = "MONTH(fecha) AS grupo, MONTHNAME(fecha) AS etiqueta";
    $groupGrafica = "MONTH(fecha)";
}
if ($filtro == "personalizado") {
    $where = "DATE(fecha) BETWEEN '$fechaInicio' AND '$fechaFin'";
    $tituloPeriodo = "Del " . date("d/m/Y", strtotime($fechaInicio)) . " al " . date("d/m/Y", strtotime($fechaFin));

    if ($fechaInicio == $fechaFin) {
        $selectGrafica = "HOUR(fecha) AS grupo, CONCAT(HOUR(fecha), ':00') AS etiqueta";
        $groupGrafica = "HOUR(fecha)";
    } else {
        $selectGrafica = "DATE(fecha) AS grupo, DATE_FORMAT(fecha, '%d/%m') AS etiqueta";
        $groupGrafica = "DATE(fecha)";
    }
}

$whereVentasAlias = str_replace("fecha", "v.fecha", $where);

$totalQuery = $conn->query("
    SELECT SUM(total) AS total 
    FROM ventas 
    WHERE $where
");
$totalPeriodo = $totalQuery->fetch_assoc()['total'] ?? 0;


$cantidadVentasQuery = $conn->query("
    SELECT COUNT(*) AS cantidad 
    FROM ventas 
    WHERE $where
");
$cantidadVentas = $cantidadVentasQuery->fetch_assoc()['cantidad'] ?? 0;

$ticketPromedio = ($cantidadVentas > 0) ? ($totalPeriodo / $cantidadVentas) : 0;


// Ajustar el GROUP BY para incluir todas las expresiones no agregadas
if ($filtro == "hoy" || ($filtro == "personalizado" && $fechaInicio == $fechaFin)) {
    $groupBy = "HOUR(fecha), CONCAT(HOUR(fecha), ':00')";
} elseif ($filtro == "mes" || $filtro == "semana" || ($filtro == "personalizado" && $fechaInicio != $fechaFin)) {
    $groupBy = "DATE(fecha), DATE_FORMAT(fecha, '%d/%m')";
} elseif ($filtro == "año") {
    $groupBy = "MONTH(fecha), MONTHNAME(fecha)";
} else {
    $groupBy = $groupGrafica; // fallback
}

$graficaVentasQuery = $conn->query("
    SELECT $selectGrafica, SUM(total) AS total
    FROM ventas
    WHERE $where
    GROUP BY $groupBy
    ORDER BY grupo ASC
");

$labelsVentas = [];
$dataVentas = [];

while ($g = $graficaVentasQuery->fetch_assoc()) {
    $labelsVentas[] = $g['etiqueta'];
    $dataVentas[] = (float)$g['total'];
}

$metodosPagoQuery = $conn->query("
    SELECT vp.metodo_pago, SUM(vp.monto) AS total
    FROM venta_pagos vp
    INNER JOIN ventas v ON v.id = vp.venta_id
    WHERE $whereVentasAlias
    GROUP BY vp.metodo_pago
    ORDER BY total DESC
");

$labelsPagos = [];
$dataPagos = [];

while ($mp = $metodosPagoQuery->fetch_assoc()) {
    $labelsPagos[] = ucfirst($mp['metodo_pago']);
    $dataPagos[] = (float)$mp['total'];
}

$productosQuery = $conn->query("
    SELECT p.nombre, SUM(d.cantidad) AS total_vendidos
    FROM detalle_venta d
    INNER JOIN productos p ON p.id = d.producto_id
    INNER JOIN ventas v ON v.id = d.venta_id
    WHERE $whereVentasAlias
    GROUP BY p.nombre
    ORDER BY total_vendidos DESC
    LIMIT 5
");

$productosRows = [];
$labelsProductos = [];
$dataProductos = [];

while ($p = $productosQuery->fetch_assoc()) {
    $productosRows[] = $p;
    $labelsProductos[] = $p['nombre'];
    $dataProductos[] = (int)$p['total_vendidos'];
}

$ventasQuery = $conn->query("
    SELECT id, fecha, total
    FROM ventas
    WHERE $where
    ORDER BY id DESC
    LIMIT 20
");
$topCantidadQuery = $conn->query("
    SELECT p.nombre, SUM(d.cantidad) AS total
    FROM detalle_venta d
    INNER JOIN productos p ON p.id = d.producto_id
    INNER JOIN ventas v ON v.id = d.venta_id
    WHERE $whereVentasAlias
    GROUP BY p.nombre
    ORDER BY total DESC
    LIMIT 1
");

$topCantidad = $topCantidadQuery->fetch_assoc();

$topIngresoQuery = $conn->query("
    SELECT p.nombre, SUM(d.cantidad * d.precio) AS ingreso
    FROM detalle_venta d
    INNER JOIN productos p ON p.id = d.producto_id
    INNER JOIN ventas v ON v.id = d.venta_id
    WHERE $whereVentasAlias
    GROUP BY p.nombre
    ORDER BY ingreso DESC
    LIMIT 1
");

$topIngreso = $topIngresoQuery->fetch_assoc();

$topPagoQuery = $conn->query("
    SELECT vp.metodo_pago, SUM(vp.monto) AS total
    FROM venta_pagos vp
    INNER JOIN ventas v ON v.id = vp.venta_id
    WHERE $whereVentasAlias
    GROUP BY vp.metodo_pago
    ORDER BY total DESC
    LIMIT 1
");

$topPago = $topPagoQuery->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
    * {
        box-sizing: border-box;
    }

    body {
        overflow-x: hidden;
    }

    .main-content {
        width: calc(100% - 240px);
        max-width: 100%;
    }

    .dashboard-header {
        gap: 14px;
    }

    .dashboard-title {
        margin: 0;
        font-size: clamp(20px, 2vw, 28px);
        font-weight: 700;
    }

    .dashboard-filters {
        max-width: 100%;
    }

    .dashboard-filters form {
        max-width: 100%;
    }

    .dashboard-filters input[type="date"] {
        min-width: 145px;
    }

    .resumen-card{
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        overflow: hidden;
        height: 100%;
    }

    .metric-card {
        min-height: 125px;
        display: flex;
        justify-content: center;
    }

    .metric-card h5,
    .metric-card h6 {
        font-size: clamp(14px, 1.4vw, 18px);
        margin-bottom: 8px;
    }

    .metric-card h2 {
        font-size: clamp(24px, 3vw, 34px);
        margin-bottom: 0;
        word-break: break-word;
    }

    .dashboard-chart-card {
        min-height: 300px;
        height: 100%;
    }

    .dashboard-chart-box {
        position: relative;
        width: 100%;
        height: 230px;
        min-height: 230px;
    }

    .dashboard-chart-box canvas {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
    }

    .ranking-card {
        min-height: 300px;
        height: 100%;
    }

    .ranking-card .table-responsive {
        max-height: 230px;
        overflow-y: auto;
        overflow-x: auto;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        margin-bottom: 0;
        vertical-align: middle;
    }

    .tabla-ventas {
        min-width: 760px;
    }

    .tabla-ranking {
        min-width: 360px;
    }

    @media (max-width: 1199.98px) {
        .main-content {
            width: calc(100% - 240px);
        }

        .dashboard-chart-card,
        .ranking-card {
            min-height: auto;
        }

        .dashboard-chart-box {
            height: 260px;
            min-height: 260px;
        }

        .ranking-card .table-responsive {
            max-height: 260px;
        }
    }

    @media (max-width: 991.98px) {
        .dashboard-header {
            align-items: flex-start !important;
            flex-direction: column;
        }

        .dashboard-filters,
        .dashboard-filters form {
            width: 100%;
        }

        .dashboard-filters .btn,
        .dashboard-filters form .btn {
            flex: 1 1 auto;
        }

        .dashboard-filters input[type="date"] {
            flex: 1 1 150px;
        }
    }

    @media (max-width: 767.98px) {
        .main-content {
            width: 100%;
            margin-left: 0 !important;
            padding: 82px 12px 20px 12px !important;
        }

        .dashboard-filters {
            gap: 8px !important;
        }

        .dashboard-filters > a {
            min-width: calc(50% - 4px);
        }

        .dashboard-filters form {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 8px !important;
        }

        .dashboard-filters form button {
            grid-column: 1 / -1;
            width: 100%;
        }

        .dashboard-chart-box {
            height: 240px;
            min-height: 240px;
        }

        .resumen-card {
            border-radius: 14px;
        }

        .table {
            font-size: 13px;
        }
    }

    @media (max-width: 575.98px) {
        .dashboard-filters > a {
            min-width: 100%;
        }

        .dashboard-filters form {
            grid-template-columns: 1fr;
        }

        .dashboard-filters form button {
            grid-column: auto;
        }

        .dashboard-chart-box {
            height: 220px;
            min-height: 220px;
        }

        .tabla-ranking {
            min-width: 300px;
        }

        .tabla-ventas {
            min-width: 680px;
        }
    }
    </style>

          
</head>

<body class="bg-light">

<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<div class="main-content">

    <div class="dashboard-header d-flex justify-content-between align-items-center mb-3">
        <h3 class="dashboard-title">Dashboard - <?php echo $tituloPeriodo; ?></h3>

        <div class="dashboard-filters d-flex gap-2 flex-wrap align-items-center">
            <a href="?filtro=hoy" class="btn btn-sm btn-outline-primary <?php echo ($filtro=='hoy')?'active':''; ?>">Hoy</a>
            <a href="?filtro=semana" class="btn btn-sm btn-outline-primary <?php echo ($filtro=='semana')?'active':''; ?>">Semana</a>
            <a href="?filtro=mes" class="btn btn-sm btn-outline-primary <?php echo ($filtro=='mes')?'active':''; ?>">Mes</a>
            <a href="?filtro=año" class="btn btn-sm btn-outline-primary <?php echo ($filtro=='año')?'active':''; ?>">Año</a>

            <form method="GET" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="filtro" value="personalizado">

                <input 
                    type="date" 
                    name="fecha_inicio" 
                    class="form-control form-control-sm"
                    value="<?php echo htmlspecialchars($fechaInicio); ?>"
                    required
                >

                <input 
                    type="date" 
                    name="fecha_fin" 
                    class="form-control form-control-sm"
                    value="<?php echo htmlspecialchars($fechaFin); ?>"
                    required
                >

                <button class="btn btn-sm btn-primary">
                    Aplicar
                </button>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 col-12 mb-3">
            <div class="card resumen-card metric-card p-4 text-center">
                <h5>Total vendido</h5>
                <h2 class="text-success">$ <?php echo number_format($totalPeriodo, 0, ',', '.'); ?></h2>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 col-12 mb-3">
            <div class="card resumen-card metric-card p-4 text-center">
                <h5>Número de ventas</h5>
                <h2 class="text-primary"><?php echo $cantidadVentas; ?></h2>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 col-12 mb-3">
            <div class="card resumen-card metric-card p-4 text-center">
                <h5>Ticket promedio</h5>
                <h2 class="text-warning">$ <?php echo number_format($ticketPromedio, 0, ',', '.'); ?></h2>
            </div>
        </div>
    </div>
    <div class="row mb-4">

    <div class="col-xl-4 col-md-6 col-12 mb-3">
        <div class="card resumen-card metric-card p-3 text-center">
            <h6>🔥 Producto más vendido</h6>
            <strong>
                <?php echo $topCantidad['nombre'] ?? 'N/A'; ?>
            </strong><br>
            <small><?php echo $topCantidad['total'] ?? 0; ?> ventas</small>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 col-12 mb-3">
        <div class="card resumen-card metric-card p-3 text-center">
            <h6>💰 Producto más rentable</h6>
            <strong>
                <?php echo $topIngreso['nombre'] ?? 'N/A'; ?>
            </strong><br>
            <small>
                $ <?php echo number_format($topIngreso['ingreso'] ?? 0, 0, ',', '.'); ?>
            </small>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 col-12 mb-3">
        <div class="card resumen-card metric-card p-3 text-center">
            <h6>💳 Método de pago top</h6>
            <strong>
                <?php echo ucfirst($topPago['metodo_pago'] ?? 'N/A'); ?>
            </strong><br>
            <small>
                $ <?php echo number_format($topPago['total'] ?? 0, 0, ',', '.'); ?>
            </small>
        </div>
    </div>

</div>

    <div class="row mb-4">
        <div class="col-xl-8 col-lg-12 mb-3">
            <div class="card resumen-card dashboard-chart-card p-3">
                <h5>Ventas del periodo</h5>
                <div class="dashboard-chart-box">
                    <canvas id="graficaVentas"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-12 mb-3">
            <div class="card resumen-card dashboard-chart-card p-3">
                <h5>Métodos de pago</h5>
                <div class="dashboard-chart-box">
                    <canvas id="graficaPagos"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-7 col-lg-12 mb-3">
            <div class="card resumen-card dashboard-chart-card p-3">
                <h5>Productos más vendidos</h5>
                <div class="dashboard-chart-box">
                    <canvas id="graficaProductos"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-5 col-lg-12 mb-3">
            <div class="card resumen-card ranking-card p-3">
                <h5>Ranking productos</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm mt-3 tabla-ranking">
                    <thead class="table-dark">
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach($productosRows as $p) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                <td><?php echo $p['total_vendidos']; ?></td>
                            </tr>
                        <?php } ?>

                        <?php if (count($productosRows) == 0) { ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted">
                                    No hay productos vendidos en este periodo
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    </div>

    <div class="card resumen-card p-3 mt-4 mb-4">
        <h5>Últimas ventas del periodo</h5>

        <div class="table-responsive">
            <table class="table table-bordered mt-3 tabla-ventas">
                <thead class="table-dark">
                    <tr>
                        <th>ID Venta</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Total</th>
                        <th width="140">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while($venta = $ventasQuery->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $venta['id']; ?></td>
                            <td><?php echo date("d/m/Y", strtotime($venta['fecha'])); ?></td>
                            <td><?php echo date("h:i A", strtotime($venta['fecha'])); ?></td>
                            <td>$ <?php echo number_format($venta['total'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="detalle_venta.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-primary">
                                    Ver detalle
                                </a>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if ($ventasQuery->num_rows == 0) { ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No hay ventas registradas en este periodo
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const labelsVentas = <?php echo json_encode($labelsVentas); ?>;
const dataVentas = <?php echo json_encode($dataVentas); ?>;

new Chart(document.getElementById('graficaVentas'), {
    type: 'line',
    data: {
        labels: labelsVentas,
        datasets: [{
            label: 'Ventas',
            data: dataVentas,
            borderWidth: 3,
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

const labelsPagos = <?php echo json_encode($labelsPagos); ?>;
const dataPagos = <?php echo json_encode($dataPagos); ?>;

new Chart(document.getElementById('graficaPagos'), {
    type: 'doughnut',
    data: {
        labels: labelsPagos,
        datasets: [{
            label: 'Métodos de pago',
            data: dataPagos,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

const labelsProductos = <?php echo json_encode($labelsProductos); ?>;
const dataProductos = <?php echo json_encode($dataProductos); ?>;

new Chart(document.getElementById('graficaProductos'), {
    type: 'bar',
    data: {
        labels: labelsProductos,
        datasets: [{
            label: 'Cantidad vendida',
            data: dataProductos,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</body>
</html>