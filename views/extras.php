<?php
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

soloAdmin();

$extras = $conn->query("SELECT * FROM extras ORDER BY id DESC");
$tiposExtras = $conn->query("SELECT * FROM tipos_extra ORDER BY nombre ASC");

$tiposArray = [];
$tiposConsulta = $conn->query("SELECT * FROM tipos_extra ORDER BY nombre ASC");
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
</head>

<body class="bg-light">

<?php include 'layout/header.php'; ?>

<div class="container mt-4">

    <h3>Gestión de Extras</h3>

    <!-- FORMULARIO CREAR -->
    <div class="card p-3 mb-3">
        <form action="../controllers/extraController.php" method="POST">
            <input type="hidden" name="accion" value="crear">

            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre del extra" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Precio</label>
                    <input type="number" name="precio" class="form-control" placeholder="Precio" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select" required>
                        <option value="">Seleccione tipo</option>
                        <?php while($tipo = $tiposExtras->fetch_assoc()) { ?>
                            <option value="<?php echo htmlspecialchars($tipo['nombre']); ?>">
                                <?php echo ucfirst(htmlspecialchars($tipo['nombre'])); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-success w-100">Guardar extra</button>
                </div>
            </div>
        </form>
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
                <th width="220">Acciones</th>
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
                    <button 
                        type="button"
                        class="btn btn-warning btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditarExtra<?php echo $e['id']; ?>"
                    >
                        Editar
                    </button>

                    <form action="../controllers/extraController.php" method="POST" style="display:inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                        <button class="btn btn-danger btn-sm">X</button>
                    </form>
                </td>
            </tr>

            <!-- MODAL EDITAR EXTRA -->
            <div class="modal fade" id="modalEditarExtra<?php echo $e['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
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

        <?php } ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-secondary">Volver</a>

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