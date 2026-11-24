window.Ventas = window.Ventas || {};

(function (V) {
    // Datos de la empresa para el recibo
    V.CFG = {
        empresa: 'CORE IU',                          // NOMBRE DE LA EMPRESA
        detalle: 'NIT 0000000 · Dirección · Teléfono',
        moneda: 'Bs'                                 // vacío = sin símbolo
    };

    // Formato 1,234.50 (igual que number_format() de PHP)
    V.num = function (n) {
        return Number(n).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    V.money = function (n) {
        return (V.CFG.moneda ? V.CFG.moneda + ' ' : '') + V.num(n);
    };

    // Escapa texto para insertarlo en HTML
    V.esc = function (t) {
        const d = document.createElement('div');
        d.textContent = (t === null || t === undefined) ? '' : t;
        return d.innerHTML;
    };

    V.hayDatos = function () {
        if (!window.EXPORT_VENTAS || EXPORT_VENTAS.ventas.length === 0) {
            alert('No hay ventas para exportar en este rango.');
            return false;
        }
        return true;
    };

    V.total = function () {
        return EXPORT_VENTAS.ventas.reduce(function (acc, v) {
            return acc + v.total;
        }, 0);
    };
})(window.Ventas);