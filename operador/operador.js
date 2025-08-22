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
            this.loadSavedPhoto();
        }
        async openCamera() {
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
            const overlay = document.createElement('div');
            overlay.className = 'photo-overlay';
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'delete-btn';
            deleteBtn.innerHTML = '🗑️';
            deleteBtn.onclick = () => this.deletePhoto();
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
                    const overlay = document.createElement('div');
                    overlay.className = 'photo-overlay';
                    const deleteBtn = document.createElement('button');
                    deleteBtn.className = 'delete-btn';
                    deleteBtn.innerHTML = '🗑️';
                    deleteBtn.onclick = () => this.deletePhoto();
                    overlay.appendChild(deleteBtn);
                    this.profilePhoto.innerHTML = '';
                    this.profilePhoto.appendChild(img);
                    this.profilePhoto.appendChild(overlay);
                }
            } catch (error) {}
        }
        deletePhoto() {
            this.profilePhoto.innerHTML = `
                <div class="placeholder-icon" id="placeholderIcon">
                    <div class="user-icon"></div>
                </div>
            `;
            try {
                localStorage.removeItem(this.storageKey);
            } catch (error) {}
            fetch('eliminar_foto.php', { method: 'POST' }).catch(() => {});
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

    // Mostrar/ocultar tabla de últimas 24 horas
    const btnUltimas24h = document.getElementById('toggleUltimas24h');
    const tablaUltimas24h = document.getElementById('tablaUltimas24h');
    if (btnUltimas24h && tablaUltimas24h) {
        btnUltimas24h.addEventListener('click', () => {
            tablaUltimas24h.style.display = tablaUltimas24h.style.display === 'none' ? 'block' : 'none';
        });
    }

    // Alerta de registro exitoso
    if (window.location.search.includes('registro=exitoso')) {
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
});