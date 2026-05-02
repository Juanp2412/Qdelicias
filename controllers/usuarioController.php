<?php
require_once "../config/conexion.php";

$accion = $_POST['accion'] ?? '';

if ($accion == "crear") {

    $nombre = $_POST['nombre'];
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['password'];
    $rol = $_POST['rol'];

    $conn->query("
        INSERT INTO usuarios (nombre, usuario, contraseña, rol)
        VALUES ('$nombre', '$usuario', '$contraseña', '$rol')
    ");
}

if ($accion == "editar") {

    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $usuario = $_POST['usuario'];
    $rol = $_POST['rol'];

    $sqlPassword = "";

    if (!empty($_POST['password'])) {
        $contraseña = $_POST['password'];
        $sqlPassword = ", contraseña='$contraseña'";
    }

    $conn->query("
        UPDATE usuarios
        SET nombre='$nombre',
            usuario='$usuario',
            rol='$rol'
            $sqlPassword
        WHERE id=$id
    ");
}

if ($accion == "eliminar") {

    $id = $_POST['id'];

    $conn->query("DELETE FROM usuarios WHERE id=$id");
}

header("Location: ../views/usuarios.php");
exit();