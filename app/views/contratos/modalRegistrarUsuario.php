<?php
$nombre_creador = $_SESSION['usuario'] ?? 'Usuario no identificado';
$idusuario = $_SESSION['idpersona'] ?? '';
?>

<!-- El resto del contenido HTML sigue abajo -->
<div class="modal fade" id="modalRegistrarUsuario" tabindex="-1" aria-labelledby="modalRegistrarUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="crearUsuarioDesdeContrato.php" onsubmit="return validarContrasenas();">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="modalRegistrarUsuarioLabel">
            Registrar Usuario para <span id="modalPersonaNombre" class="fw-bold"></span>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <!-- Datos ocultos -->
          <input type="hidden" name="idcontrato" id="inputIdContrato">
          <input type="hidden" name="fecha_creacion" id="fecha_creacion">
          <input type="hidden" name="creado_por" value="<?= htmlspecialchars($idusuario) ?>">

          <!-- Usuario -->
          <div class="mb-3">
            <label for="namuser" class="form-label">Nombre de Usuario</label>
            <input type="text" class="form-control" name="namuser" id="namuser" required>
          </div>

          <!-- Contraseña -->
          <div class="mb-3">
            <label for="passuser" class="form-label">Contraseña</label>
            <input type="password" class="form-control" name="passuser" id="passuser" required>
          </div>

          <!-- Confirmar contraseña -->
          <div class="mb-3">
            <label for="confirmar_passuser" class="form-label">Confirmar Contraseña</label>
            <input type="password" class="form-control" id="confirmar_passuser" required>
            <div id="mensajeContrasena" class="form-text text-danger d-none">Las contraseñas no coinciden.</div>
          </div>

          <!-- Usuario que registra -->
          <div class="mb-3">
            <label class="form-label">Usuario que registra</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($nombre_creador) ?>" disabled>
          </div>

          <!-- Fecha de creación -->
          <div class="mb-3">
            <label class="form-label">Fecha de Creación</label>
            <input type="datetime-local" class="form-control" id="fecha_creacion_display" disabled>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Registrar Usuario</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  const modalUsuario = document.getElementById('modalRegistrarUsuario');
  modalUsuario.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    const idContrato = button.getAttribute('data-id');
    const persona = button.getAttribute('data-persona');

    modalUsuario.querySelector('#inputIdContrato').value = idContrato;
    modalUsuario.querySelector('#modalPersonaNombre').textContent = persona;

    const now = new Date();
    const pad = num => num.toString().padStart(2, '0');
    const fechaFormatted = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;

    modalUsuario.querySelector('#fecha_creacion_display').value = fechaFormatted;
    modalUsuario.querySelector('#fecha_creacion').value = fechaFormatted.replace('T', ' ') + ':00';
  });

  function validarContrasenas() {
    const pass = document.getElementById('passuser').value;
    const confirm = document.getElementById('confirmar_passuser').value;
    const mensaje = document.getElementById('mensajeContrasena');

    if (pass !== confirm) {
      mensaje.classList.remove('d-none');
      return false; // Previene el envío
    } else {
      mensaje.classList.add('d-none');
      return true;
    }
  }
</script>

<script>
  document.querySelector('#modalRegistrarUsuario form').addEventListener('submit', function (e) {
    e.preventDefault(); // Evita que recargue

    if (!validarContrasenas()) return; // Asegura validación previa

    const form = e.target;
    const formData = new FormData(form);

    fetch('http://localhost/andream/app/views/contratos/crearUsuarioDesdeContrato.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert(data.message);
        // Opcional: cerrar modal y resetear formulario
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalRegistrarUsuario'));
        modal.hide();
        form.reset();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch(error => {
      console.error('Error en la petición:', error);
      alert("Error al enviar datos.");
    });
  });
</script>
