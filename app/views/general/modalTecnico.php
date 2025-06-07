<?php
$modalId = "modalTecnico{$id}";

// Conexión
$conexion = new mysqli("localhost", "root", "", "compuservic");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Ejecutar procedimiento almacenado para listar técnicos
$sql = "CALL listar_tecnicos()";
$resultado = $conexion->query($sql);

$tecnicos = [];
if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $tecnicos[] = $fila;
    }
    $resultado->free();
    $conexion->next_result();
}

// Inicializa las variables
$assignedTechnician = null;
$assignedTechnicianName = '';

// Obtener el técnico asignado (si existe)
$sqlAssigned = "
    SELECT 
        p.idpersona,
        CONCAT(p.nombres, ' ', p.Primer_Apellido, ' ', p.Segundo_Apellido) AS nombre_completo
    FROM detalle_servicios ds
    INNER JOIN usuarios u ON ds.idusuario_soporte = u.idusuario
    INNER JOIN contratos c ON u.idcontrato = c.idcontrato
    INNER JOIN personas p ON c.idpersona = p.idpersona
    WHERE ds.iddetequipo = ?
";

$stmtAssigned = $conexion->prepare($sqlAssigned);
$stmtAssigned->bind_param("i", $id);
$stmtAssigned->execute();
$stmtAssigned->store_result();

if ($stmtAssigned->num_rows > 0) {
    $stmtAssigned->bind_result($assignedTechnician, $assignedTechnicianName);
    $stmtAssigned->fetch();
}
$stmtAssigned->close();
?>
<!-- Modal -->
<div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="<?= $modalId ?>Label">
          <?= $assignedTechnician ? "Modificar Técnico asignado" : "Asignar Técnico al equipo #{$id}" ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">

        <form id="formAsignarTecnico<?= $id ?>">
          <input type="hidden" name="iddetequipo" value="<?= $id ?>">
          <?php if ($assignedTechnicianName): ?>
          <div class="alert alert-info">
            Técnico asignado actualmente: <strong><?= htmlspecialchars($assignedTechnicianName) ?></strong>
          </div>
          <?php endif; ?>
          <div class="mb-3">
            <label for="tecnico<?= $id ?>" class="form-label">Selecciona Técnico:</label>
            <select id="tecnico<?= $id ?>" name="idTecnico" class="form-control" required>
              <option value="">-- Selecciona un técnico --</option>
              <?php foreach ($tecnicos as $tecnico): ?>
                <option value="<?= htmlspecialchars($tecnico['idpersona']) ?>"
                  <?= ($tecnico['idpersona'] == $assignedTechnician) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($tecnico['nombre_completo']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">
            <?= $assignedTechnician ? "Actualizar Asignación" : "Asignar" ?>
          </button>
        </form>

      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('formAsignarTecnico<?= $id ?>');
  
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    
    const formData = new FormData(form);
    
    fetch('http://localhost/andream/app/views/general/guardarTecnico.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert(data.message);
        // Recargar la página después de guardar
        location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Ocurrió un error en la solicitud.');
    });
  });
});


</script>
