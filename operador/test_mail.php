<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'benjaminibarrajimnez@gmail.com'; // Tu correo
    $mail->Password = 'plju mndc idfq ckdp'; // Contraseña de aplicación de Gmail
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('tucorreo@gmail.com', 'Sistema');
    $mail->addAddress('destino@ejemplo.com', 'Propietario');
    $mail->Subject = 'Prueba de correo PHPMailer';
    $mail->Body = '¡Este es un correo de prueba usando PHPMailer y SMTP!';

    $mail->send();
    echo 'Correo enviado correctamente';
} catch (Exception $e) {
    echo "Error: {$mail->ErrorInfo}";
}