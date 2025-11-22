<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/functions.php';
$proveedores = getAllProveedores();
$config = obtenerConfiguracion(); 
?>
<!DOCTYPE html>
<html lang="es" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores - <?= htmlspecialchars($config['nombre_tienda']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="theme.js" defer></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <style>
        @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .dark .gradient-bg {
            background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
        }
        .dark body {
            background: linear-gradient(-45deg, #0f172a, #1e293b, #0e4a5f, #164e63);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: linear-gradient(45deg, #67e8f9, #60a5fa); border-radius: 3px; }
        .dark .custom-scrollbar::-webkit-scrollbar-track { background: #334155; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: linear-gradient(45deg, #06b6d4, #3b82f6); }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">

    <header class="bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 shadow-lg border-b border-cyan-300 dark:border-slate-700 sticky top-0 z-30">
        <div class="container mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center">
            <div class="flex items-center space-x-3 mb-4 sm:mb-0">
                <div class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-xl flex items-center justify-center shadow-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                <div><h1 class="text-2xl font-bold text-white dark:text-cyan-100 tracking-tight">Gestión de Proveedores</h1><p class="text-sm text-cyan-100 dark:text-slate-300 mt-1">Administra tus contactos comerciales</p></div>
            </div>
            <div class="flex items-center space-x-2">
                <button id="theme-toggle-btn" class="p-2 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 transition-colors" title="Cambiar tema">
                    <svg id="theme-icon-light" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <svg id="theme-icon-dark" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>
                <a href="index.php" class="flex items-center space-x-1 bg-slate-200 dark:bg-slate-600/90 hover:bg-slate-300 dark:hover:bg-slate-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-slate-300 dark:border-slate-400/20"><svg class="w-4 h-4 text-slate-700 dark:text-slate-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg><span class="text-sm text-slate-700 dark:text-slate-50">Volver</span></a>
                <a href="agregar_proveedor.php" class="flex items-center space-x-1 bg-cyan-600/90 hover:bg-cyan-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-cyan-400/20"><svg class="w-4 h-4 text-cyan-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg><span class="text-sm text-cyan-50">Nuevo</span></a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <?php if (isset($_SESSION['success'])): echo "<div class='bg-emerald-100 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 p-4 mb-6 rounded-lg' role='alert'><p class='font-bold'>Éxito</p><p>{$_SESSION['success']}</p></div>"; unset($_SESSION['success']); endif; ?>
        <?php if (isset($_SESSION['error'])): echo "<div class='bg-red-100 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-800 dark:text-red-300 p-4 mb-6 rounded-lg' role='alert'><p class='font-bold'>Error</p><p>{$_SESSION['error']}</p></div>"; unset($_SESSION['error']); endif; ?>

        <section class="bg-white/80 dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 via-blue-600 to-cyan-700 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border-b border-cyan-300 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-white">Listado de Proveedores</h2>
            </div>
            
            <?php if (empty($proveedores)):
                echo '<div class="p-8 flex flex-col items-center justify-center text-center"><svg class="w-16 h-16 text-slate-400 dark:text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.124-1.28-.35-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.124-1.28.35-1.857m0 0a3.001 3.001 0 015.3 0m-5.3 0a3.001 3.001 0 00-5.3 0m10.6 0a3.001 3.001 0 015.3 0m-5.3 0a3.001 3.001 0 00-5.3 0"></path></svg><p class="text-slate-600 dark:text-slate-400 font-medium">No hay proveedores registrados</p><p class="text-slate-500 dark:text-slate-500 text-sm mt-2">Añade un nuevo proveedor para empezar</p></div>';
            else:
                echo '<div class="overflow-x-auto custom-scrollbar"><table class="w-full"><thead class="bg-slate-100 dark:bg-slate-900/50"><tr class="border-b border-slate-200 dark:border-slate-700"><th class="px-6 py-3 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">ID</th><th class="px-6 py-3 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Nombre</th><th class="px-6 py-3 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase hidden md:table-cell">Teléfono</th><th class="px-6 py-3 text-left text-xs font-medium text-slate-600 dark:text-slate-400 uppercase hidden lg:table-cell">Email</th><th class="px-6 py-3 text-center text-xs font-medium text-slate-600 dark:text-slate-400 uppercase">Acciones</th></tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-700">';
                foreach ($proveedores as $proveedor) {
                    echo '<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">';
                    echo '<td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100">' . htmlspecialchars($proveedor['id']) . '</td>';
                    echo '<td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">' . htmlspecialchars($proveedor['nombre']) . '</td>';
                    echo '<td class="px-6 py-4 text-slate-700 dark:text-slate-300 hidden md:table-cell">' . htmlspecialchars($proveedor['telefono'] ?? 'N/A') . '</td>';
                    echo '<td class="px-6 py-4 text-slate-700 dark:text-slate-300 hidden lg:table-cell">' . htmlspecialchars($proveedor['email'] ?? 'N/A') . '</td>';
                    echo '<td class="px-6 py-4"><div class="flex justify-center items-center gap-3">';
                    echo '<a href="editar_proveedor.php?id=' . $proveedor['id'] . '" class="p-2 bg-blue-100 dark:bg-blue-500/20 hover:bg-blue-200 dark:hover:bg-blue-500/40 text-blue-600 dark:text-blue-300 rounded-lg transition-all" title="Editar"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></a>';
                    echo '<button type="button" class="delete-provider-btn p-2 bg-red-100 dark:bg-red-500/20 hover:bg-red-200 dark:hover:bg-red-500/40 text-red-600 dark:text-red-300 rounded-lg transition-all" title="Eliminar" data-provider-id="' . $proveedor['id'] . '" data-provider-name="' . htmlspecialchars($proveedor['nombre']) . '">';
                    echo '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
                    echo '</button>';
                    echo '</div></td>';
                    echo '</tr>';
                }
                echo '</tbody></table></div>';
            endif; ?>
        </section>
    </main>

<div id="delete-provider-modal" class="fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm z-[100] flex items-center justify-center p-4 hidden animate-fade-in">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md transform transition-all border border-slate-200 dark:border-slate-700">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-500/10 mb-4 ring-4 ring-red-200 dark:ring-red-500/20">
                <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Confirmar Eliminación</h3>
            <p class="mt-2 text-slate-600 dark:text-slate-400">¿Estás seguro de eliminar al proveedor <strong id="modal-provider-name" class="text-slate-900 dark:text-slate-100"></strong>? Esta acción no se puede deshacer.</p>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-slate-200 dark:border-slate-700">
            <button id="modal-provider-cancel-btn" type="button" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors">Cancelar</button>
            <a id="modal-provider-confirm-btn" href="#" class="px-6 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors">Eliminar</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteProviderModal = document.getElementById('delete-provider-modal');
    const modalProviderName = document.getElementById('modal-provider-name');
    const modalProviderCancelBtn = document.getElementById('modal-provider-cancel-btn');
    const modalProviderConfirmBtn = document.getElementById('modal-provider-confirm-btn');
    const deleteProviderButtons = document.querySelectorAll('.delete-provider-btn');

    function openProviderModal(providerId, providerName) {
        if(modalProviderName) modalProviderName.textContent = `"${providerName}"`;
        if(modalProviderConfirmBtn) modalProviderConfirmBtn.href = `eliminar_proveedor.php?id=${providerId}`;
        if(deleteProviderModal) deleteProviderModal.classList.remove('hidden');
    }

    function closeProviderModal() {
       if(deleteProviderModal) deleteProviderModal.classList.add('hidden');
    }

    deleteProviderButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const providerId = this.dataset.providerId;
            const providerName = this.dataset.providerName;
            openProviderModal(providerId, providerName);
        });
    });

    if(modalProviderCancelBtn) modalProviderCancelBtn.addEventListener('click', closeProviderModal);
    if(deleteProviderModal) deleteProviderModal.addEventListener('click', e => {
        if (e.target === deleteProviderModal) {
            closeProviderModal();
        }
    });
});
</script>

<?php include 'includes/chatbot_widget.php'; ?>
</body>
</html>