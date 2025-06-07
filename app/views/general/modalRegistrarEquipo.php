<?php
// Habilitar errores para facilitar la depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificar si es una solicitud AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['categoria'])) {
    header('Content-Type: application/json');

    $conexion = new mysqli("localhost", "root", "", "compuservic");
    if ($conexion->connect_error) {
        echo json_encode(["status" => "error", "message" => "Conexión fallida: " . $conexion->connect_error]);
        exit;
    }
    
    // Agrega esta línea para ver qué está pasando
    error_log("Formulario recibido y procesado");

    // Validación de datos POST
    $camposObligatorios = ['categoria', 'subcategoria', 'marca', 'modelo', 'numserie', 'descripcion', 'condicion_entrada', 'fecha_entrega'];
    foreach ($camposObligatorios as $campo) {
        if (empty($_POST[$campo])) {
            echo json_encode(["status" => "error", "message" => "El campo '$campo' es obligatorio."]);
            exit;
        }
    }

    // Variables
    $categoria = $_POST['categoria'];
    $subcategoria = $_POST['subcategoria'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $numserie = $_POST['numserie'];
    $descripcion = $_POST['descripcion'];
    $condicion_entrada = $_POST['condicion_entrada'];
    $fecha_entrega = $_POST['fecha_entrega'];
    $caracteristicas = $_POST['caracteristicas'] ?? '';

    // ID de la orden (GET)
    $idorden_servicio = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($idorden_servicio <= 0) {
        echo json_encode(["status" => "error", "message" => "ID de orden de servicio no válido."]);
        exit;
    }

    // Llamar procedimiento almacenado
    $query = "CALL RegistrarEquipo(?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Error en la preparación: " . $conexion->error]);
        exit;
    }

    $stmt->bind_param("iiissssss", $idorden_servicio, $categoria, $subcategoria, $marca, $modelo, $numserie, $descripcion, $condicion_entrada, $fecha_entrega);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Equipo registrado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al registrar: " . $stmt->error]);
    }

    $stmt->close();
    $conexion->close();
    exit;
}

?>

<!-- HTML y JavaScript empiezan aquí -->
<style>
  .modal-body {
    max-height: 70vh;
    overflow-y: auto;
  }
</style>

<!-- Modal de Registro de Equipo -->
<div class="modal fade" id="modalRegistrarEquipo" tabindex="-1" aria-labelledby="modalRegistrarEquipoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalRegistrarEquipoLabel">Registrar Nuevo Equipo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formRegistrarEquipo" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="categoria" class="form-label">Categoría:</label>
            <select id="categoria" name="categoria" class="form-select" required>
                <option value="">-- Selecciona una categoría --</option>
                <?php
                $conexion = new mysqli("localhost", "root", "", "compuservic");
                $query = "SELECT id_categoria, NombreCategoria FROM categorias";
                $resultado = $conexion->query($query);
                while ($fila = $resultado->fetch_assoc()) {
                    echo "<option value='" . $fila['id_categoria'] . "'>" . $fila['NombreCategoria'] . "</option>";
                }
                ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="subcategoria" class="form-label">Subcategoría:</label>
            <select id="subcategoria" name="subcategoria" class="form-select" required>
                <option value="">-- Selecciona una subcategoría --</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="marca" class="form-label">Marca:</label>
            <select id="marca" name="marca" class="form-select" required>
                <option value="">-- Selecciona una marca --</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="modelo" class="form-label">Modelo</label>
            <input type="text" class="form-control" id="modelo" name="modelo" required>
          </div>
          <div class="mb-3">
            <label for="numserie" class="form-label">Número de Serie</label>
            <input type="text" class="form-control" id="numserie" name="numserie" required>
          </div>
          <div class="mb-3">
            <label for="descripcion" class="form-label">Problema reportado por el Cliente</label>
            <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>
          </div>
          <div class="mb-3">
            <label for="condicion_entrada" class="form-label">Condición de Entrada</label>
            <textarea class="form-control" id="condicion_entrada" name="condicion_entrada" required></textarea>
          </div>
          <div class="mb-3">
            <label for="fecha_entrega" class="form-label">Fecha aprox de Entrega</label>
            <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega" required>
          </div>

          <input type="hidden" name="caracteristicas" value="">

          <button type="submit" class="btn btn-primary">Registrar</button>
        </form>
      </div>
    </div>
  </div>
</div>



<!-- Bootstrap & jQuery -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
  $(document).ready(function(){
    $('#categoria').change(function(){
        let categoriaID = $(this).val();

        $.post("../serviciotecnico/obtener_subcategorias.php", { categoria_id: categoriaID }, function(data){
            $('#subcategoria').html(data);
        });

        $.post("../serviciotecnico/obtener_marcas.php", { categoria_id: categoriaID }, function(data){
            $('#marca').html(data);
        });
    });

    $('#formRegistrarEquipo').off('submit').on('submit', function(e){
        e.preventDefault();

        const urlParams = new URLSearchParams(window.location.search);
        const idOrden = urlParams.get('id');

        $.ajax({
            url: '../../views/general/modalRegistrarEquipo.php?id=' + idOrden,// El mismo archivo procesará el formulario
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response){
                if (response.status === "success") {
                    // Si es exitoso, mostramos el mensaje de éxito
                    alert(response.message);

                    // Cerramos el modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalRegistrarEquipo'));
                    if (modal) modal.hide();

                    // Recargamos la página después de 1 segundo
                    setTimeout(() => {
                        location.reload(); // Recarga la página
                    }, 1000);
                } else {
                    // Si es un error, mostramos el mensaje de error
                    alert("Error: " + response.message);
                }
            },
            error: function(xhr, status, error){
                // En caso de un error en la solicitud AJAX, mostramos un mensaje de red
                alert("Error de red: " + error);
            }
        });
    });
});

</script>
