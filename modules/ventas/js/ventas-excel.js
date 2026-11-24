document.addEventListener('DOMContentLoaded', function () {
    const V = window.Ventas;
    const btnExcel = document.getElementById('btnExportExcel');
    if (!btnExcel) return;

    btnExcel.addEventListener('click', function () {
        if (!V.hayDatos()) return;
        if (typeof XLSX === 'undefined') {
            alert('No se pudo cargar la librería de Excel. Revisa tu conexión a internet.');
            return;
        }

        const datos = EXPORT_VENTAS.ventas;
        const total = V.total();

        const filas = [
            ['REPORTE DE VENTAS'],
            ['Período: ' + EXPORT_VENTAS.periodo],
            ['Generado: ' + new Date().toLocaleString('es')],
            [],
            ['N°', 'Fecha', 'Hora', 'Cliente', 'Vendedor', 'Total']
        ];
        const inicioDatos = filas.length;

        datos.forEach(function (v) {
            filas.push([v.id, v.fecha, v.hora, v.cliente, v.vendedor, v.total]);
        });
        filas.push([]);
        filas.push(['', '', '', '', 'TOTAL', total]);

        const hoja = XLSX.utils.aoa_to_sheet(filas);
        hoja['!cols'] = [{ wch: 8 }, { wch: 12 }, { wch: 8 }, { wch: 32 }, { wch: 24 }, { wch: 14 }];

        // Formato con 2 decimales en la columna Total
        for (let r = inicioDatos; r < filas.length; r++) {
            const celda = hoja[XLSX.utils.encode_cell({ r: r, c: 5 })];
            if (celda && typeof celda.v === 'number') celda.z = '#,##0.00';
        }

        const libro = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(libro, hoja, 'Ventas');
        XLSX.writeFile(libro, EXPORT_VENTAS.archivo + '.xlsx');
    });
});