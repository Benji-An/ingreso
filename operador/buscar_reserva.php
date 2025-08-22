<?php
header('Content-Type: application/json');
require_once '../config/connection.php'; // Ajusta la ruta según tu estructura

$connection = new Connection();
$conn = $connection->connect();

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM reservas WHERE id = ?");
$stmt->execute([$id]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if ($reserva) {
    echo json_encode($reserva);
} else {
    echo json_encode(['error' => 'Reserva no encontrada']);
}

?>