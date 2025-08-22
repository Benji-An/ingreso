<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/connection.php'; // Ajusta la ruta según tu estructura

$connection = new Connection();
$conn = $connection->connect();

session_start();
if (!isset($_SESSION['propietario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesión no válida.']);
    exit;
}
$propietario_id = $_SESSION['propietario_id']; // Asegúrate de que este valor esté en la sesión

try {
    $stmt = $conn->prepare("INSERT INTO reservas 
        (numero_documento, primer_apellido, segundo_apellido, primer_nombre, segundo_nombre, genero, fecha_nacimiento, torre, apartamento, propietario_id, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo')");
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
        $propietario_id // Usar el id de la sesión
    ]);
    $idReserva = $conn->lastInsertId();
    echo json_encode(['id' => $idReserva]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar la reserva', 'detalle' => $e->getMessage()]);
}

// Cierre de conexión
$conn = null;   
?>