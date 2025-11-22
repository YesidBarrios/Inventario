<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'includes/functions.php';

$reporte_tipo = $_GET['reporte'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01 00:00:00');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t 23:59:59');

if (empty($reporte_tipo)) {
    die("Tipo de reporte no especificado.");
}

$filename = "reporte_{$reporte_tipo}_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");

switch ($reporte_tipo) {
    case 'ventas_periodo':
        fputcsv($output, ['ID Venta', 'Producto', 'Cantidad', 'Unidad', 'Precio Unitario', 'Precio Total', 'Usuario', 'Fecha', 'ID Transacción'], ';');
        $datos = obtenerVentasPorPeriodo($fecha_inicio, $fecha_fin);
        foreach ($datos as $fila) {
            fputcsv($output, [
                $fila['id'],
                $fila['nombre_producto'],
                $fila['cantidad_vendida'],
                $fila['unidad_vendida'],
                $fila['precio_unitario'],
                $fila['precio_total'],
                $fila['nombre_usuario'] ?? 'N/A',
                $fila['fecha_venta'],
                $fila['transaccion_id']
            ], ';');
        }
        break;

    case 'compras_periodo':
        fputcsv($output, ['ID Compra', 'Proveedor', 'Total Compra', 'Fecha'], ';');
        $datos = obtenerComprasPorPeriodo($fecha_inicio, $fecha_fin);
        foreach ($datos as $fila) {
            fputcsv($output, [
                $fila['id'],
                $fila['nombre_proveedor'] ?? 'N/A',
                $fila['total_compra'],
                $fila['fecha_compra']
            ], ';');
        }
        break;

    default:
        fputcsv($output, ['Error' => 'Tipo de reporte no válido.'], ';');
        break;
}

fclose($output);
exit;