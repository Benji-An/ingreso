<?php
date_default_timezone_set('America/Bogota');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$conexion = new mysqli("localhost", "root", "", "control_acceso");


if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if (isset($_POST['visitantes']) && is_array($_POST['visitantes'])) {
    $visitantesRegistrados = [];
    foreach ($_POST['visitantes'] as $visitante) {
        $foto_visitante = isset($visitante['foto_visitante']) ? $visitante['foto_visitante'] : null;
        if ($foto_visitante && strpos($foto_visitante, 'data:image') === 0) {
            $foto_visitante = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $foto_visitante));
        }

        $tipo_documento = $visitante['tipo_documento'];
        $numero_documento = $visitante['numero_documento'];
        $torre = $visitante['torre'];
        $apartamento = $visitante['apartamento'];
        $tipo_visitante = $visitante['tipo_visitante'];
        $numero_manilla = isset($visitante['numero_manilla']) ? $visitante['numero_manilla'] : null;
        $tiene_vehiculo = isset($visitante['tiene_vehiculo']) ? 1 : 0;
        $placa = isset($visitante['placa']) ? $visitante['placa'] : null;
        $marca = isset($visitante['marca']) ? $visitante['marca'] : null;
        $color = isset($visitante['color']) ? $visitante['color'] : null;
        $primer_apellido = $visitante['primer_apellido'];
        $segundo_apellido = $visitante['segundo_apellido'];
        $primer_nombre = $visitante['primer_nombre'];
        $segundo_nombre = $visitante['segundo_nombre'];
        $genero = $visitante['genero'];
        $fecha_nacimiento = $visitante['fecha_nacimiento'];
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha_nacimiento, $m)) {
            $fecha_nacimiento = "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        $stmt = $conexion->prepare("INSERT INTO visitantes (
            tipo_documento, numero_documento, torre, apartamento, tipo_visitante, numero_manilla, tiene_vehiculo, placa, marca, color, estado, foto, primer_apellido, segundo_apellido, primer_nombre, segundo_nombre, genero, fecha_nacimiento
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo', ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sssssssssssssssss",
            $tipo_documento,
            $numero_documento,
            $torre,
            $apartamento,
            $tipo_visitante,
            $numero_manilla,
            $tiene_vehiculo,
            $placa,
            $marca,
            $color,
            $foto_visitante,
            $primer_apellido,
            $segundo_apellido,
            $primer_nombre,
            $segundo_nombre,
            $genero,
            $fecha_nacimiento
        );

        if ($stmt->execute()) {
            // Guardar datos para el correo
            $visitantesRegistrados[] = [
                'primer_nombre' => $primer_nombre,
                'segundo_nombre' => $segundo_nombre,
                'primer_apellido' => $primer_apellido,
                'segundo_apellido' => $segundo_apellido,
                'tipo_documento' => $tipo_documento,
                'numero_documento' => $numero_documento,
                'torre' => $torre,
                'apartamento' => $apartamento,
                'fecha_hora' => date('Y-m-d H:i:s'),
            ];

            // Si hay reserva_id, marcar como inactiva
            if (!empty($visitante['reserva_id'])) {
                $reserva_id = $visitante['reserva_id'];
                $stmt_reserva = $conexion->prepare("UPDATE reservas SET estado = 'Inactivo' WHERE id = ?");
                $stmt_reserva->bind_param("i", $reserva_id);
                $stmt_reserva->execute();
                $stmt_reserva->close();
            }
        }
        $stmt->close();
    }
    // Enviar correo con todos los visitantes registrados
    if (count($visitantesRegistrados) > 0) {
        // Usar los datos del primer visitante para buscar el propietario
        $primer = $visitantesRegistrados[0];
        $stmt_propietario = $conexion->prepare("SELECT email, nombre FROM propietarios WHERE torre = ? AND apartamento = ?");
        $stmt_propietario->bind_param("ss", $primer['torre'], $primer['apartamento']);
        $stmt_propietario->execute();
        $resultado_propietario = $stmt_propietario->get_result();
        $propietario = $resultado_propietario->fetch_assoc();
        if ($propietario && filter_var($propietario['email'], FILTER_VALIDATE_EMAIL)) {
            require_once __DIR__ . '/PHPMailer/src/Exception.php';
            require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
            require_once __DIR__ . '/PHPMailer/src/SMTP.php';
            $mail = new PHPMailer(true);
            try {
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'benjaminibarrajimnez@gmail.com';
                $mail->Password = 'irdf bdum laqc qltm';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->setFrom('tucorreo@gmail.com', 'Sistema de Ingreso');
                $mail->addAddress($propietario['email'], $propietario['nombre']);
                $mail->Subject = 'Notificación de visita';
                $mail->isHTML(true);
                $body = '<div style="font-family: Arial, Helvetica, sans-serif; background: #f4f8fb; padding: 30px;">
                    <div style="max-width: 500px; margin: auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 30px;">
                        <div style="text-align:center; margin-bottom: 20px;">
                            <img src="https://via.placeholder.com/180x60?text=Logo" alt="Presentación" style="max-width: 180px; margin-bottom: 10px;">
                        </div>
                        <h2 style="color: #2a4365; margin-bottom: 10px;">Notificación de Registro de Visita</h2>
                        <p style="color: #333; font-size: 16px;">Estimado(a) <strong>' . htmlspecialchars($propietario['nombre']) . '</strong>,</p>
                        <p style="color: #333; font-size: 15px;">Le informamos que se ha registrado el ingreso de los siguientes visitantes a su apartamento. Por favor, verifique la información a continuación:</p>';
                $body .= '<table style="width:100%; border-collapse:collapse; margin: 20px 0;">';
                $body .= '<tr><th style="text-align:left; padding:4px;">Nombre</th><th style="text-align:left; padding:4px;">Documento</th><th style="text-align:left; padding:4px;">Fecha y hora</th></tr>';
                foreach ($visitantesRegistrados as $v) {
                    $body .= '<tr>';
                    $body .= '<td style="padding:4px;">' . htmlspecialchars($v['primer_nombre'] . ' ' . $v['segundo_nombre'] . ' ' . $v['primer_apellido'] . ' ' . $v['segundo_apellido']) . '</td>';
                    $body .= '<td style="padding:4px;">' . htmlspecialchars($v['tipo_documento'] . ' ' . $v['numero_documento']) . '</td>';
                    $body .= '<td style="padding:4px;">' . htmlspecialchars($v['fecha_hora']) . '</td>';
                    $body .= '</tr>';
                }
                $body .= '</table>';
                $body .= '<p style="color: #333; font-size: 15px;">Si usted no reconoce a estas personas, por favor comuníquese de inmediato con la administración.</p>';
                $body .= '<div style="text-align:center; margin-top: 30px;"><small style="color: #888;">Este es un mensaje automático del Sistema de Control de Ingreso.</small></div></div></div>';
                $mail->Body = $body;
                $mail->send();
            } catch (Exception $e) {
                error_log('Mailer Error: ' . $mail->ErrorInfo);
            }
        }
    }
    header("Location: index.php?registro=exitoso");
    exit();
} else {
    echo "No se recibieron datos de visitantes.";
}

$conexion->close();
    $resultado_propietario = $stmt_propietario->get_result();

