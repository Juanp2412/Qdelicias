<?php
require_once "../config/conexion.php";

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($accion == "crear") {
    $nombre = trim($_POST['nombre']);
    $conn->query("INSERT INTO tipos_extra (nombre) VALUES ('$nombre')");
}
if ($accion == "desactivar") {
    $id = $_GET['id'] ?? $_POST['id'] ?? 0;
    $conn->query("UPDATE tipos_extra SET estado = 0 WHERE id = $id");
}

if ($accion == "activar") {
    $id = $_GET['id'] ?? $_POST['id'] ?? 0;
    $conn->query("UPDATE tipos_extra SET estado = 1 WHERE id = $id");
}

if ($accion == "editar") {
    $id = (int) $_POST['id'];
    $nombre = trim($_POST['nombre']);

    $nombreAnterior = $conn->query("SELECT nombre FROM tipos_extra WHERE id = $id");
    $filaAnterior = $nombreAnterior->fetch_assoc();
    $anterior = $filaAnterior['nombre'];

    $conn->query("UPDATE tipos_extra SET nombre = '$nombre' WHERE id = $id");
    $conn->query("UPDATE extras SET tipo = '$nombre' WHERE tipo = '$anterior'");
    $conn->query("UPDATE producto_reglas_extras SET tipo_extra = '$nombre' WHERE tipo_extra = '$anterior'");
}

if ($accion == "eliminar") {
    $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

    $nombreTipo = $conn->query("SELECT nombre FROM tipos_extra WHERE id = $id");
    $filaTipo = $nombreTipo->fetch_assoc();
    $nombre = $filaTipo['nombre'];

    $usoEnExtras = $conn->query("SELECT COUNT(*) AS total FROM extras WHERE tipo = '$nombre'");
    $filaUso = $usoEnExtras->fetch_assoc();

    if ($filaUso['total'] == 0) {
        $conn->query("DELETE FROM tipos_extra WHERE id = $id");
    }
}

header("Location: ../views/tipos_extra.php");
exit();