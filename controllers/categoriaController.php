<?php
require_once "../config/conexion.php";

$accion = $_POST['accion'] ?? '';

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
    $id = $_POST['id'];

    $conn->query("DELETE FROM categorias WHERE id=$id");
}

header("Location: ../views/categorias.php");