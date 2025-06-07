<!-- Modal para ver credencial del usuario -->
<div class="modal fade" id="modalVerCredencial" tabindex="-1" aria-labelledby="modalVerCredencialLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalVerCredencialLabel">Credencial del Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
<div class="mb-3 d-flex justify-content-between align-items-center">
  <label class="form-label fw-bold mb-0">Nombre de Usuario:</label>
  <button type="button" class="btn btn-sm btn-danger" id="btnEliminarCredencial" onclick="eliminarCredencial()" title="Eliminar Credenciales" style="display: none;">
    <i class="fa fa-trash"></i>
  </button>
</div>
<div id="namuserContenido" class="text-primary">Cargando...</div>

        <!-- ID oculto (no visible para el usuario) -->
        <div id="idUsuarioContenido" style="display: none;"></div>

        <!-- Nombre de usuario -->
        <div class="mb-3">
          <label class="form-label fw-bold">Nombre de Usuario:</label>
          <div id="namuserContenido" class="text-primary">Cargando...</div>
        </div>

        <!-- Contraseña -->
        <div class="mb-2">
          <label class="form-label fw-bold">Contraseña:</label>
          <div class="input-group justify-content-center">
            <input type="password" id="passuserContenido" class="form-control text-center" readonly style="max-width: 250px;">
            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()" id="btnVerPassword">
              <i class="fa fa-eye" id="iconoPassword"></i>
            </button>
          </div>
        </div>

      </div>

      
    </div>
  </div>
</div>

<script>
function verCredencial(btn) {
    const idContrato = btn.getAttribute('data-idcontrato');

    fetch('http://localhost/andream/app/views/contratos/procesar_credencial.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `idcontrato=${encodeURIComponent(idContrato)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.idusuario) {
            document.getElementById('idUsuarioContenido').textContent = data.idusuario;
            document.getElementById('namuserContenido').textContent = data.namuser;
            document.getElementById('passuserContenido').value = data.passuser;
            document.getElementById('btnVerPassword').style.display = 'inline-block';
            document.getElementById('btnEliminarCredencial').style.display = 'inline-block';  // Mostrar botón eliminar
        } else {
            document.getElementById('idUsuarioContenido').textContent = '';
            document.getElementById('namuserContenido').innerHTML = '<span class="text-danger">Aún falta crear sus credenciales</span>';
            document.getElementById('passuserContenido').value = '';
            document.getElementById('btnVerPassword').style.display = 'none';
            document.getElementById('btnEliminarCredencial').style.display = 'none';  // Ocultar botón eliminar
        }

        // Siempre ocultar la contraseña por defecto
        document.getElementById('passuserContenido').type = 'password';
        document.getElementById('iconoPassword').classList.remove('fa-eye-slash');
        document.getElementById('iconoPassword').classList.add('fa-eye');

        const modal = new bootstrap.Modal(document.getElementById('modalVerCredencial'));
        modal.show();
    })
    .catch(err => {
        console.error(err);
        document.getElementById('idUsuarioContenido').textContent = '';
        document.getElementById('namuserContenido').innerHTML = '<span class="text-danger">Error al cargar datos</span>';
        document.getElementById('passuserContenido').value = '';
        document.getElementById('btnVerPassword').style.display = 'none';
    });
}

function togglePassword() {
    const input = document.getElementById('passuserContenido');
    const icon = document.getElementById('iconoPassword');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}



function eliminarCredencial() {
    const idUsuario = document.getElementById('idUsuarioContenido').textContent;

    if (!idUsuario) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay credencial para eliminar',
        });
        return;
    }

    Swal.fire({
        title: '¿Confirmas eliminar estas credenciales?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('http://localhost/andream/app/views/contratos/eliminar_credencial.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `idusuario=${encodeURIComponent(idUsuario)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Credencial eliminada',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Limpiar modal
                        document.getElementById('idUsuarioContenido').textContent = '';
                        document.getElementById('namuserContenido').innerHTML = '<span class="text-danger">Credenciales eliminadas</span>';
                        document.getElementById('passuserContenido').value = '';
                        document.getElementById('btnVerPassword').style.display = 'none';
                        document.getElementById('btnEliminarCredencial').style.display = 'none';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudo eliminar la credencial.'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo contactar con el servidor.'
                });
            });
        }
    });
}

</script>
