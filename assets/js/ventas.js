(() => {
    const config = window.VENTAS_CONFIG || {};
    const controllerUrl = config.controllerUrl || "../controllers/ventascontroller.php";
    const extrasCatalogo = Array.isArray(config.extras) ? config.extras : [];

    let carrito = [];
    let totalEstimado = 0;
    let cantidadesExtras = {};

    extrasCatalogo.forEach(extra => {
        cantidadesExtras[extra.id] = 0;
    });

    function cambiarCantidadExtra(extraId, cambio) {
        const actual = cantidadesExtras[extraId] || 0;
        let nuevaCantidad = actual + cambio;

        if (nuevaCantidad < 0) {
            nuevaCantidad = 0;
        }

        cantidadesExtras[extraId] = nuevaCantidad;

        const nodo = document.getElementById("cantidad_extra_" + extraId);
        if (nodo) {
            nodo.innerText = nuevaCantidad;
        }
    }

    function obtenerExtrasSeleccionados() {
        const extrasSeleccionados = [];

        extrasCatalogo.forEach(extra => {
            const cantidad = cantidadesExtras[extra.id] || 0;

            if (cantidad > 0) {
                extrasSeleccionados.push({
                    id: parseInt(extra.id, 10),
                    nombre: extra.nombre,
                    precio: parseFloat(extra.precio),
                    cantidad: cantidad,
                });
            }
        });

        return extrasSeleccionados;
    }

    function limpiarExtrasSeleccionados() {
        extrasCatalogo.forEach(extra => {
            cantidadesExtras[extra.id] = 0;

            const nodo = document.getElementById("cantidad_extra_" + extra.id);
            if (nodo) {
                nodo.innerText = 0;
            }
        });
    }

    function generarClaveLinea(productoId, extras) {
        const extrasClave = extras
            .map(e => `${e.id}:${e.cantidad}`)
            .sort()
            .join("|");

        return `${productoId}|${extrasClave}`;
    }

    function calcularPrecioUnitarioEstimado(precioBase, extras) {
        const totalExtras = extras.reduce((acum, extra) => {
            return acum + (extra.cantidad * extra.precio);
        }, 0);

        return precioBase + totalExtras;
    }

    function agregarProductoConExtras(id, nombre, precio) {
        const extrasSeleccionados = obtenerExtrasSeleccionados();
        const clave = generarClaveLinea(id, extrasSeleccionados);
        const precioUnitarioEstimado = calcularPrecioUnitarioEstimado(precio, extrasSeleccionados);

        const existente = carrito.find(item => item.clave === clave);

        if (existente) {
            existente.cantidad += 1;
            existente.subtotal_estimado += precioUnitarioEstimado;
        } else {
            carrito.push({
                clave,
                id,
                nombre,
                precio_base: precio,
                cantidad: 1,
                extras: extrasSeleccionados,
                subtotal_estimado: precioUnitarioEstimado,
            });
        }

        totalEstimado += precioUnitarioEstimado;
        renderCarrito();
        limpiarExtrasSeleccionados();
    }

    function disminuirCantidad(index) {
        if (!carrito[index]) {
            return;
        }

        const valorUnitario = carrito[index].subtotal_estimado / carrito[index].cantidad;

        if (carrito[index].cantidad > 1) {
            carrito[index].cantidad -= 1;
            carrito[index].subtotal_estimado -= valorUnitario;
            totalEstimado -= valorUnitario;
        } else {
            totalEstimado -= carrito[index].subtotal_estimado;
            carrito.splice(index, 1);
        }

        renderCarrito();
    }

    function eliminar(index) {
        if (!carrito[index]) {
            return;
        }

        totalEstimado -= carrito[index].subtotal_estimado;
        carrito.splice(index, 1);
        renderCarrito();
    }

    function vaciarCarrito() {
        carrito = [];
        totalEstimado = 0;
        renderCarrito();
        limpiarExtrasSeleccionados();
    }

    function formatearPeso(valor) {
        return new Intl.NumberFormat('es-CO').format(Math.round(valor));
    }

    function renderCarrito() {
        const tabla = document.querySelector("#tabla tbody");

        if (!tabla) {
            return;
        }

        tabla.innerHTML = "";

        if (carrito.length === 0) {
            tabla.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted">No hay productos en el carrito</td>
                </tr>
            `;
        } else {
            carrito.forEach((item, index) => {
                const extrasTexto = item.extras.length > 0
                    ? item.extras.map(extra => `${extra.nombre} x${extra.cantidad}`).join(", ")
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
                        <td>$ ${formatearPeso(item.subtotal_estimado)}</td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="eliminar(${index})">X</button>
                        </td>
                    </tr>
                `;
            });
        }

        const totalNodo = document.getElementById("total");
        if (totalNodo) {
            totalNodo.innerText = formatearPeso(totalEstimado);
        }
    }

    async function guardarVenta() {
        if (carrito.length === 0) {
            alert("Agrega al menos un producto");
            return;
        }

        try {
            const res = await fetch(controllerUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ carrito })
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                alert(data.message || "Error al guardar la venta");
                return;
            }

            const totalBackend = data?.venta?.total ?? 0;
            const ventaId = data?.venta_id ?? "";
            alert(`Venta #${ventaId} guardada correctamente. Total final: $ ${formatearPeso(totalBackend)}`);

            vaciarCarrito();
        } catch (error) {
            alert("Error al guardar la venta");
            console.error(error);
        }
    }

    window.cambiarCantidadExtra = cambiarCantidadExtra;
    window.agregarProductoConExtras = agregarProductoConExtras;
    window.disminuirCantidad = disminuirCantidad;
    window.eliminar = eliminar;
    window.vaciarCarrito = vaciarCarrito;
    window.guardarVenta = guardarVenta;

    renderCarrito();
})();
