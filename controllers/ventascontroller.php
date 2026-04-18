<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/conexion.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo "Error: no llegaron datos";
    exit();
}

$carrito = $data['carrito'];
$total = $data['total'];

if (empty($carrito)) {
    echo "Error: carrito vacío";
    exit();
}

if (!$conn->query("INSERT INTO ventas (total) VALUES ($total)")) {
    echo "Error en venta: " . $conn->error;
    exit();
}

$venta_id = $conn->insert_id;

foreach ($carrito as $item) {
    $producto_id = (int)$item['id'];
    $cantidad = (int)$item['cantidad'];
    $precioBase = (float)$item['precio_base'];

    if (!$conn->query("INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio)
                      VALUES ($venta_id, $producto_id, $cantidad, $precioBase)")) {
        echo "Error en detalle: " . $conn->error;
        exit();
    }

    $detalle_venta_id = $conn->insert_id;

    if (!empty($item['extras'])) {
        foreach ($item['extras'] as $extra) {
            $extra_id = (int)$extra['id'];
            $cantidad_extra = (int)$extra['cantidad'];

            for ($i = 1; $i <= $cantidad_extra; $i++) {
                $precio_extra = ($i <= (int)$extra['cantidad_incluida']) ? 0 : (float)$extra['precio'];

                if (!$conn->query("INSERT INTO detalle_venta_extras (detalle_venta_id, extra_id, precio)
                                  VALUES ($detalle_venta_id, $extra_id, $precio_extra)")) {
                    echo "Error en extra: " . $conn->error;
                    exit();
                }
            }
        }
    }
}

echo "Venta guardada correctamente";