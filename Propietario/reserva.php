<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/connection.php';
$connection = new Connection();
$conn = $connection->connect();

// Guardar reserva con todos los datos del formulario

session_start();
if (!isset($_SESSION['propietario_id'])) {
    echo json_encode(['error' => 'Sesión no válida.']);
    exit;
}
$required = ['numero_documento', 'primer_apellido', 'segundo_apellido', 'primer_nombre', 'segundo_nombre', 'genero', 'fecha_nacimiento', 'torre', 'apartamento'];
foreach ($required as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(['error' => 'Falta el campo: ' . $field]);
        exit;
    }
}

try {
    $stmt = $conn->prepare("INSERT INTO reservas (numero_documento, primer_apellido, segundo_apellido, primer_nombre, segundo_nombre, genero, fecha_nacimiento, torre, apartamento, propietario_id, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo')");
    $stmt->execute([
        $_POST['numero_documento'],
        $_POST['primer_apellido'],
        $_POST['segundo_apellido'],
        $_POST['primer_nombre'],
        $_POST['segundo_nombre'],
        $_POST['genero'],
        $_POST['fecha_nacimiento'],
        $_POST['torre'],
        $_POST['apartamento'],
        $_SESSION['propietario_id']
    ]);
    $idReserva = $conn->lastInsertId();
    echo json_encode(['id' => $idReserva]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar la reserva', 'detalle' => $e->getMessage()]);
}
$conn = null;
?>