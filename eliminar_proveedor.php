<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/functions.php';
requireAdmin();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Función para eliminar proveedor (la crearemos a continuación)
    if (deleteProveedor($id)) {
        $_SESSION['success'] = "Proveedor eliminado exitosamente.";
    } else {
        $_SESSION['error'] = "Error al eliminar el proveedor.";
    }
} else {
    $_SESSION['error'] = "ID de proveedor no especificado.";
}

header("Location: proveedores.php");
exit;
?>