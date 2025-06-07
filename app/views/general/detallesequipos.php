<?php
// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'modalRegistrarEquipo.php';
include 'modalEliminar.php';

// Verificación de la sesión
if (!isset($_SESSION['idpersona'])) {
    die("Error: No se encontró el ID de la persona en la sesión. Verifica que iniciaste sesión correctamente.");
}

require_once '../../models/general/general.php';
require_once '../../controllers/general/GeneralController.php';
require_once '../../models/distritos/Distrito.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Crear instancias de modelos
$distritoModel = new Distrito();
$general = new GeneralController();

// Obtener los distritos
$distritos = $distritoModel->obtenerDistritos();

// Función segura para imprimir HTML sin errores por valores nulos
function safe($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Establecer la conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'compuservic');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener el ID de la orden de servicio desde la URL
$idorden_servicio = $_GET['id'] ?? null;
$nombre_cliente = $_GET['cliente'] ?? 'Cliente no especificado';



// Verificar si se ha pasado un ID de orden de servicio

// Función para obtener los detalles de la orden por ID usando el procedimiento almacenado
function obtenerOrdenPorId($conexion, $idorden_servicio) {
    if ($idorden_servicio) {
        $query = "CALL ListarOrdenPorID(?)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $idorden_servicio);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }
    return null;
}

$query = "SELECT * FROM detequipos WHERE idorden_servicio = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $idorden_servicio);
$stmt->execute();
$result = $stmt->get_result();

$cantidad_equipos_registrados = $result->num_rows; // <-- AQUI CUENTAS LOS EQUIPOS


// Obtener la orden
$orden = obtenerOrdenPorId($conexion, $idorden_servicio);
?>

<?php
if (isset($_GET['mensaje'])) {
    echo "<div class='alert alert-info'>" . htmlspecialchars($_GET['mensaje']) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipos por Orden</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        td a {
            margin-right: 8px;
            text-decoration: none;
            font-size: 1.1rem;
        }

        td a i.fa-pen { color: #0d6efd; }
        td a i.fa-trash { color: #dc3545; }
        td a i.fa-list-check { color: #198754; }
        td a i.fa-eye { color: #6f42c1; }
        td a i.fa-user-secret { color: #fd7e14; }
        td a i.fa-toolbox { color: #20c997; }

        td a:hover i {
            opacity: 0.8;
            transform: scale(1.1);
            transition: all 0.2s ease-in-out;
        }

        .table thead {
            background-color: rgb(239, 240, 242);
            color: white;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row mb-3">
        <div class="col-md-12 text-center">
            <h2><strong>ORDEN Y SERVICIO</strong></h2>

        </div>
    </div>

    <!-- Interfaz para ORDEN SERVICIO -->
   <!-- Interfaz para ORDEN SERVICIO -->
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6 d-flex align-items-center">
                <!-- Puedes agregar algo aquí si lo necesitas -->
            </div>
        </div>
    </div>
  
<div class="card">
    <div class="card-header" style="background-color: #02505F; color: white;">
        <h5 class="mb-0">Datos de la Orden</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <!-- DNI -->
            <div class="col-md-2">
                <div class="form-floating">
                    <input type="text" class="form-control form-control-sm" maxlength="8" id="dni" value="<?php echo safe($orden['documento_cliente'] ?? ''); ?>" autofocus>
                    <label for="dni" class="form-label">DNI</label>
                </div>
            </div>

            <!-- Cliente -->
            <div class="col-md-4">
                <div class="form-floating">
                    <input type="text" class="form-control form-control-sm" id="apellidos" value="<?php echo safe($orden['nombre_cliente'] ?? ''); ?>">
                    <label for="cliente" class="form-label">Cliente</label>
                </div>
            </div>

            <!-- Teléfono -->
            <div class="col-md-2">
                <div class="form-floating">
                    <input type="text" class="form-control form-control-sm" id="telefono" value="<?php echo safe($orden['telefono_cliente'] ?? ''); ?>">
                    <label for="telefono" class="form-label">Teléfono</label>
                </div>
            </div>

            <!-- Cantidad de Equipos -->
            <div class="col-md-2">
                <div class="form-floating">
                    <input type="text" class="form-control form-control-sm" id="cantidad_equipo" value="<?php echo safe($cantidad_equipos_registrados); ?>" readonly>
                    <label for="cantidad_equipo" class="form-label">N°Equipos</label>
                </div>
            </div>

        <!-- Botón GENERAR ORDEN solo ícono -->
<!-- Botón GENERAR ORDEN con ícono y texto -->
<div class="col-md-1 d-flex justify-content-center align-items-end">
    <a href="http://localhost/andream/app/views/generar_pdf.php?idorden=<?php echo $idorden_servicio; ?>" 
       target="_blank" 
       class="btn btn-danger btn-sm p-2 d-flex flex-column justify-content-center align-items-center me-2 mt-1 w-100 h-100"
       title="Generar PDF">
        <i class="fa-solid fa-file-lines fa-2x mb-1"></i>
        <small>Generar Orden</small>
    </a>
</div>

<!-- Botón WHATSAPP con ícono y texto -->
<div class="col-md-1 d-flex justify-content-center align-items-end">
    <a href="https://wa.me/51<?php echo preg_replace('/[^0-9]/', '', $orden['telefono_cliente'] ?? ''); ?>" 
       target="_blank" 
       class="btn btn-success btn-sm p-2 d-flex flex-column justify-content-center align-items-center mt-1 w-100 h-100"
       style="background-color: #25D366; border-color: #25D366;"
       title="Enviar WhatsApp">
        <i class="fa-brands fa-whatsapp fa-2x mb-1"></i>
        <small>WhatsApp</small>
    </a>
</div>




        </div>
    </div>
</div>

</div>


    </div>

   <div class="container mt-5">
        <?php
        $conexion = new mysqli("localhost", "root", "", "compuservic");
        if ($conexion->connect_error) {
            die("Conexión fallida: " . $conexion->connect_error);
        }

        echo "<div class='card'>";
        echo "<div class='card-header' style='background-color: #02505F; color: white;' class='bg-dark text-white'> ";

        echo "<div class='row align-items-center mb-3'>
                <div class='col-md-6'>
                    <h4 class='mb-0'>Cliente: " . safe($nombre_cliente) . "</h4>
                </div>
                <div class='col-md-3 text-center'>
                    <h4 class='mb-0'>Orden de Servicio: " . safe($idorden_servicio) . "</h4>
                </div>
                <div class='col-md-3 text-end'>
                    <button class='btn btn-sm btn-primary' data-bs-toggle='modal' data-bs-target='#modalRegistrarEquipo'>Registrar Equipo</button>
                </div>
              </div>";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<div class='table-responsive'>";
        echo "<table class='table table-hover'>";
        echo "<thead>
                <tr>
                  <th>ID</th>
                  <th>Tipo</th>
                  <th>Marca</th>
                  <th>Modelo</th>
                  <th>Serie</th>
                  <th>Problema Reportado</th>
                  <th>Salida</th>
                  <th>Acciones</th>
                </tr>
              </thead>";
        echo "<tbody>";

        $query = "CALL VerEquiposConFKPorOrden(?)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $idorden_servicio);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $id = $row['iddetequipo'];
                echo "<tr>";
                echo "<td>{$id}</td>";
                echo "<td>" . safe($row['categoria_nombre']) . "</td>";
                echo "<td>" . safe($row['marca_nombre']) . "</td>";
                echo "<td>" . safe($row['modelo']) . "</td>";
                echo "<td>" . safe($row['numserie']) . "</td>";
                echo "<td>" . safe($row['descripcionentrada']) . "</td>";
                echo "<td>" . safe($row['fechaentrega']) . "</td>";
                echo "<td>

                        <a href='#' title='Eliminar' class='btnEliminar' data-id='<?php echo $id; ?>'>
                            <i class='fa-solid fa-trash'></i>
                        </a>
                        <a href='#' title='Editar' data-bs-toggle='modal' data-bs-target='#modalEditar{$id}'>
                            <i class='fa-solid fa-pen'></i>
                        </a>
                        <a href='#' title='Características' data-bs-toggle='modal' data-bs-target='#modalCaracteristicas{$id}'>
                            <i class='fa-solid fa-list-check'></i>
                        </a>
                        <a href='#' title='Evidencias' data-bs-toggle='modal' data-bs-target='#modalEvidencias{$id}'>
                            <i class='fa-solid fa-eye'></i>
                        </a>
                        <a href='#' title='Asignar técnico' data-bs-toggle='modal' data-bs-target='#modalTecnico{$id}'>
                            <i class='fa-solid fa-user-secret'></i>
                        </a>
                        <a href='#' title='Revisión' data-bs-toggle='modal' data-bs-target='#modalRevision{$id}'>
                            <i class='fa-solid fa-toolbox'></i>
                        </a>
                        <a href='../generar_informe_pdf.php?id={$id}' title='Generar PDF' target='_blank'>
                            <i class='fa-solid fa-file-pdf'></i>
                        </a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8' class='text-center text-muted'>No hay equipos registrados para esta orden de servicio.</td></tr>";
        }

        echo "</tbody>";
        echo "</table>";
        echo "</div></div></div>";

        // Incluir los modales después de la tabla
      if ($result->num_rows > 0) {
            $result->data_seek(0); // Reiniciar el puntero del resultado
            while ($row = $result->fetch_assoc()) {
                $id = $row['iddetequipo'];
                $modelo = $row['modelo'];
                $fechaentrega = $row['fechaentrega'];
                $servicios = $row['servicios_realizados'];

                // Incluir los modales
                include 'modal_evidencias.php';
                include 'modalEliminar.php';
                include 'modalTecnico.php';
                include 'modal_caracteristicas.php';
                include 'modal_editar.php';
                include 'modalrevision.php';
            }
        }
        $conexion->close();
        ?>
    </div>
</div>


<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function mostrarCampos() {
        const tipo = document.getElementById("tipo_registro").value;
        const camposPersona = document.getElementById("campos_persona");
        const camposEmpresa = document.getElementById("campos_empresa");

        camposPersona.style.display = tipo === "persona" ? "block" : "none";
        camposEmpresa.style.display = tipo === "empresa" ? "block" : "none";

        if (tipo === "persona") {
            document.querySelector("input[name='telefono']").setAttribute("required", "required");
        } else {
            document.querySelector("input[name='telefono']").removeAttribute("required");
        }

        const selects = document.querySelectorAll('select[name="iddistrito"]');
        selects.forEach(select => {
            if ((tipo === "persona" && select.closest("#campos_persona")) ||
                (tipo === "empresa" && select.closest("#campos_empresa"))) {
                select.setAttribute("required", "required");
            } else {
                select.removeAttribute("required");
            }
            
        });
    }
</script>

<!-- Modals -->
<?php include 'modalRegistrarEquipo.php'; ?>
<?php include 'modalEliminar.php'; ?>
<script>
$(document).on('click', '.btnEliminar', function () {
    var id = $(this).data('id');
    var button = $(this);  // Guardamos el contexto del botón de eliminación
    console.log('ID a eliminar:', id); // Añadir log para verificar el ID

    $.ajax({
        url: 'http://localhost/andream/app/views/general/procesar_eliminacion.php', // Actualiza la URL
        type: 'GET',
        data: { id: id },
        success: function (response) {
            console.log(response); // Verificar la respuesta
            if (response.status === 'success') {
                // Opcional: eliminar visualmente una fila
                button.closest('tr').remove(); // Elimina la fila que contiene el botón
            } else {
                alert('No se pudo eliminar el registro: ' + response.message);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('Error en la solicitud:', textStatus, errorThrown); // Agregar más detalles del error
            alert('Error en la conexión con el servidor.');
        }
    });
});
</script>






</body>
</html>