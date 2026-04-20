<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$productos = $conn->query("SELECT * FROM productos ORDER BY nombre ASC");
$extras = $conn->query("SELECT * FROM extras ORDER BY nombre ASC");
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
        body{
            background-color: #f8f9fa;
        }
        .top-bar{
            background: #212529;
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .producto-btn{
            width: 100%;
            min-height: 90px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 14px;
        }
        .precio-text{
            display: block;
            font-size: 15px;
            font-weight: normal;
            margin-top: 6px;
        }
        .panel-card{
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: none;
        }
        .total-box{
            background: #198754;
            color: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
        }
        .acciones-finales{
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .detalle-extra{
            font-size: 13px;
            color: #6c757d;
            display: block;
            margin-top: 4px;
        }
        .extra-item{
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 10px;
            background: #fff;
        }
        .extra-cantidad{
            min-width: 32px;
            text-align: center;
            font-weight: bold;
        }
        @media (max-width: 768px){
            .producto-btn{
                min-height: 80px;
                font-size: 16px;
            }
            .total-box{
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid p-3 p-md-4">
    
    <div class="top-bar d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="m-0">QDelicias POS</h3>
            <small>Usuario: <?php echo $_SESSION['usuario']; ?> | Rol: <?php echo $_SESSION['rol']; ?></small>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
            <a href="../controllers/logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-lg-7">
            <div class="card panel-card p-3 mb-4">
                <h4 class="mb-3">Selecciona extras</h4>

                <div class="row">
                    <?php foreach($extrasData as $e) { ?>
                        <div class="col-md-6">
                            <div class="extra-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong><?php echo htmlspecialchars($e['nombre']); ?></strong><br>
                                        <small class="text-muted">
                                            $ <?php echo number_format($e['precio'], 0, ',', '.'); ?> | Tipo: <?php echo htmlspecialchars($e['tipo']); ?>
                                        </small>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <button 
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm"
                                        onclick="cambiarCantidadExtra(<?php echo $e['id']; ?>, -1)"
                                    >-</button>

                                    <span class="extra-cantidad" id="cantidad_extra_<?php echo $e['id']; ?>">0</span>

                                    <button 
                                        type="button"
                                        class="btn btn-outline-primary btn-sm"
                                        onclick="cambiarCantidadExtra(<?php echo $e['id']; ?>, 1)"
                                    >+</button>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <small class="text-muted mt-2 d-block">
                    Ajusta las cantidades de extras y luego toca el producto.
                </small>
            </div>

            <div class="card panel-card p-3">
                <h4 class="mb-3">Productos</h4>
                <div class="row g-3">
                    <?php
                    mysqli_data_seek($productos, 0);
                    while ($p = $productos->fetch_assoc()) {
                    ?>
                        <div class="col-6 col-md-4">
                            <button 
                                class="btn btn-outline-primary producto-btn"
                                onclick="agregarProductoConExtras(<?php echo $p['id']; ?>, '<?php echo addslashes($p['nombre']); ?>', <?php echo $p['precio']; ?>)"
                            >
                                <?php echo $p['nombre']; ?>
                                <span class="precio-text">$ <?php echo number_format($p['precio'], 0, ',', '.'); ?></span>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card panel-card p-3">
                <h4 class="mb-3">Carrito</h4>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="tabla">
                        <thead class="table-dark">
                            <tr>
                                <th>Detalle</th>
                                <th width="90">Cant.</th>
                                <th width="130">Subtotal</th>
                                <th width="90">Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="total-box mb-3">
                    Total estimado: $ <span id="total">0</span>
                </div>
                <small class="text-muted d-block mb-3">
                    El total final se valida y calcula en backend al guardar.
                </small>

                <div class="acciones-finales">
                    <button class="btn btn-success flex-fill" onclick="guardarVenta()">Guardar venta</button>
                    <button class="btn btn-secondary flex-fill" onclick="vaciarCarrito()">Vaciar carrito</button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
window.VENTAS_CONFIG = {
    controllerUrl: "../controllers/ventascontroller.php",
    extras: <?php echo json_encode($extrasData, JSON_UNESCAPED_UNICODE); ?>
};
</script>
<script src="../assets/js/ventas.js"></script>

</body>
</html>
