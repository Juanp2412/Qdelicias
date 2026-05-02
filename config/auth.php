<?php

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