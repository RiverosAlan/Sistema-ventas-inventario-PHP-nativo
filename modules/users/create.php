<?php
require_once "../../clases/Usuario.php";
require_once "../../clases/Avatar.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_usuario'])) {
  $usuarioModel = new Usuario();
  $_POST['usuario'] = $_POST['cuenta'] ?? '';

  // 1. Validar la foto ANTES de crear el usuario (si es inválida, no se crea nada)
  try {
    Avatar::validar($_FILES['avatar'] ?? null);
  } catch (Exception $e) {
    header("Location: index.php?error=3");   // 3 = imagen no válida
    exit;
  }

  // 2. Crear el usuario: create() debe devolver el id nuevo (int) o false
  $nuevoId = $usuarioModel->create();

  if ($nuevoId) {
    // 3. Guardar la foto con ese id (solo si create() devolvió un id numérico)
    $fotoOk = true;
    if (is_int($nuevoId)) {
      try {
        Avatar::guardar($nuevoId, $_FILES['avatar'] ?? null);
      } catch (Exception $e) {
        error_log("Avatar: " . $e->getMessage());
        $fotoOk = false;
      }
    }
    header("Location: index.php?success=1" . ($fotoOk ? '' : '&foto_error=1'));
    exit;
  }

  header("Location: index.php?error=1");
  exit;
}
?>


<!-- ========================================================
     ESTRUCTURA VISUAL DEL MODAL DE REGISTRO
     ======================================================== -->
<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="modalCrearUsuarioLabel">Registrar Nuevo Usuario</h5>
        <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- El formulario apunta a este mismo script para procesar la lógica de arriba -->
      <form action="modules/users/create.php" method="post" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="registrar_usuario" value="1">

          <div class="mb-3">
            <label class="form-label">Nombre Completo:</label>
            <input class="form-control" type="text" name="nombre" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Cargo / Función:</label>
            <input class="form-control" type="text" name="cargo" placeholder="Ej: Administrador de Tienda, Vendedor" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Cuenta / Usuario:</label>
            <input class="form-control" type="text" name="cuenta" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Password:</label>
            <div class="input-group">
              <input class="form-control" type="password" id="create_password_field" name="password" placeholder="Escribe la clave del nuevo usuario aquí" required>
              <button class="btn btn-outline-secondary" type="button" onclick="conmutarOjo('create_password_field', this)">
                👁️
              </button>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Seleccione el Nivel:</label>
            <select name="nivel" id="nivel" class="form-control" required>
              <option value="A">Administrador</option>
              <option value="O">Operador</option>
              <option value="V">Visualizador</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="crear_avatar" class="form-label">Foto (opcional)</label>
            <input class="form-control" type="file" id="crear_avatar" name="avatar" accept="image/*">
            <div class="form-text">JPG, PNG o WEBP, máximo 2 MB. Se recorta a cuadrado.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success fw-bold text-white">Registrar Usuario</button>
        </div>
      </form>
    </div>
  </div>
</div>