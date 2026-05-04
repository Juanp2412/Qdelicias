<?php

/*
|--------------------------------------------------------------------------
| Archivo: reporte_ventas.php
|--------------------------------------------------------------------------
| Propósito:
| Genera un reporte de ventas por rango de fechas. Resume el total
| vendido, la cantidad de ventas realizadas, los productos más vendidos
| y el listado de ventas registradas dentro del período consultado.
|
| Funcionalidades principales:
| - Valida sesión del usuario.
| - Permite filtrar ventas por fecha inicio y fecha fin.
| - Calcula total vendido dentro del rango.
| - Calcula cantidad total de ventas.
| - Lista las ventas encontradas con acceso al detalle individual.
| - Muestra ranking de productos más vendidos e ingreso generado.
|
| Observación:
| Este archivo funciona como reporte administrativo básico y permite
| analizar el comportamiento comercial del negocio en un período definido.
|--------------------------------------------------------------------------
*/
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

verificarLogin();

$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

$totalVendido = 0;
$cantidadVentas = 0;
$ventas = null;

if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $queryResumen = $conn->query("
        SELECT 
            COUNT(*) AS cantidad_ventas,
            COALESCE(SUM(total), 0) AS total_vendido
        FROM ventas
        WHERE DATE(fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'
    ");

    $resumen = $queryResumen->fetch_assoc();
    $cantidadVentas = $resumen['cantidad_ventas'];
    $totalVendido = $resumen['total_vendido'];

    $ventas = $conn->query("
        SELECT id, fecha, total, metodo_pago
        FROM ventas
        WHERE DATE(fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'
        ORDER BY fecha DESC
    ");
    $productosMasVendidos = $conn->query("
        SELECT 
            p.nombre,
            SUM(d.cantidad) AS total_vendidos,
            SUM(
                (d.precio * d.cantidad) + 
                COALESCE((
                    SELECT SUM(dve.precio)
                    FROM detalle_venta_extras dve
                    WHERE dve.detalle_venta_id = d.id
                ), 0)
            ) AS ingreso_generado
        FROM detalle_venta d
        INNER JOIN productos p ON p.id = d.producto_id
        INNER JOIN ventas v ON v.id = d.venta_id
        WHERE DATE(v.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'
        GROUP BY p.id, p.nombre
        ORDER BY ingreso_generado DESC
        LIMIT 10
    ");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de ventas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .resumen-card{
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body class="bg-light">

<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<div class="main-content">
    <div class="card p-4 mb-4">
        <h3 class="mb-3">Reporte de ventas por fechas</h3>

        <form method="GET" action="reporte_ventas.php">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Fecha inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $fecha_inicio; ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Fecha fin</label>
                    <input type="date" name="fecha_fin" class="form-control" value="<?php echo $fecha_fin; ?>" required>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Consultar</button>
                </div>
            </div>
        </form>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card resumen-card p-4 text-center">
                <h5>Total vendido en el rango</h5>
                <h2 class="text-success">$ <?php echo number_format($totalVendido, 0, ',', '.'); ?></h2>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card resumen-card p-4 text-center">
                <h5>Número de ventas</h5>
                <h2 class="text-primary"><?php echo $cantidadVentas; ?></h2>
            </div>
        </div>
    </div>
    <?php
        $stmtPagos = $conn->prepare("
            SELECT vp.metodo_pago, SUM(vp.monto) AS total
            FROM venta_pagos vp
            INNER JOIN ventas v ON v.id = vp.venta_id
            WHERE DATE(v.fecha) BETWEEN ? AND ?
            GROUP BY vp.metodo_pago
        ");

        $stmtPagos->bind_param("ss", $fecha_inicio, $fecha_fin);
        $stmtPagos->execute();
        $resumenPagos = $stmtPagos->get_result();
        ?>

        <div class="card mb-4">
            <div class="card-body">
                <h5>Resumen por método de pago</h5>

                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Método</th>
                            <th>Total recibido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($pago = $resumenPagos->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo ucfirst($pago['metodo_pago']); ?></td>
                                <td>$ <?php echo number_format($pago['total'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <div class="card p-4 mb-4">
    <h5 class="mb-3">Productos más vendidos en el rango</h5>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad vendida</th>
                    <th>Ingreso generado</th>               
            </thead>
            <tbody>
                <?php if ($productosMasVendidos && $productosMasVendidos->num_rows > 0) { ?>
                    <?php while ($producto = $productosMasVendidos->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td><?php echo $producto['total_vendidos']; ?></td>
                            <td>$ <?php echo number_format($producto['ingreso_generado'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">No hay productos vendidos en ese rango</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>


    <div class="card p-4">
        <h5 class="mb-3">Ventas encontradas</h5>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID Venta</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th width="140">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ventas && $ventas->num_rows > 0) { ?>
                        <?php while ($venta = $ventas->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $venta['id']; ?></td>
                                <td><?php echo date("d/m/Y h:i A", strtotime($venta['fecha'])); ?></td>
                                <td>$ <?php echo number_format($venta['total'], 0, ',', '.'); ?></td>
                                <td>
                                    <a href="detalle_venta.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-primary">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay ventas en ese rango</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

</body>
</html>