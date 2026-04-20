<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/conexion.php";
require_once "../services/VentaService.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo "Error: no llegaron datos";
    exit();
}

$carrito = $data['carrito'] ?? [];

if (empty($carrito)) {
    echo "Error: carrito vacío";
    exit();
}

try {
    $ventaService = new VentaService($conn);
    $ventaService->registrarVenta($carrito);

    echo "Venta guardada correctamente";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Error al guardar venta: " . $e->getMessage();
}
