<?php
require_once "../config/conexion.php";

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($accion == "crear") {
    $nombre = $_POST['nombre'];

    $conn->query("INSERT INTO categorias (nombre) VALUES ('$nombre')");
}

if ($accion == "editar") {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];

    $conn->query("UPDATE categorias SET nombre='$nombre' WHERE id=$id");
}

if ($accion == "eliminar") {
    $id = $_GET['id'] ?? $_POST['id'] ?? 0;

    $conn->query("DELETE FROM categorias WHERE id=$id");
}
if ($accion == "desactivar") {
    $id = $_GET['id'] ?? $_POST['id'] ?? 0;
    $conn->query("UPDATE categorias SET estado = 0 WHERE id=$id");
}

if ($accion == "activar") {
    $id = $_GET['id'] ?? $_POST['id'] ?? 0;
    $conn->query("UPDATE categorias SET estado = 1 WHERE id=$id");
}
header("Location: ../views/categorias.php");