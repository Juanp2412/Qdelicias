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
</head>

<body class="bg-light">

<?php include 'layout/header.php'; ?>

<div class="container mt-4">

<h3>Tipos de Extra</h3>

<!-- CREAR -->
<div class="card p-3 mb-4">
    <form action="../controllers/tipoExtraController.php" method="POST">
        <input type="hidden" name="accion" value="crear">

        <div class="row g-2">
            <div class="col-md-8">
                <input type="text" name="nombre" class="form-control" placeholder="Nombre del tipo" required>
            </div>

            <div class="col-md-4">
                <button class="btn btn-success w-100">Guardar</button>
            </div>
        </div>
    </form>
</div>

<!-- TABLA -->
<table class="table table-bordered align-middle">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Nombre</th>
<th width="220">Acciones</th>
</tr>
</thead>

<tbody>

<?php while ($t = $tipos->fetch_assoc()) { ?>
<tr>
<td><?php echo $t['id']; ?></td>

<td>
    <span class="badge bg-primary fs-6">
        <?php echo ucfirst($t['nombre']); ?>
    </span>
</td>

<td>
    <!-- EDITAR -->
    <button 
        type="button"
        class="btn btn-warning btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#modalTipo<?php echo $t['id']; ?>"
    >
        Editar
    </button>

    <!-- ELIMINAR -->
    <form action="../controllers/tipoExtraController.php" method="POST" style="display:inline;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
        <button class="btn btn-danger btn-sm">X</button>
    </form>
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

<?php } ?>

</tbody>
</table>

<a href="dashboard.php" class="btn btn-secondary">Volver</a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>