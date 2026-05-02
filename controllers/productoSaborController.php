<?php
require_once "../config/conexion.php";

$producto_id = $_POST['producto_id'] ?? null;
$sabores = $_POST['sabores'] ?? [];

if ($producto_id) {

    $conn->query("DELETE FROM producto_sabores WHERE producto_id = $producto_id");

    foreach ($sabores as $sabor_id) {
        $conn->query("INSERT INTO producto_sabores (producto_id, sabor_id) VALUES ($producto_id, $sabor_id)");
    }
}

header("Location: ../views/producto_sabores.php?producto_id=" . $producto_id);