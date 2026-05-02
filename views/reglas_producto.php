<?php
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

soloAdmin();

$productos = $conn->query("SELECT * FROM productos ORDER BY nombre ASC");
$tiposExtras = $conn->query("SELECT * FROM tipos_extra ORDER BY nombre ASC");

$productosFiltro = $conn->query("SELECT * FROM productos ORDER BY nombre ASC");
$tiposFiltro = $conn->query("SELECT * FROM tipos_extra ORDER BY nombre ASC");

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
</head>

<body class="bg-light">

<?php include 'layout/header.php'; ?>

<div class="container mt-4">

    <h3 class="mb-3">Reglas de extras por producto</h3>

    <!-- CREAR REGLA -->
    <div class="card p-3 mb-4">
        <form action="../controllers/reglaProductoController.php" method="POST">
            <input type="hidden" name="accion" value="crear">

            <div class="row g-2">
                <div class="col-md-4">
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

                <div class="col-md-3">
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

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-success w-100">Guardar</button>
                </div>
            </div>

            <small class="text-muted d-block mt-2">
                Ejemplo: Mini waffle + Toppings + 1 significa que incluye 1 topping gratis.
            </small>
        </form>
    </div>

    <!-- FILTROS -->
    <div class="card p-3 mb-3">
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
    <div class="card p-3">
        <h5 class="mb-3">Reglas actuales</h5>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
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
                            <strong><?php echo htmlspecialchars($r['producto_nombre']); ?></strong>
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
                                class="btn btn-warning btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#modalRegla<?php echo $r['id']; ?>"
                            >
                                Editar
                            </button>

                            <form action="../controllers/reglaProductoController.php" method="POST" style="display:inline;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                <button class="btn btn-danger btn-sm">X</button>
                            </form>
                        </td>
                    </tr>

                    <!-- MODAL EDITAR -->
                    <div class="modal fade" id="modalRegla<?php echo $r['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
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

                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Volver</a>

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