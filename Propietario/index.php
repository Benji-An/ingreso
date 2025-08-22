<?php
    session_start();
    if (!isset($_SESSION['username']) || $_SESSION['role_id'] != 3) {
        header('Location: ../index.php');
        exit;
    }

    require_once '../config/connection.php';
    $connection = new Connection();
    $conn = $connection->connect();

    // Obtener información personal del propietario
    $stmt = $conn->prepare("SELECT * FROM propietarios WHERE username = :username");
    $stmt->execute([':username' => $_SESSION['username']]);
    $propietario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Establecer el ID del propietario en la sesión
    $_SESSION['propietario_id'] = $propietario['id'];

    // Personas actualmente en la residencia (solo visitantes sin salida)
    $sqlPresentes = "
        SELECT 
            CONCAT_WS(' ', v.primer_apellido, v.segundo_apellido, v.primer_nombre, v.segundo_nombre) AS nombre,
            v.tipo_visitante AS tipo,
            v.hora_entrada AS fecha_ingreso,
            NULL AS salida
        FROM visitantes v
        WHERE v.torre = :torre AND v.apartamento = :apartamento AND v.hora_salida IS NULL
    ";
    $stmt2 = $conn->prepare($sqlPresentes);
    $stmt2->execute([
        ':torre' => $propietario['torre'],
        ':apartamento' => $propietario['apartamento']
    ]);
    $presentes = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Historial de ingresos (últimos 50, solo titulares)
    $sqlHistorial = "
        SELECT 
            CONCAT_WS(' ', v.primer_apellido, v.segundo_apellido, v.primer_nombre, v.segundo_nombre) AS nombre,
            v.tipo_visitante AS tipo,
            v.hora_entrada AS fecha_ingreso,
            v.hora_salida AS salida
        FROM visitantes v
        WHERE v.torre = :torre AND v.apartamento = :apartamento
        ORDER BY fecha_ingreso DESC
        LIMIT 50
    ";
    $stmt3 = $conn->prepare($sqlHistorial);
    $stmt3->execute([
        ':torre' => $propietario['torre'],
        ':apartamento' => $propietario['apartamento']
    ]);
    $historial = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents('debug_post.txt', print_r($_POST, true));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Propietario</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Bienvenido, <?php echo htmlspecialchars($propietario['nombre']); ?></h2>
        <div class="card mb-4">
            <div class="card-header">Información Personal</div>
            <div class="card-body">
                <p><strong>Torre:</strong> <?php echo htmlspecialchars($propietario['torre']); ?></p>
                <p><strong>Apartamento:</strong> <?php echo htmlspecialchars($propietario['apartamento']); ?></p>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($propietario['nombre']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($propietario['email']); ?></p>
                <p><strong>Ocupación máxima:</strong> <?php echo htmlspecialchars($propietario['ocupacion_maxima']); ?></p>
                <p><strong>Usuario:</strong> <?php echo htmlspecialchars($propietario['username']); ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Personas actualmente en la residencia</div>
            <div class="card-body">
                <?php if (count($presentes) > 0): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Fecha de ingreso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($presentes as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($p['tipo']); ?></td>
                                    <td><?php echo htmlspecialchars($p['fecha_ingreso']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No hay personas actualmente en la residencia.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Historial de ingresos</div>
            <div class="card-body">
                <?php if (count($historial) > 0): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Fecha de ingreso</th>
                                <th>Fecha de salida</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $h): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($h['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($h['tipo']); ?></td>
                                    <td><?php echo htmlspecialchars($h['fecha_ingreso']); ?></td>
                                    <td><?php echo htmlspecialchars($h['salida'] ?? '---'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No hay historial de ingresos.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reserva para visitantes -->
        <div class="card mb-4">
            <div class="card-header">Hacer una Reserva</div>
            <div class="card-body">
                <form id="formReserva" method="POST">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Número de Documento</label>
                            <input type="text" name="numero_documento" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Primer Apellido</label>
                            <input type="text" name="primer_apellido" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Segundo Apellido</label>
                            <input type="text" name="segundo_apellido" class="form-control">
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-4">
                            <label class="form-label">Primer Nombre</label>
                            <input type="text" name="primer_nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Segundo Nombre</label>
                            <input type="text" name="segundo_nombre" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Género</label>
                            <select name="genero" class="form-select" required>
                                <option value="">Seleccione</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="O">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-4">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Torre</label>
                            <input type="text" name="torre" class="form-control" value="<?php echo htmlspecialchars($propietario['torre']); ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Apartamento</label>
                            <input type="text" name="apartamento" class="form-control" value="<?php echo htmlspecialchars($propietario['apartamento']); ?>" readonly>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Generar QR</button>
                    </div>
                </form>
                <div id="qrReserva" class="mt-4 text-center"></div>
            </div>
        </div>

        <a href="../InicioSesion/CerrarSesion.php" class="btn btn-danger">Cerrar sesión</a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="reserva.js"></script>
</body>
</html>