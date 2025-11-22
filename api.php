<?php
header('Content-Type: application/json');
session_start();
require_once 'includes/functions.php';

// Verificación de que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_product_details':
        $id = $_GET['id'] ?? 0;
        if (empty($id)) {
            echo json_encode(['error' => 'ID de producto no válido']);
            exit;
        }

        $producto = obtenerProductoPorId((int)$id);
        if (!$producto) {
            echo json_encode(['error' => 'Producto no encontrado']);
            exit;
        }

        // Obtener todas las unidades para conversiones
        $unidades = getAllUnidades();
        $producto['unidades_compatibles'] = $unidades; // De momento, devolvemos todas. Se podría filtrar por tipo.

        echo json_encode($producto);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
