(function (V) {

    function hayJsPdf() {
        if (!window.jspdf || !window.jspdf.jsPDF) {
            alert('No se pudo cargar la librería de PDF. Revisa tu conexión a internet.');
            return false;
        }
        return true;
    }

    // ===== PDF del listado de ventas =====
    document.addEventListener('DOMContentLoaded', function () {
        const btnPdf = document.getElementById('btnExportPdf');
        if (!btnPdf) return;

        btnPdf.addEventListener('click', function () {
            if (!V.hayDatos()) return;
            if (!hayJsPdf()) return;

            const datos = EXPORT_VENTAS.ventas;
            const total = V.total();

            const doc = new window.jspdf.jsPDF({ unit: 'pt', format: 'a4' });
            doc.setFontSize(16);
            doc.text('Reporte de ventas', 40, 45);
            doc.setFontSize(10);
            doc.text('Período: ' + EXPORT_VENTAS.periodo, 40, 64);
            doc.text('Generado: ' + new Date().toLocaleString('es'), 40, 78);

            doc.autoTable({
                startY: 95,
                head: [['N°', 'Fecha', 'Hora', 'Cliente', 'Vendedor', 'Total']],
                body: datos.map(function (v) {
                    return [v.id, v.fecha, v.hora, v.cliente, v.vendedor, V.num(v.total)];
                }),
                foot: [['', '', '', '', 'TOTAL', V.num(total)]],
                showFoot: 'lastPage',
                styles: { fontSize: 9 },
                headStyles: { fillColor: [33, 37, 41] },
                footStyles: { fillColor: [233, 236, 239], textColor: 20, fontStyle: 'bold' },
                columnStyles: { 0: { halign: 'center' }, 5: { halign: 'right' } }
            });

            doc.save(EXPORT_VENTAS.archivo + '.pdf');
        });
    });

    // ===== PDF del recibo (lo llama ventas-recibo.js) =====
    V.reciboPdf = function (d) {
        if (!hayJsPdf()) return;

        const doc = new window.jspdf.jsPDF({ unit: 'pt', format: 'a5' });
        const W = doc.internal.pageSize.getWidth();
        const H = doc.internal.pageSize.getHeight();
        const M = 36;
        const c = d.cliente || {};

        // Cabecera oscura
        doc.setFillColor(33, 37, 41);
        doc.rect(0, 0, W, 78, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(16);
        doc.text(V.CFG.empresa, M, 36);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(8);
        doc.text(doc.splitTextToSize(V.CFG.detalle, W - 2 * M - 110), M, 52);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(14);
        doc.text('RECIBO', W - M, 34, { align: 'right' });
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.text('N° ' + d.numero, W - M, 52, { align: 'right' });

        // Bloques de información (cliente a la izquierda, venta a la derecha)
        function bloque(x, y, lista, alin, anchoMax) {
            lista.forEach(function (par) {
                if (!par[1]) return;
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(7.5);
                doc.setTextColor(134, 142, 150);
                doc.text(par[0].toUpperCase(), x, y, { align: alin });
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(10);
                doc.setTextColor(33, 37, 41);
                const lineas = doc.splitTextToSize(String(par[1]), anchoMax);
                doc.text(lineas, x, y + 12, { align: alin });
                y += 12 + lineas.length * 12 + 6;
            });
            return y;
        }
        const mitad = (W - 2 * M) / 2 - 10;
        const yIzq = bloque(M, 100, [
            ['Cliente', c.nombre],
            ['NIT', c.nit],
            ['Dirección', c.direccion],
            ['Teléfono', c.telf]
        ], 'left', mitad);
        const yDer = bloque(W - M, 100, [
            ['Fecha', d.fecha + ' · ' + d.hora],
            ['Atendido por', d.vendedor]
        ], 'right', mitad);

        // Tabla de productos
        doc.autoTable({
            startY: Math.max(yIzq, yDer) + 4,
            margin: { left: M, right: M },
            head: [[
                'Producto',
                { content: 'Cant.', styles: { halign: 'center' } },
                { content: 'P. Unit.', styles: { halign: 'right' } },
                { content: 'Subtotal', styles: { halign: 'right' } }
            ]],
            body: d.items.map(function (i) {
                return [i.producto, i.cantidad, V.num(i.precio), V.num(i.subtotal)];
            }),
            styles: { fontSize: 9, cellPadding: 5, textColor: [33, 37, 41] },
            headStyles: { fillColor: [233, 236, 239], textColor: [33, 37, 41], fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [248, 249, 250] },
            columnStyles: {
                1: { halign: 'center', cellWidth: 40 },
                2: { halign: 'right', cellWidth: 65 },
                3: { halign: 'right', cellWidth: 70 }
            }
        });

        // Total
        let y = doc.lastAutoTable.finalY + 16;
        if (y + 80 > H) {
            doc.addPage();
            y = 50;
        }
        doc.setDrawColor(33, 37, 41);
        doc.setLineWidth(1.2);
        doc.line(W - M - 190, y, W - M, y);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(33, 37, 41);
        doc.setFontSize(11);
        doc.text('TOTAL', W - M - 190, y + 19);
        doc.setFontSize(15);
        doc.text(V.money(d.total), W - M, y + 19, { align: 'right' });

        // Pie
        doc.setFont('helvetica', 'italic');
        doc.setFontSize(9);
        doc.setTextColor(134, 142, 150);
        doc.text('¡Gracias por su compra!', W / 2, y + 62, { align: 'center' });

        doc.save('recibo_' + d.numero + '.pdf');
    };

})(window.Ventas);