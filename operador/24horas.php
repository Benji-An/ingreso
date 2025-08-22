<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role_id'] != 2) {
    header('Location: ../index.php');
    exit;
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de las últimas 24 horas</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="http://localhost/ingreso/Operador/index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="reservas_activas.php">Reservas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="24horas.php">Últimas 24 horas</a>
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
                    <h3 class="mb-4">Registros de las últimas 24 horas</h3>
                    <table class="table table-hover table-bordered align-middle main-box mt-4" style="border-radius: 0 !important; font-size: 14px;">
                        <thead class="table-light">
                            <tr>
                                <th>Foto</th>
                                <th>Nombre</th>
                                <th>Género</th>
                                <th>Fecha de Nacimiento</th>
                                <th>Tipo de Documento</th>
                                <th>Número</th>
                                <th>Torre</th>
                                <th>Apartamento</th>
                                <th>Numero de manilla</th>
                                <th>Estado</th>
                                <th>Hora de Ingreso</th>
                                <th>Hora de Salida</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $conexion = new mysqli("localhost", "root", "", "control_acceso");
                            $fechaLimite = date('Y-m-d H:i:s', strtotime('-24 hours'));

                            // Usar el campo correcto de ingreso, por ejemplo 'hora_entrada' o 'fecha_ingreso'
                            $sql = "SELECT * FROM visitantes WHERE hora_entrada >= DATE_SUB(NOW(), INTERVAL 1 DAY);";
                            $resultado = $conexion->query($sql);

                            while ($fila = $resultado->fetch_assoc()) {
                                // Visitante principal
                                echo "<tr>
                                        <td>";
                                if (!empty($fila['foto'])) {
                                    echo "<img src='data:image/jpeg;base64," . base64_encode($fila['foto']) . "' alt='Foto' style='width:60px;height:60px;object-fit:cover;border-radius:50%;'>";
                                } else {
                                    echo "<span style='color:gray'>Sin foto</span>";
                                }
                                echo "</td>
                                        <td>{$fila['primer_apellido']} {$fila['segundo_apellido']} {$fila['primer_nombre']} {$fila['segundo_nombre']}</td>
                                        <td>{$fila['genero']}</td>
                                        <td>{$fila['fecha_nacimiento']}</td>
                                        <td>{$fila['tipo_documento']}</td>
                                        <td>{$fila['numero_documento']}</td>
                                        <td>{$fila['torre']}</td>
                                        <td>{$fila['apartamento']}</td>
                                        <td>{$fila['numero_manilla']}</td>
                                        <td>{$fila['estado']}</td>
                                        <td>{$fila['hora_entrada']}</td>
                                        <td>" . ($fila['hora_salida'] ? $fila['hora_salida'] : 'Pendiente') . "</td>
                                    </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</body>
</html>
