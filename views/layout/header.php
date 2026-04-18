<?php
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}
?>

<div style="background:#212529; color:white; padding:15px; border-radius:10px; margin-bottom:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
        
        <div>
            <strong>QDelicias POS</strong><br>
            <small><?php echo $_SESSION['usuario']; ?> | <?php echo $_SESSION['rol']; ?></small>
        </div>

        <div style="display:flex; gap:10px;">
            <a href="dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
            <a href="ventas.php" class="btn btn-success btn-sm">Ventas</a>
            <a href="../controllers/logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
        </div>

    </div>
</div>