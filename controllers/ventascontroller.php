<?php
/*
|--------------------------------------------------------------------------
| Archivo: ventascontroller.php
|--------------------------------------------------------------------------
| Propósito:
| Procesa y guarda las ventas enviadas desde la vista de caja. Recibe el
| carrito en formato JSON, registra la venta principal, guarda los
| productos vendidos, almacena los extras relacionados y registra el
| desglose de pagos por método.
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/conexion.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo "Error: no llegaron datos";
    exit();
}

$carrito = $data['carrito'] ?? [];
$total = isset($data['total']) ? (float)$data['total'] : 0;
$pagos = $data['pagos'] ?? [];

if (empty($carrito)) {
    echo "Error: carrito vacío";
    exit();
}

if ($total <= 0) {
    echo "Error: total inválido";
    exit();
}

$efectivo = isset($pagos['efectivo']) ? (float)$pagos['efectivo'] : 0;
$nequi = isset($pagos['nequi']) ? (float)$pagos['nequi'] : 0;
$daviplata = isset($pagos['daviplata']) ? (float)$pagos['daviplata'] : 0;
$transferencia = isset($pagos['transferencia']) ? (float)$pagos['transferencia'] : 0;

$totalPagado = $efectivo + $nequi + $daviplata + $transferencia;

if ($totalPagado <= 0) {
    echo "Error: no se registró ningún pago";
    exit();
}

if (abs($totalPagado - $total) > 0.01) {
    echo "Error: la suma de pagos no coincide con el total de la venta";
    exit();
}

$metodosUsados = [];

if ($efectivo > 0) $metodosUsados[] = 'efectivo';
if ($nequi > 0) $metodosUsados[] = 'nequi';
if ($daviplata > 0) $metodosUsados[] = 'daviplata';
if ($transferencia > 0) $metodosUsados[] = 'transferencia';

$metodo_pago = count($metodosUsados) === 1 ? $metodosUsados[0] : 'mixto';

$conn->begin_transaction();

try {
    $stmtVenta = $conn->prepare("INSERT INTO ventas (total, metodo_pago) VALUES (?, ?)");
    $stmtVenta->bind_param("ds", $total, $metodo_pago);

    if (!$stmtVenta->execute()) {
        throw new Exception("Error al guardar la venta: " . $stmtVenta->error);
    }

    $venta_id = $conn->insert_id;

    $stmtDetalle = $conn->prepare("INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)");
    $stmtExtra = $conn->prepare("INSERT INTO detalle_venta_extras (detalle_venta_id, extra_id, precio) VALUES (?, ?, ?)");
    $stmtSabor = $conn->prepare("INSERT INTO detalle_venta_sabores (detalle_venta_id, sabor_id, cantidad) VALUES (?, ?, ?)");   
    $stmtPago = $conn->prepare("INSERT INTO venta_pagos (venta_id, metodo_pago, monto) VALUES (?, ?, ?)");

    foreach ($carrito as $item) {
        $producto_id = (int)$item['id'];
        $cantidad = (int)$item['cantidad'];
        $precioBase = (float)$item['precio_base'];

        $stmtDetalle->bind_param("iiid", $venta_id, $producto_id, $cantidad, $precioBase);

        if (!$stmtDetalle->execute()) {
            throw new Exception("Error al guardar detalle: " . $stmtDetalle->error);
        }

        $detalle_venta_id = $conn->insert_id;

        if (!empty($item['extras'])) {
            foreach ($item['extras'] as $extra) {
                $extra_id = (int)$extra['id'];
                $cantidad_extra = (int)$extra['cantidad'];
                $cantidad_incluida = (int)$extra['cantidad_incluida'];
                $precio_extra_base = (float)$extra['precio'];

                for ($i = 1; $i <= $cantidad_extra; $i++) {
                    $precio_extra = ($i <= $cantidad_incluida) ? 0 : $precio_extra_base;

                    $stmtExtra->bind_param("iid", $detalle_venta_id, $extra_id, $precio_extra);

                    if (!$stmtExtra->execute()) {
                        throw new Exception("Error al guardar extra: " . $stmtExtra->error);
                    }
                }
            }
        }
        if (!empty($item['sabores'])) {
            foreach ($item['sabores'] as $sabor) {
                $sabor_id = (int)$sabor['id'];
                $cantidad_sabor = (int)$sabor['cantidad'];

                $stmtSabor->bind_param("iii", $detalle_venta_id, $sabor_id, $cantidad_sabor);

                if (!$stmtSabor->execute()) {
                    throw new Exception("Error al guardar sabor: " . $stmtSabor->error);
                }
            }
        }
    }

    $pagosRegistrar = [
        'efectivo' => $efectivo,
        'nequi' => $nequi,
        'daviplata' => $daviplata,
        'transferencia' => $transferencia
    ];

    foreach ($pagosRegistrar as $metodo => $monto) {
        if ($monto > 0) {
            $stmtPago->bind_param("isd", $venta_id, $metodo, $monto);

            if (!$stmtPago->execute()) {
                throw new Exception("Error al guardar pago: " . $stmtPago->error);
            }
        }
    }

    $conn->commit();
    echo "Venta guardada correctamente";
} catch (Exception $e) {
    $conn->rollback();
    echo $e->getMessage();
}