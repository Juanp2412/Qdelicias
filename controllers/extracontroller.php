<?php
require_once "../config/conexion.php";

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($accion == "crear") {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $tipo = $_POST['tipo'];

    $conn->query("INSERT INTO extras (nombre, precio, tipo) 
                  VALUES ('$nombre', $precio, '$tipo')");
}

if ($accion == "editar") {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $tipo = $_POST['tipo'];

    $conn->query("UPDATE extras 
                  SET nombre='$nombre', precio=$precio, tipo='$tipo' 
                  WHERE id=$id");
}

if ($accion == "eliminar") {
    $id = $_GET['id'] ?? $_POST['id'] ?? 0;

    if ($id > 0) {
        $conn->query("DELETE FROM extras WHERE id=$id");
    }
}

if ($accion == "desactivar") {
    $id = $_GET['id'] ?? $_POST['id'] ?? 0;
    $conn->query("UPDATE extras SET estado = 0 WHERE id=$id");
}

if ($accion == "activar") {
    $id = $_GET['id'] ?? $_POST['id'] ?? 0;
    $conn->query("UPDATE extras SET estado = 1 WHERE id=$id");
}

header("Location: ../views/extras.php");
exit();