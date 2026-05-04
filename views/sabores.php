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

<body class="bg-light">

<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Sabores</h3>
            <small class="text-muted">Administra sabores para alitas, jugos, malteadas y productos especiales</small>
        </div>

        <button 
            class="btn btn-success"
            data-bs-toggle="modal"
            data-bs-target="#modalCrearSabor"
        >
            + Nuevo sabor
        </button>
    </div>


    <table class="table table-hover table-bordered align-middle bg-white shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th width="300">Acciones</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($s = $sabores->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $s['id']; ?></td>
                <td><?php echo $s['nombre']; ?></td>
                <td><?php echo $s['tipo']; ?></td>
                <td>
                    <?php if ($s['activo'] == 1) { ?>
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
                            data-bs-target="#modalEditarSabor<?php echo $s['id']; ?>"
                        >
                            Editar
                        </button>

                        <?php if ($s['activo'] == 1) { ?>
                            <a 
                                href="../controllers/saborController.php?accion=desactivar&id=<?php echo $s['id']; ?>" 
                                class="btn btn-sm btn-outline-secondary"
                            >
                                Desactivar
                            </a>
                        <?php } else { ?>
                            <a 
                                href="../controllers/saborController.php?accion=activar&id=<?php echo $s['id']; ?>" 
                                class="btn btn-sm btn-outline-success"
                            >
                                Activar
                            </a>
                        <?php } ?>

                        <button 
                            type="button"
                            class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEliminarSabor<?php echo $s['id']; ?>"
                        >
                            Eliminar
                        </button>

                    </div>
                </td>
            </tr>
            <!-- MODAL EDITAR SABOR -->
            <div class="modal fade" id="modalEditarSabor<?php echo $s['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <form action="../controllers/saborController.php" method="POST">
                            <input type="hidden" name="accion" value="editar">
                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                            <input type="hidden" name="activo" value="<?php echo $s['activo']; ?>">

                            <div class="modal-header">
                                <h5 class="modal-title">Editar sabor</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input 
                                        type="text" 
                                        name="nombre" 
                                        value="<?php echo htmlspecialchars($s['nombre']); ?>" 
                                        class="form-control" 
                                        required
                                    >
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tipo</label>
                                    <select name="tipo" class="form-select" required>
                                        <option value="salsa_alitas" <?php echo $s['tipo'] == 'salsa_alitas' ? 'selected' : ''; ?>>Salsa alitas</option>
                                        <option value="fruta_jugo" <?php echo $s['tipo'] == 'fruta_jugo' ? 'selected' : ''; ?>>Fruta jugo</option>
                                        <option value="sabor_malteada" <?php echo $s['tipo'] == 'sabor_malteada' ? 'selected' : ''; ?>>Sabor malteada</option>
                                        <option value="general" <?php echo $s['tipo'] == 'general' ? 'selected' : ''; ?>>General</option>
                                    </select>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <button class="btn btn-success">Guardar cambios</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
            <!-- MODAL ELIMINAR SABOR -->
            <div class="modal fade" id="modalEliminarSabor<?php echo $s['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Eliminar sabor</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p class="mb-1">¿Seguro que deseas eliminar este sabor?</p>
                            <span><?php echo htmlspecialchars($s['nombre']); ?></span>

                            <div class="alert alert-warning mt-3 mb-0">
                                Si este sabor ya fue usado en ventas o está asignado a productos, eliminarlo puede afectar el historial.
                                Lo recomendado es <strong>desactivarlo</strong>.
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                Cancelar
                            </button>

                            <a 
                                href="../controllers/saborController.php?accion=eliminar&id=<?php echo $s['id']; ?>"
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
<!-- MODAL CREAR SABOR -->
<div class="modal fade" id="modalCrearSabor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="../controllers/saborController.php" method="POST">
                <input type="hidden" name="accion" value="crear">

                <div class="modal-header">
                    <h5 class="modal-title">Nuevo sabor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input 
                            type="text" 
                            name="nombre" 
                            class="form-control" 
                            placeholder="Ej: BBQ, mango, fresa, vainilla..." 
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="salsa_alitas">Salsa alitas</option>
                            <option value="fruta_jugo">Fruta jugo</option>
                            <option value="sabor_malteada">Sabor malteada</option>
                            <option value="general">General</option>
                        </select>
                    </div>

                    <div class="alert alert-info mb-0">
                        El tipo ayuda a mostrar solo los sabores correctos según el producto.
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success">Guardar sabor</button>
                </div>
            </form>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>