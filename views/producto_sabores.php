<?php
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

soloAdmin();

$producto_id = $_GET['producto_id'] ?? '';

$productos = $conn->query("
    SELECT id, nombre, tipo_configuracion 
    FROM productos 
    ORDER BY nombre ASC
");

$sabores = $conn->query("
    SELECT * 
    FROM sabores 
    WHERE activo = 1 
    ORDER BY tipo ASC, nombre ASC
");

$saboresAsignados = [];

if ($producto_id != '') {
    $asignados = $conn->query("
        SELECT sabor_id 
        FROM producto_sabores 
        WHERE producto_id = $producto_id
    ");

    while ($a = $asignados->fetch_assoc()) {
        $saboresAsignados[] = $a['sabor_id'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Sabores</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">
<?php include 'layout/header.php'; ?>

    <h3>Asignar sabores a productos</h3>

    <div class="card p-3 mb-4">
        <form method="GET" action="producto_sabores.php">
            <label class="form-label">Selecciona un producto</label>

            <div class="row g-2">
                <div class="col-md-8">
                    <select name="producto_id" class="form-select" required>
                        <option value="">Seleccione producto</option>

                        <?php while ($p = $productos->fetch_assoc()) { ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($producto_id == $p['id']) ? 'selected' : ''; ?>>
                                <?php echo $p['nombre']; ?> — <?php echo $p['tipo_configuracion']; ?>
                            </option>
                        <?php } ?>

                    </select>
                </div>

                <div class="col-md-4">
                    <button class="btn btn-primary w-100">Cargar sabores</button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($producto_id != '') { ?>

        <form action="../controllers/productoSaborController.php" method="POST">
            <input type="hidden" name="producto_id" value="<?php echo $producto_id; ?>">

            <div class="card p-3 mb-4">
                <h5>Sabores disponibles</h5>

                <div class="row">
                    <?php while ($s = $sabores->fetch_assoc()) { ?>
                        <div class="col-md-3 mb-2">
                            <div class="form-check border rounded p-2 bg-white">
                                <input 
                                    class="form-check-input ms-1" 
                                    type="checkbox" 
                                    name="sabores[]" 
                                    value="<?php echo $s['id']; ?>"
                                    <?php echo in_array($s['id'], $saboresAsignados) ? 'checked' : ''; ?>
                                >

                                <label class="form-check-label ms-2">
                                    <strong><?php echo $s['nombre']; ?></strong><br>
                                    <small class="text-muted"><?php echo $s['tipo']; ?></small>
                                </label>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <button class="btn btn-success mt-3">Guardar sabores del producto</button>
            </div>
        </form>

    <?php } ?>

    <a href="dashboard.php" class="btn btn-secondary">Volver</a>

</div>

</body>
</html>