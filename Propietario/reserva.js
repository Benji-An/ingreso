document.getElementById('formReserva').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    fetch('/ingreso/Propietario/reserva.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        console.log(res);
        return res.json();
    })
    .then(data => {
        if (data.id) {
            document.getElementById('qrReserva').innerHTML = '';
            new QRCode(document.getElementById('qrReserva'), {
                text: data.id.toString(),
                width: 180,
                height: 180
            });
        } else {
            let errorMsg = data.error || 'No se pudo generar la reserva.';
            if (data.detalle) errorMsg += '<br><small>' + data.detalle + '</small>';
            document.getElementById('qrReserva').innerHTML = '<div class="alert alert-danger">' + errorMsg + '</div>';
        }
    })
    .catch((err) => {
        console.log('Error:', err);
        document.getElementById('qrReserva').innerHTML = '<div class="alert alert-danger">Error de conexión.</div>';
    });
});