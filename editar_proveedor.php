<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Solo admin puede editar
require_once 'includes/functions.php';
requireAdmin();

$error = '';
$success = '';
$proveedor = null;
$config = obtenerConfiguracion(); // Obtener config para el título

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $proveedor = getProveedorById($id);
    if (!$proveedor) {
        $_SESSION['error'] = "Proveedor no encontrado.";
        header("Location: proveedores.php");
        exit;
    }
} else {
    $_SESSION['error'] = "ID de proveedor no especificado.";
    header("Location: proveedores.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_post = (int)$_POST['id']; // Asegurar que el ID del post coincide
    if ($id_post !== $id) {
         $error = "Intento de modificación no válido.";
    } else {
        $nombre = trim($_POST['nombre'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nombre)) {
            $error = "El nombre del proveedor es obligatorio.";
        } else {
            if (updateProveedor($id, $nombre, $telefono, $email)) {
                $_SESSION['success'] = "Proveedor actualizado exitosamente."; // Usar $_SESSION para mensaje post-redirección
                header("Location: proveedores.php"); // Redirigir a la lista tras éxito
                exit;
            } else {
                $error = "Error al actualizar el proveedor. El email podría ya existir.";
                $proveedor = getProveedorById($id); // Recargar datos actuales si falla
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" class=""> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor - <?= htmlspecialchars($config['nombre_tienda']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="theme.js" defer></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <style>
        @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .dark .gradient-bg { background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63); background-size: 400% 400%; animation: gradient-shift 15s ease infinite; }
    </style>
</head>
<body class="bg-slate-100 dark:bg-transparent text-slate-800 dark:text-slate-200 min-h-screen">
<div class="hidden dark:block gradient-bg fixed inset-0 -z-10"></div>

<header class="bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 shadow-lg border-b border-cyan-300 dark:border-slate-700 sticky top-0 z-30">
    <div class="container mx-auto px-6 py-4 flex items-center justify-between">
         <div class="flex items-center space-x-3">
             <div class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-xl flex items-center justify-center shadow-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
             <div><h1 class="text-2xl font-bold text-white">Editar Proveedor</h1><p class="text-sm text-cyan-100 mt-1">Actualiza los datos del contacto</p></div>
        </div>
        <div class="flex items-center space-x-3">
             <button id="theme-toggle-btn" class="p-2 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 transition-colors" title="Cambiar tema">
                 <svg id="theme-icon-light" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                 <svg id="theme-icon-dark" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
             </button>
            <a href="proveedores.php" class="flex items-center space-x-1 bg-slate-200 dark:bg-slate-600/90 hover:bg-slate-300 dark:hover:bg-slate-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-slate-300 dark:border-slate-400/20">
                <svg class="w-4 h-4 text-slate-700 dark:text-slate-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                <span class="text-sm text-slate-700 dark:text-slate-50">Volver</span>
            </a>
        </div>
    </div>
</header>

<main class="container mx-auto px-4 py-8 max-w-2xl">
    
    <?php if ($error): ?>
        <div class="bg-red-100 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-800 dark:text-red-300 p-4 mb-6 rounded-lg" role="alert"><p class="font-bold">Error</p><p><?= htmlspecialchars($error) ?></p></div>
    <?php endif; ?>

    <?php if ($proveedor): ?>
        <section class="bg-white dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border-b border-cyan-300 dark:border-slate-700 rounded-t-xl">
                <h2 class="text-lg font-semibold text-white">Información del Proveedor #<?= htmlspecialchars($proveedor['id']) ?></h2>
            </div>
            <div class="p-6 sm:p-8">
                <form action="editar_proveedor.php?id=<?= $proveedor['id'] ?>" method="POST" class="space-y-6">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($proveedor['id']) ?>">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($proveedor['nombre']) ?>" required
                               class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors">
                    </div>
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($proveedor['telefono'] ?? '') ?>"
                               class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($proveedor['email'] ?? '') ?>"
                               class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors">
                    </div>
                    <div class="flex items-center justify-end pt-4">
                        <button type="submit" class="flex items-center justify-center space-x-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:shadow-lg hover:shadow-cyan-500/30 transition-all transform hover:-translate-y-1 px-6 py-2.5 rounded-xl shadow-md text-white font-semibold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Actualizar Proveedor</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>
    <?php endif; ?>
</main>

<script src="theme.js" defer></script>

<?php include 'includes/chatbot_widget.php'; ?>
</body>
</html>