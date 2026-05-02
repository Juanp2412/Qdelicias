<?php
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

soloAdmin();

$sabores = $conn->query("SELECT * FROM sabores ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sabores</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">
<?php include 'layout/header.php'; ?>

    <h3>Gestión de Sabores</h3>

    <div class="card p-3 mb-4">
        <form action="../controllers/saborController.php" method="POST">
            <input type="hidden" name="accion" value="crear">

            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre del sabor" required>
                </div>

                <div class="col-md-4">
                    <select name="tipo" class="form-select" required>
                        <option value="salsa_alitas">Salsa alitas</option>
                        <option value="fruta_jugo">Fruta jugo</option>
                        <option value="sabor_malteada">Sabor malteada</option>
                        <option value="general">General</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-success w-100">Guardar sabor</button>
                </div>
            </div>
        </form>
    </div>

    <table class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Activo</th>
                <th width="420">Acciones</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($s = $sabores->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $s['id']; ?></td>
                <td><?php echo $s['nombre']; ?></td>
                <td><?php echo $s['tipo']; ?></td>
                <td><?php echo $s['activo'] == 1 ? 'Sí' : 'No'; ?></td>

                <td>
                    <form action="../controllers/saborController.php" method="POST" class="d-inline-flex gap-2">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">

                        <input type="text" name="nombre" value="<?php echo $s['nombre']; ?>" class="form-control form-control-sm" required>

                        <select name="tipo" class="form-select form-select-sm" required>
                            <option value="salsa_alitas" <?php echo $s['tipo'] == 'salsa_alitas' ? 'selected' : ''; ?>>Salsa alitas</option>
                            <option value="fruta_jugo" <?php echo $s['tipo'] == 'fruta_jugo' ? 'selected' : ''; ?>>Fruta jugo</option>
                            <option value="sabor_malteada" <?php echo $s['tipo'] == 'sabor_malteada' ? 'selected' : ''; ?>>Sabor malteada</option>
                            <option value="general" <?php echo $s['tipo'] == 'general' ? 'selected' : ''; ?>>General</option>
                        </select>

                        <select name="activo" class="form-select form-select-sm">
                            <option value="1" <?php echo $s['activo'] == 1 ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo $s['activo'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                        </select>

                        <button class="btn btn-warning btn-sm">Editar</button>
                    </form>

                    <form action="../controllers/saborController.php" method="POST" style="display:inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">

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