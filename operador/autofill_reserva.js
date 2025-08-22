document.getElementById('inputQR').addEventListener('change', function() {
    const id = this.value.trim();
    if (!id) return;
    fetch('buscar_reserva.php?id=' + encodeURIComponent(id))
        .then(res => res.json())
        .then(data => {
            if (data && !data.error) {
                document.querySelector('[name="numero_documento"]').value = data.numero_documento || '';
                document.querySelector('[name="primer_apellido"]').value = data.primer_apellido || '';
                document.querySelector('[name="segundo_apellido"]').value = data.segundo_apellido || '';
                document.querySelector('[name="primer_nombre"]').value = data.primer_nombre || '';
                document.querySelector('[name="segundo_nombre"]').value = data.segundo_nombre || '';
                document.querySelector('[name="genero"]').value = data.genero || '';
                document.querySelector('[name="fecha_nacimiento"]').value = data.fecha_nacimiento || '';
                document.querySelector('[name="torre"]').value = data.torre || '';
                document.querySelector('[name="apartamento"]').value = data.apartamento || '';
                // Limpia el campo QR para el siguiente escaneo
                this.value = '';
                // Opcional: enfoca el primer campo del formulario
                document.querySelector('[name="numero_documento"]').focus();
            } else {
                alert('Reserva no encontrada');
                this.value = '';
            }
        })
        .catch(() => {
            alert('Error al buscar la reserva');
            this.value = '';
        });
});