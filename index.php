<?php
session_start();

if (isset($_SESSION['usuario'])) {
    header("Location: views/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - QDelicias</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4" style="width: 300px;">
        <h4 class="text-center">Iniciar Sesión</h4>
        <form action="controllers/loginController.php" method="POST">
            <input type="text" name="usuario" class="form-control mb-2" placeholder="Usuario" required>
            <input type="password" name="password" class="form-control mb-3" placeholder="Contraseña" required>
            <button class="btn btn-primary w-100">Ingresar</button>
        </form>
    </div>
</div>

</body>
</html>