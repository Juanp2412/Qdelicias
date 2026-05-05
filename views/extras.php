<?php
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

soloAdmin();

$extras = $conn->query("SELECT * FROM extras ORDER BY id DESC");
$tiposExtras = $conn->query("SELECT * FROM tipos_extra WHERE estado = 1 ORDER BY nombre ASC");

$tiposArray = [];
$tiposConsulta = $conn->query("SELECT * FROM tipos_extra WHERE estado = 1 ORDER BY nombre ASC");
while ($tipo = $tiposConsulta->fetch_assoc()) {
    $tiposArray[] = $tipo['nombre'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Extras</title>
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

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Extras</h3>
            <small class="text-muted">Administra toppings, salsas, bases y adicionales</small>
        </div>

        <button 
            class="btn btn-success"
            data-bs-toggle="modal"
            data-bs-target="#modalCrearExtra"
        >
            + Nuevo extra
        </button>
    </div>

    

    <!-- BUSCADOR Y FILTROS -->
    <div class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-6">
                <input 
                    type="text" 
                    id="buscadorExtras" 
                    class="form-control" 
                    placeholder="Buscar extra por nombre..."
                >
            </div>

            <div class="col-md-4">
                <select id="filtroTipoExtra" class="form-select">
                    <option value="">Todos los tipos</option>
                    <?php foreach($tiposArray as $tipoNombre) { ?>
                        <option value="<?php echo strtolower(htmlspecialchars($tipoNombre)); ?>">
                            <?php echo ucfirst(htmlspecialchars($tipoNombre)); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-2">
                <button type="button" class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- TABLA -->
    <table class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Precio</th>
                <th>Estado</th>
                <th width="300">Acciones</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($e = $extras->fetch_assoc()) { ?>
            <tr 
                class="fila-extra"
                data-nombre="<?php echo strtolower(htmlspecialchars($e['nombre'])); ?>"
                data-tipo="<?php echo strtolower(htmlspecialchars($e['tipo'])); ?>"
            >
                <td><?php echo $e['id']; ?></td>
                
                <td><?php echo htmlspecialchars($e['nombre']); ?></td>

                <td>
                    <span class="badge bg-primary">
                        <?php echo ucfirst(htmlspecialchars($e['tipo'])); ?>
                    </span>
                </td>

                <td>$ <?php echo number_format($e['precio'], 0, ',', '.'); ?></td>
                <td>
                    <?php if ($e['estado'] == 1) { ?>
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
                            data-bs-target="#modalEditarExtra<?php echo $e['id']; ?>"
                        >
                            Editar
                        </button>

                        <?php if ($e['estado'] == 1) { ?>
                            <a 
                                href="../controllers/extraController.php?accion=desactivar&id=<?php echo $e['id']; ?>" 
                                class="btn btn-sm btn-outline-secondary"
                            >
                                Desactivar
                            </a>
                        <?php } else { ?>
                            <a 
                                href="../controllers/extraController.php?accion=activar&id=<?php echo $e['id']; ?>" 
                                class="btn btn-sm btn-outline-success"
                            >
                                Activar
                            </a>
                        <?php } ?>

                        <button 
                            type="button"
                            class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEliminarExtra<?php echo $e['id']; ?>"
                        >
                            Eliminar
                        </button>

                    </div>
                </td>
            </tr>

            <!-- MODAL EDITAR EXTRA -->
            <div class="modal fade" id="modalEditarExtra<?php echo $e['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <form action="../controllers/extraController.php" method="POST">
                            <input type="hidden" name="accion" value="editar">
                            <input type="hidden" name="id" value="<?php echo $e['id']; ?>">

                            <div class="modal-header">
                                <h5 class="modal-title">Editar extra</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input 
                                        type="text" 
                                        name="nombre" 
                                        value="<?php echo htmlspecialchars($e['nombre']); ?>" 
                                        class="form-control" 
                                        required
                                    >
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Precio</label>
                                    <input 
                                        type="number" 
                                        name="precio" 
                                        value="<?php echo $e['precio']; ?>" 
                                        class="form-control" 
                                        required
                                    >
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tipo</label>
                                    <select name="tipo" class="form-select" required>
                                        <?php foreach($tiposArray as $tipoNombre) { ?>
                                            <option 
                                                value="<?php echo htmlspecialchars($tipoNombre); ?>" 
                                                <?php echo ($e['tipo'] == $tipoNombre) ? 'selected' : ''; ?>
                                            >
                                                <?php echo ucfirst(htmlspecialchars($tipoNombre)); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                                <button class="btn btn-success">
                                    Guardar cambios
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <!-- MODAL ELIMINAR EXTRA -->
            <div class="modal fade" id="modalEliminarExtra<?php echo $e['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Eliminar extra</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p class="mb-1">¿Seguro que deseas eliminar este extra?</p>
                            <strong><?php echo htmlspecialchars($e['nombre']); ?></strong>

                            <div class="alert alert-warning mt-3 mb-0">
                                Si este extra ya fue usado en ventas o reglas de productos, eliminarlo puede romper el historial.
                                Lo recomendado es <strong>desactivarlo</strong>.
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                Cancelar
                            </button>

                            <a 
                                href="../controllers/extraController.php?accion=eliminar&id=<?php echo $e['id']; ?>" 
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
<!-- MODAL CREAR EXTRA -->
<div class="modal fade" id="modalCrearExtra" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="../controllers/extraController.php" method="POST">
                <input type="hidden" name="accion" value="crear">

                <div class="modal-header">
                    <h5 class="modal-title">Nuevo extra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Precio</label>
                        <input type="number" name="precio" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Seleccione</option>
                            <?php foreach($tiposArray as $tipoNombre) { ?>
                                <option value="<?php echo htmlspecialchars($tipoNombre); ?>">
                                    <?php echo ucfirst(htmlspecialchars($tipoNombre)); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success">Guardar extra</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
function filtrarExtras() {
    const texto = document.getElementById('buscadorExtras').value.toLowerCase();
    const tipo = document.getElementById('filtroTipoExtra').value.toLowerCase();

    document.querySelectorAll('.fila-extra').forEach(fila => {
        const nombreFila = fila.dataset.nombre;
        const tipoFila = fila.dataset.tipo;

        const coincideNombre = nombreFila.includes(texto);
        const coincideTipo = tipo === '' || tipoFila === tipo;

        fila.style.display = (coincideNombre && coincideTipo) ? '' : 'none';
    });
}

function limpiarFiltros() {
    document.getElementById('buscadorExtras').value = '';
    document.getElementById('filtroTipoExtra').value = '';
    filtrarExtras();
}

document.getElementById('buscadorExtras').addEventListener('input', filtrarExtras);
document.getElementById('filtroTipoExtra').addEventListener('change', filtrarExtras);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>