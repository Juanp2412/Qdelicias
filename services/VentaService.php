<?php

class VentaService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Calcula totales y desglose de incluidos/cobrados por producto.
     * No confía en precios ni totales enviados desde frontend.
     */
    public function calcularVenta(array $carrito): array
    {
        if (empty($carrito)) {
            throw new InvalidArgumentException('Carrito vacío');
        }

        $productoIds = [];
        $extraIds = [];

        foreach ($carrito as $linea) {
            if (!isset($linea['id'])) {
                throw new InvalidArgumentException('Producto inválido en carrito');
            }

            $productoIds[] = (int) $linea['id'];

            $extrasLinea = $linea['extras'] ?? [];
            foreach ($extrasLinea as $extra) {
                if (isset($extra['id'])) {
                    $extraIds[] = (int) $extra['id'];
                }
            }
        }

        $productos = $this->cargarProductos($productoIds);
        $extrasCatalogo = $this->cargarExtras($extraIds);
        $reglas = $this->cargarReglas($productoIds);

        $itemsCalculados = [];
        $totalFinal = 0.0;

        foreach ($carrito as $linea) {
            $productoId = (int) $linea['id'];
            $cantidadProducto = max(1, (int) ($linea['cantidad'] ?? 1));

            if (!isset($productos[$productoId])) {
                throw new RuntimeException("Producto no encontrado: {$productoId}");
            }

            $producto = $productos[$productoId];
            $precioBase = (float) $producto['precio'];
            $nombreProducto = $producto['nombre'];

            $extrasConsolidados = $this->consolidarExtras($linea['extras'] ?? []);
            $reglasProducto = $reglas[$productoId] ?? [];
            $usadosPorTipo = [];
            $extrasCalculados = [];
            $totalExtrasCobradosLinea = 0.0;

            foreach ($extrasConsolidados as $extraLinea) {
                $extraId = (int) $extraLinea['id'];
                $cantidadExtraPorUnidad = (int) $extraLinea['cantidad'];

                if ($cantidadExtraPorUnidad <= 0) {
                    continue;
                }

                if (!isset($extrasCatalogo[$extraId])) {
                    throw new RuntimeException("Extra no encontrado: {$extraId}");
                }

                $extraDB = $extrasCatalogo[$extraId];
                $tipo = $extraDB['tipo'];
                $precioExtra = (float) $extraDB['precio'];
                $nombreExtra = $extraDB['nombre'];

                $limiteGratis = isset($reglasProducto[$tipo]) ? (int) $reglasProducto[$tipo] : 0;
                $incluidosYaUsados = $usadosPorTipo[$tipo] ?? 0;
                $disponiblesGratis = max(0, $limiteGratis - $incluidosYaUsados);

                $cantidadIncluidaPorUnidad = min($cantidadExtraPorUnidad, $disponiblesGratis);
                $cantidadCobradaPorUnidad = $cantidadExtraPorUnidad - $cantidadIncluidaPorUnidad;

                // Si la línea representa N unidades del mismo producto,
                // los extras seleccionados por unidad se multiplican por N.
                $cantidadIncluidaTotal = $cantidadIncluidaPorUnidad * $cantidadProducto;
                $cantidadCobradaTotal = $cantidadCobradaPorUnidad * $cantidadProducto;

                $subtotalExtra = $cantidadCobradaTotal * $precioExtra;
                $totalExtrasCobradosLinea += $subtotalExtra;

                $usadosPorTipo[$tipo] = $incluidosYaUsados + $cantidadIncluidaPorUnidad;

                $extrasCalculados[] = [
                    'id' => $extraId,
                    'nombre' => $nombreExtra,
                    'tipo' => $tipo,
                    'precio_unitario' => $precioExtra,
                    'cantidad_por_unidad' => $cantidadExtraPorUnidad,
                    'cantidad_total' => $cantidadExtraPorUnidad * $cantidadProducto,
                    'cantidad_incluida_por_unidad' => $cantidadIncluidaPorUnidad,
                    'cantidad_cobrada_por_unidad' => $cantidadCobradaPorUnidad,
                    'cantidad_incluida' => $cantidadIncluidaTotal,
                    'cantidad_cobrada' => $cantidadCobradaTotal,
                    'subtotal' => $subtotalExtra,
                ];
            }

            $subtotalBase = $precioBase * $cantidadProducto;
            $subtotalLinea = $subtotalBase + $totalExtrasCobradosLinea;
            $totalFinal += $subtotalLinea;

            $itemsCalculados[] = [
                'producto_id' => $productoId,
                'producto_nombre' => $nombreProducto,
                'cantidad' => $cantidadProducto,
                'precio_base' => $precioBase,
                'subtotal_base' => $subtotalBase,
                'extras' => $extrasCalculados,
                'subtotal_extras' => $totalExtrasCobradosLinea,
                'subtotal' => $subtotalLinea,
            ];
        }

        return [
            'items' => $itemsCalculados,
            'total' => $totalFinal,
        ];
    }

    /**
     * Calcula y persiste venta/detalle/extras en una transacción.
     */
    public function registrarVenta(array $carrito): array
    {
        $ventaCalculada = $this->calcularVenta($carrito);
        $total = $ventaCalculada['total'];
        $items = $ventaCalculada['items'];

        $this->conn->begin_transaction();

        try {
            $stmtVenta = $this->conn->prepare('INSERT INTO ventas (total) VALUES (?)');
            if (!$stmtVenta) {
                throw new RuntimeException('Error preparando INSERT venta: ' . $this->conn->error);
            }

            $stmtVenta->bind_param('d', $total);
            if (!$stmtVenta->execute()) {
                throw new RuntimeException('Error guardando venta: ' . $stmtVenta->error);
            }

            $ventaId = (int) $this->conn->insert_id;
            $stmtVenta->close();

            $stmtDetalle = $this->conn->prepare(
                'INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)'
            );
            if (!$stmtDetalle) {
                throw new RuntimeException('Error preparando INSERT detalle_venta: ' . $this->conn->error);
            }

            $stmtExtra = $this->conn->prepare(
                'INSERT INTO detalle_venta_extras (detalle_venta_id, extra_id, precio) VALUES (?, ?, ?)'
            );
            if (!$stmtExtra) {
                throw new RuntimeException('Error preparando INSERT detalle_venta_extras: ' . $this->conn->error);
            }

            foreach ($items as $item) {
                $productoId = (int) $item['producto_id'];
                $cantidad = (int) $item['cantidad'];
                $precioBase = (float) $item['precio_base'];

                $stmtDetalle->bind_param('iiid', $ventaId, $productoId, $cantidad, $precioBase);
                if (!$stmtDetalle->execute()) {
                    throw new RuntimeException('Error guardando detalle de venta: ' . $stmtDetalle->error);
                }

                $detalleVentaId = (int) $this->conn->insert_id;

                foreach ($item['extras'] as $extra) {
                    $extraId = (int) $extra['id'];
                    $precioUnitario = (float) $extra['precio_unitario'];
                    $cantidadIncluida = (int) $extra['cantidad_incluida'];
                    $cantidadCobrada = (int) $extra['cantidad_cobrada'];

                    for ($i = 0; $i < $cantidadIncluida; $i++) {
                        $precio = 0.0;
                        $stmtExtra->bind_param('iid', $detalleVentaId, $extraId, $precio);
                        if (!$stmtExtra->execute()) {
                            throw new RuntimeException('Error guardando extra incluido: ' . $stmtExtra->error);
                        }
                    }

                    for ($i = 0; $i < $cantidadCobrada; $i++) {
                        $precio = $precioUnitario;
                        $stmtExtra->bind_param('iid', $detalleVentaId, $extraId, $precio);
                        if (!$stmtExtra->execute()) {
                            throw new RuntimeException('Error guardando extra cobrado: ' . $stmtExtra->error);
                        }
                    }
                }
            }

            $stmtDetalle->close();
            $stmtExtra->close();
            $this->conn->commit();

            return [
                'venta_id' => $ventaId,
                'venta' => $ventaCalculada,
            ];
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    private function cargarProductos(array $productoIds): array
    {
        $productoIds = $this->normalizarIds($productoIds);
        if (empty($productoIds)) {
            return [];
        }

        $sql = 'SELECT id, nombre, precio FROM productos WHERE id IN (' . implode(',', $productoIds) . ')';
        $result = $this->conn->query($sql);

        if (!$result) {
            throw new RuntimeException('Error consultando productos: ' . $this->conn->error);
        }

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[(int) $row['id']] = $row;
        }

        return $productos;
    }

    private function cargarExtras(array $extraIds): array
    {
        $extraIds = $this->normalizarIds($extraIds);
        if (empty($extraIds)) {
            return [];
        }

        $sql = 'SELECT id, nombre, tipo, precio FROM extras WHERE id IN (' . implode(',', $extraIds) . ')';
        $result = $this->conn->query($sql);

        if (!$result) {
            throw new RuntimeException('Error consultando extras: ' . $this->conn->error);
        }

        $extras = [];
        while ($row = $result->fetch_assoc()) {
            $extras[(int) $row['id']] = $row;
        }

        return $extras;
    }

    private function cargarReglas(array $productoIds): array
    {
        $productoIds = $this->normalizarIds($productoIds);
        if (empty($productoIds)) {
            return [];
        }

        $sql = 'SELECT producto_id, tipo_extra, cantidad_incluida FROM producto_reglas_extras WHERE producto_id IN ('
            . implode(',', $productoIds) . ')';
        $result = $this->conn->query($sql);

        if (!$result) {
            throw new RuntimeException('Error consultando reglas de extras: ' . $this->conn->error);
        }

        $reglas = [];
        while ($row = $result->fetch_assoc()) {
            $productoId = (int) $row['producto_id'];
            $tipo = (string) $row['tipo_extra'];
            $cantidadIncluida = (int) $row['cantidad_incluida'];

            if (!isset($reglas[$productoId])) {
                $reglas[$productoId] = [];
            }

            $reglas[$productoId][$tipo] = $cantidadIncluida;
        }

        return $reglas;
    }

    private function consolidarExtras(array $extras): array
    {
        $consolidado = [];

        foreach ($extras as $extra) {
            if (!isset($extra['id'])) {
                continue;
            }

            $extraId = (int) $extra['id'];
            $cantidad = max(0, (int) ($extra['cantidad'] ?? 0));

            if (!isset($consolidado[$extraId])) {
                $consolidado[$extraId] = [
                    'id' => $extraId,
                    'cantidad' => 0,
                ];
            }

            $consolidado[$extraId]['cantidad'] += $cantidad;
        }

        return array_values($consolidado);
    }

    private function normalizarIds(array $ids): array
    {
        $limpios = [];

        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $limpios[$id] = $id;
            }
        }

        return array_values($limpios);
    }
}
