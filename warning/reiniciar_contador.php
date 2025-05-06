<?php
require_once 'includes/db.php';

function reiniciarAutoIncrement() {
    $conn = conectarDB();
    $sql = "ALTER TABLE productos AUTO_INCREMENT = 1";
    
    if ($conn->query($sql) === TRUE) {
        echo "El contador de AUTO_INCREMENT ha sido reiniciado exitosamente.";
    } else {
        echo "Error al reiniciar el contador: " . $conn->error;
    }
    
    $conn->close();
}

// Ejecutar la función
reiniciarAutoIncrement();
?>