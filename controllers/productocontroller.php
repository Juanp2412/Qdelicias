<?php
require_once "../config/conexion.php";

$accion = $_POST['accion'];

if ($accion == "crear") {

    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];

    $conn->query("INSERT INTO productos (nombre, precio) VALUES ('$nombre', $precio)");

}

if ($accion == "editar") {

    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];

    $conn->query("UPDATE productos SET nombre='$nombre', precio=$precio WHERE id=$id");

}

if ($accion == "eliminar") {

    $id = $_POST['id'];

    $conn->query("DELETE FROM productos WHERE id=$id");

}

header("Location: ../views/productos.php");