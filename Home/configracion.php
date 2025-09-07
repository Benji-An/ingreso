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

// Eliminar propietario
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conexion->query("DELETE FROM propietarios WHERE id = $id");
    header("Location: configracion.php");
    exit;
}

// Editar propietario
$editData = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conexion->query("SELECT * FROM propietarios WHERE id = $id");
    $editData = $res->fetch_assoc();
}

// Actualizar propietario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_propietario'])) {
    $id = intval($_POST['id']);
    $torre = $_POST['torre'];
    $apartamento = $_POST['apartamento'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $ocupacion_maxima = $_POST['ocupacion_maxima'];
    $usuario = $_POST['usuario'];
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("UPDATE propietarios SET torre=?, apartamento=?, nombre=?, email=?, ocupacion_maxima=?, username=?, password=? WHERE id=?");
        $stmt->bind_param("ssssissi", $torre, $apartamento, $nombre, $email, $ocupacion_maxima, $usuario, $password, $id);
    } else {
        $stmt = $conexion->prepare("UPDATE propietarios SET torre=?, apartamento=?, nombre=?, email=?, ocupacion_maxima=?, username=? WHERE id=?");
        $stmt->bind_param("ssssisi", $torre, $apartamento, $nombre, $email, $ocupacion_maxima, $usuario, $id);
    }
    $stmt->execute();
    $mensaje = "Propietario actualizado correctamente.";
    $editData = null;
}

// Crear propietario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_propietario'])) {
    $torre = $_POST['torre'];
    $apartamento = $_POST['apartamento'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $ocupacion_maxima = $_POST['ocupacion_maxima'];
    $usuario = $_POST['usuario'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insertar en propietarios
    $stmt = $conexion->prepare("INSERT INTO propietarios (torre, apartamento, nombre, email, ocupacion_maxima, username, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiss", $torre, $apartamento, $nombre, $email, $ocupacion_maxima, $usuario, $password);
    $stmt->execute();

    // Insertar en usuarios (rol 3)
    $stmt2 = $conexion->prepare("INSERT INTO usuarios (username, password, role_id) VALUES (?, ?, 3)");
    $stmt2->bind_param("ss", $usuario, $password);
    $stmt2->execute();

    $mensaje = "Propietario creado correctamente.";
}

// Descargar plantilla CSV
if (isset($_GET['descargar_plantilla'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="plantilla_propietarios.csv"');
    echo "torre;apartamento;nombre;email;ocupacion_maxima;usuario;password\n";
    exit;
}

// Procesar carga de archivo CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cargar_csv'])) {
    if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] === UPLOAD_ERR_OK) {
        $archivoTmp = $_FILES['archivo_csv']['tmp_name'];
        $handle = fopen($archivoTmp, 'r');
        $primera = true;
        $creados = 0;
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            if ($primera) { $primera = false; continue; } // Saltar encabezado
            if (count($data) < 7) { continue; } // Saltar filas incompletas
            list($torre, $apartamento, $nombre, $email, $ocupacion_maxima, $usuario, $password) = $data;
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar en propietarios
            $stmt = $conexion->prepare("INSERT INTO propietarios (torre, apartamento, nombre, email, ocupacion_maxima, username, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssiss", $torre, $apartamento, $nombre, $email, $ocupacion_maxima, $usuario, $passwordHash);
            $stmt->execute();

            // Insertar en usuarios (rol 3)
            $stmt2 = $conexion->prepare("INSERT INTO usuarios (username, password, role_id) VALUES (?, ?, 3)");
            $stmt2->bind_param("ss", $usuario, $passwordHash);
            $stmt2->execute();

            $creados++;
        }
        fclose($handle);
        $mensaje = "$creados propietarios creados correctamente desde el archivo.";
    } else {
        $mensaje = "Error al cargar el archivo.";
    }
}

