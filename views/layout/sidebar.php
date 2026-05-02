<?php
$rol = $_SESSION['rol'] ?? 'vendedor';
?>

<div style="width:250px; position:fixed; top:56px; left:0; height:calc(100vh - 56px); background:#212529; color:white; overflow-y:auto;">

    <ul class="nav flex-column px-2 pt-3">

        <li class="nav-item mb-2">
            <a href="dashboard.php" class="nav-link text-white">📊 Dashboard</a>
        </li>

        <li class="nav-item mb-2">
            <a href="ventas.php" class="nav-link text-white">💰 Ventas</a>
        </li>

        <li class="nav-item mb-2">
            <a href="reporte_ventas.php" class="nav-link text-white">📈 Reportes</a>
        </li>

        <?php if ($rol == 'admin') { ?>

            <hr class="border-secondary">

            <li class="nav-item mb-2">
                <a href="productos.php" class="nav-link text-white">📦 Productos</a>
            </li>

            <li class="nav-item mb-2">
                <a href="categorias.php" class="nav-link text-white">📁 Categorías</a>
            </li>

            <li class="nav-item mb-2">
                <a href="extras.php" class="nav-link text-white">➕ Extras</a>
            </li>

            <li class="nav-item mb-2">
                <a href="tipos_extra.php" class="nav-link text-white">🧩 Tipos extra</a>
            </li>

            <li class="nav-item mb-2">
                <a href="reglas_producto.php" class="nav-link text-white">⚙️ Reglas</a>
            </li>

            <li class="nav-item mb-2">
                <a href="sabores.php" class="nav-link text-white">🍓 Sabores</a>
            </li>

            <li class="nav-item mb-2">
                <a href="producto_sabores.php" class="nav-link text-white">🔗 Asignar sabores</a>
            </li>

            <li class="nav-item mb-2">
                <a href="usuarios.php" class="nav-link text-white">👤 Usuarios</a>
            </li>

        <?php } ?>

        <hr class="border-secondary">

        <li class="nav-item">
            <a href="../controllers/logoutController.php" class="nav-link text-danger">🚪 Cerrar sesión</a>
        </li>

    </ul>

</div>