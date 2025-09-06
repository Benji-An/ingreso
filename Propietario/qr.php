<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar existencia de la librería
if (!file_exists(__DIR__ . '/phpqrcode/qrlib.php')) {
    header('Content-Type: text/plain');
    die('No se encuentra phpqrcode/qrlib.php');
}
require_once __DIR__ . '/phpqrcode/qrlib.php';
if (!class_exists('QRcode')) {
    header('Content-Type: text/plain');
    die('No se cargó la clase QRcode');
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('Falta el parámetro id');
}
$id = $_GET['id'];

header('Content-Type: image/png');
QRcode::png($id, null, QR_ECLEVEL_L, 8, 2);
exit;
