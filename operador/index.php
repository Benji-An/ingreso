<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Ingreso</title>
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
                        <a class="nav-link active" aria-current="page" href="http://localhost/ingreso/Operador/index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="reservas_activas.php">Reservas</a>
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
                    <h3 class="mb-4">Registro de Visitante</h3>
                    <form id="registroForm" action="guardar.php" method="POST">
                        <div id="visitantesContainer"></div>
                        <div class="action-buttons">
                            <button type="submit" class="btn btn-outline-primary mb-3">Registrar</button>
                            <button type="button" id="agregarVisitanteBtn" class="btn btn-outline-primary">Agregar otro visitante</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

        <div class="container-fluid mt-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-container">
                        <h3 class="mb-4">Registro de Activos</h3>
                        <table class="table table-hover table-bordered align-middle main-box mt-3" style="border-radius: 0 !important; font-size: 14px;">
                <thead">
                    <tr class="table-secondary">
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
                        <th>Hora de Salida</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="registroActivos">
                    <?php
                    $conexion = new mysqli("localhost", "root", "", "control_acceso");

                    // 1. Mostrar visitantes activos y sus acompañantes
                    $sql = "SELECT * FROM visitantes WHERE estado = 'Activo'";
                    $resultado = $conexion->query($sql);
                    $visitantes_activos = [];
                    while ($fila = $resultado->fetch_assoc()) {
                        $visitantes_activos[] = $fila['id'];
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
                                <td>" . ($fila['hora_salida'] ? $fila['hora_salida'] : 'Pendiente') . "</td>
                                <td><button class=\"btn btn-sm btn-primary mb-3\" onclick=\"registrarSalida({$fila['id']})\">Registrar Salida</button></td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3"></div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
   
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <script>
        // Permitir agregar múltiples visitantes en un solo formulario
        document.addEventListener('DOMContentLoaded', () => {
            const visitantesContainer = document.getElementById('visitantesContainer');
            const agregarBtn = document.getElementById('agregarVisitanteBtn');
            let visitanteIndex = 0;

            function getReservaParams() {
                const params = {};
                const urlParams = new URLSearchParams(window.location.search);
                [
                    'numero_documento', 'primer_apellido', 'segundo_apellido', 'primer_nombre', 'segundo_nombre',
                    'genero', 'fecha_nacimiento', 'torre', 'apartamento', 'reserva_id'
                ].forEach(key => {
                    params[key] = urlParams.get(key) || '';
                });
                return params;
            }

            function crearBloqueVisitante(idx) {
                const reserva = idx === 0 ? getReservaParams() : {};
                return `
                <div class="visitante-bloque border rounded p-3 mb-3 position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 eliminarVisitanteBtn" title="Quitar visitante" style="z-index:2;"></button>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="photo-registration">
                                <div class="photo-circle" id="profilePhoto_${idx}">
                                    <i class="fas fa-user photo-icon"></i>
                                </div>
                                <button id="cameraBtn_${idx}" class="camera-btn" type="button">
                                    <i class="fas fa-camera"></i>
                                    Usar Cámara
                                </button>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="camera-section" id="cameraSection_${idx}" style="display:none;">
                                <div class="camera-container">
                                    <div class="camera-preview">
                                        <video id="video_${idx}" autoplay playsinline></video>
                                    </div>
                                    <canvas id="canvas_${idx}"></canvas>
                                    <div class="camera-controls">
                                        <button id="captureBtn_${idx}" class="btn-capture" type="button"><i class="fas fa-camera"></i> Capturar</button>
                                        <button id="cancelBtn_${idx}" class="btn-cancel" type="button"><i class="fas fa-times"></i> Cancelar</button>
                                    </div>
                                </div>
                            </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Documento:</label>
                            <select name="visitantes[${idx}][tipo_documento]" class="form-select" required>
                                <option value="">Seleccione</option>
                                <option value="Cédula de Ciudadanía" ${reserva.numero_documento ? 'selected' : ''}>Cédula de Ciudadanía</option>
                                <option value="Cédula de Extranjería">Cédula de Extranjería</option>
                                <option value="Pasaporte">Pasaporte</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="inputQR_${idx}" class="form-label">Escanea el QR aquí</label>
                            <input type="text" id="inputQR_${idx}" class="form-control" autocomplete="off" autofocus placeholder="Presione aca y escanee el QR">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Número de Documento:</label>
                            <input type="text" name="visitantes[${idx}][numero_documento]" class="form-control" required value="${reserva.numero_documento || ''}">
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-3">
                            <label class="form-label">Primer Apellido:</label>
                            <input type="text" name="visitantes[${idx}][primer_apellido]" class="form-control" required value="${reserva.primer_apellido || ''}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Segundo Apellido:</label>
                            <input type="text" name="visitantes[${idx}][segundo_apellido]" class="form-control" required value="${reserva.segundo_apellido || ''}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Primer Nombre:</label>
                            <input type="text" name="visitantes[${idx}][primer_nombre]" class="form-control" required value="${reserva.primer_nombre || ''}">
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-3">
                            <label class="form-label">Segundo Nombre (opcional):</label>
                            <input type="text" name="visitantes[${idx}][segundo_nombre]" class="form-control" value="${reserva.segundo_nombre || ''}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Género:</label>
                            <select name="visitantes[${idx}][genero]" class="form-select" required>
                                <option value="">Seleccione</option>
                                <option value="M" ${reserva.genero === 'M' ? 'selected' : ''}>Masculino</option>
                                <option value="F" ${reserva.genero === 'F' ? 'selected' : ''}>Femenino</option>
                                <option value="O" ${reserva.genero === 'O' ? 'selected' : ''}>Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-4">
                            <label class="form-label">Fecha de Nacimiento:</label>
                            <input type="date" name="visitantes[${idx}][fecha_nacimiento]" class="form-control" placeholder="YYYY-MM-DD" required value="${reserva.fecha_nacimiento || ''}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Torre:</label>
                            <input type="text" name="visitantes[${idx}][torre]" class="form-control" required value="${reserva.torre || ''}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Apartamento:</label>
                            <input type="text" name="visitantes[${idx}][apartamento]" class="form-control" required value="${reserva.apartamento || ''}">
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Visitante:</label>
                            <select name="visitantes[${idx}][tipo_visitante]" class="form-select tipo_visitante_select" data-idx="${idx}" required>
                                <option value="Familiar">Familiar</option>
                                <option value="Huésped">Huésped</option>
                                <option value="Domicilio">Domicilio</option>
                                <option value="Servicio Técnico">Servicio Técnico</option>
                            </select>
                        </div>
                        <div class="col-md-6 manillaContainer hidden">
                            <label class="form-label">Número de Manilla:</label>
                            <input type="text" name="visitantes[${idx}][numero_manilla]" class="form-control">
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-6 d-flex align-items-center">
                            <input type="checkbox" class="form-check-input me-2 conVehiculoCheck" name="visitantes[${idx}][tiene_vehiculo]">
                            <label class="form-check-label mb-0">¿Ingresa con Vehículo?</label>
                        </div>
                    </div>
                    <div class="vehiculoContainer hidden mt-2">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Placa:</label>
                                <input type="text" name="visitantes[${idx}][placa]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Marca:</label>
                                <input type="text" name="visitantes[${idx}][marca]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color:</label>
                                <input type="text" name="visitantes[${idx}][color]" class="form-control">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="visitantes[${idx}][foto_visitante]" id="fotoVisitante_${idx}">
                    <input type="hidden" name="visitantes[${idx}][reserva_id]" value="${reserva.reserva_id || ''}">
                </div>
                `;
            }

            function inicializarBloque(idx) {
                // Lógica de foto de perfil y cámara para cada bloque
                const profilePhoto = document.getElementById(`profilePhoto_${idx}`);
                const cameraBtn = document.getElementById(`cameraBtn_${idx}`);
                const cameraSection = document.getElementById(`cameraSection_${idx}`);
                const video = document.getElementById(`video_${idx}`);
                const canvas = document.getElementById(`canvas_${idx}`);
                const captureBtn = document.getElementById(`captureBtn_${idx}`);
                const cancelBtn = document.getElementById(`cancelBtn_${idx}`);
                let stream = null;
                
                cameraBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    openCamera();
                });
                captureBtn.addEventListener('click', () => capturePhoto());
                cancelBtn.addEventListener('click', () => closeCamera());
                function openCamera() {
                    // Limpiar foto anterior antes de abrir la cámara
                    try {
                        localStorage.removeItem(`foto_visitante_${idx}`);
                        console.log(`Foto anterior eliminada para visitante ${idx}`);
                    } catch (error) {
                        console.log('Error al eliminar foto anterior:', error);
                    }
                    
                    profilePhoto.innerHTML = `<i class="fas fa-user photo-icon"></i>`;
                    document.getElementById(`fotoVisitante_${idx}`).value = '';
                    
                    mostrarIndicadorFoto(idx, false);
                    
                    navigator.mediaDevices.getUserMedia({ video: true }).then(s => {
                        stream = s;
                        video.srcObject = stream;
                        cameraSection.style.display = 'flex';
                    }).catch(() => alert('Error al acceder a la cámara.'));
                }
                function closeCamera() {
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                        stream = null;
                        video.srcObject = null;
                    }
                    cameraSection.style.display = 'none';
                }
                function capturePhoto() {
                    if (!stream) return;
                    const size = 400;
                    canvas.width = size;
                    canvas.height = size;
                    const ctx = canvas.getContext('2d');
                    const videoWidth = video.videoWidth;
                    const videoHeight = video.videoHeight;
                    const minDimension = Math.min(videoWidth, videoHeight);
                    const sourceX = (videoWidth - minDimension) / 2;
                    const sourceY = (videoHeight - minDimension) / 2;
                    ctx.drawImage(video, sourceX, sourceY, minDimension, minDimension, 0, 0, size, size);
                    const dataURL = canvas.toDataURL('image/jpeg', 0.9);
                    setProfilePhoto(dataURL);
                    closeCamera();
                }
                function setProfilePhoto(dataURL) {
                    const img = document.createElement('img');
                    img.src = dataURL;
                    img.alt = 'Foto de perfil';
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '50%';
                    
                    const overlay = document.createElement('div');
                    overlay.className = 'photo-overlay';
                    overlay.style.position = 'absolute';
                    overlay.style.top = '0';
                    overlay.style.left = '0';
                    overlay.style.right = '0';
                    overlay.style.bottom = '0';
                    overlay.style.background = 'rgba(0, 123, 255, 0.7)';
                    overlay.style.color = 'white';
                    overlay.style.borderRadius = '50%';
                    overlay.style.display = 'flex';
                    overlay.style.alignItems = 'center';
                    overlay.style.justifyContent = 'center';
                    overlay.style.cursor = 'pointer';
                    overlay.style.fontSize = '18px';
                    
                    const deleteBtn = document.createElement('button');
                    deleteBtn.className = 'delete-btn';
                    deleteBtn.innerHTML = '🗑️';
                    deleteBtn.style.background = 'none';
                    deleteBtn.style.border = 'none';
                    deleteBtn.style.color = 'white';
                    deleteBtn.style.cursor = 'pointer';
                    deleteBtn.style.fontSize = '25px';
                    deleteBtn.onclick = () => {
                        profilePhoto.innerHTML = `<i class="fas fa-user photo-icon"></i>`;
                        document.getElementById(`fotoVisitante_${idx}`).value = '';
                        
                        try {
                            localStorage.removeItem(`foto_visitante_${idx}`);
                            console.log(`Foto eliminada para visitante ${idx}`);
                        } catch (error) {
                            console.log('Error al eliminar la foto del localStorage:', error);
                        }

                        mostrarIndicadorFoto(idx, false);
                    };
                    
                    overlay.appendChild(deleteBtn);
                    profilePhoto.innerHTML = '';
                    profilePhoto.appendChild(img);
                    profilePhoto.appendChild(overlay);
                    
                    // Guardar la foto en el campo hidden del formulario
                    document.getElementById(`fotoVisitante_${idx}`).value = dataURL;
                    
                    // Guardar la foto en localStorage para persistencia
                    try {
                        localStorage.setItem(`foto_visitante_${idx}`, dataURL);
                        console.log(`Foto guardada para visitante ${idx}`);
                    } catch (error) {
                        console.log('Error al guardar la foto en localStorage:', error);
                    }
                    
                    // Mostrar indicador de éxito
                    mostrarIndicadorFoto(idx, true);
                }
                
                function mostrarIndicadorFoto(idx, exitoso) {
                    const cameraBtn = document.getElementById(`cameraBtn_${idx}`);
                    if (exitoso) {
                        cameraBtn.innerHTML = '<i class="fas fa-check"></i> Foto Tomada';
                        cameraBtn.style.background = '#28a745';
                        cameraBtn.style.color = 'white';
                        cameraBtn.style.borderColor = '#28a745';
                    } else {
                        cameraBtn.innerHTML = '<i class="fas fa-camera"></i> Usar Cámara';
                        cameraBtn.style.background = '';
                        cameraBtn.style.color = '';
                        cameraBtn.style.borderColor = '';
                    }
                }

                
                function loadSavedPhoto(idx) {
                    try {
                        const savedPhoto = localStorage.getItem(`foto_visitante_${idx}`);
                        if (savedPhoto) {
                            const img = document.createElement('img');
                            img.src = savedPhoto;
                            img.alt = 'Foto de perfil';
                            img.style.width = '100%';
                            img.style.height = '100%';
                            img.style.objectFit = 'cover';
                            img.style.borderRadius = '50%';
                            
                            const overlay = document.createElement('div');
                            overlay.className = 'photo-overlay';
                            overlay.style.position = 'absolute';
                            overlay.style.top = '0';
                            overlay.style.left = '0';
                            overlay.style.right = '0';
                            overlay.style.bottom = '0';
                            overlay.style.background = 'rgba(0, 123, 255, 0.7)';
                            overlay.style.color = 'white';
                            overlay.style.borderRadius = '50%';
                            overlay.style.display = 'flex';
                            overlay.style.alignItems = 'center';
                            overlay.style.justifyContent = 'center';
                            overlay.style.cursor = 'pointer';
                            overlay.style.fontSize = '18px';
                            
                            const deleteBtn = document.createElement('button');
                            deleteBtn.className = 'delete-btn';
                            deleteBtn.innerHTML = '🗑️';
                            deleteBtn.style.background = 'none';
                            deleteBtn.style.border = 'none';
                            deleteBtn.style.color = 'white';
                            deleteBtn.style.cursor = 'pointer';
                            deleteBtn.style.fontSize = '18px';
                            deleteBtn.onclick = () => {
                                profilePhoto.innerHTML = `<i class="fas fa-user photo-icon"></i>`;
                                document.getElementById(`fotoVisitante_${idx}`).value = '';
                                
                                try {
                                    localStorage.removeItem(`foto_visitante_${idx}`);
                                    console.log(`Foto eliminada para visitante ${idx}`);
                                } catch (error) {
                                    console.log('Error al eliminar la foto del localStorage:', error);
                                }
                                
                                mostrarIndicadorFoto(idx, false);
                            };
                            
                            overlay.appendChild(deleteBtn);
                            profilePhoto.innerHTML = '';
                            profilePhoto.appendChild(img);
                            profilePhoto.appendChild(overlay);
                            
                            document.getElementById(`fotoVisitante_${idx}`).value = savedPhoto;
                            console.log(`Foto cargada para visitante ${idx}`);
                            mostrarIndicadorFoto(idx, true);
                        }
                    } catch (error) {
                        console.log('Error al cargar la foto del localStorage:', error);
                    }
                }
            }

            function agregarVisitante() {
                const temp = document.createElement('div');
                temp.innerHTML = crearBloqueVisitante(visitanteIndex);
                const bloque = temp.firstElementChild;
                visitantesContainer.appendChild(bloque);
                inicializarBloque(visitanteIndex);
                // Mostrar/ocultar manilla y vehículo por bloque
                const tipoVisitanteSelect = bloque.querySelector('.tipo_visitante_select');
                const manillaContainer = bloque.querySelector('.manillaContainer');
                tipoVisitanteSelect.addEventListener('change', function() {
                    manillaContainer.classList.toggle('hidden', this.value !== 'Huésped');
                });
                const conVehiculoCheck = bloque.querySelector('.conVehiculoCheck');
                const vehiculoContainer = bloque.querySelector('.vehiculoContainer');
                conVehiculoCheck.addEventListener('change', function() {
                    vehiculoContainer.classList.toggle('hidden', !this.checked);
                });
                
                // Agregar evento para el checkbox de vehículo en el primer formulario
                const vehicleCheck = document.getElementById('vehicleCheck');
                const vehicleFields = document.getElementById('vehicleFields');
                if (vehicleCheck && vehicleFields) {
                    vehicleCheck.addEventListener('change', function() {
                        if (this.checked) {
                            vehicleFields.style.display = 'block';
                            vehicleFields.classList.add('show');
                            vehicleFields.classList.remove('hide');
                        } else {
                            vehicleFields.classList.add('hide');
                            vehicleFields.classList.remove('show');
                            setTimeout(() => {
                                vehicleFields.style.display = 'none';
                            }, 300);
                        }
                    });
                }
                // Eliminar visitante
                bloque.querySelector('.eliminarVisitanteBtn').addEventListener('click', function() {
                    // Limpiar la foto del localStorage antes de eliminar
                    try {
                        localStorage.removeItem(`foto_visitante_${visitanteIndex}`);
                    } catch (error) {
                        console.log('Error al limpiar la foto del localStorage:', error);
                    }
                    bloque.remove();
                });
                visitanteIndex++;
            }

            agregarBtn.addEventListener('click', agregarVisitante);
            

            
            // Limpiar localStorage al cargar la página para evitar fotos residuales
            function limpiarFotosResiduales() {
                try {
                    const keys = Object.keys(localStorage);
                    keys.forEach(key => {
                        if (key.startsWith('foto_visitante_') || key === 'profile_photo_data') {
                            localStorage.removeItem(key);
                            console.log(`Foto residual eliminada: ${key}`);
                        }
                    });
                } catch (error) {
                    console.log('Error al limpiar fotos residuales:', error);
                }
            }
            
            // Limpiar fotos residuales al cargar la página
            limpiarFotosResiduales();
            
            // Función para limpiar completamente el caché de la cámara
            function limpiarCacheCamara() {
                try {
                    // Limpiar localStorage
                    const keys = Object.keys(localStorage);
                    keys.forEach(key => {
                        if (key.startsWith('foto_visitante_') || key === 'profile_photo_data') {
                            localStorage.removeItem(key);
                        }
                    });
                    
                    // Limpiar sessionStorage
                    sessionStorage.clear();
                    
                    // Limpiar caché del navegador para elementos multimedia
                    if ('caches' in window) {
                        caches.keys().then(names => {
                            names.forEach(name => {
                                caches.delete(name);
                            });
                        });
                    }
                    
                    console.log('Caché de cámara limpiado completamente');
                } catch (error) {
                    console.log('Error al limpiar caché:', error);
                }
            }
            
            // Limpiar caché al cargar la página
            limpiarCacheCamara();
            
            // Agregar uno por defecto
            agregarVisitante();
            
            // Agregar validación del formulario antes de enviar
            document.getElementById('registroForm').addEventListener('submit', function(e) {
                e.preventDefault();

                // Validar que todas las fotos estén presentes
                const visitantes = document.querySelectorAll('.visitante-bloque');
                let fotosValidas = true;
                let mensajeError = '';

                visitantes.forEach((visitante, index) => {
                    const fotoField = visitante.querySelector(`#fotoVisitante_${index}`);
                    if (!fotoField || !fotoField.value) {
                        fotosValidas = false;
                        mensajeError += `• Visitante ${index + 1}: Falta tomar la foto\n`;
                    }
                });

                if (!fotosValidas) {
                    swal({
                        title: "Fotos requeridas",
                        text: "Por favor, tome una foto para cada visitante:\n\n" + mensajeError,
                        icon: "warning",
                        buttons: {
                            confirm: {
                                text: "Entendido",
                                value: true,
                                visible: true,
                                className: "swal-button",
                            }
                        }
                    });
                    return false;
                }

                // Validar que todos los visitantes tengan la misma torre y apartamento
                if (visitantes.length > 1) {
                    let torre = null;
                    let apartamento = null;
                    let errorTorreApto = false;
                    visitantes.forEach((visitante, index) => {
                        const torreField = visitante.querySelector(`input[name="visitantes[${index}][torre]"]`);
                        const aptoField = visitante.querySelector(`input[name="visitantes[${index}][apartamento]"]`);
                        if (index === 0) {
                            torre = torreField.value.trim();
                            apartamento = aptoField.value.trim();
                        } else {
                            if (torreField.value.trim() !== torre || aptoField.value.trim() !== apartamento) {
                                errorTorreApto = true;
                            }
                        }
                    });
                    if (errorTorreApto) {
                        swal({
                            title: "Datos inconsistentes",
                            text: "Todos los visitantes deben tener la misma torre y apartamento.",
                            icon: "error",
                            buttons: {
                                confirm: {
                                    text: "Corregir",
                                    value: true,
                                    visible: true,
                                    className: "swal-button",
                                }
                            }
                        });
                        return false;
                    }
                }

                // Si todo está bien, enviar el formulario
                this.submit();
            });
        });
        // Registrar salida de visitante
        function registrarSalida(id) {
            fetch(`registrar_salida.php?id=${id}`)
                .then(response => response.text())
                .then(data => {
                    swal({
                        title: "Salida registrada",
                        text: data,
                        icon: "success",
                        buttons: {
                            confirm: {
                                text: "Continuar",
                                value: true,
                                visible: true,
                                className: "swal-button",
                            }
                        }
                    }).then(() => location.reload());
                });
        }

        // Mostrar/ocultar campos según selección
        document.addEventListener('DOMContentLoaded', () => {
            const tipoVisitante = document.getElementById("tipo_visitante");
            const manillaContainer = document.getElementById("manillaContainer");
            const conAcompanantes = document.getElementById("conAcompanantes");
            const acompanantesContainer = document.getElementById("acompanantesContainer");
            const cantidadAcompanantes = document.getElementById("cantidadAcompanantes");
            const conVehiculo = document.getElementById("conVehiculo");
            const vehiculoContainer = document.getElementById("vehiculoContainer");

            tipoVisitante.addEventListener("change", function() {
                manillaContainer.classList.toggle("hidden", this.value !== "Huésped");
            });

            conVehiculo.addEventListener("change", function() {
                vehiculoContainer.classList.toggle("hidden", !this.checked);
            });

            // Foto de perfil visitante
            class SimpleProfilePhoto {
                constructor() {
                    this.profilePhoto = document.getElementById('profilePhoto');
                    this.fileInput = document.getElementById('fileInput');
                    this.cameraBtn = document.getElementById('cameraBtn');
                    this.cameraSection = document.getElementById('cameraSection');
                    this.video = document.getElementById('video');
                    this.canvas = document.getElementById('canvas');
                    this.captureBtn = document.getElementById('captureBtn');
                    this.cancelBtn = document.getElementById('cancelBtn');
                    this.stream = null;
                    this.storageKey = 'profile_photo_data';
                    this.init();
                }
                init() {
                    this.fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
                    this.cameraBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.openCamera();
                    });
                    this.captureBtn.addEventListener('click', () => this.capturePhoto());
                    this.cancelBtn.addEventListener('click', () => this.closeCamera());
                    // No cargar fotos automáticamente para evitar fotos residuales
                    // this.loadSavedPhoto();
                }
                async openCamera() {
                    // Limpiar foto anterior antes de abrir la cámara
                    try {
                        localStorage.removeItem(this.storageKey);
                        console.log('Foto anterior eliminada del localStorage');
                    } catch (error) {
                        console.log('Error al eliminar foto anterior:', error);
                    }
                    
                    // Limpiar visualmente la foto del círculo de perfil
                    this.profilePhoto.innerHTML = `<i class="fas fa-user photo-icon"></i>`;
                    document.getElementById('fotoVisitante').value = '';
                    
                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({ video: true });
                        this.video.srcObject = this.stream;
                        this.cameraSection.style.display = 'flex';
                    } catch (error) {
                        alert('Error al acceder a la cámara.');
                    }
                }
                closeCamera() {
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                        this.stream = null;
                        this.video.srcObject = null;
                    }
                    this.cameraSection.style.display = 'none';
                }
                capturePhoto() {
                    if (!this.stream) return;
                    const size = 400;
                    this.canvas.width = size;
                    this.canvas.height = size;
                    const ctx = this.canvas.getContext('2d');
                    const videoWidth = this.video.videoWidth;
                    const videoHeight = this.video.videoHeight;
                    const minDimension = Math.min(videoWidth, videoHeight);
                    const sourceX = (videoWidth - minDimension) / 2;
                    const sourceY = (videoHeight - minDimension) / 2;
                    ctx.drawImage(this.video, sourceX, sourceY, minDimension, minDimension, 0, 0, size, size);
                    const dataURL = this.canvas.toDataURL('image/jpeg', 0.9);
                    this.setProfilePhoto(dataURL);
                    this.closeCamera();
                }
                setProfilePhoto(dataURL) {
                    const img = document.createElement('img');
                    img.src = dataURL;
                    img.alt = 'Foto de perfil';
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '50%';
                    
                    const overlay = document.createElement('div');
                    overlay.className = 'photo-overlay';
                    overlay.style.position = 'absolute';
                    overlay.style.top = '0';
                    overlay.style.left = '0';
                    overlay.style.right = '0';
                    overlay.style.bottom = '0';
                    overlay.style.background = 'rgba(0, 123, 255, 0.7)';
                    overlay.style.color = 'white';
                    overlay.style.borderRadius = '50%';
                    overlay.style.display = 'flex';
                    overlay.style.alignItems = 'center';
                    overlay.style.justifyContent = 'center';
                    overlay.style.cursor = 'pointer';
                    overlay.style.fontSize = '18px';
                    
                    const deleteBtn = document.createElement('button');
                    deleteBtn.className = 'delete-btn';
                    deleteBtn.innerHTML = '🗑️';
                    deleteBtn.style.background = 'none';
                    deleteBtn.style.border = 'none';
                    deleteBtn.style.color = 'white';
                    deleteBtn.style.cursor = 'pointer';
                    deleteBtn.style.fontSize = '25px';
                    deleteBtn.onclick = () => {
                        this.profilePhoto.innerHTML = `<i class="fas fa-user photo-icon"></i>`;
                        document.getElementById('fotoVisitante').value = '';
                        
                        // Eliminar la foto del localStorage
                        try {
                            localStorage.removeItem(this.storageKey);
                        } catch (error) {}
                        
                        // Limpiar el file input
                        this.fileInput.value = '';
                    };
                    
                    overlay.appendChild(deleteBtn);
                    this.profilePhoto.innerHTML = '';
                    this.profilePhoto.appendChild(img);
                    this.profilePhoto.appendChild(overlay);
                    this.savePhoto(dataURL);
                    document.getElementById('fotoVisitante').value = dataURL;
                    this.fileInput.value = '';
                }
                savePhoto(dataURL) {
                    try {
                        localStorage.setItem(this.storageKey, dataURL);
                    } catch (error) {}
                }
                loadSavedPhoto() {
                    try {
                        const savedPhoto = localStorage.getItem(this.storageKey);
                        if (savedPhoto) {
                            const img = document.createElement('img');
                            img.src = savedPhoto;
                            img.alt = 'Foto de perfil';
                            img.style.width = '100%';
                            img.style.height = '100%';
                            img.style.objectFit = 'cover';
                            img.style.borderRadius = '50%';
                            
                            const overlay = document.createElement('div');
                            overlay.className = 'photo-overlay';
                            overlay.style.position = 'absolute';
                            overlay.style.top = '0';
                            overlay.style.left = '0';
                            overlay.style.right = '0';
                            overlay.style.bottom = '0';
                            overlay.style.background = 'rgba(0, 123, 255, 0.7)';
                            overlay.style.color = 'white';
                            overlay.style.borderRadius = '50%';
                            overlay.style.display = 'flex';
                            overlay.style.alignItems = 'center';
                            overlay.style.justifyContent = 'center';
                            overlay.style.cursor = 'pointer';
                            overlay.style.fontSize = '18px';
                            
                            const deleteBtn = document.createElement('button');
                            deleteBtn.className = 'delete-btn';
                            deleteBtn.innerHTML = '🗑️';
                            deleteBtn.style.background = 'none';
                            deleteBtn.style.border = 'none';
                            deleteBtn.style.color = 'white';
                            deleteBtn.style.cursor = 'pointer';
                            deleteBtn.style.fontSize = '25px';
                            deleteBtn.onclick = () => {
                                this.profilePhoto.innerHTML = `<i class="fas fa-user photo-icon"></i>`;
                                document.getElementById('fotoVisitante').value = '';
                                try {
                                    localStorage.removeItem(this.storageKey);
                                } catch (error) {}
                                
                                // Limpiar el file input
                                this.fileInput.value = '';
                            };
                            
                            overlay.appendChild(deleteBtn);
                            this.profilePhoto.innerHTML = '';
                            this.profilePhoto.appendChild(img);
                            this.profilePhoto.appendChild(overlay);
                        }
                    } catch (error) {}
                }

                handleFileSelect(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (event) => this.setProfilePhoto(event.target.result);
                    reader.readAsDataURL(file);
                }
            }
            new SimpleProfilePhoto();
            document.getElementById('cerrarAcomp').onclick = function() {
                document.getElementById('modalCamaraAcomp').style.display = 'none';
                if (streamAcomp) {
                    streamAcomp.getTracks().forEach(track => track.stop());
                    streamAcomp = null;
                }
            };
            document.getElementById('capturarAcomp').onclick = function() {
                const video = document.getElementById('videoAcomp');
                const canvas = document.getElementById('canvasAcomp');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                const dataURL = canvas.toDataURL('image/jpeg', 0.9);
                document.querySelector(`input[name="acompanantes[${currentAcompIndex}][foto]"]`).value = dataURL;
                document.querySelectorAll('.foto-acomp-preview')[currentAcompIndex].innerHTML = `<img src="${dataURL}" width="80">`;
                document.getElementById('modalCamaraAcomp').style.display = 'none';
                if (streamAcomp) {
                    streamAcomp.getTracks().forEach(track => track.stop());
                    streamAcomp = null;
                }
            };

            // Alerta de registro exitoso (fuera de DOMContentLoaded para que siempre se ejecute)
            if (window.location.search.includes('registro=exitoso')) {
                clearAllPhotos();
                swal({
                    title: "¡Registro exitoso!",
                    text: "El visitante ha sido registrado correctamente.",
                    icon: "success",
                    buttons: {
                        confirm: {
                            text: "Continuar",
                            value: true,
                            visible: true,
                            className: "swal-button",
                        }
                    }
                }).then(() => {
                    // Elimina el parámetro de la URL sin recargar la página
                    const url = new URL(window.location);
                    url.searchParams.delete('registro');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }

            // Función para limpiar todas las fotos del localStorage
            function clearAllPhotos() {
                try {
                    // Limpiar todas las claves que empiecen con 'foto_visitante_'
                    const keys = Object.keys(localStorage);
                    keys.forEach(key => {
                        if (key.startsWith('foto_visitante_')) {
                            localStorage.removeItem(key);
                            console.log(`Foto eliminada del localStorage: ${key}`);
                        }
                    });
                    console.log('Todas las fotos han sido limpiadas del localStorage');
                } catch (error) {
                    console.log('Error al limpiar las fotos del localStorage:', error);
                }
            }

            function verificarFotosGuardadas() {
                try {
                    const keys = Object.keys(localStorage);
                    const fotosGuardadas = keys.filter(key => key.startsWith('foto_visitante_'));
                    if (fotosGuardadas.length > 0) {
                        console.log(`Se encontraron ${fotosGuardadas.length} fotos guardadas en localStorage`);
                        return true;
                    }
                } catch (error) {
                    console.log('Error al verificar fotos guardadas:', error);
                }
                return false;
            }

            verificarFotosGuardadas();
                    });
    </script>
         
    <script src="autofill_reserva.js"></script>
 </body>
</html>