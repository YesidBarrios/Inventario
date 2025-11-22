<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';

// Obtener todo el historial de ventas
$historial_ventas = obtenerHistorialVentas();

$filename = "reporte_ventas_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");

// Escribir la fila de encabezado
fputcsv($output, array('ID Venta', 'Producto', 'Cantidad Vendida', 'Precio Unitario', 'Precio Total', 'Fecha de Venta', 'Usuario'), ';');

// Escribir los datos de las ventas
if (!empty($historial_ventas)) {
    foreach ($historial_ventas as $venta) {
        fputcsv($output, array(
            $venta['id'],
            $venta['nombre_producto'],
            $venta['cantidad_vendida'],
            $venta['precio_unitario'],
            $venta['precio_total'],
            $venta['fecha_venta'],
            $venta['nombre_usuario'] ?? 'N/A'
        ), ';');
    }
}

fclose($output);
exit;
?>