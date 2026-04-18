<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

$extras = $conn->query("SELECT * FROM extras ORDER BY id DESC");
$tiposExtras = $conn->query("SELECT * FROM tipos_extra ORDER BY nombre ASC");
$tiposExtrasEdicion = $conn->query("SELECT * FROM tipos_extra ORDER BY nombre ASC");

$tiposArray = [];
$tiposConsulta = $conn->query("SELECT * FROM tipos_extra ORDER BY nombre ASC");
while ($tipo = $tiposConsulta->fetch_assoc()) {
    $tiposArray[] = $tipo['nombre'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Extras</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php include 'layout/header.php'; ?>

<div class="container mt-4">

    <h3>Gestión de Extras</h3>

    <div class="card p-3 mb-3">
        <form action="../controllers/extraController.php" method="POST">
            <input type="hidden" name="accion" value="crear">

            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre del extra" required>
                </div>

                <div class="col-md-2">
                    <input type="number" name="precio" class="form-control" placeholder="Precio" required>
                </div>

                <div class="col-md-3">
                    <select name="tipo" class="form-control" required>
                        <option value="">Tipo</option>
                        <?php while($tipo = $tiposExtras->fetch_assoc()) { ?>
                            <option value="<?php echo htmlspecialchars($tipo['nombre']); ?>">
                                <?php echo ucfirst(htmlspecialchars($tipo['nombre'])); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-success w-100">Guardar</button>
                </div>
            </div>
        </form>
    </div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Precio</th>
                <th width="250">Acciones</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($e = $extras->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $e['id']; ?></td>
                
                <td><?php echo htmlspecialchars($e['nombre']); ?></td>

                <td>
                    <span class="badge bg-primary">
                        <?php echo ucfirst(htmlspecialchars($e['tipo'])); ?>
                    </span>
                </td>

                <td>$ <?php echo number_format($e['precio'],0,',','.'); ?></td>

                <td>
                    <form action="../controllers/extraController.php" method="POST" style="display:inline;">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="id" value="<?php echo $e['id']; ?>">

                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($e['nombre']); ?>" required>
                        <input type="number" name="precio" value="<?php echo $e['precio']; ?>" required>

                        <select name="tipo" required>
                            <?php foreach($tiposArray as $tipoNombre) { ?>
                                <option value="<?php echo htmlspecialchars($tipoNombre); ?>" <?php if($e['tipo'] == $tipoNombre) echo 'selected'; ?>>
                                    <?php echo ucfirst(htmlspecialchars($tipoNombre)); ?>
                                </option>
                            <?php } ?>
                        </select>

                        <button class="btn btn-warning btn-sm">Editar</button>
                    </form>

                    <form action="../controllers/extraController.php" method="POST" style="display:inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                        <button class="btn btn-danger btn-sm">X</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

</div>

</body>
</html>