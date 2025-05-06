<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';

$historial_ventas = obtenerHistorialVentas();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas - Gestor de Inventario</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-100 to-slate-50">
    <header class="bg-gradient-to-r from-slate-900 to-slate-800 shadow-xl">
        <div class="container mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center">
            <div class="flex items-center space-x-2 mb-4 sm:mb-0">
                <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <h1 class="text-2xl font-bold text-cyan-100 tracking-tight">Historial de Transacciones</h1>
            </div>
            <a href="registrar_venta.php" class="flex items-center space-x-1 bg-cyan-600/90 hover:bg-cyan-700 transition-all px-4 py-2 rounded-lg shadow-sm border border-cyan-400/20">
                <svg class="w-4 h-4 text-cyan-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="text-sm text-cyan-50">Nueva Venta</span>
            </a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <section class="bg-white rounded-xl shadow-2xl border border-slate-200">
            <div class="px-6 py-4 bg-slate-900 border-b">
                <h2 class="text-lg font-semibold text-cyan-100">Registro Completo de Ventas</h2>
            </div>
            
            <?php if (!empty($historial_ventas)): ?>
                <div class="overflow-x-auto p-6">
                    <table class="w-full">
                        <thead class="bg-slate-800 text-slate-200 text-sm">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">ID</th>
                                <th class="px-6 py-3 text-left font-medium">Producto</th>
                                <th class="px-6 py-3 text-left font-medium">Cantidad</th>
                                <th class="px-6 py-3 text-left font-medium">P. Unitario</th>
                                <th class="px-6 py-3 text-left font-medium">Total</th>
                                <th class="px-6 py-3 text-left font-medium">Fecha</th>
                                <th class="px-6 py-3 text-left font-medium hidden md:table-cell">Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-700">
                            <?php foreach ($historial_ventas as $venta): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900"><?php echo $venta['id']; ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($venta['nombre_producto']); ?></td>
                                    <td class="px-6 py-4"><?php echo $venta['cantidad_vendida']; ?></td>
                                    <td class="px-6 py-4 font-semibold text-emerald-700">$<?php echo number_format($venta['precio_unitario'], 2); ?></td>
                                    <td class="px-6 py-4 font-semibold text-cyan-700">$<?php echo number_format($venta['precio_total'], 2); ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?php echo $venta['fecha_venta']; ?></td>
                                    <td class="px-6 py-4 text-slate-600 hidden md:table-cell"><?php echo $venta['usuario_id']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-8 flex flex-col items-center justify-center text-center">
                    <svg class="w-16 h-16 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H5a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-slate-600 font-medium">No se encontraron registros de ventas</p>
                    <p class="text-slate-500 text-sm mt-2">Todas las transacciones aparecerán aquí</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>