<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

$productos = $conn->query("SELECT * FROM productos ORDER BY nombre ASC");
$tiposExtras = $conn->query("SELECT * FROM tipos_extra ORDER BY nombre ASC");
$reglas = $conn->query("
    SELECT r.*, p.nombre AS producto_nombre
    FROM producto_reglas_extras r
    INNER JOIN productos p ON p.id = r.producto_id
    ORDER BY p.nombre ASC, r.tipo_extra ASC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reglas por producto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php include 'layout/header.php'; ?>

<div class="container mt-4">
    <div class="card p-3 mb-4">
        <h3 class="mb-3">Configurar reglas de extras por producto</h3>

        <form action="../controllers/reglaProductoController.php" method="POST">
            <input type="hidden" name="accion" value="crear">

            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Producto</label>
                    <select name="producto_id" class="form-control" required>
                        <option value="">Seleccione</option>
                        <?php while($p = $productos->fetch_assoc()) { ?>
                            <option value="<?php echo $p['id']; ?>">
                                <?php echo htmlspecialchars($p['nombre']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tipo de extra</label>
                    <select name="tipo_extra" class="form-control" required>
                        <option value="">Seleccione</option>
                        <?php while($t = $tiposExtras->fetch_assoc()) { ?>
                            <option value="<?php echo htmlspecialchars($t['nombre']); ?>">
                                <?php echo ucfirst(htmlspecialchars($t['nombre'])); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Cantidad incluida</label>
                    <input type="number" name="cantidad_incluida" class="form-control" min="0" required>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-success w-100">Guardar</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card p-3">
        <h4 class="mb-3">Reglas actuales</h4>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Tipo extra</th>
                        <th>Cantidad incluida</th>
                        <th width="220">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($r = $reglas->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['producto_nombre']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($r['tipo_extra'])); ?></td>
                            <td><?php echo $r['cantidad_incluida']; ?></td>
                            <td>
                                <form action="../controllers/reglaProductoController.php" method="POST" class="d-inline">
                                    <input type="hidden" name="accion" value="editar">
                                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                    <input type="number" name="cantidad_incluida" value="<?php echo $r['cantidad_incluida']; ?>" min="0" required style="width:80px;">
                                    <button class="btn btn-warning btn-sm">Editar</button>
                                </form>

                                <form action="../controllers/reglaProductoController.php" method="POST" class="d-inline">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                    <button class="btn btn-danger btn-sm">X</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if ($reglas->num_rows == 0) { ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay reglas configuradas</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>