document.addEventListener('DOMContentLoaded', function () {
    const V = window.Ventas;

    const RECIBO_CSS = `
        .recibo-papel{background:#fff;color:#212529;font-family:Arial,Helvetica,sans-serif;font-size:13px;max-width:560px;margin:0 auto;border:1px solid #dee2e6;border-radius:6px;overflow:hidden;-webkit-print-color-adjust:exact;print-color-adjust:exact}
        .recibo-papel .rc-head{background:#212529;color:#fff;padding:18px 22px;display:flex;justify-content:space-between;align-items:center;gap:16px}
        .recibo-papel .rc-empresa{font-size:18px;font-weight:700;letter-spacing:.5px}
        .recibo-papel .rc-sub{font-size:11px;opacity:.75;margin-top:3px}
        .recibo-papel .rc-titulo{text-align:right}
        .recibo-papel .rc-titulo b{display:block;font-size:16px;letter-spacing:2px}
        .recibo-papel .rc-num{font-size:12px;opacity:.85;margin-top:3px}
        .recibo-papel .rc-body{padding:20px 22px}
        .recibo-papel .rc-info{display:flex;justify-content:space-between;gap:20px;margin-bottom:18px}
        .recibo-papel .rc-der{text-align:right}
        .recibo-papel .rc-lbl{font-size:10px;text-transform:uppercase;letter-spacing:.6px;color:#868e96;margin-top:8px}
        .recibo-papel .rc-lbl:first-child{margin-top:0}
        .recibo-papel .rc-val{font-weight:600}
        .recibo-papel table{width:100%;border-collapse:collapse}
        .recibo-papel th{background:#e9ecef;font-size:11px;text-transform:uppercase;letter-spacing:.4px;padding:8px 10px;text-align:left}
        .recibo-papel td{padding:8px 10px;border-bottom:1px solid #f1f3f5}
        .recibo-papel .rc-r{text-align:right}
        .recibo-papel .rc-c{text-align:center}
        .recibo-papel .rc-total{display:flex;justify-content:space-between;align-items:baseline;border-top:2px solid #212529;margin:14px 0 0 auto;padding-top:8px;width:60%}
        .recibo-papel .rc-total span:first-child{font-weight:700;letter-spacing:1px}
        .recibo-papel .rc-total span:last-child{font-size:20px;font-weight:700}
        .recibo-papel .rc-gracias{text-align:center;color:#868e96;font-style:italic;margin-top:24px;font-size:12px}
    `;

    const rcStyle = document.createElement('style');
    rcStyle.textContent = RECIBO_CSS;
    document.head.appendChild(rcStyle);

    let reciboActual = null;
    const $cuerpo   = document.getElementById('reciboCuerpo');
    const $titulo   = document.getElementById('tituloRecibo');
    const $imprimir = document.getElementById('btnReciboImprimir');
    const $pdf      = document.getElementById('btnReciboPdf');
    if (!$cuerpo || !$imprimir || !$pdf) return;

    function campo(etiqueta, valor) {
        return valor
            ? '<div class="rc-lbl">' + V.esc(etiqueta) + '</div><div class="rc-val">' + V.esc(valor) + '</div>'
            : '';
    }

    // ----- Vista previa (HTML) -----
    function reciboHtml(d) {
        const c = d.cliente || {};
        const filas = d.items.map(function (i) {
            return '<tr><td>' + V.esc(i.producto) + '</td>' +
                '<td class="rc-c">' + i.cantidad + '</td>' +
                '<td class="rc-r">' + V.num(i.precio) + '</td>' +
                '<td class="rc-r">' + V.num(i.subtotal) + '</td></tr>';
        }).join('');

        return '<div class="recibo-papel" id="reciboPapel">' +
            '<div class="rc-head">' +
                '<div><div class="rc-empresa">' + V.esc(V.CFG.empresa) + '</div><div class="rc-sub">' + V.esc(V.CFG.detalle) + '</div></div>' +
                '<div class="rc-titulo"><b>RECIBO</b><div class="rc-num">N° ' + V.esc(d.numero) + '</div></div>' +
            '</div>' +
            '<div class="rc-body">' +
                '<div class="rc-info">' +
                    '<div>' + campo('Cliente', c.nombre) + campo('NIT', c.nit) + campo('Dirección', c.direccion) + campo('Teléfono', c.telf) + '</div>' +
                    '<div class="rc-der">' + campo('Fecha', d.fecha + ' · ' + d.hora) + campo('Atendido por', d.vendedor) + '</div>' +
                '</div>' +
                '<table><thead><tr>' +
                    '<th>Producto</th><th class="rc-c">Cant.</th><th class="rc-r">P. Unit.</th><th class="rc-r">Subtotal</th>' +
                '</tr></thead><tbody>' + filas + '</tbody></table>' +
                '<div class="rc-total"><span>TOTAL</span><span>' + V.esc(V.money(d.total)) + '</span></div>' +
                '<div class="rc-gracias">¡Gracias por su compra!</div>' +
            '</div>' +
        '</div>';
    }

    // ----- Imprimir: solo el recibo, en un iframe oculto -----
    function imprimir() {
        const papel = document.getElementById('reciboPapel');
        if (!papel) return;

        const iframe = document.createElement('iframe');
        iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
        document.body.appendChild(iframe);

        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write('<!doctype html><html><head><meta charset="utf-8"><title>Recibo ' + V.esc(reciboActual.numero) + '</title>' +
            '<style>' + RECIBO_CSS + '@page{margin:12mm}body{margin:0}.recibo-papel{border:none}</style></head><body>' +
            papel.outerHTML + '</body></html>');
        doc.close();

        setTimeout(function () {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            setTimeout(function () { iframe.remove(); }, 60000);
        }, 300);
    }

    // ----- Eventos -----
    document.querySelectorAll('.btn-recibo-venta').forEach(function (btn) {
        btn.addEventListener('click', function () {
            reciboActual = null;
            $imprimir.disabled = true;
            $pdf.disabled = true;
            $cuerpo.innerHTML = '<p class="text-center text-muted my-4">Cargando recibo...</p>';

            fetch('modules/ventas/recibo.php?id=' + encodeURIComponent(btn.dataset.id))
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.error) {
                        $cuerpo.innerHTML = '<div class="alert alert-warning mb-0">' + V.esc(d.error) + '</div>';
                        return;
                    }
                    reciboActual = d;
                    $titulo.innerHTML = '<i class="fas fa-receipt me-2"></i>Recibo N° ' + V.esc(d.numero);
                    $cuerpo.innerHTML = reciboHtml(d);
                    $imprimir.disabled = false;
                    $pdf.disabled = false;
                })
                .catch(function () {
                    $cuerpo.innerHTML = '<div class="alert alert-danger mb-0">No se pudo cargar el recibo.</div>';
                });
        });
    });

    $imprimir.addEventListener('click', function () { if (reciboActual) imprimir(); });
    $pdf.addEventListener('click', function () { if (reciboActual) V.reciboPdf(reciboActual); });
});