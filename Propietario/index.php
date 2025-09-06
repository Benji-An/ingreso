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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Propietario</title>
    <link rel="stylesheet" href="http://localhost/ingreso/propietario/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="http://localhost/ingreso/Propietario/index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="#formularioreserva">Reservas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="#personaspresentes">Residencia</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="#historial">Historial</a>
                    </li>
                </ul>
            </div>
            <div>
                <a href="../InicioSesion/CerrarSesion.php" class="btn btn-custom btn-danger-custom">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>
    <div class="container-fluid p-3">
        <div class="main-container">
            <!-- Header Section -->
            <div class="header-section">
                <div class="header-content">
                    <h1 class="welcome-title">
                        Bienvenido, <?php echo htmlspecialchars($propietario['nombre']); ?>
                    </h1>
                    <div class="apartment-info">
                        <i class="bi bi-building me-2"></i>
                        Torre <?php echo htmlspecialchars($propietario['torre']); ?> - Apartamento <?php echo htmlspecialchars($propietario['apartamento']); ?>
                    </div>
                </div>
            </div>

            <div class="content-section">
                <!-- Información Personal -->
                <div class="info-card">
                    <div class="card-header-custom">
                        <i class="bi bi-person-circle fs-4"></i>
                        <span">Información Personal</span>
                    </div>
                    <div class="card-body-custom">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-building"></i>
                                    Torre
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($propietario['torre']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-door-open"></i>
                                    Apartamento
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($propietario['apartamento']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-person"></i>
                                    Nombre Completo
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($propietario['nombre']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-envelope"></i>
                                    Email
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($propietario['email']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-people"></i>
                                    Ocupación Máxima
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($propietario['ocupacion_maxima']); ?> personas</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-person-badge"></i>
                                    Usuario
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($propietario['username']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Formulario de Reserva -->
                 <div class="info-card" id="formularioreserva">
                    <div class="card-header-custom">
                        <i class="bi bi-qr-code-scan fs-4"></i>
                        <span>Generar Código QR para Visitante</span>
                    </div>
                    <div class="card-body-custom">
                        <div class="form-section">
                            <form id="formReserva" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-credit-card me-1"></i>
                                            Número de Documento
                                        </label>
                                        <input type="text" name="numero_documento" class="form-control form-control-custom" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-person me-1"></i>
                                            Primer Apellido
                                        </label>
                                        <input type="text" name="primer_apellido" class="form-control form-control-custom" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-person me-1"></i>
                                            Segundo Apellido
                                        </label>
                                        <input type="text" name="segundo_apellido" class="form-control form-control-custom">
                                    </div>
                                </div>
                                
                                <div class="row g-3 mt-2">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-person-plus me-1"></i>
                                            Primer Nombre
                                        </label>
                                        <input type="text" name="primer_nombre" class="form-control form-control-custom" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-secondary">
                                            <i class="bi bi-person-plus me-1"></i>
                                            Segundo Nombre
                                        </label>
                                        <input type="text" name="segundo_nombre" class="form-control form-control-custom">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-gender-ambiguous me-1"></i>
                                            Género
                                        </label>
                                        <select name="genero" class="form-select form-control-custom" required>
                                            <option value="">Seleccione</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Femenino</option>
                                            <option value="O">Otro</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row g-3 mt-2">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-calendar me-1"></i>
                                            Fecha de Nacimiento
                                        </label>
                                        <input type="date" name="fecha_nacimiento" class="form-control form-control-custom" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-building me-1"></i>
                                            Torre
                                        </label>
                                        <input type="text" name="torre" class="form-control form-control-custom" value="<?php echo htmlspecialchars($propietario['torre']); ?>" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-door-open me-1"></i>
                                            Apartamento
                                        </label>
                                        <input type="text" name="apartamento" class="form-control form-control-custom" value="<?php echo htmlspecialchars($propietario['apartamento']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-custom btn-primary-custom">
                                        <i class="bi bi-qr-code me-2"></i>
                                        Generar Código QR
                                    </button>
                                </div>
                            </form>
                            <div id="qrSection" class="d-flex flex-column align-items-center justify-content-center mt-4" style="display:none;">
                                <div id="qrReserva" class="qr-container mb-2"></div>
                                <div id="qrIdText" class="fw-bold mb-2"></div>
                                <div class="d-flex gap-2">
                                    <button id="btnCompartirQR" type="button" class="btn btn-success" style="display:none;">
                                        <i class="bi bi-share"></i> Compartir QR
                                    </button>
                                    <button id="btnWhatsappQR" type="button" class="btn btn-success" style="display:none;">
                                        <i class="bi bi-whatsapp"></i> WhatsApp
                                    </button>
                                    <button id="btnCopiarQR" type="button" class="btn btn-secondary" style="display:none;">
                                        <i class="bi bi-clipboard"></i> Copiar enlace
                                    </button>
                                    <button id="btnDescargarQR" type="button" class="btn btn-primary" style="display:none;">
                                        <i class="bi bi-download"></i> Descargar QR
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personas Presentes -->
                <div class="info-card" id="personaspresentes">
                    <div class="card-header-custom">
                        <i class="bi bi-people-fill fs-4"></i>
                        <span>Personas Actualmente en la Residencia</span>
                        <span class="badge bg-success ms-auto"><?php echo count($presentes); ?></span>
                    </div>
                    <div class="card-body-custom">
                        <?php if (count($presentes) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-custom table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-person me-2"></i>Nombre</th>
                                            <th><i class="bi bi-tag me-2"></i>Tipo</th>
                                            <th><i class="bi bi-box-arrow-in-right me-2"></i>Ingreso</th>
                                            <th><i class="bi bi-box-arrow-right me-2"></i>Salida</th>
                                        </tr>
                                    </thead>
                                    <tbody id="presentes-tbody">
                                        <?php foreach ($presentes as $p): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-person-circle me-2 text-secondary"></i>
                                                        <?php echo htmlspecialchars($p['nombre']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($p['tipo']); ?></td>
                                                <td><?php echo htmlspecialchars($p['fecha_ingreso']); ?></td>
                                                <td>
                                                    <?php if ($p['salida']): ?>
                                                        <span class="text-dark">
                                                            <i class="bi bi-check-circle me-1 text-success"></i>
                                                            <?php echo htmlspecialchars($p['salida']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-dark">
                                                            <i class="bi bi-clock me-1 text-warning"></i>
                                                            En la residencia
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="presentes-pagination" class="mt-3"></div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-house"></i>
                                <h5>No hay personas actualmente en la residencia</h5>
                                <p class="text-muted">Todas las visitas han finalizado</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Historial -->
                <div class="info-card" id="historial">
                    <div class="card-header-custom">
                        <i class="bi bi-clock-history fs-4"></i>
                        <span>Historial de Ingresos</span>
                        <small class="text-muted ms-auto">Últimos 50 registros</small>
                    </div>
                    <div class="card-body-custom">
                        <?php if (count($historial) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-custom table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-person me-2"></i>Nombre</th>
                                            <th><i class="bi bi-tag me-2"></i>Tipo</th>
                                            <th><i class="bi bi-box-arrow-in-right me-2"></i>Ingreso</th>
                                            <th><i class="bi bi-box-arrow-right me-2"></i>Salida</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historial as $h): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-person-circle me-2 text-secondary"></i>
                                                        <?php echo htmlspecialchars($h['nombre']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($h['tipo']); ?></td>
                                                <td><?php echo htmlspecialchars($h['fecha_ingreso']); ?></td>
                                                <td>
                                                    <?php if ($h['salida']): ?>
                                                        <span class="text-dark">
                                                            <i class="bi bi-check-circle me-1 text-success"></i>
                                                            <?php echo htmlspecialchars($h['salida']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-dark">
                                                            <i class="bi bi-clock me-1 text-warning"></i>
                                                            En la residencia
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="historial-pagination" class="mt-3 d-flex justify-content-center"></div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-journal-x"></i>
                                <h5>No hay historial de ingresos</h5>
                                <p class="text-muted">Aún no se han registrado visitas</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="reserva.js"></script>
    <script>
        // Paginación para Personas Presentes
        document.addEventListener('DOMContentLoaded', function() {
            const rowsPerPage = 5;
            const tbody = document.getElementById('presentes-tbody');
            const pagination = document.getElementById('presentes-pagination');
            if (!tbody) return; // Evita error si no existe el tbody
            const rows = Array.from(tbody.querySelectorAll('tr')).reverse(); // Mostrar más recientes primero
            let currentPage = 1;
            const totalPages = Math.ceil(rows.length / rowsPerPage);

            function showPage(page) {
                currentPage = page;
                rows.forEach((row, idx) => {
                    row.style.display = (idx >= (page-1)*rowsPerPage && idx < page*rowsPerPage) ? '' : 'none';
                });
                renderPagination();
            }

            function renderPagination() {
                if (totalPages <= 1) {
                    pagination.innerHTML = '';
                    return;
                }
                let html = `<nav><ul class="pagination justify-content-end">`; 
                html += `<li class="page-item${currentPage === 1 ? ' disabled' : ''}">
                            <a class="page-link" href="#" tabindex="-1" data-page="${currentPage-1}">Anterior</a>
                        </li>`;
                for (let i = 1; i <= totalPages; i++) {
                    html += `<li class="page-item${i === currentPage ? ' active' : ''}">
                                <a class="page-link" href="#" data-page="${i}">${i}</a>
                            </li>`;
                }
                html += `<li class="page-item${currentPage === totalPages ? ' disabled' : ''}">
                            <a class="page-link" href="#" data-page="${currentPage+1}">Siguiente</a>
                        </li>`;
                html += `</ul></nav>`;
                pagination.innerHTML = html;

                // Add event listeners
                pagination.querySelectorAll('a.page-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const page = parseInt(this.getAttribute('data-page'));
                        if (!isNaN(page) && page >= 1 && page <= totalPages) {
                            showPage(page);
                        }
                    });
                });
            }

            showPage(1);
        });

        // Paginación para Historial de Ingresos
        document.addEventListener('DOMContentLoaded', function() {
            const rowsPerPageHist = 5;
            const tbodyHist = document.querySelector('#historial .card-body-custom tbody');
            const paginationHist = document.getElementById('historial-pagination');
            if (!tbodyHist) return; // Evita error si no hay historial
            const rowsHist = Array.from(tbodyHist.querySelectorAll('tr'));
            let currentPageHist = 1;
            const totalPagesHist = Math.ceil(rowsHist.length / rowsPerPageHist);

            function showPageHist(page) {
                currentPageHist = page;
                rowsHist.forEach((row, idx) => {
                    row.style.display = (idx >= (page-1)*rowsPerPageHist && idx < page*rowsPerPageHist) ? '' : 'none';
                });
                renderPaginationHist();
            }

            function renderPaginationHist() {
                if (totalPagesHist <= 1) {
                    paginationHist.innerHTML = '';
                    return;
                }
                let html = `<nav><ul class="pagination justify-content-center">`;
                html += `<li class="page-item${currentPageHist === 1 ? ' disabled' : ''}">
                            <a class="page-link" href="#" tabindex="-1" data-page="${currentPageHist-1}">Anterior</a>
                        </li>`;
                for (let i = 1; i <= totalPagesHist; i++) {
                    html += `<li class="page-item${i === currentPageHist ? ' active' : ''}">
                                <a class="page-link" href="#" data-page="${i}">${i}</a>
                            </li>`;
                }
                html += `<li class="page-item${currentPageHist === totalPagesHist ? ' disabled' : ''}">
                            <a class="page-link" href="#" data-page="${currentPageHist+1}">Siguiente</a>
                        </li>`;
                html += `</ul></nav>`;
                paginationHist.innerHTML = html;

                paginationHist.querySelectorAll('a.page-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const page = parseInt(this.getAttribute('data-page'));
                        if (!isNaN(page) && page >= 1 && page <= totalPagesHist) {
                            showPageHist(page);
                        }
                    });
                });
            }

            showPageHist(1);
        });
    </script>
</body>
</html>