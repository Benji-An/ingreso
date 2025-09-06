<!-- Mostrar todo lo relacionado a las reservas -->
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

require_once '../config/connection.php';

$connection = new Connection();
$pdo = $connection->connect();

$sql = 'SELECT r.*, p.nombre AS nombre_propietario 
        FROM reservas r 
        LEFT JOIN propietarios p ON r.propietario_id = p.id';



?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <div class="reservas">
        <h2>Lista de Reservas</h2>
        <div class="mb-3">
            <button id="btnExcel" class="btn btn-success me-2">Exportar a Excel</button>
            <button id="btnPDF" class="btn btn-danger">Exportar a PDF</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-reservas" id="tablaReservas">
                <thead>
                    <tr>
                        <th>ID Reserva</th>
                        <th>Propietario</th>
                        <th>Primer Nombre</th>
                        <th>Segundo Nombre</th>
                        <th>Primer Apellido</th>
                        <th>Segundo Apellido</th>
                        <th>Fecha de Reserva</th>
                        <th>Torre</th>
                        <th>Apartamento</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($reservas as $reserva) {
                        echo "<tr>";
                        echo "<td>{$reserva['id']}</td>";
                        echo "<td>" . htmlspecialchars($reserva['nombre_propietario']) . "</td>";
                        echo "<td>{$reserva['primer_nombre']}</td>";
                        echo "<td>{$reserva['segundo_nombre']}</td>";
                        echo "<td>{$reserva['primer_apellido']}</td>";
                        echo "<td>{$reserva['segundo_apellido']}</td>";
                        echo "<td>{$reserva['fecha_reserva']}</td>";
                        echo "<td>{$reserva['torre']}</td><td>{$reserva['apartamento']}</td>";
                        echo "<td>";
                          $estado = $reserva['estado'];
                          $badge = $estado === 'Activo' ? 'success' : 'secondary';
                          echo "<span class='badge bg-$badge'>$estado</span>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Bootstrap JS (opcional para funcionalidades como responsive) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Scripts para exportar a Excel y PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script>
    document.getElementById('btnExcel').addEventListener('click', function() {
        exportTableToExcel('tablaReservas', 'lista_reservas');
    });

    document.getElementById('btnPDF').addEventListener('click', function() {
        exportTableToPDF('tablaReservas', 'lista_reservas');
    });

    function exportTableToExcel(tableID, filename = '') {
        var table = document.getElementById(tableID).cloneNode(true);
        var wb = XLSX.utils.table_to_book(table, {sheet:"Sheet JS"});
        XLSX.writeFile(wb, filename ? filename + ".xlsx" : "Export.xlsx");
    }

    function exportTableToPDF(tableID, filename = '') {
        var { jsPDF } = window.jspdf;
        var doc = new jsPDF('l', 'pt', 'a4');
        doc.text("Lista de Reservas", 40, 30);

        var table = document.getElementById(tableID);
        doc.autoTable({ 
            html: table, 
            startY: 50,
            styles: { fontSize: 8 }
        });
        doc.save((filename ? filename : "lista_reservas") + ".pdf");
    }
    </script>
</body>
</html>