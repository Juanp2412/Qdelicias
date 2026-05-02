<?php
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

soloAdmin();

$productos = $conn->query("
    SELECT p.*, c.nombre AS categoria
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    ORDER BY p.id DESC
");

$categorias = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-4">
<?php include 'layout/header.php'; ?>

    <h3>Gestión de Productos</h3>

    <!-- FORMULARIO CREAR -->
    <div class="card p-3 mb-4">
        <form action="../controllers/productoController.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="crear">

            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre del producto" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Precio</label>
                    <input type="number" name="precio" class="form-control" placeholder="Precio" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" class="form-select" required>
                        <option value="">Seleccione</option>
                        <?php while ($cat = $categorias->fetch_assoc()) { ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo $cat['nombre']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select name="tipo_configuracion" class="form-select" required>
                        <option value="simple">Simple</option>
                        <option value="extras">Extras</option>
                        <option value="sabores">Sabores</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Imagen</label>
                    <input type="file" name="imagen" class="form-control" accept="image/*">
                </div>

                <div class="col-md-2 mt-3">
                    <button class="btn btn-success w-100">Guardar</button>
                </div>
            </div>
        </form>
    </div>
    <div class="card p-3 mb-3">
    <div class="row g-2">
        <div class="col-md-5">
            <input 
                type="text" 
                id="buscadorProductos" 
                class="form-control" 
                placeholder="Buscar producto por nombre..."
            >
        </div>

        <div class="col-md-3">
            <select id="filtroCategoria" class="form-select">
                <option value="">Todas las categorías</option>
                <?php
                $categoriasFiltro = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
                while ($catFiltro = $categoriasFiltro->fetch_assoc()) {
                ?>
                    <option value="<?php echo strtolower($catFiltro['nombre']); ?>">
                        <?php echo $catFiltro['nombre']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-3">
            <select id="filtroTipo" class="form-select">
                <option value="">Todos los tipos</option>
                <option value="simple">Simple</option>
                <option value="extras">Extras</option>
                <option value="sabores">Sabores</option>
            </select>
        </div>
    </div>
</div>
    

    <!-- TABLA -->
    <table class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Categoría</th>
                <th>Tipo</th>
                <th width="260">Acciones</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($p = $productos->fetch_assoc()) { ?>
            <tr 
                class="fila-producto"
                data-nombre="<?php echo strtolower($p['nombre']); ?>"
                data-categoria="<?php echo strtolower($p['categoria'] ?? 'sin categoría'); ?>"
                data-tipo="<?php echo strtolower($p['tipo_configuracion']); ?>"
            >
                <td><?php echo $p['id']; ?></td>

                <td>
                    <?php if (!empty($p['imagen'])) { ?>
                        <img src="../<?php echo $p['imagen']; ?>" width="70" height="55" style="object-fit: cover; border-radius: 8px;">
                    <?php } else { ?>
                        <span class="text-muted">Sin imagen</span>
                    <?php } ?>
                </td>

                <td><?php echo $p['nombre']; ?></td>
                <td>$ <?php echo number_format($p['precio'], 0, ',', '.'); ?></td>
                <td><?php echo $p['categoria'] ?? 'Sin categoría'; ?></td>
                <td><?php echo $p['tipo_configuracion']; ?></td>

                <td>
                    <button 
                        type="button"
                        class="btn btn-warning btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditar<?php echo $p['id']; ?>"
                    >
                        Editar
                    </button>

                    <a href="producto_sabores.php?producto_id=<?php echo $p['id']; ?>" class="btn btn-info btn-sm">
                        Sabores
                    </a>

                    <form action="../controllers/productoController.php" method="POST" style="display:inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">

                        <button class="btn btn-danger btn-sm">X</button>
                    </form>
                </td>
            </tr>

            <!-- MODAL EDITAR -->
            <div class="modal fade" id="modalEditar<?php echo $p['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <form action="../controllers/productoController.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="accion" value="editar">
                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">

                            <div class="modal-header">
                                <h5 class="modal-title">Editar producto</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="mb-2">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="nombre" class="form-control" value="<?php echo $p['nombre']; ?>" required>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Precio</label>
                                    <input type="number" name="precio" class="form-control" value="<?php echo $p['precio']; ?>" required>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Categoría</label>
                                    <select name="categoria_id" class="form-select" required>
                                        <?php
                                        $categoriasModal = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
                                        while ($catModal = $categoriasModal->fetch_assoc()) {
                                        ?>
                                            <option value="<?php echo $catModal['id']; ?>" <?php echo ($catModal['id'] == $p['categoria_id']) ? 'selected' : ''; ?>>
                                                <?php echo $catModal['nombre']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Tipo configuración</label>
                                    <select name="tipo_configuracion" class="form-select" required>
                                        <option value="simple" <?php echo ($p['tipo_configuracion'] == 'simple') ? 'selected' : ''; ?>>Simple</option>
                                        <option value="extras" <?php echo ($p['tipo_configuracion'] == 'extras') ? 'selected' : ''; ?>>Extras</option>
                                        <option value="sabores" <?php echo ($p['tipo_configuracion'] == 'sabores') ? 'selected' : ''; ?>>Sabores</option>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Cambiar imagen</label>
                                    <input type="file" name="imagen" class="form-control" accept="image/*">
                                </div>

                                <?php if (!empty($p['imagen'])) { ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Imagen actual:</small><br>
                                        <img src="../<?php echo $p['imagen']; ?>" width="120" height="90" style="object-fit: cover; border-radius: 8px;">
                                    </div>
                                <?php } ?>

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

<script>
function filtrarProductos() {
    const texto = document.getElementById('buscadorProductos').value.toLowerCase();
    const categoria = document.getElementById('filtroCategoria').value.toLowerCase();
    const tipo = document.getElementById('filtroTipo').value.toLowerCase();

    document.querySelectorAll('.fila-producto').forEach(fila => {
        const nombreFila = fila.dataset.nombre;
        const categoriaFila = fila.dataset.categoria;
        const tipoFila = fila.dataset.tipo;

        const coincideNombre = nombreFila.includes(texto);
        const coincideCategoria = categoria === '' || categoriaFila === categoria;
        const coincideTipo = tipo === '' || tipoFila === tipo;

        fila.style.display = (coincideNombre && coincideCategoria && coincideTipo) ? '' : 'none';
    });
}

document.getElementById('buscadorProductos').addEventListener('input', filtrarProductos);
document.getElementById('filtroCategoria').addEventListener('change', filtrarProductos);
document.getElementById('filtroTipo').addEventListener('change', filtrarProductos);
</script>
</body>
</html>