// Obtener todos los propietarios
$propietarios = $conexion->query("SELECT * FROM propietarios");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
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
        <div class="title3">
            <h1>Gestión de Propietarios</h1>
        </div>
        <div class="card carta" >
            <h3 class="mb-3"><?php echo $editData ? "Editar Propietario" : "Crear Propietario"; ?></h3>
            <?php if (isset($mensaje)) { echo "<p style='color:green;'>$mensaje</p>"; } ?>
            <form action="" method="post" enctype="multipart/form-data">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <?php endif; ?>
                <div class="row">
                    <div class="col">
                        <label for="torre">Torre:</label>
                        <input type="text" id="torre" name="torre" class="form-control" value="<?php echo $editData['torre'] ?? ''; ?>" required>
                    </div>
                    <div class="col">
                        <label for="apartamento">Apartamento:</label>
                        <input type="text" id="apartamento" name="apartamento" class="form-control" value="<?php echo $editData['apartamento'] ?? ''; ?>" required>
                    </div>
                    <div class="col">
                        <label for="nombre">Nombre completo:</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo $editData['nombre'] ?? ''; ?>" required>
                    </div>
                    <div class="col">
                        <label for="email">Correo electrónico:</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo $editData['email'] ?? ''; ?>" required>
                    </div>
                    <div class="col">
                        <label for="ocupacion_maxima">Ocupación máxima:</label>
                        <input type="number" id="ocupacion_maxima" name="ocupacion_maxima" class="form-control" min="1" value="<?php echo $editData['ocupacion_maxima'] ?? ''; ?>" required>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col">
                        <label for="usuario">Usuario:</label>
                        <input type="text" id="usuario" name="usuario" class="form-control" value="<?php echo $editData['usuario'] ?? ''; ?>" required>
                    </div>
                    <div class="col">
                        <label for="password"><?php echo $editData ? "Nueva contraseña (opcional):" : "Contraseña:"; ?></label>
                        <input type="password" id="password" name="password" class="form-control" <?php echo $editData ? "" : "required"; ?>>
                    </div>
                </div>
                <div class="mt-3">
                    <?php if ($editData): ?>
                        <button type="submit" name="update_propietario" class="btn btn-success">Actualizar</button>
                        <a href="configracion.php" class="btn btn-secondary">Cancelar</a>
                    <?php else: ?>
                        <button type="submit" name="crear_propietario" class="btn btn-primary">Crear Propietario</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <div class="card mt-2" >
            <h3 class="mb-3">Importar/Exportar Propietarios</h3>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="archivo_csv">Cargar archivo CSV:</label>
                    <input type="file" id="archivo_csv" name="archivo_csv" class="form-control" accept=".csv" required>
                </div>
                <div class="mt-3">
                    <button type="submit" name="cargar_csv" class="btn btn-primary">Cargar Propietarios</button>
                     <a href="configracion.php?descargar_plantilla=1" class="btn btn-success downoald">Descargar plantilla CSV</a>
                </div>
            </form>
        </div>
        <div class="card mt-2" >
            <h3 class="mb-3">Lista de Propietarios</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tabla-propietarios">
                    <thead>
                        <tr>
                            <th>Torre</th>
                            <th>Apartamento</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Ocupación Máxima</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $propietarios->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['torre']); ?></td>
                            <td><?php echo htmlspecialchars($row['apartamento']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['ocupacion_maxima']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>
                                <a href="configracion.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                <a href="configracion.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este propietario?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tabla-propietarios').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es.json'
                }
            });
        });
        $(document).ready(function() {
        $('#tablaPropietarios').DataTable({
            "pageLength": 5,
            "lengthChange": false,
            "language": {
                "paginate": {
                    "previous": "Anterior",
                    "next": "Siguiente"
                },
                "info": "Mostrando _START_ a _END_ de _TOTAL_ propietarios",
                "emptyTable": "No hay propietarios registrados",
                "search": "Buscar:"
        }
    });
});
    </script>
</body>
</html>