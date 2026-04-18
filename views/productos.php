<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

$productos = $conn->query("SELECT * FROM productos ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">
<?php include 'layout/header.php'; ?>
    <h3>Gestión de Productos</h3>

    <!-- FORMULARIO -->
    <div class="card p-3 mb-4">
        <form action="../controllers/productoController.php" method="POST">
            <input type="hidden" name="accion" value="crear">

            <div class="row">
                <div class="col-md-5">
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre del producto" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="precio" class="form-control" placeholder="Precio" required>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success w-100">Guardar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- TABLA -->
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th width="180">Acciones</th>
            </tr>
        </thead>
        <tbody>

        <?php while ($p = $productos->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $p['id']; ?></td>
                <td><?php echo $p['nombre']; ?></td>
                <td>$ <?php echo number_format($p['precio'],0,',','.'); ?></td>

                <td>
                    <!-- EDITAR -->
                    <form action="../controllers/productoController.php" method="POST" style="display:inline;">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">

                        <input type="text" name="nombre" value="<?php echo $p['nombre']; ?>" required>
                        <input type="number" name="precio" value="<?php echo $p['precio']; ?>" required>

                        <button class="btn btn-warning btn-sm">Editar</button>
                    </form>

                    <!-- ELIMINAR -->
                    <form action="../controllers/productoController.php" method="POST" style="display:inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">

                        <button class="btn btn-danger btn-sm">X</button>
                    </form>
                </td>
            </tr>
        <?php } ?>

        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-secondary">Volver</a>

</div>

</body>

</html>