document.addEventListener('DOMContentLoaded', function () {
    const cuerpo = document.getElementById('detalleVentaBody');
    if (!cuerpo) return;

    document.querySelectorAll('.btn-ver-venta').forEach(function (btn) {
        btn.addEventListener('click', function () {
            cuerpo.innerHTML = '<p class="text-center text-muted my-4">Cargando...</p>';
            fetch('modules/ventas/detalle.php?id=' + encodeURIComponent(btn.dataset.id))
                .then(function (r) { return r.text(); })
                .then(function (html) { cuerpo.innerHTML = html; })
                .catch(function () {
                    cuerpo.innerHTML = '<div class="alert alert-danger mb-0">No se pudo cargar el detalle.</div>';
                });
        });
    });
});