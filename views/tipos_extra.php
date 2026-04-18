<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

$tipos = $conn->query("SELECT * FROM tipos_extra ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tipos de Extra</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php include 'layout/header.php'; ?>

<div class="container mt-4">
    <h3>Gestión de Tipos de Extra</h3>

    <div class="card p-3 mb-3">
        <form action="../controllers/tipoExtraController.php" method="POST">
            <input type="hidden" name="accion" value="crear">

            <div class="row g-2">
                <div class="col-md-8">
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre del tipo de extra" required>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-success w-100">Guardar</button>
                </div>
            </div>
        </form>
    </div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th width="220">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($t = $tipos->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $t['id']; ?></td>
                    <td><?php echo htmlspecialchars($t['nombre']); ?></td>
                    <td>
                        <form action="../controllers/tipoExtraController.php" method="POST" style="display:inline;">
                            <input type="hidden" name="accion" value="editar">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($t['nombre']); ?>" required>
                            <button class="btn btn-warning btn-sm">Editar</button>
                        </form>

                        <form action="../controllers/tipoExtraController.php" method="POST" style="display:inline;">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <button class="btn btn-danger btn-sm">X</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>