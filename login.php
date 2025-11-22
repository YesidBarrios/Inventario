<?php
session_start();
require_once 'includes/functions.php';

// Si el usuario ya está logueado, redirigir al index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = verificarCredenciales($username, $password);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['rol'] = $user['rol'];

        // Lógica para "Recordarme"
        if (isset($_POST['remember'])) {
            setcookie('remember_user', $username, time() + (86400 * 30), "/"); // Cookie por 30 días
        } else {
            setcookie('remember_user', '', time() - 3600, "/"); // Eliminar cookie
        }

        header("Location: index.php");
        exit;
    } else {
        $error = "Credenciales incorrectas. Inténtalo de nuevo.";
    }
}

$config = obtenerConfiguracion();
$saved_username = isset($_COOKIE['remember_user']) ? htmlspecialchars($_COOKIE['remember_user']) : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - <?= htmlspecialchars($config['nombre_tienda']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <style>
        /* Estilos generales */
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-15px); } }
        @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 15px rgba(34, 211, 238, 0.3); } 50% { box-shadow: 0 0 30px rgba(34, 211, 238, 0.5); } }
        @keyframes slide-in { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .float-animation { animation: float 6s ease-in-out infinite; }
        .pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }
        .slide-in { animation: slide-in 0.6s ease-out forwards; }
        .gradient-bg { background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63); background-size: 400% 400%; animation: gradient-shift 15s ease infinite; }
        .glass-effect { background: rgba(255, 255, 255, 0.97); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .input-focus:focus { box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.2); border-color: #22d3ee; }
        
        /* CAMBIO: Se restauró el efecto de hover original y completo */
        .btn-hover {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .btn-hover::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(34, 211, 238, 0.2); /* Tono cian suave */
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
            z-index: 0;
        }
        .btn-hover:hover::before {
            width: 300px; /* Ancho de la onda */
            height: 300px; /* Alto de la onda */
        }
        .btn-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(34, 211, 238, 0.3);
        }
        /* Para que el texto y el icono estén por encima de la onda */
        .btn-hover > * {
            position: relative;
            z-index: 1;
        }

        .particle { position: absolute; background: rgba(34, 211, 238, 0.5); border-radius: 50%; pointer-events: none; }
        @keyframes particle-float {
            0%   { transform: translateY(100vh) scale(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(-100vh) scale(1); opacity: 0; }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div id="particles-container" class="absolute inset-0 overflow-hidden pointer-events-none"></div>
    <div class="absolute top-10 left-10 w-24 h-24 bg-cyan-400/10 rounded-full blur-3xl float-animation"></div>
    <div class="absolute bottom-20 right-20 w-32 h-32 bg-cyan-500/10 rounded-full blur-3xl float-animation"></div>

    <main class="w-full max-w-4xl grid grid-cols-1 lg:grid-cols-2 gap-6 items-center z-10">
        <div class="hidden lg:flex flex-col justify-center p-6 text-white slide-in">
             <div class="flex items-center space-x-4 mb-6"><div class="pulse-glow rounded-xl bg-gradient-to-br from-cyan-400 to-cyan-600 p-3 flex-shrink-0"><i class="fas fa-box-open text-3xl text-white"></i></div><div><h1 class="text-3xl font-black tracking-tight bg-gradient-to-r from-white to-cyan-200 bg-clip-text text-transparent"><?= htmlspecialchars($config['nombre_tienda']) ?></h1><p class="text-cyan-300 text-xs mt-1">Sistema de Gestión de Inventario</p></div></div>
             <div class="space-y-4"><div class="flex items-start space-x-3 group"><div class="bg-cyan-500/10 p-2 rounded-md mt-1"><i class="fas fa-boxes-stacked text-cyan-400 text-lg"></i></div><div><h3 class="font-bold text-white">Control de Inventario</h3><p class="text-slate-300 text-sm">Gestión de productos y alertas de stock.</p></div></div><div class="flex items-start space-x-3 group"><div class="bg-cyan-500/10 p-2 rounded-md mt-1"><i class="fas fa-cash-register text-cyan-400 text-lg"></i></div><div><h3 class="font-bold text-white">Ventas y Reportes</h3><p class="text-slate-300 text-sm">Registro de ventas y reportes financieros.</p></div></div><div class="flex items-start space-x-3 group"><div class="bg-cyan-500/10 p-2 rounded-md mt-1"><i class="fas fa-robot text-cyan-400 text-lg"></i></div><div><h3 class="font-bold text-white">Asistente con IA</h3><p class="text-slate-300 text-sm">Chatbot para consultas en lenguaje natural.</p></div></div></div>
        </div>

        <div class="slide-in" style="animation-delay: 0.1s;">
            <div class="glass-effect rounded-2xl p-8 shadow-2xl">
                <div class="text-center mb-6"><div class="inline-block bg-slate-900 p-3 rounded-xl mb-3 pulse-glow"><i class="fas fa-user-circle text-3xl text-cyan-400"></i></div><h2 class="text-2xl font-bold text-slate-800">¡Bienvenido!</h2><p class="text-slate-500 text-sm">Ingresa tus credenciales para continuar</p></div>
                <?php if ($error): ?><div class="mb-4 bg-red-50 border-l-4 border-red-400 text-red-700 p-3 rounded-md flex items-center text-sm"><i class="fas fa-exclamation-circle mr-3"></i><span><?= $error ?></span></div><?php endif; ?>
                <form action="login.php" method="POST" class="space-y-4">
                    <div>
                        <label for="username" class="text-sm font-semibold text-slate-700 mb-1 flex items-center"><i class="fas fa-user text-cyan-500 mr-2 text-xs"></i>Usuario</label>
                        <div class="relative"><input type="text" id="username" name="username" required class="input-focus w-full px-4 py-3 pl-10 border border-slate-200 rounded-lg outline-none text-slate-800" placeholder="Ingresa tu usuario" value="<?= $saved_username ?>"><i class="fas fa-user absolute left-3.5 top-1/2 transform -translate-y-1/2 text-slate-400 text-sm"></i></div>
                    </div>
                    <div>
                        <label for="password" class="text-sm font-semibold text-slate-700 mb-1 flex items-center"><i class="fas fa-lock text-cyan-500 mr-2 text-xs"></i>Contraseña</label>
                        <div class="relative"><input type="password" id="password" name="password" required class="input-focus w-full px-4 py-3 pl-10 pr-10 border border-slate-200 rounded-lg outline-none text-slate-800" placeholder="Ingresa tu contraseña"><i class="fas fa-lock absolute left-3.5 top-1/2 transform -translate-y-1/2 text-slate-400 text-sm"></i><button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-cyan-500"><i id="toggleIcon" class="fas fa-eye"></i></button></div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center cursor-pointer"><input type="checkbox" name="remember" class="w-4 h-4 text-cyan-500 border-slate-300 rounded focus:ring-cyan-500" <?= $saved_username ? 'checked' : '' ?>><span class="ml-2 text-slate-600">Recordarme</span></label>
                        <a href="#" class="text-cyan-600 hover:underline font-medium" title="Función no implementada aún">¿Olvidaste tu contraseña?</a>
                    </div>
                    <button type="submit" class="btn-hover w-full bg-slate-800 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center space-x-2">
                        <span>Ingresar al Sistema</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleIcon.classList.toggle('fa-eye', !isPassword);
            toggleIcon.classList.toggle('fa-eye-slash', isPassword);
        }

        function createParticles() {
            const container = document.getElementById('particles-container');
            if (!container) return;
            const particleCount = 20;
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                const size = Math.random() * 8 + 5;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.animation = `particle-float ${Math.random() * 25 + 20}s linear infinite`;
                particle.style.animationDelay = `${Math.random() * 5}s`;
                container.appendChild(particle);
            }
        }
        
        window.addEventListener('load', () => {
            setTimeout(createParticles, 200);
        });

        document.querySelector('form')?.addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            if (button) {
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Validando...';
                button.disabled = true;
            }
        });
    </script>
</body>
</html>