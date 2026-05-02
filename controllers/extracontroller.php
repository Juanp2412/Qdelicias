<?php
require_once "../config/conexion.php";

$accion = $_POST['accion'];

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
    $id = $_POST['id'];
    $conn->query("DELETE FROM extras WHERE id=$id");
}

header("Location: ../views/extras.php");
exit();