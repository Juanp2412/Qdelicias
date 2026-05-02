<?php
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

soloAdmin();

$categorias = $conn->query("SELECT * FROM categorias ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">
<?php include 'layout/header.php'; ?>

    <h3>Gestión de Categorías</h3>

    <div class="card p-3 mb-4">
        <form action="../controllers/categoriaController.php" method="POST">
            <input type="hidden" name="accion" value="crear">

            <div class="row g-2">
                <div class="col-md-8">
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre de la categoría" required>
                </div>

                <div class="col-md-4">
                    <button class="btn btn-success w-100">Guardar categoría</button>
                </div>
            </div>
        </form>
    </div>

    <table class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th width="320">Acciones</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($cat = $categorias->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $cat['id']; ?></td>
                <td><?php echo $cat['nombre']; ?></td>

                <td>
                    <form action="../controllers/categoriaController.php" method="POST" class="d-inline-flex gap-2">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">

                        <input type="text" name="nombre" value="<?php echo $cat['nombre']; ?>" class="form-control form-control-sm" required>

                        <button class="btn btn-warning btn-sm">Editar</button>
                    </form>

                    <form action="../controllers/categoriaController.php" method="POST" style="display:inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">

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