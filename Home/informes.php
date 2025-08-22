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

$sql = 'SELECT * FROM visitantes';

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <style>
        /* Solo para informes.php */
        .sidebar {
            transition: left 0.3s;
        }
        .sidebar.hidden {
            left: -260px;
        }
        .main {
            transition: margin-left 0.3s;
        }
        .main.full {
            margin-left: 0 !important;
        }
        @media (max-width: 991px) {
            .sidebar {
                left: 0;
                width: 220px;
            }
            .main {
                margin-left: 0 !important;
            }
        }

        .foto-registro {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
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
        <div class="card">
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
                    <input type="date" id="filtroFechaHasta" class="form-control" placeholder="Hasta">
                </div>
            </div>
            <div class="mb-3">
                <button id="btnExcel" class="btn btn-success">Exportar a Excel</button>
                <button id="btnPDF" class="btn btn-danger">Exportar a PDF</button>
            </div>
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
                $result = $pdo->query($sql);

                if ($result->rowCount() > 0) {
                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        echo '<tr class="fila-informe">
                                <td>
                                    ' . (!empty($row['foto']) ? "<img src='data:image/jpeg;base64," . base64_encode($row['foto']) . "' alt='Foto' class='foto-registro'>" : "<span style='color:gray'>Sin foto</span>") . '
                                </td>
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
                    echo 'No se encontraron registros de visitantes.';
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Botón hamburguesa para mostrar/ocultar sidebar -->
    <button id="toggleSidebar" class="btn btn-primary d-lg-none" style="position:fixed;top:15px;left:15px;z-index:1100;">
        <span class="navbar-toggler-icon"></span>
    </button>
    <!-- jQuery y DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
    <script>
    $(document).ready(function() {
        // Agrega filtros por columna
        $('#tablaInforme thead tr').clone(true).appendTo( '#tablaInforme thead' );
        $('#tablaInforme thead tr:eq(1) th').each( function (i) {
            $(this).html( '<input type="text" placeholder="Filtrar" style="width:100%"/>' );
            $('input', this).on('keyup change', function () {
                if ( $('#tablaInforme').DataTable().column(i).search() !== this.value ) {
                    $('#tablaInforme').DataTable()
                        .column(i)
                        .search( this.value )
                        .draw();
                }
            });
        });
    });

    document.getElementById('toggleSidebar').addEventListener('click', function() {
        var sidebar = document.querySelector('.sidebar');
        var main = document.querySelector('.main');
        sidebar.classList.toggle('hidden');
        main.classList.toggle('full');
    });

    document.addEventListener('DOMContentLoaded', function() {
        const filtroNombre = document.getElementById('filtroNombre');
        const filtroGenero = document.getElementById('filtroGenero');
        const filtroTipo = document.getElementById('filtroTipo');
        const filtroDocumento = document.getElementById('filtroDocumento');
        const filtroFechaDesde = document.getElementById('filtroFechaDesde');
        const filtroFechaHasta = document.getElementById('filtroFechaHasta');
        const filas = document.querySelectorAll('.fila-informe');

        function filtrar() {
            const nombre = filtroNombre.value.toLowerCase();
            const genero = filtroGenero.value;
            const tipo = filtroTipo.value;
            const documento = filtroDocumento.value.toLowerCase();
            const fechaDesde = filtroFechaDesde.value;
            const fechaHasta = filtroFechaHasta.value;

            filas.forEach(fila => {
                const tds = fila.querySelectorAll('td');
                const nombreCompleto = tds[1].textContent.toLowerCase();
                const generoTd = tds[2].textContent;
                const fechaTd = tds[3].textContent;
                const tipoTd = tds[8].textContent;
                const documentoTd = tds[5].textContent.toLowerCase();

                let mostrar = true;
                if (nombre && !nombreCompleto.includes(nombre)) mostrar = false;
                if (genero && generoTd !== genero) mostrar = false;
                if (tipo && tipoTd !== tipo) mostrar = false;
                if (documento && !documentoTd.includes(documento)) mostrar = false;

                // Rango de fechas
                if (fechaDesde && fechaTd < fechaDesde) mostrar = false;
                if (fechaHasta && fechaTd > fechaHasta) mostrar = false;

                fila.style.display = mostrar ? '' : 'none';
            });
        }

        filtroNombre.addEventListener('input', filtrar);
        filtroGenero.addEventListener('change', filtrar);
        filtroTipo.addEventListener('change', filtrar);
        filtroDocumento.addEventListener('input', filtrar);
        filtroFechaDesde.addEventListener('change', filtrar);
        filtroFechaHasta.addEventListener('change', filtrar);

        // Exportar a Excel
        document.getElementById('btnExcel').addEventListener('click', function() {
            exportTableToExcel('tablaInforme', 'informe_visitantes');
        });

        // Exportar a PDF
        document.getElementById('btnPDF').addEventListener('click', function() {
            exportTableToPDF('tablaInforme', 'informe_visitantes');
        });
    });

    function exportTableToExcel(tableID, filename = '') {
        // Clona la tabla
        var table = document.getElementById(tableID).cloneNode(true);
        // Elimina las filas ocultas
        Array.from(table.querySelectorAll('tbody tr')).forEach(tr => {
            if (tr.style.display === 'none') tr.remove();
        });
        var wb = XLSX.utils.table_to_book(table, {sheet:"Sheet JS"});
        XLSX.writeFile(wb, filename ? filename + ".xlsx" : "Export.xlsx");
    }

    function exportTableToPDF(tableID, filename = '') {
        var { jsPDF } = window.jspdf;
        var doc = new jsPDF('l', 'pt', 'a4');
        doc.text("Informe de Visitantes", 40, 30);

        // Solo filas visibles
        var table = document.getElementById(tableID);
        var head = table.querySelector('thead');
        var visibleRows = Array.from(table.querySelectorAll('tbody tr')).filter(tr => tr.style.display !== 'none');
        // Crea una tabla temporal solo con filas visibles
        var tempTable = document.createElement('table');
        tempTable.appendChild(head.cloneNode(true));
        var tbody = document.createElement('tbody');
        visibleRows.forEach(tr => tbody.appendChild(tr.cloneNode(true)));
        tempTable.appendChild(tbody);

        doc.autoTable({ 
            html: tempTable, 
            startY: 50,
            styles: { fontSize: 8 }
        });
        doc.save((filename ? filename : "informe_visitantes") + ".pdf");
    }
    </script>
</body>
</html>

