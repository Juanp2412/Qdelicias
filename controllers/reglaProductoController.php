<?php
require_once "../config/conexion.php";

$accion = $_POST['accion'];

if ($accion == "crear") {
    $producto_id = (int) $_POST['producto_id'];
    $tipo_extra = $_POST['tipo_extra'];
    $cantidad_incluida = (int) $_POST['cantidad_incluida'];

    $verificar = $conn->query("
        SELECT id FROM producto_reglas_extras
        WHERE producto_id = $producto_id
        AND tipo_extra = '$tipo_extra'
    ");

    if ($verificar->num_rows > 0) {
        $fila = $verificar->fetch_assoc();
        $id = $fila['id'];

        $conn->query("
            UPDATE producto_reglas_extras
            SET cantidad_incluida = $cantidad_incluida
            WHERE id = $id
        ");
    } else {
        $conn->query("
            INSERT INTO producto_reglas_extras (producto_id, tipo_extra, cantidad_incluida)
            VALUES ($producto_id, '$tipo_extra', $cantidad_incluida)
        ");
    }
}

if ($accion == "editar") {
    $id = (int) $_POST['id'];
    $cantidad_incluida = (int) $_POST['cantidad_incluida'];

    $conn->query("
        UPDATE producto_reglas_extras
        SET cantidad_incluida = $cantidad_incluida
        WHERE id = $id
    ");
}

if ($accion == "eliminar") {
    $id = (int) $_POST['id'];

    $conn->query("DELETE FROM producto_reglas_extras WHERE id = $id");
}

header("Location: ../views/reglas_producto.php");
exit();