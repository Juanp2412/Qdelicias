<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$productos = $conn->query("SELECT * FROM productos ORDER BY nombre ASC");
$extras = $conn->query("SELECT * FROM extras ORDER BY nombre ASC");
$reglasDB = $conn->query("SELECT producto_id, tipo_extra, cantidad_incluida FROM producto_reglas_extras");
$reglasProductos = [];

while ($r = $reglasDB->fetch_assoc()) {
    $productoId = $r['producto_id'];
    $tipoExtra = $r['tipo_extra'];
    $cantidadIncluida = (int)$r['cantidad_incluida'];

    if (!isset($reglasProductos[$productoId])) {
        $reglasProductos[$productoId] = [];
    }

    $reglasProductos[$productoId][$tipoExtra] = $cantidadIncluida;
}
$extrasData = [];

while ($e = $extras->fetch_assoc()) {
    $extrasData[] = $e;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - QDelicias POS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body{
            background-color: #f8f9fa;
        }
        .top-bar{
            background: #212529;
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .producto-btn{
            width: 100%;
            min-height: 90px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 14px;
        }
        .precio-text{
            display: block;
            font-size: 15px;
            font-weight: normal;
            margin-top: 6px;
        }
        .panel-card{
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: none;
        }
        .total-box{
            background: #198754;
            color: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
        }
        .acciones-finales{
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .detalle-extra{
            font-size: 13px;
            color: #6c757d;
            display: block;
            margin-top: 4px;
        }
        .extra-item{
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 10px;
            background: #fff;
        }
        .extra-cantidad{
            min-width: 32px;
            text-align: center;
            font-weight: bold;
        }
        @media (max-width: 768px){
            .producto-btn{
                min-height: 80px;
                font-size: 16px;
            }
            .total-box{
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid p-3 p-md-4">
    
    <div class="top-bar d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="m-0">QDelicias POS</h3>
            <small>Usuario: <?php echo $_SESSION['usuario']; ?> | Rol: <?php echo $_SESSION['rol']; ?></small>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
            <a href="../controllers/logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-lg-7">
            <div class="card panel-card p-3 mb-4">
                <h4 class="mb-3">Selecciona extras</h4>

                <div class="row">
                    <?php foreach($extrasData as $e) { ?>
                        <div class="col-md-6">
                            <div class="extra-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong><?php echo htmlspecialchars($e['nombre']); ?></strong><br>
                                        <small class="text-muted">
                                            $ <?php echo number_format($e['precio'], 0, ',', '.'); ?> | Tipo: <?php echo htmlspecialchars($e['tipo']); ?>
                                        </small>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <button 
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm"
                                        onclick="cambiarCantidadExtra(<?php echo $e['id']; ?>, -1)"
                                    >-</button>

                                    <span class="extra-cantidad" id="cantidad_extra_<?php echo $e['id']; ?>">0</span>

                                    <button 
                                        type="button"
                                        class="btn btn-outline-primary btn-sm"
                                        onclick="cambiarCantidadExtra(<?php echo $e['id']; ?>, 1)"
                                    >+</button>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <small class="text-muted mt-2 d-block">
                    Ajusta las cantidades de extras y luego toca el producto.
                </small>
            </div>

            <div class="card panel-card p-3">
                <h4 class="mb-3">Productos</h4>
                <div class="row g-3">
                    <?php
                    mysqli_data_seek($productos, 0);
                    while ($p = $productos->fetch_assoc()) {
                    ?>
                        <div class="col-6 col-md-4">
                            <button 
                                class="btn btn-outline-primary producto-btn"
                                onclick="agregarProductoConExtras(<?php echo $p['id']; ?>, '<?php echo addslashes($p['nombre']); ?>', <?php echo $p['precio']; ?>)"
                            >
                                <?php echo $p['nombre']; ?>
                                <span class="precio-text">$ <?php echo number_format($p['precio'], 0, ',', '.'); ?></span>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card panel-card p-3">
                <h4 class="mb-3">Carrito</h4>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="tabla">
                        <thead class="table-dark">
                            <tr>
                                <th>Detalle</th>
                                <th width="90">Cant.</th>
                                <th width="130">Subtotal</th>
                                <th width="90">Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="total-box mb-3">
                    Total: $ <span id="total">0</span>
                </div>

                <div class="acciones-finales">
                    <button class="btn btn-success flex-fill" onclick="guardarVenta()">Guardar venta</button>
                    <button class="btn btn-secondary flex-fill" onclick="vaciarCarrito()">Vaciar carrito</button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
let carrito = [];
let total = 0;

// Ajusta esto con los IDs reales de tus productos
const reglasProductos = <?php echo json_encode($reglasProductos, JSON_UNESCAPED_UNICODE); ?>;
const extrasCatalogo = <?php echo json_encode($extrasData, JSON_UNESCAPED_UNICODE); ?>;
let cantidadesExtras = {};

extrasCatalogo.forEach(extra => {
    cantidadesExtras[extra.id] = 0;
});

function cambiarCantidadExtra(extraId, cambio) {
    let actual = cantidadesExtras[extraId] || 0;
    let nuevaCantidad = actual + cambio;

    if (nuevaCantidad < 0) {
        nuevaCantidad = 0;
    }

    cantidadesExtras[extraId] = nuevaCantidad;
    document.getElementById("cantidad_extra_" + extraId).innerText = nuevaCantidad;
}

function obtenerExtrasSeleccionados() {
    let extrasSeleccionados = [];

    extrasCatalogo.forEach(extra => {
        let cantidad = cantidadesExtras[extra.id] || 0;

        if (cantidad > 0) {
            extrasSeleccionados.push({
                id: parseInt(extra.id),
                nombre: extra.nombre,
                precio: parseFloat(extra.precio),
                tipo: extra.tipo,
                cantidad: cantidad,
                cantidad_incluida: 0,
                cantidad_cobrada: 0
            });
        }
    });

    return extrasSeleccionados;
}

function limpiarExtrasSeleccionados() {
    extrasCatalogo.forEach(extra => {
        cantidadesExtras[extra.id] = 0;
        document.getElementById("cantidad_extra_" + extra.id).innerText = 0;
    });
}

function generarClaveLinea(productoId, extras) {
    let extrasClave = extras
        .map(e => `${e.id}:${e.cantidad}`)
        .sort()
        .join("|");

    return productoId + "|" + extrasClave;
}

function agregarProductoConExtras(id, nombre, precio) {
    let extrasSeleccionados = obtenerExtrasSeleccionados();
    let reglas = reglasProductos[id] || {};
    let totalExtrasCobrados = 0;

    // Lleva control acumulado por tipo
    let usadosPorTipo = {};

    extrasSeleccionados.forEach(extra => {
        let tipo = extra.tipo;
        let limiteGratis = parseInt(reglas[tipo] || 0);

        if (!usadosPorTipo[tipo]) {
            usadosPorTipo[tipo] = 0;
        }

        let disponiblesGratis = limiteGratis - usadosPorTipo[tipo];
        if (disponiblesGratis < 0) {
            disponiblesGratis = 0;
        }

        let cantidadIncluida = Math.min(extra.cantidad, disponiblesGratis);
        let cantidadCobrada = extra.cantidad - cantidadIncluida;

        extra.cantidad_incluida = cantidadIncluida;
        extra.cantidad_cobrada = cantidadCobrada;

        usadosPorTipo[tipo] += cantidadIncluida;
        totalExtrasCobrados += cantidadCobrada * extra.precio;
    });

    let precioUnitarioLinea = precio + totalExtrasCobrados;
    let clave = generarClaveLinea(id, extrasSeleccionados);

    let productoExistente = carrito.find(item => item.clave === clave);

    if (productoExistente) {
        productoExistente.cantidad++;
        productoExistente.subtotal += precioUnitarioLinea;
    } else {
        carrito.push({
            clave: clave,
            id: id,
            nombre: nombre,
            precio_base: precio,
            cantidad: 1,
            extras: extrasSeleccionados,
            subtotal: precioUnitarioLinea
        });
    }

    total += precioUnitarioLinea;
    renderCarrito();
    limpiarExtrasSeleccionados();
}

function disminuirCantidad(index) {
    let valorUnitario = carrito[index].subtotal / carrito[index].cantidad;

    if (carrito[index].cantidad > 1) {
        carrito[index].cantidad--;
        carrito[index].subtotal -= valorUnitario;
        total -= valorUnitario;
    } else {
        total -= carrito[index].subtotal;
        carrito.splice(index, 1);
    }

    renderCarrito();
}

function eliminar(index) {
    total -= carrito[index].subtotal;
    carrito.splice(index, 1);
    renderCarrito();
}

function vaciarCarrito() {
    carrito = [];
    total = 0;
    renderCarrito();
    limpiarExtrasSeleccionados();
}

function formatearPeso(valor) {
    return new Intl.NumberFormat('es-CO').format(Math.round(valor));
}

function renderCarrito() {
    let tabla = document.querySelector("#tabla tbody");
    tabla.innerHTML = "";

    if (carrito.length === 0) {
        tabla.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted">No hay productos en el carrito</td>
            </tr>
        `;
    } else {
        carrito.forEach((item, index) => {
            let extrasTexto = item.extras.length > 0
                ? item.extras.map(extra => {
                    let partes = [];

                    if (extra.cantidad_incluida > 0) {
                        partes.push(`${extra.cantidad_incluida} incluido`);
                    }

                    if (extra.cantidad_cobrada > 0) {
                        partes.push(`${extra.cantidad_cobrada} cobrado (+$${formatearPeso(extra.cantidad_cobrada * extra.precio)})`);
                    }

                    return `${extra.nombre} x${extra.cantidad} [${partes.join(", ")}]`;
                }).join(", ")
                : "Sin extras";

            tabla.innerHTML += `
                <tr>
                    <td>
                        <strong>${item.nombre}</strong>
                        <span class="detalle-extra">${extrasTexto}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span>${item.cantidad}</span>
                            <button class="btn btn-warning btn-sm" onclick="disminuirCantidad(${index})">-</button>
                        </div>
                    </td>
                    <td>$ ${formatearPeso(item.subtotal)}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="eliminar(${index})">X</button>
                    </td>
                </tr>
            `;
        });
    }

    document.getElementById("total").innerText = formatearPeso(total);
}

function guardarVenta() {
    if (carrito.length === 0) {
        alert("Agrega al menos un producto");
        return;
    }

    fetch("../controllers/ventascontroller.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ carrito, total })
    })
    .then(res => res.text())
    .then(res => {
        alert(res);

        if (res.includes("correctamente")) {
            carrito = [];
            total = 0;
            renderCarrito();
            limpiarExtrasSeleccionados();
        }
    })
    .catch(error => {
        alert("Error al guardar la venta");
        console.error(error);
    });
}

renderCarrito();
</script>

</body>
</html>