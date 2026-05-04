<?php
function noCache() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: 0");
}
function verificarLogin() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: ../index.php");
        exit();
    }
}

function soloAdmin() {
    verificarLogin();

    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
        header("Location: ../views/dashboard.php");
        exit();
    }
}
function validarUsuarioActivo() {
    global $conn;

    if (!isset($_SESSION['id_usuario'])) {
        header("Location: ../index.php");
        exit;
    }

    $id = $_SESSION['id_usuario'];

    $sql = "SELECT estado FROM usuarios WHERE id = $id";
    $resultado = $conn->query($sql);

    if ($resultado->num_rows == 0) {
        session_destroy();
        header("Location: ../index.php");
        exit;
    }

    $usuario = $resultado->fetch_assoc();

    if ($usuario['estado'] != 1) {
        session_destroy();
        header("Location: ../index.php");
        exit;
    }
}