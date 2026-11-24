<?php
require_once "../../config/conexion.php";
require_once "../../config/helpers.php";
require_once "../../clases/Avatar.php";
$UserConnected = verifySession();

if ($UserConnected->nivel != 'A') {
  header("Location: ../../error-pages/401.html");
  exit;
}

require_once "../../clases/Usuario.php";
$usuarioModel = new Usuario();
$data = $usuarioModel->read();

include('../../layout/header.php');
?>

<div class="row">
  <div class="col-md-12">
    <div class="card sin_margenes">
      <div id="cabeza" class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Usuarios</h4>
        <button type="button" class="btn btn-success" data-coreui-toggle="modal" data-coreui-target="#modalCrearUsuario">
          <i class="fas fa-plus"></i> Adicionar
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>AVATAR</th>
                <th>NOMBRE</th>
                <th>CUENTA / USUARIO</th>
                <th>ROL / NIVEL</th>
                <th>ESTADO</th>
                <th>ACCIONES</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $data->fetch(PDO::FETCH_OBJ)) { ?>
                <tr>
                  <td><?= $row->id ?></td>
                  <td>
                    <?php $avatarUrl = Avatar::url((int) $row->id); ?>
                    <?php if ($avatarUrl) { ?>
                      <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES) ?>" width="48" height="48"
                        class="rounded-circle border" style="object-fit:cover;" alt="">
                    <?php } else { ?>
                      <i class="fas fa-user-circle fa-3x text-muted"></i>
                    <?php } ?>
                  </td>
                  <td><strong><?= $row->nombre ?></strong><br><small class="text-muted"><?= $row->cargo ?></small></td>
                  <td><?= $row->usuario ?></td>
                  <td>
                    <span class="badge bg-secondary"><?= nivel($row->nivel) ?></span>
                  </td>
                  <td>
                    <?php if ($row->id == $UserConnected->id) { ?>
                      <span class="badge bg-success">Activo</span>
                    <?php } else { ?>
                      <form action="modules/users/cambiar_estado.php" method="POST" style="display:inline;"
                        onsubmit="return confirmarCambioEstado('<?= $row->activo ?? '1' ?>');">
                        <input type="hidden" name="id" value="<?= $row->id ?>">
                        <?php if (($row->activo ?? '1') == '1') { ?>
                          <input type="hidden" name="nuevo_estado" value="0">
                          <button type="submit" class="btn btn-sm btn-success text-white">Activo</button>
                        <?php } else { ?>
                          <input type="hidden" name="nuevo_estado" value="1">
                          <button type="submit" class="btn btn-sm btn-danger text-white">Inactivo</button>
                        <?php } ?>
                      </form>
                    <?php } ?>
                  </td>
                  <td>
                    <!-- MODIFICADO: El botón de editar ahora abre el modal dinámico del usuario actual -->
                    <button type="button" class="btn btn-warning btn-sm text-white" data-coreui-toggle="modal" data-coreui-target="#modalEditarUsuario<?= $row->id ?>">
                      <i class="fas fa-edit"></i> Editar
                    </button>

                    <?php if ($row->id != $UserConnected->id) { ?>
                      <a href="modules/users/delete.php?id=<?= $row->id ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar a <?= $row->nombre ?>?')">
                        <i class="fas fa-trash"></i> Eliminar
                      </a>
                    <?php } ?>
                  </td>
                </tr>
              <?php
                // INCORPORACIÓN CLAVE: Incluimos el modal de edición dentro del bucle 
                // para que $row contenga los datos correctos de esta fila específica
                include "edit.php";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Incluimos el modal aislado para el registro de nuevos usuarios
include "create.php";
?>

<!-- ========================================================
     MODALES PEQUEÑOS DE ÉXITO (Notificaciones automáticas)
     ======================================================== -->
<div class="modal fade" id="modalExito" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content border-success">
      <div class="modal-header bg-success text-white py-2">
        <h6 class="modal-title"><i class="fas fa-check-circle me-2"></i>¡Excelente!</h6>
        <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-3">
        <p id="txt-exito-dinamico" class="mb-0 fw-semibold text-success"></p>
      </div>
    </div>
  </div>
</div>

<?php include('../../layout/footer.php'); ?>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const txtExito = document.getElementById('txt-exito-dinamico');
    const modalElement = document.getElementById('modalExito');

    // Verificamos si existe el contenedor del modal en el HTML antes de ejecutar
    if (modalElement && txtExito) {
      let mostrarModal = false;

      // 1. Alerta: Registro de nuevo usuario Exitoso
      if (urlParams.get('success') === '1') {
        txtExito.textContent = "El usuario fue agregado con éxito.";
        modalElement.querySelector('.modal-header').className = "modal-header bg-success text-white py-2";
        txtExito.className = "mb-0 fw-semibold text-success";
        mostrarModal = true;
      }

      // 2. Alerta: Edición de usuario Exitosa
      if (urlParams.get('edit_success') === '1') {
        txtExito.textContent = "Los datos del usuario fueron actualizados con éxito.";
        modalElement.querySelector('.modal-header').className = "modal-header bg-success text-white py-2";
        txtExito.className = "mb-0 fw-semibold text-success";
        mostrarModal = true;
      }

      // 3. Alerta: Actualización de Perfil Propio Exitosa
      if (urlParams.get('perfil_success') === '1') {
        txtExito.textContent = "Tus datos de perfil personal han sido actualizados con éxito.";
        modalElement.querySelector('.modal-header').className = "modal-header bg-success text-white py-2";
        txtExito.className = "mb-0 fw-semibold text-success";
        mostrarModal = true;
      }

      // 4. Alerta: Eliminación de usuario Exitosa (Cambia a color rojo)
      if (urlParams.get('delete_success') === '1') {
        txtExito.textContent = "El usuario ha sido eliminado del sistema con éxito.";
        modalElement.querySelector('.modal-header').className = "modal-header bg-danger text-white py-2";
        txtExito.className = "mb-0 fw-semibold text-danger";
        mostrarModal = true;
      }

      // Si se cumplió cualquiera de las condiciones anteriores, levantamos el modal
      if (mostrarModal) {
        const modalExito = new coreui.Modal(modalElement);
        modalExito.show();

        // Limpia los parámetros de la URL (?success=1, etc.) discretamente sin recargar la página
        window.history.replaceState({}, document.title, window.location.pathname);
      }
    }
  });

  // Función para confirmar la activación / desactivación de estados en la lista
  function confirmarCambioEstado(estadoActual) {
    let mensaje = (estadoActual === '1') ?
      "¿Estás seguro de que deseas DESACTIVAR a este usuario?" :
      "¿Estás seguro de que deseas ACTIVAR a este usuario?";
    return confirm(mensaje);
  }

  // Función interactiva universal para alternar la visibilidad de los inputs de contraseña (Ojo)
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