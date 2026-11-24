<?php /** @var stdClass $UserConnected */ ?>

<!-- ========================================================
     MODAL GLOBAL DE ÉXITO (Notificaciones del Sistema)
     ======================================================== -->
<div class="modal fade" id="modalExito" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content border-success" id="modalExitoBorde">
      <div class="modal-header bg-success text-white py-2" id="modalExitoHeader">
        <h6 class="modal-title" id="modalExitoTitulo"><i class="fas fa-check-circle me-2"></i>¡Excelente!</h6>
        <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-3">
        <p id="txt-exito-dinamico" class="mb-0 fw-semibold text-success"></p>
      </div>
    </div>
  </div>
</div>

</div> <!-- /Cierre de bloques de la plantilla -->
</div> <!-- /Cierre de bloques de la plantilla -->

<footer class="footer px-4">
  <div>
    <a href="https://coreui.io">CoreUI</a>
    <a href="https://coreui.io/product/free-bootstrap-admin-template/">Bootstrap Admin Template</a>
    &copy; 2026 creativeLabs.
  </div>
  <div class="ms-auto">
    Powered by&nbsp;
    <a href="https://coreui.io/bootstrap/docs/">CoreUI UI Components</a>
  </div>
</footer>
</div>

<!-- CoreUI and necessary plugins-->
<script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
<script src="vendors/simplebar/js/simplebar.min.js"></script>
<!-- Enlace al validador de contraseñas independiente -->
<script src="assets/js/validador_password.js"></script>

<script>
  const header = document.querySelector("header.header");
  document.addEventListener("scroll", () => {
    if (header) {
      header.classList.toggle("shadow-sm", document.documentElement.scrollTop > 0);
    }
  });
</script>

<!-- ========================================================
     SISTEMA DE CONTROL DE NOTIFICACIONES Y MODALES GLOBAL
     ======================================================== -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const txtExito = document.getElementById('txt-exito-dinamico');
    const modalElement = document.getElementById('modalExito');
    
    if (modalElement && txtExito) {
        let mostrarModal = false;
        const modalHeader = document.getElementById('modalExitoHeader');
        const modalBorde = document.getElementById('modalExitoBorde');
        const modalTitulo = document.getElementById('modalExitoTitulo');

        // 1. Registro de nuevo usuario exitoso
        if (urlParams.get('success') === '1') {
            txtExito.textContent = "Agregado con éxito.";
            mostrarModal = true;
        }
        
        // 2. Edición de usuario exitosa
        if (urlParams.get('edit_success') === '1') {
            txtExito.textContent = "Datos actualizados con éxito.";
            mostrarModal = true;
        }

        // 3. ACTUALIZACIÓN DE PERFIL PROPIO EXITOSA (Contraseña cambiada)
        if (urlParams.get('perfil_success') === '1') {
            txtExito.textContent = "Tus datos de perfil personal y contraseña han sido actualizados con éxito.";
            mostrarModal = true;
        }

        // 4. Eliminación de usuario exitosa
        if (urlParams.get('delete_success') === '1') {
            txtExito.textContent = "Eliminado sistema con éxito.";
            if(modalHeader) modalHeader.className = "modal-header bg-danger text-white py-2";
            if(modalBorde) modalBorde.className = "modal-content border-danger";
            txtExito.className = "mb-0 fw-semibold text-danger";
            if(modalTitulo) modalTitulo.innerHTML = '<i class="fas fa-trash-alt me-2"></i>Eliminado';
            mostrarModal = true;
        }

        // Si se cumple cualquiera de los escenarios, disparamos el modal de CoreUI
        if (mostrarModal) {
            const modalNotificacion = new coreui.Modal(modalElement);
            modalNotificacion.show();
            
            // Limpia la URL de parámetros feos sin recargar el navegador
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
});

// Función universal interactiva para conmutar la visualización de claves (Ojo)
function conmutarOjo(idInput, boton) {
    const input = document.getElementById(idInput);
    if (input) {
        if (input.type === "password") {
            input.type = "text";
            boton.textContent = "🙈";
        } else {
            input.type = "password";
            boton.textContent = "👁️";
        }
    }
}
</script>

</body>
</html>
