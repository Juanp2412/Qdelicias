<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/conexion.php";
require_once "../services/VentaService.php";

header("Content-Type: application/json; charset=utf-8");

function responderJson(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    responderJson(400, [
        'success' => false,
        'message' => 'Error: no llegaron datos',
    ]);
}

$carrito = $data['carrito'] ?? [];
$modo = $data['modo'] ?? 'guardar';

if (empty($carrito)) {
    responderJson(400, [
        'success' => false,
        'message' => 'Error: carrito vacío',
    ]);
}

try {
    $ventaService = new VentaService($conn);

    if ($modo === 'calcular') {
        $calculo = $ventaService->calcularVenta($carrito);

        responderJson(200, [
            'success' => true,
            'message' => 'Cálculo generado correctamente',
            'venta' => $calculo,
        ]);
    }

    $resultado = $ventaService->registrarVenta($carrito);

    responderJson(200, [
        'success' => true,
        'message' => 'Venta guardada correctamente',
        'venta_id' => $resultado['venta_id'],
        'venta' => $resultado['venta'],
    ]);
} catch (Throwable $e) {
    responderJson(500, [
        'success' => false,
        'message' => 'Error al guardar venta: ' . $e->getMessage(),
    ]);
}
