document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formReserva');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        // Ocultar sección QR y botones antes de enviar
        const qrSection = document.getElementById('qrSection');
        qrSection.style.display = 'none';
        // Enviar datos
        const formData = new FormData(form);
        fetch('reserva.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.id) {
                // Mostrar sección QR y botones
                qrSection.style.display = 'flex';
                // Mostrar QR
                const qrDiv = document.getElementById('qrReserva');
                qrDiv.innerHTML = `<img id="imgQR" src="qr.php?id=${data.id}" alt="QR Reserva" style="width:200px;height:200px;">`;
                // Mostrar el id debajo del QR
                const qrIdText = document.getElementById('qrIdText');
                qrIdText.textContent = 'ID de la reserva: ' + data.id;

                // Compartir nativo
                const btnCompartir = document.getElementById('btnCompartirQR');
                btnCompartir.style.display = 'inline-block';
                btnCompartir.onclick = function() {
                    if (navigator.share) {
                        navigator.share({
                            title: 'Reserva',
                            text: 'ID de reserva: ' + data.id,
                            url: window.location.href
                        });
                    } else {
                        alert('La función de compartir no está disponible en este navegador.');
                    }
                };
                // WhatsApp
                const btnWhatsapp = document.getElementById('btnWhatsappQR');
                btnWhatsapp.style.display = 'inline-block';
                btnWhatsapp.onclick = function() {
                    const url = encodeURIComponent(window.location.href);
                    const text = encodeURIComponent('ID de reserva: ' + data.id + '\n' + url);
                    window.open('https://wa.me/?text=' + text, '_blank');
                };
                // Copiar enlace
                const btnCopiar = document.getElementById('btnCopiarQR');
                btnCopiar.style.display = 'inline-block';
                btnCopiar.onclick = function() {
                    const url = window.location.href;
                    navigator.clipboard.writeText('ID de reserva: ' + data.id + '\n' + url);
                    btnCopiar.textContent = 'Copiado!';
                    setTimeout(() => { btnCopiar.innerHTML = '<i class="bi bi-clipboard"></i> Copiar enlace'; }, 1500);
                };
                // Descargar QR
                const btnDescargar = document.getElementById('btnDescargarQR');
                btnDescargar.style.display = 'inline-block';
                btnDescargar.onclick = function() {
                    const img = document.getElementById('imgQR');
                    const link = document.createElement('a');
                    link.href = img.src;
                    link.download = 'qr_reserva_' + data.id + '.png';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                };
            } else {
                qrSection.style.display = 'none';
                alert('Error al crear la reserva. Inténtalo de nuevo.');
            }
        })
        .catch(() => {
            qrSection.style.display = 'none';
            alert('Error de conexión.');
        });
    });
});