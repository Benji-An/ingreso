<?php
try {
    $conn = new PDO('mysql:host=localhost;dbname=control_acceso;charset=utf8mb4', 'root', '');
    echo "Conexión exitosa";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}