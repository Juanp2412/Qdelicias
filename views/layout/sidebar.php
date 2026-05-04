<?php
$rol = $_SESSION['rol'] ?? 'vendedor';
$paginaActual = basename($_SERVER['PHP_SELF']);

function activo($pagina, $paginaActual) {
    return $pagina === $paginaActual ? 'active' : '';
}
?>

<div class="sidebar">

    <div class="sidebar-logo">
        🚀 SmartPOS
    </div>

    <div class="section">General</div>

    <a href="/Qdelicias/views/dashboard.php" class="<?php echo activo('dashboard.php', $paginaActual); ?>">📊 Dashboard</a>
    <a href="/Qdelicias/views/ventas.php" class="<?php echo activo('ventas.php', $paginaActual); ?>">💰 Ventas</a>
    <a href="/Qdelicias/views/reporte_ventas.php" class="<?php echo activo('reporte_ventas.php', $paginaActual); ?>">📈 Reportes</a>

    <?php if ($rol == 'admin') { ?>

        <div class="section">Administración</div>

        <a href="/Qdelicias/views/productos.php" class="<?php echo activo('productos.php', $paginaActual); ?>">📦 Productos</a>
        <a href="/Qdelicias/views/categorias.php" class="<?php echo activo('categorias.php', $paginaActual); ?>">📁 Categorías</a>
        <a href="/Qdelicias/views/extras.php" class="<?php echo activo('extras.php', $paginaActual); ?>">➕ Extras</a>
        <a href="/Qdelicias/views/tipos_extra.php" class="<?php echo activo('tipos_extra.php', $paginaActual); ?>">🧩 Tipos extra</a>
        <a href="/Qdelicias/views/reglas_producto.php" class="<?php echo activo('reglas_producto.php', $paginaActual); ?>">⚙️ Reglas</a>
        <a href="/Qdelicias/views/sabores.php" class="<?php echo activo('sabores.php', $paginaActual); ?>">🍓 Sabores</a>
        <a href="/Qdelicias/views/producto_sabores.php" class="<?php echo activo('producto_sabores.php', $paginaActual); ?>">🔗 Asignar sabores</a>
        <a href="/Qdelicias/views/usuarios.php" class="<?php echo activo('usuarios.php', $paginaActual); ?>">👤 Usuarios</a>

    <?php } ?>

    <div class="section">Cuenta</div>

    <a href="/Qdelicias/controllers/logout.php" class="logout">🚪 Cerrar sesión</a>

</div>