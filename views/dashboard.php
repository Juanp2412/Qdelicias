<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

// Total ventas hoy
$totalHoyQuery = $conn->query("
    SELECT SUM(total) AS total 
    FROM ventas 
    WHERE DATE(fecha) = CURDATE()
");
$totalHoy = $totalHoyQuery->fetch_assoc()['total'] ?? 0;

// Número de ventas hoy
$cantidadVentasQuery = $conn->query("
    SELECT COUNT(*) AS cantidad 
    FROM ventas 
    WHERE DATE(fecha) = CURDATE()
");
$cantidadVentas = $cantidadVentasQuery->fetch_assoc()['cantidad'] ?? 0;

// Productos más vendidos hoy
$productosQuery = $conn->query("
    SELECT p.nombre, SUM(d.cantidad) AS total_vendidos
    FROM detalle_venta d
    INNER JOIN productos p ON p.id = d.producto_id
    INNER JOIN ventas v ON v.id = d.venta_id
    WHERE DATE(v.fecha) = CURDATE()
    GROUP BY p.nombre
    ORDER BY total_vendidos DESC
    LIMIT 5
");

$ventasHoyQuery = $conn->query("
    SELECT id, fecha, total
    FROM ventas
    WHERE DATE(fecha) = CURDATE()
    ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .menu-card{
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .acceso-btn{
            min-height: 90px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 14px;
            width: 100%;
        }
        .resumen-card{
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body class="bg-light">

<?php include 'layout/header.php'; ?>

<div class="container mt-4">

    <h3 class="mb-4">Dashboard - Hoy (<?php echo date("d/m/Y"); ?>)</h3>

    <div class="card menu-card p-3 mb-4">
        <h5 class="mb-3">Accesos rápidos</h5>
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <a href="ventas.php" class="btn btn-success acceso-btn">Ventas</a>
            </div>
            <div class="col-md-3 col-6">
                <a href="productos.php" class="btn btn-primary acceso-btn">Productos</a>
            </div>
            <div class="col-md-3 col-6">
                <a href="tipos_extra.php" class="btn btn-dark acceso-btn">Tipos de extra</a>
            </div>
            <div class="col-md-3 col-6">
                <a href="extras.php" class="btn btn-warning acceso-btn">Extras</a>
            </div>
            <div class="col-md-3 col-6">
                <a href="reglas_producto.php" class="btn btn-info acceso-btn">Reglas por producto</a>
            </div>
            <div class="col-md-3 col-6">
                <a href="reporte_ventas.php" class="btn btn-secondary acceso-btn">Reporte ventas</a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card resumen-card p-4 text-center">
                <h5>Total vendido hoy</h5>
                <h2 class="text-success">
                    $ <?php echo number_format($totalHoy, 0, ',', '.'); ?>
                </h2>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card resumen-card p-4 text-center">
                <h5>Número de ventas</h5>
                <h2 class="text-primary">
                    <?php echo $cantidadVentas; ?>
                </h2>
            </div>
        </div>
    </div>

    <div class="card resumen-card p-3">
        <h5>Productos más vendidos hoy</h5>

        <table class="table table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad vendida</th>
                </tr>
            </thead>
            <tbody>
                <?php while($p = $productosQuery->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                        <td><?php echo $p['total_vendidos']; ?></td>
                    </tr>
                <?php } ?>

                <?php if ($productosQuery->num_rows == 0) { ?>
                    <tr>
                        <td colspan="2" class="text-center text-muted">
                            No hay ventas hoy
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <div class="card resumen-card p-3 mt-4">
    <h5>Ventas del día</h5>

    <div class="table-responsive">
        <table class="table table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>ID Venta</th>
                    <th>Hora</th>
                    <th>Total</th>
                    <th width="140">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while($venta = $ventasHoyQuery->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $venta['id']; ?></td>
                        <td><?php echo date("h:i A", strtotime($venta['fecha'])); ?></td>
                        <td>$ <?php echo number_format($venta['total'], 0, ',', '.'); ?></td>
                        <td>
                            <a href="detalle_venta.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-primary">
                                Ver detalle
                            </a>
                        </td>
                    </tr>
                <?php } ?>

                <?php if ($ventasHoyQuery->num_rows == 0) { ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">No hay ventas registradas hoy</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>