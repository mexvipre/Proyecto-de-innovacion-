
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/conexion.php'; // conexión PDO
require_once '../../models/clientes/Clientes.php';

$conexion = Conexion::conectar();
$clienteModel = new ClienteModel();
$clientes = $clienteModel->obtenerClientes();

?>

<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formRegistrarOrden" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAgregarLabel">Registrar Orden</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="fecha_recepcion" class="form-label">Fecha de recepción</label>
          <input type="datetime-local" class="form-control" id="fecha_recepcion" name="fecha_recepcion" 
                 value="<?= date('Y-m-d\TH:i') ?>" required>
        </div>

        <!-- Campo Documento (DNI o RUC) -->
        <div class="mb-3">
          <label for="dni" class="form-label">Documento (DNI o RUC)</label>
          <input type="text" class="form-control" id="dni" name="dni" placeholder="Ingrese DNI (8) o RUC (11)" required>
        </div>

        <!-- Campo para mostrar el nombre del cliente -->
        <div class="mb-3" id="campo-nombre-cliente" style="display: none;">
          <label for="nombre_cliente" class="form-label">Nombre del Cliente</label>
          <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" readonly>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            <label>Creado por:</label>
            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($_SESSION['usuario']); ?>" disabled>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Registrar Orden</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {
  // Buscar cliente al escribir DNI/RUC
  $('#dni').on('input', function () {
    var documento = $(this).val();

    if (documento.length >= 8) {
      $.ajax({
        url: '../../views/general/buscar_cliente.php',
        type: 'GET',
        data: { dni: documento },
        success: function (response) {
          try {
            var data = JSON.parse(response);
            if (data.success) {
              $('#campo-nombre-cliente').show();
              $('#nombre_cliente').val(data.nombre);
            } else {
              $('#campo-nombre-cliente').hide();
              $('#nombre_cliente').val('');
            }
          } catch (e) {
            console.error('Respuesta no válida:', response);
          }
        },
        error: function () {
          console.log('Error en la petición AJAX');
        }
      });
    } else {
      $('#campo-nombre-cliente').hide();
      $('#nombre_cliente').val('');
    }
  });

  // Enviar formulario por AJAX POST
  $('#formRegistrarOrden').on('submit', function (e) {
    e.preventDefault();

    if (!confirm("¿Estás seguro de que deseas registrar esta orden de servicio?")) {
      return;
    }

    $.ajax({
      url: '../../views/general/registrar_orden.php',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function (data) {
        if (data.success) {
          alert(data.message);
          window.location.href = "http://localhost/andream/app/views/dashboard/dashboard.php?view=inicio";
        } else {
          alert("Error: " + data.message);
        }
      },
      error: function (xhr, status, error) {
        console.error('Error en la petición:', error);
        alert('Ocurrió un error al registrar la orden.');
      }
    });
  });
});
</script>
