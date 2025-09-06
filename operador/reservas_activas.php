<!-- Ver las reservas activas -->
<?php
require_once '../config/connection.php';

$connection = new Connection();
$conn = $connection->connect();

// Procesar el ingreso de la reserva
if (isset($_POST['ingresar_reserva_id'])) {
    $id = $_POST['ingresar_reserva_id'];
    // Guardar los datos de la reserva para pasarlos a index.php
    $params = [
        'numero_documento' => $_POST['numero_documento'],
        'primer_apellido' => $_POST['primer_apellido'],
        'segundo_apellido' => $_POST['segundo_apellido'],
        'primer_nombre' => $_POST['primer_nombre'],
        'segundo_nombre' => $_POST['segundo_nombre'],
        'genero' => $_POST['genero'],
        'fecha_nacimiento' => $_POST['fecha_nacimiento'],
        'torre' => $_POST['torre'],
        'apartamento' => $_POST['apartamento'],
        'reserva_id' => $_POST['ingresar_reserva_id']
    ];
    $stmt = $conn->prepare("UPDATE reservas SET estado = 'Inactivo' WHERE id = ?");
    $stmt->execute([$id]);
    // Redirigir a index.php con los datos de la reserva
    $query = http_build_query($params);
    header(header: "Location: index.php?" . $query);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM reservas WHERE estado = 'Activo'");
    $stmt->execute();
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener las reservas: ' . $e->getMessage()]);
}
$conn = null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas Activas</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/fontawesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap">   
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="http://localhost/ingreso/Operador/index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="reservas_activas.php">Reservas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="24horas.php">Últimas 24 horas</a>
                    </li>
                </ul>
            </div>
        <div class="d-flex">
            <a href="http://localhost/ingreso/InicioSesion/CerrarSesion.php" class="btn2 btn-danger">Cerrar Sesión</a>
        </div>
    </nav>
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-md-12">
                <div class="form-container">
                    <h3 class="mb-4">Reservas Activas</h3>
                    <table class="table table-hover table-bordered align-middle main-box mt-4" style="border-radius: 0 !important; font-size: 14px;">
            <thead>
                <tr class="bg-primary">
                    <th>Nombre</th>
                    <th>Torre</th>
                    <th>Apartamento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $reserva) : ?>
                    <tr>
                        <td><?php echo $reserva['primer_nombre'] . ' ' . $reserva['segundo_nombre'] . ' ' . $reserva['primer_apellido'] . ' ' . $reserva['segundo_apellido']; ?></td>
                        <td><?php echo $reserva['torre']; ?></td>
                        <td><?php echo $reserva['apartamento']; ?></td>
                        <td>
                            <!-- Formulario para ingresar la reserva -->
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="ingresar_reserva_id" value="<?php echo $reserva['id']; ?>">
                                <input type="hidden" name="numero_documento" value="<?php echo htmlspecialchars($reserva['numero_documento']); ?>">
                                <input type="hidden" name="primer_apellido" value="<?php echo htmlspecialchars($reserva['primer_apellido']); ?>">
                                <input type="hidden" name="segundo_apellido" value="<?php echo htmlspecialchars($reserva['segundo_apellido']); ?>">
                                <input type="hidden" name="primer_nombre" value="<?php echo htmlspecialchars($reserva['primer_nombre']); ?>">
                                <input type="hidden" name="segundo_nombre" value="<?php echo htmlspecialchars($reserva['segundo_nombre']); ?>">
                                <input type="hidden" name="genero" value="<?php echo htmlspecialchars($reserva['genero']); ?>">
                                <input type="hidden" name="fecha_nacimiento" value="<?php echo htmlspecialchars($reserva['fecha_nacimiento']); ?>">
                                <input type="hidden" name="torre" value="<?php echo htmlspecialchars($reserva['torre']); ?>">
                                <input type="hidden" name="apartamento" value="<?php echo htmlspecialchars($reserva['apartamento']); ?>">
                                <button type="submit" class="btn btn-success btn-sm">Ingresar Reserva</button>
                            </form>
                            <!-- Botón para eliminar (opcional, ya estaba) -->
                            <button class="btn btn-danger btn-sm" data-id="<?php echo $reserva['id']; ?>">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-3"></div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</html>
