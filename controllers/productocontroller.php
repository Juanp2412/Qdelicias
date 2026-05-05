<?php
require_once "../config/conexion.php";

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($accion == "crear") {

    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $categoria_id = $_POST['categoria_id'];
    $tipo_configuracion = $_POST['tipo_configuracion'];
    $imagen = null;

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {

        $carpetaDestino = "../assets/img/productos/";

        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        $nombreOriginal = $_FILES['imagen']['name'];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extension, $extensionesPermitidas)) {
            $nombreImagen = uniqid("producto_") . "." . $extension;
            $rutaFinal = $carpetaDestino . $nombreImagen;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaFinal)) {
                $imagen = "assets/img/productos/" . $nombreImagen;
            }
        }
    }

    $conn->query("INSERT INTO productos (nombre, precio, imagen, categoria_id, tipo_configuracion) 
                  VALUES ('$nombre', $precio, '$imagen', $categoria_id, '$tipo_configuracion')");
}

if ($accion == "editar") {

    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $categoria_id = $_POST['categoria_id'];
    $tipo_configuracion = $_POST['tipo_configuracion'];

    $sqlImagen = "";

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {

        $carpetaDestino = "../assets/img/productos/";

        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        $nombreOriginal = $_FILES['imagen']['name'];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extension, $extensionesPermitidas)) {
            $nombreImagen = uniqid("producto_") . "." . $extension;
            $rutaFinal = $carpetaDestino . $nombreImagen;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaFinal)) {
                $imagen = "assets/img/productos/" . $nombreImagen;
                $sqlImagen = ", imagen='$imagen'";
            }
        }
    }

    $conn->query("UPDATE productos 
                  SET nombre='$nombre', 
                      precio=$precio, 
                      categoria_id=$categoria_id, 
                      tipo_configuracion='$tipo_configuracion'
                      $sqlImagen
                  WHERE id=$id");
}

if ($accion == "eliminar") {

    $id = $_GET['id'] ?? $_POST['id'] ?? 0;

    $conn->query("DELETE FROM productos WHERE id=$id");
}

if ($accion == "desactivar") {

    $id = $_GET['id'] ?? $_POST['id'] ?? 0;

    $conn->query("UPDATE productos SET estado = 0 WHERE id=$id");
}

if ($accion == "activar") {

    $id = $_GET['id'] ?? $_POST['id'] ?? 0;

    $conn->query("UPDATE productos SET estado = 1 WHERE id=$id");
}
header("Location: ../views/productos.php");