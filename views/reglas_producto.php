<?php
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

soloAdmin();

$productos = $conn->query("SELECT * FROM productos WHERE estado = 1 AND tipo_configuracion = 'extras' ORDER BY nombre ASC");
$tiposExtras = $conn->query("SELECT * FROM tipos_extra WHERE estado = 1 ORDER BY nombre ASC");

$productosFiltro = $conn->query("SELECT * FROM productos WHERE estado = 1 AND tipo_configuracion = 'extras' ORDER BY nombre ASC");
$tiposFiltro = $conn->query("SELECT * FROM tipos_extra WHERE estado = 1 ORDER BY nombre ASC");

$reglas = $conn->query("
    SELECT r.*, p.nombre AS producto_nombre
    FROM producto_reglas_extras r
    INNER JOIN productos p ON p.id = r.producto_id
    ORDER BY p.nombre ASC, r.tipo_extra ASC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reglas por producto</title>
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
            <h3 class="mb-0">Reglas de extras</h3>
            <small class="text-muted">Define cuántos extras incluye cada producto</small>
        </div>

        <button 
            class="btn btn-success"
            data-bs-toggle="modal"
            data-bs-target="#modalCrearRegla"
        >
            + Nueva regla
        </button>
    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-5">
                <input 
                    type="text" 
                    id="buscadorReglas" 
                    class="form-control" 
                    placeholder="Buscar producto..."
                >
            </div>

            <div class="col-md-4">
                <select id="filtroTipoRegla" class="form-select">
                    <option value="">Todos los tipos de extra</option>
                    <?php while($tf = $tiposFiltro->fetch_assoc()) { ?>
                        <option value="<?php echo strtolower(htmlspecialchars($tf['nombre'])); ?>">
                            <?php echo ucfirst(htmlspecialchars($tf['nombre'])); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-3">
                <button type="button" class="btn btn-secondary w-100" onclick="limpiarFiltrosReglas()">
                    Limpiar filtros
                </button>
            </div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="card border-0 shadow-sm p-3">
        <h5 class="mb-3">Reglas actuales</h5>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Tipo extra</th>
                        <th>Cantidad incluida</th>
                        <th width="220">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($reglas->num_rows == 0) { ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            No hay reglas configuradas
                        </td>
                    </tr>
                <?php } ?>

                <?php while($r = $reglas->fetch_assoc()) { ?>
                    <tr 
                        class="fila-regla"
                        data-producto="<?php echo strtolower(htmlspecialchars($r['producto_nombre'])); ?>"
                        data-tipo="<?php echo strtolower(htmlspecialchars($r['tipo_extra'])); ?>"
                    >
                        <td>
                            <span class="text-dark">
                                <?php echo htmlspecialchars($r['producto_nombre']); ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge bg-primary">
                                <?php echo ucfirst(htmlspecialchars($r['tipo_extra'])); ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge bg-success fs-6">
                                <?php echo $r['cantidad_incluida']; ?>
                            </span>
                        </td>

                        <td>
                            <button 
                                type="button"
                                class="btn btn-sm btn-outline-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#modalRegla<?php echo $r['id']; ?>"
                            >
                                Editar
                            </button>

                            <button 
                                type="button"
                                class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEliminarRegla<?php echo $r['id']; ?>"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>

                    <!-- MODAL EDITAR -->
                    <div class="modal fade" id="modalRegla<?php echo $r['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">

                                <form action="../controllers/reglaProductoController.php" method="POST">
                                    <input type="hidden" name="accion" value="editar">
                                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Editar regla</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">

                                        <div class="mb-3">
                                            <label class="form-label">Producto</label>
                                            <input 
                                                type="text" 
                                                class="form-control" 
                                                value="<?php echo htmlspecialchars($r['producto_nombre']); ?>" 
                                                disabled
                                            >
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Tipo de extra</label>
                                            <input 
                                                type="text" 
                                                class="form-control" 
                                                value="<?php echo ucfirst(htmlspecialchars($r['tipo_extra'])); ?>" 
                                                disabled
                                            >
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Cantidad incluida</label>
                                            <input 
                                                type="number" 
                                                name="cantidad_incluida" 
                                                value="<?php echo $r['cantidad_incluida']; ?>" 
                                                min="0" 
                                                class="form-control" 
                                                required
                                            >
                                        </div>

                                        <div class="alert alert-info">
                                            Solo se edita la cantidad incluida. Si necesitas cambiar producto o tipo, elimina esta regla y crea una nueva.
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
                    <!-- MODAL ELIMINAR REGLA -->
                    <div class="modal fade" id="modalEliminarRegla<?php echo $r['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">

                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Eliminar regla</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <p class="mb-1">¿Seguro que deseas eliminar esta regla?</p>

                                    <span>
                                        <?php echo htmlspecialchars($r['producto_nombre']); ?> —
                                        <?php echo ucfirst(htmlspecialchars($r['tipo_extra'])); ?>
                                    </span>

                                    <div class="alert alert-warning mt-3 mb-0">
                                        Esta regla controla cuántos extras se incluyen gratis en este producto.
                                        Si la eliminas, el cálculo de extras puede cambiar en ventas nuevas.
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                        Cancelar
                                    </button>

                                    <a 
                                        href="../controllers/reglaProductoController.php?accion=eliminar&id=<?php echo $r['id']; ?>"
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
        </div>
    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Volver</a>

</div>
<!-- MODAL CREAR REGLA -->
<div class="modal fade" id="modalCrearRegla" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form action="../controllers/reglaProductoController.php" method="POST">
                <input type="hidden" name="accion" value="crear">

                <div class="modal-header">
                    <h5 class="modal-title">Nueva regla de extras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Producto</label>
                            <select name="producto_id" class="form-select" required>
                                <option value="">Seleccione producto</option>
                                <?php while($p = $productos->fetch_assoc()) { ?>
                                    <option value="<?php echo $p['id']; ?>">
                                        <?php echo htmlspecialchars($p['nombre']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tipo de extra</label>
                            <select name="tipo_extra" class="form-select" required>
                                <option value="">Seleccione tipo</option>
                                <?php while($t = $tiposExtras->fetch_assoc()) { ?>
                                    <option value="<?php echo htmlspecialchars($t['nombre']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($t['nombre'])); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Cantidad incluida</label>
                            <input 
                                type="number" 
                                name="cantidad_incluida" 
                                class="form-control" 
                                min="0" 
                                placeholder="Ej: 1"
                                required
                            >
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        Ejemplo: Mini waffle + Toppings + 1 significa que incluye 1 topping gratis.
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success">Guardar regla</button>
                </div>
            </form>

        </div>
    </div>
</div>
<script>
function filtrarReglas() {
    const texto = document.getElementById('buscadorReglas').value.toLowerCase();
    const tipo = document.getElementById('filtroTipoRegla').value.toLowerCase();

    document.querySelectorAll('.fila-regla').forEach(fila => {
        const productoFila = fila.dataset.producto;
        const tipoFila = fila.dataset.tipo;

        const coincideProducto = productoFila.includes(texto);
        const coincideTipo = tipo === '' || tipoFila === tipo;

        fila.style.display = (coincideProducto && coincideTipo) ? '' : 'none';
    });
}

function limpiarFiltrosReglas() {
    document.getElementById('buscadorReglas').value = '';
    document.getElementById('filtroTipoRegla').value = '';
    filtrarReglas();
}

document.getElementById('buscadorReglas').addEventListener('input', filtrarReglas);
document.getElementById('filtroTipoRegla').addEventListener('change', filtrarReglas);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>