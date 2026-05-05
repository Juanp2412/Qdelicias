<?php
require_once "../config/conexion.php";

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($accion == "crear") {
    $nombre = $_POST['nombre'];
    $tipo = $_POST['tipo'];

    $conn->query("INSERT INTO sabores (nombre, tipo, activo) VALUES ('$nombre', '$tipo', 1)");
}

if ($accion == "editar") {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $tipo = $_POST['tipo'];
    $activo = $_POST['activo'];

    $conn->query("UPDATE sabores SET nombre='$nombre', tipo='$tipo', activo=$activo WHERE id=$id");
}
if ($accion == "desactivar") {
    $id = $_GET['id'] ?? $_POST['id'] ?? 0;
    $conn->query("UPDATE sabores SET activo = 0 WHERE id=$id");
}

if ($accion == "activar") {
    $id = $_GET['id'] ?? $_POST['id'] ?? 0;
    $conn->query("UPDATE sabores SET activo = 1 WHERE id=$id");
}
if ($accion == "eliminar") {
    $id = $_GET['id'] ?? $_POST['id'] ?? 0;

    if ($id > 0) {
        $conn->query("DELETE FROM sabores WHERE id=$id");
    }
}

header("Location: ../views/sabores.php");