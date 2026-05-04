<?php
session_start();
require_once "../config/conexion.php";
require_once "../config/auth.php";

soloAdmin();

$categorias = $conn->query("SELECT * FROM categorias ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías</title>
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
            <h3 class="mb-0">Categorías</h3>
            <small class="text-muted">Organiza los productos del negocio</small>
        </div>

        <button 
            class="btn btn-success"
            data-bs-toggle="modal"
            data-bs-target="#modalCrearCategoria"
        >
            + Nueva categoría
        </button>
    </div>

    <div class="card border-0 shadow-sm p-3 mb-3">
        <input 
            type="text" 
            id="buscarCategoria" 
            class="form-control" 
            placeholder="Buscar categoría por nombre..."
        >
    </div>

    <table class="table table-hover table-bordered align-middle bg-white shadow-sm">
        <thead class="table-dark">
            <tr>
                <th width="80">ID</th>
                <th>Nombre</th>
                <th width="120">Estado</th>
                <th width="280">Acciones</th>
            </tr>
        </thead>

        <tbody id="tablaCategorias">
        <?php while ($cat = $categorias->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $cat['id']; ?></td>

                <td>
                    <span class="text-dark"><?php echo htmlspecialchars($cat['nombre']); ?></span>
                </td>

                <td>
                    <?php if ($cat['estado'] == 1) { ?>
                        <span class="badge bg-success">Activa</span>
                    <?php } else { ?>
                        <span class="badge bg-secondary">Inactiva</span>
                    <?php } ?>
                </td>

                <td>
                    <div class="d-flex gap-2 flex-wrap">

                        <button 
                            class="btn btn-sm btn-outline-warning"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditarCategoria<?php echo $cat['id']; ?>"
                        >
                            Editar
                        </button>

                        <?php if ($cat['estado'] == 1) { ?>
                            <a 
                                href="../controllers/categoriaController.php?accion=desactivar&id=<?php echo $cat['id']; ?>"
                                class="btn btn-sm btn-outline-secondary"
                            >
                                Desactivar
                            </a>
                        <?php } else { ?>
                            <a 
                                href="../controllers/categoriaController.php?accion=activar&id=<?php echo $cat['id']; ?>"
                                class="btn btn-sm btn-outline-success"
                            >
                                Activar
                            </a>
                        <?php } ?>

                        <button 
                            class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEliminarCategoria<?php echo $cat['id']; ?>"
                        >
                            Eliminar
                        </button>

                    </div>
                </td>
            </tr>

            <!-- MODAL EDITAR -->
            <div class="modal fade" id="modalEditarCategoria<?php echo $cat['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <form action="../controllers/categoriaController.php" method="POST">
                            <input type="hidden" name="accion" value="editar">
                            <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">

                            <div class="modal-header">
                                <h5 class="modal-title">Editar categoría</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <label class="form-label">Nombre</label>
                                <input 
                                    type="text" 
                                    name="nombre" 
                                    class="form-control" 
                                    value="<?php echo htmlspecialchars($cat['nombre']); ?>" 
                                    required
                                >
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <button class="btn btn-success">Guardar cambios</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <!-- MODAL ELIMINAR -->
            <div class="modal fade" id="modalEliminarCategoria<?php echo $cat['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Eliminar categoría</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p class="mb-1">¿Seguro que deseas eliminar esta categoría?</p>
                            <strong><?php echo htmlspecialchars($cat['nombre']); ?></strong>

                            <div class="alert alert-warning mt-3 mb-0">
                                Si esta categoría tiene productos asociados, eliminarla puede causar errores.
                                Lo recomendado es <strong>desactivarla</strong>.
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>

                            <a 
                                href="../controllers/categoriaController.php?accion=eliminar&id=<?php echo $cat['id']; ?>"
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

<!-- MODAL CREAR -->
<div class="modal fade" id="modalCrearCategoria" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="../controllers/categoriaController.php" method="POST">
                <input type="hidden" name="accion" value="crear">

                <div class="modal-header">
                    <h5 class="modal-title">Nueva categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label class="form-label">Nombre</label>
                    <input 
                        type="text" 
                        name="nombre" 
                        class="form-control" 
                        placeholder="Ej: Waffles, Alitas, Jugos..." 
                        required
                    >
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success">Guardar categoría</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.getElementById('buscarCategoria').addEventListener('keyup', function () {
    let filtro = this.value.toLowerCase();
    let filas = document.querySelectorAll('#tablaCategorias tr');

    filas.forEach(function (fila) {
        let texto = fila.innerText.toLowerCase();
        fila.style.display = texto.includes(filtro) ? '' : 'none';
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>