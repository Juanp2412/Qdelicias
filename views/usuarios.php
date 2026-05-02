<?php
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

soloAdmin();

$usuarios = $conn->query("SELECT * FROM usuarios ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<?php include 'layout/header.php'; ?>

<div class="container mt-4">

    <h3>Gestión de Usuarios</h3>

    <div class="card p-3 mb-4">
        <form action="../controllers/usuarioController.php" method="POST">
            <input type="hidden" name="accion" value="crear">

            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-control" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Rol</label>
                    <select name="rol" class="form-select" required>
                        <option value="admin">Admin</option>
                        <option value="vendedor">Vendedor</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-success w-100">Guardar</button>
                </div>
            </div>
        </form>
    </div>

    <table class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th width="220">Acciones</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($u = $usuarios->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                <td><?php echo htmlspecialchars($u['usuario']); ?></td>
                <td>
                    <span class="badge bg-primary">
                        <?php echo htmlspecialchars($u['rol']); ?>
                    </span>
                </td>

                <td>
                    <button 
                        type="button" 
                        class="btn btn-warning btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modalUsuario<?php echo $u['id']; ?>"
                    >
                        Editar
                    </button>

                    <form action="../controllers/usuarioController.php" method="POST" style="display:inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                        <button class="btn btn-danger btn-sm">X</button>
                    </form>
                </td>
            </tr>

            <div class="modal fade" id="modalUsuario<?php echo $u['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <form action="../controllers/usuarioController.php" method="POST">
                            <input type="hidden" name="accion" value="editar">
                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">

                            <div class="modal-header">
                                <h5 class="modal-title">Editar usuario</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($u['nombre']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Usuario</label>
                                    <input type="text" name="usuario" class="form-control" value="<?php echo htmlspecialchars($u['usuario']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nueva contraseña</label>
                                    <input type="password" name="password" class="form-control" placeholder="Dejar vacío si no cambia">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Rol</label>
                                    <select name="rol" class="form-select">
                                        <option value="admin" <?php echo ($u['rol'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                        <option value="vendedor" <?php echo ($u['rol'] == 'vendedor') ? 'selected' : ''; ?>>Vendedor</option>
                                    </select>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button class="btn btn-success">Guardar cambios</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        <?php } ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-secondary">Volver</a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>