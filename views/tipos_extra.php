<?php
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

soloAdmin();

$tipos = $conn->query("SELECT * FROM tipos_extra ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tipos de Extra</title>
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
        <h3 class="mb-0">Tipos de extra</h3>
        <small class="text-muted">Organiza toppings, salsas, bases y adicionales</small>
    </div>

    <button 
        class="btn btn-success"
        data-bs-toggle="modal"
        data-bs-target="#modalCrearTipo"
    >
        + Nuevo tipo
    </button>
</div>


<!-- TABLA -->
<table class="table table-bordered align-middle">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Estado</th>
<th width="300">Acciones</th>
</tr>
</thead>

<tbody>

<?php while ($t = $tipos->fetch_assoc()) { ?>
<tr>
<td><?php echo $t['id']; ?></td>

<td><?php echo ucfirst(htmlspecialchars($t['nombre'])); ?></td>
<td>
    <?php if ($t['estado'] == 1) { ?>
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
            data-bs-target="#modalTipo<?php echo $t['id']; ?>"
        >
            Editar
        </button>

        <?php if ($t['estado'] == 1) { ?>
            <a 
                href="../controllers/tipoExtraController.php?accion=desactivar&id=<?php echo $t['id']; ?>" 
                class="btn btn-sm btn-outline-secondary"
            >
                Desactivar
            </a>
        <?php } else { ?>
            <a 
                href="../controllers/tipoExtraController.php?accion=activar&id=<?php echo $t['id']; ?>" 
                class="btn btn-sm btn-outline-success"
            >
                Activar
            </a>
        <?php } ?>

        <button 
            type="button"
            class="btn btn-sm btn-outline-danger"
            data-bs-toggle="modal"
            data-bs-target="#modalEliminarTipo<?php echo $t['id']; ?>"
        >
            Eliminar
        </button>

    </div>
</td>
</tr>

<!-- MODAL EDITAR -->
<div class="modal fade" id="modalTipo<?php echo $t['id']; ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <form action="../controllers/tipoExtraController.php" method="POST">
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="id" value="<?php echo $t['id']; ?>">

        <div class="modal-header">
          <h5 class="modal-title">Editar tipo de extra</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <div class="mb-2">
                <label>Nombre</label>
                <input 
                    type="text" 
                    name="nombre" 
                    value="<?php echo $t['nombre']; ?>" 
                    class="form-control" 
                    required
                >
            </div>

            <div class="alert alert-warning mt-2">
                ⚠️ Si cambias el nombre, se actualizarán los extras y reglas automáticamente.
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

<!-- MODAL ELIMINAR TIPO -->
<div class="modal fade" id="modalEliminarTipo<?php echo $t['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Eliminar tipo de extra</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="mb-1">¿Seguro que deseas eliminar este tipo?</p>
                <strong><?php echo htmlspecialchars($t['nombre']); ?></strong>

                <div class="alert alert-warning mt-3 mb-0">
                    Si este tipo tiene extras o reglas asociadas, eliminarlo puede causar errores.
                    Lo recomendado es <strong>desactivarlo</strong>.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <a 
                    href="../controllers/tipoExtraController.php?accion=eliminar&id=<?php echo $t['id']; ?>" 
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
<!-- MODAL CREAR TIPO -->
<div class="modal fade" id="modalCrearTipo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="../controllers/tipoExtraController.php" method="POST">
                <input type="hidden" name="accion" value="crear">

                <div class="modal-header">
                    <h5 class="modal-title">Nuevo tipo de extra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label class="form-label">Nombre</label>
                    <input 
                        type="text" 
                        name="nombre" 
                        class="form-control" 
                        placeholder="Ej: toppings, salsas, bases..." 
                        required
                    >
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success">Guardar tipo</button>
                </div>
            </form>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>