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

    <style>
    body {
        background: #f5f6f8;
    }

    .main-content {
        margin-left: 225px;
        padding: 32px 24px;
        padding-top: 80px;
        min-height: 100vh;
    }
</style>

</head>

<body class="bg-light">

<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<div style="margin-left:250px; padding:86px 20px 20px 20px;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Usuarios</h3>
            <small class="text-muted">Administra accesos y roles del sistema</small>
        </div>

        <button 
            class="btn btn-success"
            data-bs-toggle="modal"
            data-bs-target="#modalCrearUsuario"
        >
            + Nuevo usuario
        </button>
    </div>

    <table class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Estado</th>
                <th width="300">Acciones</th>
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
                    <?php if ($u['estado'] == 1) { ?>
                        <span class="badge bg-success">Activo</span>
                    <?php } else { ?>
                        <span class="badge bg-secondary">Inactivo</span>
                    <?php } ?>
                </td>

                <td>
                    <div class="d-flex gap-2 flex-wrap">

                        <button 
                            type="button" 
                            class="btn btn-sm btn-outline-warning"
                            data-bs-toggle="modal"
                            data-bs-target="#modalUsuario<?php echo $u['id']; ?>"
                        >
                            Editar
                        </button>

                        <?php if ($u['estado'] == 1) { ?>
                            <a 
                                href="../controllers/usuarioController.php?accion=desactivar&id=<?php echo $u['id']; ?>" 
                                class="btn btn-sm btn-outline-secondary"
                            >
                                Desactivar
                            </a>
                        <?php } else { ?>
                            <a 
                                href="../controllers/usuarioController.php?accion=activar&id=<?php echo $u['id']; ?>" 
                                class="btn btn-sm btn-outline-success"
                            >
                                Activar
                            </a>
                        <?php } ?>

                        <button 
                            type="button"
                            class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEliminarUsuario<?php echo $u['id']; ?>"
                        >
                            Eliminar
                        </button>

                    </div>
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
            <div class="modal fade" id="modalEliminarUsuario<?php echo $u['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Eliminar usuario</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p class="mb-1">¿Seguro que deseas eliminar este usuario?</p>
                            <span><?php echo htmlspecialchars($u['nombre']); ?></span>

                            <div class="alert alert-warning mt-3 mb-0">
                                Si este usuario ya registró ventas, eliminarlo puede afectar el historial.
                                Lo recomendado es <strong>desactivarlo</strong>.
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                Cancelar
                            </button>

                            <a 
                                href="../controllers/usuarioController.php?accion=eliminar&id=<?php echo $u['id']; ?>" 
                                class="btn btn-danger"
                            >
                                Sí, eliminar
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        <?php } ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-secondary">Volver</a>

</div>

<div class="modal fade" id="modalCrearUsuario" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="../controllers/usuarioController.php" method="POST">
                <input type="hidden" name="accion" value="crear">

                <div class="modal-header">
                    <h5 class="modal-title">Nuevo usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="usuario" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="vendedor">Vendedor</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success">Guardar usuario</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>