

<?php
date_default_timezone_set('America/Bogota');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$conexion = new mysqli("localhost", "root", "", "control_acceso");

$id = $_GET['id'];
$sql = "UPDATE visitantes SET estado='Inactivo', hora_salida=NOW() WHERE id=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    // Obtener información del visitante y propietario para el correo
    // Relacionar visitante y propietario por apartamento y torre
    $sql_info = "SELECT v.primer_nombre, v.segundo_nombre, v.primer_apellido, v.segundo_apellido, v.tipo_documento, v.numero_documento, p.nombre AS propietario_nombre, p.email AS propietario_email FROM visitantes v JOIN propietarios p ON v.apartamento = p.apartamento AND v.torre = p.torre WHERE v.id=?";
    $stmt_info = $conexion->prepare($sql_info);
    $stmt_info->bind_param("i", $id);
    $stmt_info->execute();
    $result = $stmt_info->get_result();
    $info = $result->fetch_assoc();
    if (!$info || empty($info['propietario_email'])) {
        echo "Salida registrada correctamente, pero no se encontró propietario o email para notificar.";
        $stmt_info->close();
        $stmt->close();
        $conexion->close();
        exit;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'benjaminibarrajimnez@gmail.com'; // Tu correo
        $mail->Password = 'plju mndc idfq ckdp'; // Contraseña de aplicación de Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('tucorreo@gmail.com', 'Sistema de Ingreso');
        $mail->addAddress($info['propietario_email'], $info['propietario_nombre']);


        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = 'Notificación de Salida Registrada';
        $mail->Body = '
            <div style="background: #f4f8fb; padding: 30px;">
                <div style="max-width: 500px; margin: auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.10); padding: 32px 28px 28px 28px; text-align: center;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/Meetup_Logo.png" alt="Presentación" style="width:180px; margin-bottom:24px;">
                    <h2 style="color:#2E86C1; margin-bottom: 18px;">Notificación de Salida</h2>
                    <p style="color:#222; font-size: 16px; margin-bottom: 10px;">Estimado(a) <strong>' . htmlspecialchars($info['propietario_nombre']) . '</strong>,</p>
                    <p style="color:#333; font-size: 15px; margin-bottom: 18px;">Le informamos que el siguiente visitante ha registrado su salida del establecimiento:</p>
                    <table style="width:100%; border-collapse:collapse; margin:18px 0 22px 0;">
                        <tr style="background:#f0f4fa;"><th style="text-align:left; padding:6px 8px; border-radius:6px 0 0 6px;">Nombre</th><th style="text-align:left; padding:6px 8px;">Documento</th><th style="text-align:left; padding:6px 8px; border-radius:0 6px 6px 0;">Fecha y hora de salida</th></tr>
                        <tr>
                            <td style="padding:6px 8px;">' . $info['primer_nombre'] . ' ' . $info['segundo_nombre'] . ' ' . $info['primer_apellido'] . ' ' . $info['segundo_apellido'] . '</td>
                            <td style="padding:6px 8px;">' . $info['tipo_documento'] . ' ' . $info['numero_documento'] . '</td>
                            <td style="padding:6px 8px;">' . date('d/m/Y H:i') . '</td>
                        </tr>
                    </table>
                    <p style="margin-top:18px; color:#333; font-size: 15px;">Si tiene alguna consulta, por favor comuníquese con la administración.</p>
                    <div style="margin-top: 30px; color:#888; font-size:13px;">Este es un mensaje automático, por favor no responda a este correo.</div>
                </div>
            </div>';

        $mail->send();
        echo "Salida registrada correctamente y correo enviado";
    } catch (Exception $e) {
        echo "Salida registrada correctamente, pero error al enviar correo: {$mail->ErrorInfo}";
    }
    $stmt_info->close();
} else {
    echo "Error al registrar la salida";
}
$stmt->close();
$conexion->close();
?>
