<?php
session_start();

if (isset($_SESSION['usuario'])) {
    header("Location: /Qdelicias/views/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SmartPOS - Sistema de ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,0.18), transparent 30%),
                linear-gradient(135deg, #111827, #1f2937, #0f766e);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .background-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(2px);
            opacity: .18;
        }

        .shape-1 {
            width: 260px;
            height: 260px;
            background: #22c55e;
            top: -70px;
            left: -70px;
        }

        .shape-2 {
            width: 340px;
            height: 340px;
            background: #38bdf8;
            bottom: -120px;
            right: -100px;
        }

        .login-wrapper {
            position: relative;
            width: 920px;
            max-width: 95%;
            min-height: 520px;
            background: rgba(255,255,255,0.96);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,0.35);
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            animation: aparecer .55s ease;
        }

        .brand-panel {
            padding: 48px;
            background:
                linear-gradient(135deg, rgba(15,118,110,0.95), rgba(17,24,39,0.98)),
                url('https://images.unsplash.com/photo-1556745757-8d76bdb6984b?auto=format&fit=crop&w=1000&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-logo {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .brand-title {
            font-size: 38px;
            font-weight: 800;
            line-height: 1.1;
            margin-top: 60px;
        }

        .brand-text {
            font-size: 16px;
            color: rgba(255,255,255,0.82);
            margin-top: 16px;
            max-width: 390px;
        }

        .quote-box {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 18px;
            padding: 18px;
            font-size: 14px;
            color: rgba(255,255,255,0.9);
            backdrop-filter: blur(8px);
        }

        .login-panel {
            padding: 48px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-badge {
            display: inline-block;
            width: fit-content;
            padding: 7px 12px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .login-panel h3 {
            font-weight: 800;
            color: #111827;
            margin-bottom: 8px;
        }

        .login-panel p {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 28px;
        }

        .form-control {
            height: 48px;
            border-radius: 14px;
            border: 1px solid #d1d5db;
            padding-left: 16px;
        }

        .form-control:focus {
            border-color: #0f766e;
            box-shadow: 0 0 0 .2rem rgba(15,118,110,.15);
        }

        .btn-login {
            height: 50px;
            border-radius: 14px;
            border: none;
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            font-weight: 700;
            transition: .2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(20,184,166,.35);
        }

        .mini-info {
            display: flex;
            justify-content: space-between;
            margin-top: 22px;
            gap: 10px;
        }

        .mini-card {
            flex: 1;
            background: #f9fafb;
            border-radius: 14px;
            padding: 12px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }

        .mini-card strong {
            display: block;
            color: #111827;
            font-size: 18px;
        }

        .footer-login {
            margin-top: 26px;
            font-size: 12px;
            color: #9ca3af;
            text-align: center;
        }

        @keyframes aparecer {
            from {
                opacity: 0;
                transform: translateY(18px) scale(.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 800px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }

            .brand-panel {
                display: none;
            }

            .login-panel {
                padding: 36px 26px;
            }
        }
    </style>
</head>

<body>

<div class="background-shape shape-1"></div>
<div class="background-shape shape-2"></div>

<div class="login-wrapper">

    <div class="brand-panel">
        <div>
            <div class="brand-logo">🚀 SmartPOS</div>

            <div class="brand-title">
                Vende más rápido.<br>
                Controla mejor.
            </div>

            <div class="brand-text">
                Un sistema simple para emprendedores, cafeterías, tiendas,
                comidas rápidas y negocios que quieren crecer.
            </div>
        </div>

        <div class="quote-box">
            “Todo negocio grande empezó vendiendo su primera orden con orden,
            control y ganas de crecer.”
        </div>
    </div>

    <div class="login-panel">
        <span class="login-badge">Sistema POS para pequeños negocios</span>

        <h3>Bienvenido de nuevo</h3>
        <p>Ingresa con tu usuario para continuar administrando tus ventas.</p>

        <form action="controllers/loginController.php" method="POST">
            <div class="mb-3">
                <input 
                    type="text" 
                    name="usuario" 
                    class="form-control" 
                    placeholder="Usuario"
                    required
                >
            </div>

            <div class="mb-3">
                <input 
                    type="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="Contraseña"
                    required
                >
            </div>

            <button class="btn btn-login text-white w-100">
                Ingresar al sistema
            </button>
        </form>

        <div class="mini-info">
            <div class="mini-card">
                <strong>POS</strong>
                Ventas rápidas
            </div>
            <div class="mini-card">
                <strong>24/7</strong>
                Control local
            </div>
            <div class="mini-card">
                <strong>$</strong>
                Reportes diarios
            </div>
        </div>

        <div class="footer-login">
            Software de ventas para negocios en crecimiento
        </div>
    </div>

</div>

</body>
</html>