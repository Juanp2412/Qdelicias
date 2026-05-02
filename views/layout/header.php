<?php
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

$nombre = $_SESSION['nombre'] ?? $_SESSION['usuario'];
$rol = $_SESSION['rol'] ?? 'vendedor';
?>

<nav class="navbar navbar-expand navbar-dark bg-dark fixed-top px-3" style="height:56px;">
    <a class="navbar-brand me-4" href="dashboard.php">QDelicias POS</a>

    <div class="ms-auto d-flex align-items-center gap-2">
        <span class="text-white small me-2">
            <?php echo htmlspecialchars($nombre); ?> | <?php echo htmlspecialchars($rol); ?>
        </span>

        <a href="dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
        <a href="ventas.php" class="btn btn-success btn-sm">Ventas</a>
        <a href="../controllers/logoutController.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
    </div>
</nav>