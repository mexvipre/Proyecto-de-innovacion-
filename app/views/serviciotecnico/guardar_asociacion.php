<?php
// Procesamiento del formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Conexión a la base de datos
    $conexion = new mysqli("localhost", "root", "", "compuservic");
    if ($conexion->connect_error) {
        die("Conexión fallida: " . $conexion->connect_error);
    }

    // Obtener los datos del formulario
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $numserie = $_POST['numserie'];
    $descripcion = $_POST['descripcion'];
    $fecha_entrega = $_POST['fecha_entrega'];
    $caracteristicas = $_POST['caracteristicas'];
    
    // Si se suben evidencias, manejarlas
    $evidencias = '';
    if (isset($_FILES['evidencias'])) {
        foreach ($_FILES['evidencias']['name'] as $key => $name) {
            $tmp_name = $_FILES['evidencias']['tmp_name'][$key];
            $file_path = 'uploads/' . $name;
            move_uploaded_file($tmp_name, $file_path);
            $evidencias .= $file_path . ';'; // Guardamos los caminos de las evidencias separados por ;
        }
    }

    // Insertar los datos en la base de datos
    $query = "INSERT INTO detequipos (idorden_servicio, idmarcasoc, modelo, numserie, descripcionentrada, fechaentrega, id_caracteristica, idEvidencia)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    
    // Suponiendo que tienes un idorden_servicio que ya está disponible
    $idorden_servicio = isset($_GET['id']) ? $_GET['id'] : null;
    $id_caracteristica = 1; // Puede ser un valor predeterminado o lo que necesites
    $idEvidencia = $evidencias; // Para las evidencias subidas

    $stmt->bind_param("isssssss", $idorden_servicio, $marca, $modelo, $numserie, $descripcion, $fecha_entrega, $caracteristicas, $idEvidencia);
    if ($stmt->execute()) {
        echo "Equipo registrado exitosamente!";
    } else {
        echo "Error al registrar el equipo: " . $conexion->error;
    }

    // Cerrar la conexión
    $stmt->close();
    $conexion->close();
}
?>

<!-- Modal de Registro de Equipo -->
<div class="modal fade" id="modalRegistrarEquipo" tabindex="-1" aria-labelledby="modalRegistrarEquipoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalRegistrarEquipoLabel">Registrar Nuevo Equipo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- Formulario para registrar el equipo -->
        <form action="" method="POST" enctype="multipart/form-data">
          <!-- Campos del formulario -->
          <div class="mb-3">
            <label for="categoria" class="form-label">Categoría</label>
            <select class="form-control" id="categoria" name="categoria" required>
                <option value="">-- Selecciona una categoría --</option>
                <?php
                    $conexion = new mysqli('localhost', 'root', '', 'compuservic');
                    $query = "SELECT id_categoria, nombre_categoria FROM categoria";
                    $result = $conexion->query($query);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id_categoria'] . "'>" . $row['nombre_categoria'] . "</option>";
                    }
                ?>
            </select>
          </div>

          <!-- Subcategoría -->
          <div class="mb-3">
            <label for="subcategoria" class="form-label">Subcategoría</label>
            <select class="form-control" id="subcategoria" name="subcategoria" required>
              <option value="">-- Selecciona una subcategoría --</option>
              <?php
                if (isset($_POST['categoria'])) {
                    $categoria_id = $_POST['categoria'];
                    $query = "SELECT id_subcategoria, Nombre_SubCategoria FROM subcategoria WHERE id_categoria = $categoria_id";
                    $result = $conexion->query($query);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id_subcategoria'] . "'>" . $row['Nombre_SubCategoria'] . "</option>";
                    }
                }
              ?>
            </select>
          </div>

          <!-- Marca -->
          <div class="mb-3">
            <label for="marca" class="form-label">Marca</label>
            <select class="form-control" id="marca" name="marca" required>
              <option value="">-- Selecciona una marca --</option>
              <?php
                if (isset($_POST['categoria'])) {
                    $categoria_id = $_POST['categoria'];
                    $query = "SELECT DISTINCT m.id_marca, m.Nombre_Marca
                              FROM marcasasoc ma
                              JOIN marcas m ON ma.id_marca = m.id_marca
                              JOIN subcategoria s ON ma.id_subcategoria = s.id_subcategoria
                              WHERE s.id_categoria = $categoria_id";
                    $result = $conexion->query($query);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id_marca'] . "'>" . $row['Nombre_Marca'] . "</option>";
                    }
                }
              ?>
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
            <label for="descripcion" class="form-label">Descripción de Entrada</label>
            <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>
          </div>
          <div class="mb-3">
            <label for="fecha_entrega" class="form-label">Fecha de Entrega</label>
            <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega" required>
          </div>
          <div class="mb-3">
            <label for="caracteristicas" class="form-label">Características</label>
            <textarea class="form-control" id="caracteristicas" name="caracteristicas"></textarea>
          </div>
          <div class="mb-3">
            <label for="evidencias" class="form-label">Evidencias</label>
            <input type="file" class="form-control" id="evidencias" name="evidencias[]" multiple>
          </div>
          <!-- Botón de enviar -->
          <button type="submit" class="btn btn-primary">Registrar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Dependencias de Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
