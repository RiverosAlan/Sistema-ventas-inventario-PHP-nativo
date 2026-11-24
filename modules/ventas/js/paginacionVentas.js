
//PAGINACION DE TABLA DE VENTAS
    document.addEventListener('DOMContentLoaded', function() {

        const tabla = document.querySelector('.table-responsive table');

        if (!tabla) return;

        const tbody = tabla.querySelector('tbody');
        const contenedor = document.getElementById('controlesPaginacion');
        const informacion = document.getElementById('infoPaginacion');
        const paginacion = document.getElementById('paginacionVentas');

        if (!tbody || !contenedor || !informacion || !paginacion) return;

        const ventas = [];
        let encabezadoActual = null;

        // Obtener ventas y sus respectivos encabezados por día
        Array.from(tbody.querySelectorAll('tr')).forEach(fila => {

            if (fila.classList.contains('table-secondary')) {
                encabezadoActual = fila;
            } else if (fila.querySelectorAll('td').length === 6) {
                ventas.push({
                    fila: fila,
                    encabezado: encabezadoActual
                });
            }
        });

        const ventasPorPagina = 10;
        let paginaActual = 1;

        const totalPaginas = Math.ceil(ventas.length / ventasPorPagina);

        if (ventas.length === 0) {
            paginacion.style.display = 'none';
            return;
        }

        function mostrarPagina(pagina) {

            paginaActual = pagina;

            // Ocultar todas las filas de ventas y encabezados
            tbody.querySelectorAll('tr').forEach(fila => {
                fila.style.display = 'none';
            });

            const inicio = (pagina - 1) * ventasPorPagina;
            const fin = Math.min(inicio + ventasPorPagina, ventas.length);

            let encabezadoMostrado = null;

            for (let i = inicio; i < fin; i++) {

                const venta = ventas[i];

                // Mostrar encabezado del día cuando corresponda
                if (venta.encabezado && encabezadoMostrado !== venta.encabezado) {
                    venta.encabezado.style.display = '';
                    encabezadoMostrado = venta.encabezado;
                }

                venta.fila.style.display = '';
            }

            // Información de registros
            informacion.textContent =
                `Mostrando ${inicio + 1}-${fin} de ${ventas.length} ventas`;

            dibujarControles();
        }

        function dibujarControles() {

            contenedor.innerHTML = '';

            // Botón anterior
            agregarBoton('‹', paginaActual - 1, paginaActual === 1);

            // Números de página
            for (let i = 1; i <= totalPaginas; i++) {

                agregarBoton(i, i, false, i === paginaActual);

            }

            // Botón siguiente
            agregarBoton('›', paginaActual + 1, paginaActual === totalPaginas);

        }

        function agregarBoton(texto, pagina, deshabilitado, activo = false) {

            const li = document.createElement('li');

            li.className = 'page-item';

            if (deshabilitado) li.classList.add('disabled');
            if (activo) li.classList.add('active');

            const boton = document.createElement('button');

            boton.type = 'button';
            boton.className = 'page-link';
            boton.textContent = texto;

            if (activo) {
                boton.setAttribute('aria-current', 'page');
            }

            boton.disabled = deshabilitado;

            boton.addEventListener('click', function() {
                mostrarPagina(pagina);
            });

            li.appendChild(boton);
            contenedor.appendChild(li);
        }

        // Mostrar primera página
        mostrarPagina(1);

    });

