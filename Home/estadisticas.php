<?php
    session_start();

    // Verifica si el usuario ha iniciado sesión
    if (!isset($_SESSION['username'])) {
        header('Location: ../index.php');
        exit;
    }

    // Verifica el rol del usuario
    if ($_SESSION['role_id'] !== 1) {
        echo "Acceso denegado. Solo los administradores pueden acceder a esta página.";
        exit;
    }

    $conexion = new mysqli("localhost", "root", "", "control_acceso");

    // Ejemplo de consulta: total de propietarios y ocupación máxima total
    $totalPropietarios = $conexion->query("SELECT COUNT(*) as total FROM propietarios")->fetch_assoc()['total'];
    $ocupacionTotal = $conexion->query("SELECT SUM(ocupacion_maxima) as total FROM propietarios")->fetch_assoc()['total'];

    // Nuevas estadísticas
    $totalVisitantes = $conexion->query("SELECT COUNT(*) as total FROM visitantes")->fetch_assoc()['total'];

    // Total de reservas realizadas
    $stmtTotal = $conexion->query("SELECT COUNT(*) AS total FROM reservas");
    $totalReservas = $stmtTotal->fetch_assoc()['total'];

    // Datos para gráfico de barras: ocupación máxima por propietario
    $ocupacionPorPropietario = [];
    $result = $conexion->query("SELECT nombre, ocupacion_maxima FROM propietarios");
    while ($row = $result->fetch_assoc()) {
        $ocupacionPorPropietario[] = $row;
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="sidebar">
        <h2>Dashboard</h2>
        <a href="dashboard.php">Inicio</a>
        <a href="estadisticas.php">Estadísticas</a>
        <a href="informes.php">Informes</a>
        <a href="configracion.php">Gestion de propietarios</a>
        <a href="reservas.php">Reservas</a>
        <a href="../InicioSesion/CerrarSesion.php">Cerrar sesión</a>
    </div>
    <div class="main">
        <div class="title2">
            <h1>Estadísticas Generales</h1>
        </div>
        <div class="card" style="max-width:1000px;margin:auto;">
            <h3 class="mb-2">Resumen</h3>
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
                    <div class="stat-info">
                        <div class="stat-label">Propietarios registrados: <?php echo $totalPropietarios; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-info">
                        <div class="stat-label">Ocupación máxima total: <?php echo $ocupacionTotal; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="bi bi-person-lines-fill"></i></div>
                    <div class="stat-info">
                        <div class="stat-label">Total de visitantes: <?php echo $totalVisitantes; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-label">Total de reservas: <?php echo $totalReservas; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container graf mt-5" style="max-width:600px;">
            <h3 class="mb-4 text-center">Ocupación máxima por propietario</h3>
            <canvas id="ocupacionBar"></canvas>
        </div>


        <script>
            // Gráfico de barras
            const labels = <?php echo json_encode(array_column($ocupacionPorPropietario, 'nombre')); ?>;
            const data = <?php echo json_encode(array_column($ocupacionPorPropietario, 'ocupacion_maxima')); ?>;
            new Chart(document.getElementById('ocupacionBar'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Ocupación máxima',
                        data: data,
                        backgroundColor: '#7596e5'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } }
                }
            });
        </script>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>