<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/functions.php';
requireAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($nombre)) {
        $error = "El nombre del proveedor es obligatorio.";
    } else {
        if (addProveedor($nombre, $telefono, $email)) {
            $_SESSION['success'] = "Proveedor agregado exitosamente.";
            header("Location: proveedores.php");
            exit;
        } else {
            $error = "Error al agregar el proveedor.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Proveedor - Gestor de Inventario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="theme.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <style>
        @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .gradient-bg { background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63); background-size: 400% 400%; animation: gradient-shift 15s ease infinite; }
    </style>
</head>
<body class="bg-slate-100 dark:bg-transparent text-slate-800 dark:text-slate-200 min-h-screen">
    <div class="hidden dark:block gradient-bg fixed inset-0 -z-10"></div>

    <header class="bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 shadow-lg border-b border-cyan-300 dark:border-slate-700 sticky top-0 z-30">
        <div class="container mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center">
            <div class="flex items-center space-x-2 mb-4 sm:mb-0">
                <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.124-1.28-.35-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.124-1.28.35-1.857m0 0a3.001 3.001 0 015.3 0m-5.3 0a3.001 3.001 0 00-5.3 0m10.6 0a3.001 3.001 0 015.3 0m-5.3 0a3.001 3.001 0 00-5.3 0"></path></svg>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-cyan-100 tracking-tight">Agregar Nuevo Proveedor</h1>
            </div>
            <div class="flex items-center space-x-3">
                <button id="theme-toggle-btn" class="p-2 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 transition-colors" title="Cambiar tema">
                    <svg id="theme-icon-light" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <svg id="theme-icon-dark" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>
                <a href="proveedores.php" class="flex items-center space-x-1 bg-slate-200 dark:bg-slate-600/90 hover:bg-slate-300 dark:hover:bg-slate-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-slate-300 dark:border-slate-400/20">
                    <svg class="w-4 h-4 text-slate-700 dark:text-slate-100" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span class="text-sm text-slate-700 dark:text-slate-50">Volver a Proveedores</span>
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <?php if ($error):
                echo "<div class='bg-red-50 dark:bg-rose-500/10 border-l-4 border-red-500 dark:border-rose-500 text-red-800 dark:text-rose-300 p-4 mb-6 rounded-r-lg' role='alert'><p class='font-bold'>Error</p><p>{$error}</p></div>";
            endif; ?>

            <section class="bg-white dark:bg-slate-900/70 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 backdrop-blur-sm">
                <div class="p-8">
                    <form action="agregar_proveedor.php" method="POST" class="space-y-6">
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nombre del Proveedor</label>
                            <input type="text" id="nombre" name="nombre" required
                                   class="block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/70 border border-slate-300 dark:border-slate-600 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-cyan-500 focus:border-cyan-500 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label for="telefono" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Teléfono</label>
                            <input type="tel" id="telefono" name="telefono"
                                   class="block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/70 border border-slate-300 dark:border-slate-600 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-cyan-500 focus:border-cyan-500 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                            <input type="email" id="email" name="email"
                                   class="block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/70 border border-slate-300 dark:border-slate-600 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-cyan-500 focus:border-cyan-500 text-slate-900 dark:text-white">
                        </div>
                        <div class="pt-4">
                            <button type="submit" class="w-full flex justify-center items-center space-x-2 bg-cyan-600 hover:bg-cyan-700 transition-colors text-white font-bold py-3 px-4 rounded-lg shadow-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                <span>Agregar Proveedor</span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
<?php include 'includes/chatbot_widget.php'; ?>
</body>
</html>
