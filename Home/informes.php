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

// Consulta para históricos
$sql_historico = 'SELECT * FROM visitantes';
// Consulta para valores monetarios (manillas por huésped)
$sql_manillas = 'SELECT tipo_visitante, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, COUNT(numero_manilla) as total_manillas FROM visitantes WHERE tipo_visitante = "Huésped" GROUP BY numero_documento';
// Conteo total de manillas
$sql_total_manillas = 'SELECT COUNT(numero_manilla) as total FROM visitantes WHERE numero_manilla IS NOT NULL AND numero_manilla != ""';

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="http://127.0.0.1:5500/ingreso/ingreso/css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
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
        <div class="container-fluid mt-4">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="informeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="btn5 nav-link active text-dark" id="historico-tab" data-bs-toggle="tab" data-bs-target="#historico" type="button" role="tab" aria-controls="historico" aria-selected="true">Histórico</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="btn5 nav-link text-dark" id="monetario-tab" data-bs-toggle="tab" data-bs-target="#monetario" type="button" role="tab" aria-controls="monetario" aria-selected="false">Valores Monetarios</button>
                </li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content" id="informeTabsContent">
                <!-- Histórico -->
                <div class="tab-pane fade show active" id="historico" role="tabpanel" aria-labelledby="historico-tab">
                    <div class="card2 historico mt-4">
                        <h3>Historial de Entradas y Salidas</h3>
                        <p class="mt-3">Aquí puedes ver un resumen de tus actividades recientes.</p>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="text" id="filtroNombre" class="form-control" placeholder="Buscar por nombre">
                            </div>
                            <div class="col-md-2">
                                <select id="filtroGenero" class="form-select">
                                    <option value="">Todos los géneros</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                    <option value="O">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="filtroTipo" class="form-select">
                                    <option value="">Todos los tipos</option>
                                    <option value="Familiar">Familiar</option>
                                    <option value="Huésped">Huésped</option>
                                    <option value="Domicilio">Domicilio</option>
                                    <option value="Servicio Técnico">Servicio Técnico</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" id="filtroDocumento" class="form-control" placeholder="N° Documento">
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <input type="date" id="filtroFechaDesde" class="form-control" placeholder="Desde">
                                <input type="date" id="filtroFechaHasta" class="form-control hasta" placeholder="Hasta">
                            </div>
                        </div>
                        <div class="mb-3">
                            <button id="btnExcel" class="btn btn-success">Exportar a Excel</button>
                            <button id="btnPDF" class="btn btn-danger">Exportar a PDF</button>
                        </div>
                        <div class="table-responsive mt-3" style="max-width:100%; overflow-x:auto;">
                        <table class="table table-striped table-bordered align-middle" id="tablaInforme">
                            <thead class="table-light">
                                <tr>
                                    <th>Foto</th>
                                    <th>Nombre Completo</th>
                                    <th>Género</th>
                                    <th>Fecha de Nacimiento</th>
                                    <th>Tipo de Documento</th>
                                    <th>Número de Documento</th>
                                    <th>Torre</th>
                                    <th>Apartamento</th>
                                    <th>Tipo de Visitante</th>
                                    <th>Número de Manilla</th>
                                    <th>Estado</th>
                                    <th>Hora de Entrada</th>
                                    <th>Hora de Salida</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $result = $pdo->query($sql_historico);
                            if ($result->rowCount() > 0) {
                                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<tr class="fila-informe">
                                            <td>' . (!empty($row['foto']) ? "<img src='data:image/jpeg;base64," . base64_encode($row['foto']) . "' alt='Foto' class='foto-registro'>" : "<span style='color:gray'>Sin foto</span>") . '</td>
                                            <td>' . $row['primer_apellido'] . ' ' . $row['segundo_apellido'] . ' ' . $row['primer_nombre'] . ' ' . $row['segundo_nombre'] . '</td>
                                            <td>' . $row['genero'] . '</td>
                                            <td>' . $row['fecha_nacimiento'] . '</td>
                                            <td>' . $row['tipo_documento'] . '</td>
                                            <td>' . $row['numero_documento'] . '</td>
                                            <td>' . $row['torre'] . '</td>
                                            <td>' . $row['apartamento'] . '</td>
                                            <td>' . $row['tipo_visitante'] . '</td>
                                            <td>' . $row['numero_manilla'] . '</td>
                                            <td>' . $row['estado'] . '</td>
                                            <td>' . $row['hora_entrada'] . '</td>
                                            <td>' . ($row['hora_salida'] ? $row['hora_salida'] : 'Pendiente') . '</td>
                                          </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="13">No se encontraron registros de visitantes.</td></tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
                <!-- Valores Monetarios -->
                <div class="tab-pane fade" id="monetario" role="tabpanel" aria-labelledby="monetario-tab">
                    <div class="card2 monetario mt-4">
                        <h3>Informe de Valores Monetarios</h3>
                        <p class="mt-3">Recuento de manillas suministradas a cada huésped y total histórico.</p>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="precioManilla" class="form-label mb-0">Precio por manilla ($):</label>
                                <input type="number" min="0" step="0.01" class="form-control" id="precioManilla" name="precioManilla" value="10000">
                            </div>
                            <div class="col-md-8 d-flex align-items-end justify-content-end gap-2">
                                <button id="btnExportarExcelManillas" class="btn btn-success">Exportar a Excel</button>
                                <button id="btnExportarPDFManillas" class="btn btn-danger">Exportar a PDF</button>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-width:100%; overflow-x:auto;">
                            <table class="table table-striped table-bordered align-middle" id="tablaMonetario">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre Completo</th>
                                        <th>Tipo de Visitante</th>
                                        <th>Manillas Suministradas</th>
                                        <th>Fecha de Ingreso</th>
                                        <th>Precio Total ($)</th>
                                        <th style="display:none">Género</th>
                                        <th style="display:none">Tipo de Documento</th>
                                        <th style="display:none">Número de Documento</th>
                                    </tr>
                                    <tr class="filters">
                                        <th><input type="text" class="form-control form-control-sm" placeholder="Nombre"></th>
                                        <th>
                                            <select class="form-select form-select-sm">
                                                <option value="">Todos</option>
                                                <option value="Familiar">Familiar</option>
                                                <option value="Huésped">Huésped</option>
                                                <option value="Domicilio">Domicilio</option>
                                                <option value="Servicio Técnico">Servicio Técnico</option>
                                            </select>
                                        </th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th style="display:none">
                                            <select class="form-select form-select-sm">
                                                <option value="">Todos</option>
                                                <option value="M">Masculino</option>
                                                <option value="F">Femenino</option>
                                                <option value="O">Otro</option>
                                            </select>
                                        </th>
                                        <th style="display:none"></th>
                                        <th style="display:none"><input type="text" class="form-control form-control-sm" placeholder="N° Documento"></th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyManillas">
                                <?php
                                $sql_manillas_detalle = 'SELECT tipo_visitante, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, numero_manilla, hora_entrada, genero, tipo_documento, numero_documento FROM visitantes WHERE tipo_visitante = "Huésped" AND numero_manilla IS NOT NULL AND numero_manilla != ""';
                                $resultManillas = $pdo->query($sql_manillas_detalle);
                                if ($resultManillas->rowCount() > 0) {
                                    while ($row = $resultManillas->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<tr class="fila-monetario" data-fecha="' . $row['hora_entrada'] . '">'
                                                . '<td>' . $row['primer_apellido'] . ' ' . $row['segundo_apellido'] . ' ' . $row['primer_nombre'] . ' ' . $row['segundo_nombre'] . '</td>'
                                                . '<td>' . $row['tipo_visitante'] . '</td>'
                                                . '<td class="manillas-count">1</td>'
                                                . '<td>' . $row['hora_entrada'] . '</td>'
                                                . '<td class="precio-total">0</td>'
                                                . '<td style="display:none">' . $row['genero'] . '</td>'
                                                . '<td style="display:none">' . $row['tipo_documento'] . '</td>'
                                                . '<td style="display:none">' . $row['numero_documento'] . '</td>'
                                            . '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="8">No se encontraron huéspedes con manillas suministradas.</td>
                                        </tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <?php
                            $resultTotal = $pdo->query($sql_total_manillas);
                            $total = $resultTotal->fetch(PDO::FETCH_ASSOC);
                            echo '<strong>Total histórico de manillas suministradas: </strong>' . ($total['total'] ?? 0);
                            ?>
                            <br>
                            <strong>Sumatoria total en precio ($): </strong><span id="sumatoriaPrecio">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Botón hamburguesa para mostrar/ocultar sidebar -->
    <button id="toggleSidebar" class="btn btn-primary d-lg-none" style="position:fixed;top:15px;left:15px;z-index:1100;">
        <span class="navbar-toggler-icon"></span>
    </button>
    <!-- jQuery, Bootstrap JS y DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="informes.js"></script>
    <script>
    $(document).ready(function() {
    $('#tablaMonetario').DataTable({
        pageLength: 5,
        lengthChange: false,
        dom: 'tip',
        language: {
            paginate: {
                previous: "Anterior",
                next: "Siguiente"
            },
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            emptyTable: "No hay datos disponibles",
            search: "Buscar:"
        }
    });
});
</script>
</script>
</body>
</html>

