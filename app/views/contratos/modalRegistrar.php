<!-- modalRegistrar.php -->
<div class="modal fade" id="modalRegistrar" tabindex="-1" aria-labelledby="modalRegistrarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="modalRegistrarLabel">Registrar Nuevo Contrato</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formCrearContrato">
          <!-- No necesitas el action ni method, se hará con JS -->
          
          <div class="mb-3">
            <label for="dni" class="form-label">Buscar Persona por Documento</label>
            <input type="text" class="form-control" id="dni" placeholder="Ingresa número de documento" onkeyup="filtrarPorDocumento()" />
          </div>

          <div class="mb-3">
            <label for="idpersona" class="form-label">Persona</label>
            <select name="idpersona" id="idpersona" class="form-select" required>
                <option value="">Seleccione una persona</option>
                <?php foreach ($personas as $persona): ?>
                    <option value="<?= $persona['idpersona'] ?>" class="persona" data-dni="<?= $persona['numerodoc'] ?>">
                        <?= htmlspecialchars($persona['nombres']) ?> <?= htmlspecialchars($persona['Primer_Apellido']) ?> - <?= htmlspecialchars($persona['tipodoc']) ?>: <?= htmlspecialchars($persona['numerodoc']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="idrol" class="form-label">Rol</label>
            <select name="idrol" id="idrol" class="form-select" required>
                <option value="">Seleccione un rol</option>
                <?php foreach ($roles as $rol): ?>
                    <?php if ($rol['idrol'] != 1): ?>
                        <option value="<?= $rol['idrol'] ?>">
                            <?= htmlspecialchars($rol['rol']) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" required>
          </div>

          <div class="mb-3">
            <label for="fecha_fin" class="form-label">Fecha Fin</label>
            <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
          </div>

          <div class="mb-3">
            <label for="observaciones" class="form-label">Observaciones</label>
            <textarea class="form-control" name="observaciones" id="observaciones" required></textarea>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Contrato</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  function filtrarPorDocumento() {
    const dniInput = document.getElementById('dni').value.toLowerCase();
    const personas = document.querySelectorAll('#idpersona .persona');
    let personaEncontrada = false;

    personas.forEach(function(persona) {
      const dniPersona = persona.getAttribute('data-dni').toLowerCase();
      if (dniPersona.includes(dniInput)) {
        persona.style.display = 'block';
        if (!personaEncontrada) {
          persona.selected = true;
          personaEncontrada = true;
        }
      } else {
        persona.style.display = 'none';
      }
    });

    if (!personaEncontrada) {
      document.getElementById('idpersona').selectedIndex = 0;
    }
  }

  // Manejar envío por fetch
  document.getElementById('formCrearContrato').addEventListener('submit', function(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    fetch('http://localhost/andream/app/views/contratos/crearContrato.php', { // Cambia la ruta si es necesario
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: data.message,
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          // Cerrar modal y recargar página o actualizar tabla dinámicamente
          const modal = bootstrap.Modal.getInstance(document.getElementById('modalRegistrar'));
          modal.hide();
          window.location.reload();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: data.message
        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo contactar con el servidor.'
      });
    });
  });
</script>
