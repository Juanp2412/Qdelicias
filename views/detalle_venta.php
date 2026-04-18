<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$venta_id = (int) $_GET['id'];

$ventaQuery = $conn->query("SELECT * FROM ventas WHERE id = $venta_id");
$venta = $ventaQuery->fetch_assoc();

if (!$venta) {
    header("Location: dashboard.php");
    exit();
}

$detallesQuery = $conn->query("
    SELECT d.id, d.cantidad, d.precio, p.nombre AS producto_nombre
    FROM detalle_venta d
    INNER JOIN productos p ON p.id = d.producto_id
    WHERE d.venta_id = $venta_id
");

$detalles = [];
while ($detalle = $detallesQuery->fetch_assoc()) {
    $detalle_id = $detalle['id'];

    $extrasQuery = $conn->query("
        SELECT e.nombre, e.tipo, dve.precio
        FROM detalle_venta_extras dve
        INNER JOIN extras e ON e.id = dve.extra_id
        WHERE dve.detalle_venta_id = $detalle_id
    ");

    $extras = [];
    $totalExtras = 0;

    while ($extra = $extrasQuery->fetch_assoc()) {
        $extras[] = $extra;

        if ($extra['precio'] > 0) {
            $totalExtras += $extra['precio'];
        }
    }

    // total del producto (base + extras)
    $totalProducto = ($detalle['precio'] * $detalle['cantidad']) + $totalExtras;

    $detalle['total_producto'] = $totalProducto;

        $detalle['extras'] = $extras;
        $detalles[] = $detalle;
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de venta</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php include 'layout/header.php'; ?>

<div class="container mt-4">
    <div class="card p-4">
        <h3>Detalle de venta #<?php echo $venta['id']; ?></h3>
        <p><strong>Fecha:</strong> <?php echo date("d/m/Y h:i A", strtotime($venta['fecha'])); ?></p>
        <p><strong>Total:</strong> $ <?php echo number_format($venta['total'], 0, ',', '.'); ?></p>

        <hr>

        <h5>Productos vendidos</h5>

        <div class="table-responsive">
            <table class="table table-bordered mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio base</th>
                        <th>Extras</th>
                        <th>Total producto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($detalles as $detalle) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                            <td><?php echo $detalle['cantidad']; ?></td>
                            <td>$ <?php echo number_format($detalle['precio'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if (count($detalle['extras']) > 0) { ?>
                                    <ul class="mb-0">
                                        <?php foreach($detalle['extras'] as $extra) { ?>
                                            <li>
                                                <?php echo htmlspecialchars($extra['nombre']); ?>
                                                (<?php echo htmlspecialchars($extra['tipo']); ?>)
                                                -
                                                <?php echo $extra['precio'] > 0 ? '+$ '.number_format($extra['precio'], 0, ',', '.') : 'incluido'; ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } else { ?>
                                    <span class="text-muted">Sin extras</span>
                                <?php } ?>
                                <td>$ <?php echo number_format($detalle['total_producto'], 0, ',', '.'); ?></td>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if (count($detalles) == 0) { ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay detalles en esta venta</td>
                        </tr>
                    <?php } ?>
                    
                </tbody>
            </table>
        </div>

        <a href="dashboard.php" class="btn btn-secondary">Volver al dashboard</a>
    </div>
</div>

</body>
</html>