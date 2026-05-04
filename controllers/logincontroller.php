<?php
session_start();
require_once "../config/conexion.php";

$usuario = $_POST['usuario'];
$password = $_POST['password'];

$sql = "SELECT * FROM usuarios 
        WHERE usuario='$usuario' 
        AND contraseña='$password'
        AND estado = 1";
$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    $user = $resultado->fetch_assoc();

    $_SESSION['usuario'] = $user['usuario'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['nombre'] = $user['nombre'] ?? $user['usuario'];

    if ($user['rol'] == 'vendedor') {
    header("Location: ../views/ventas.php");
    } else {
        header("Location: ../views/dashboard.php");
}
} else {
    echo "Usuario o contraseña incorrectos";
}