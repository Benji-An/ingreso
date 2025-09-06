    // Filtros de tiempo para la tabla de manillas
    function filtrarManillasPorFecha() {
        var desde = document.getElementById('filtroFechaManillaDesde').value;
        var hasta = document.getElementById('filtroFechaManillaHasta').value;
        document.querySelectorAll('#tbodyManillas tr').forEach(function(tr) {
            var fecha = tr.getAttribute('data-fecha') || '';
            var mostrar = true;
            if (desde && fecha < desde) mostrar = false;
            if (hasta && fecha > hasta) mostrar = false;
            tr.style.display = mostrar ? '' : 'none';
        });
        actualizarPreciosManillas();
    }
    var fechaDesdeInput = document.getElementById('filtroFechaManillaDesde');
    var fechaHastaInput = document.getElementById('filtroFechaManillaHasta');
    if (fechaDesdeInput && fechaHastaInput) {
        fechaDesdeInput.addEventListener('change', filtrarManillasPorFecha);
        fechaHastaInput.addEventListener('change', filtrarManillasPorFecha);
    }
    // Alternar pestañas Bootstrap (ya lo hace Bootstrap, pero puedes forzar el scroll al cambiar)
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar DataTables con paginación en la tabla de históricos
        $('#tablaInforme').DataTable({
            "paging": true,
            "pageLength": 5,
            "lengthMenu": [5, 10, 25, 50, 100],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
            }
        });
        // Cálculo de precios en la tabla de manillas
        function actualizarPreciosManillas() {
            var precio = parseFloat(document.getElementById('precioManilla').value) || 0;
            var sumatoria = 0;
            document.querySelectorAll('#tbodyManillas tr').forEach(function(tr) {
                if (tr.style.display === 'none') return;
                var manillas = parseInt(tr.querySelector('.manillas-count')?.textContent) || 0;
                var precioTotal = manillas * precio;
                tr.querySelector('.precio-total').textContent = precioTotal.toLocaleString('es-CO');
                sumatoria += precioTotal;
            });
            document.getElementById('sumatoriaPrecio').textContent = sumatoria.toLocaleString('es-CO');
        }
        // Exportar sumatoria a Excel
        document.getElementById('btnExportarExcelManillas').addEventListener('click', function() {
            var wb = XLSX.utils.book_new();
            var ws_data = [];
            ws_data.push(['Nombre Completo', 'Tipo de Visitante', 'Fecha de Ingreso', 'Cantidad', 'Precio Total ($)']);
            document.querySelectorAll('#tbodyManillas tr:not([style*="display: none"])').forEach(function(tr) {
                var tds = tr.querySelectorAll('td');
                ws_data.push([
                    tds[0]?.textContent,
                    tds[1]?.textContent,
                    tds[3]?.textContent,
                    tds[2]?.textContent,
                    tds[4]?.textContent
                ]);
            });
            ws_data.push([]);
            ws_data.push(['Total histórico de manillas suministradas', document.querySelectorAll('#tbodyManillas tr:not([style*="display: none"])').length]);
            ws_data.push(['Sumatoria total en precio ($)', document.getElementById('sumatoriaPrecio').textContent]);
            var ws = XLSX.utils.aoa_to_sheet(ws_data);
            XLSX.utils.book_append_sheet(wb, ws, 'Sumatoria');
            XLSX.writeFile(wb, 'sumatoria_manillas.xlsx');
        });
        // Exportar sumatoria a PDF
        document.getElementById('btnExportarPDFManillas').addEventListener('click', function() {
            var { jsPDF } = window.jspdf;
            var doc = new jsPDF();
            doc.setFontSize(14);
            doc.text('Informe de Manillas Suministradas', 20, 20);
            var rows = [];
            document.querySelectorAll('#tbodyManillas tr:not([style*="display: none"])').forEach(function(tr) {
                var tds = tr.querySelectorAll('td');
                rows.push([
                    tds[0]?.textContent,
                    tds[1]?.textContent,
                    tds[3]?.textContent,
                    tds[2]?.textContent,
                    tds[4]?.textContent
                ]);
            });
            doc.autoTable({
                head: [['Nombre Completo', 'Tipo de Visitante', 'Fecha de Ingreso', 'Cantidad', 'Precio Total ($)']],
                body: rows,
                startY: 30,
                styles: { fontSize: 10 }
            });
            var finalY = doc.lastAutoTable.finalY || 30;
            doc.setFontSize(12);
            doc.text('Total histórico de manillas suministradas: ' + document.querySelectorAll('#tbodyManillas tr:not([style*="display: none"])').length, 20, finalY + 10);
            doc.text('Sumatoria total en precio ($): ' + document.getElementById('sumatoriaPrecio').textContent, 20, finalY + 20);
            doc.save('sumatoria_manillas.pdf');
        });
        var precioInput = document.getElementById('precioManilla');
        if (precioInput) {
            precioInput.addEventListener('input', actualizarPreciosManillas);
            actualizarPreciosManillas();
        }
        // Sidebar toggle
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            var sidebar = document.querySelector('.sidebar');
            var main = document.querySelector('.main');
            sidebar.classList.toggle('hidden');
            main.classList.toggle('full');
        });

    // Filtros para tabla histórica
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
        var table = document.getElementById(tableID).cloneNode(true);
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
        var table = document.getElementById(tableID);
        var head = table.querySelector('thead');
        var visibleRows = Array.from(table.querySelectorAll('tbody tr')).filter(tr => tr.style.display !== 'none');
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