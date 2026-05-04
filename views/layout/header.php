<?php
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

$nombre = $_SESSION['nombre'] ?? $_SESSION['usuario'];
$rol = $_SESSION['rol'] ?? 'vendedor';
?>

<link rel="stylesheet" href="/Qdelicias/assets/css/app.css">

<div class="topbar">

    <div class="brand">
        SmartPOS
    </div>

    <div class="d-flex align-items-center gap-3">

        <div class="user-box">
            <div class="name"><?php echo htmlspecialchars($nombre); ?></div>
            <div class="role"><?php echo htmlspecialchars($rol); ?></div>
        </div>
        <?php if ($_SESSION['rol'] == 'admin') { ?>
        <a href="/Qdelicias/views/dashboard.php" class="btn-custom btn-dashboard">Dashboard</a>
        <?php } else { ?>
        <a href="reporte_ventas.php" class="btn btn-info btn-sm">Ver Reportes</a>
        <?php } ?>

        <a href="/Qdelicias/views/ventas.php" class="btn-custom btn-ventas">Ventas</a>
        <a href="/Qdelicias/controllers/logout.php" class="btn-custom btn-logout">Cerrar sesión</a>

    </div>

</div>
  
        