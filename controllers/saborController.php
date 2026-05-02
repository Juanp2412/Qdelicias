<?php
require_once "../config/conexion.php";

$accion = $_POST['accion'] ?? '';

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

if ($accion == "eliminar") {
    $id = $_POST['id'];

    $conn->query("DELETE FROM sabores WHERE id=$id");
}

header("Location: ../views/sabores.php");