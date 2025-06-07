<?php
include 'modal.php';

// Incluir el archivo de configuración de la base de datos
require_once '../../config/conexion.php';

// Importar el controlador
require_once '../../controllers/general/generalController.php';


// Crear la conexión a la base de datos
$conexion = Conexion::conectar();

// Instanciar el controlador
$controller = new GeneralController($conexion);

// Obtener resultados
$resultados = $controller->listarOrdenesServicio();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes de Servicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body>

<main class="contenedor">
    <header>
        <h2>Listado de Órdenes de Servicio</h2>
    </header>

    <section class="mt-2">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            Órdenes de servicio
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="date" class="form-control form-control-sm" value="2025-04-11">
                                <!-- Botón que abre el modal -->
                                <a href="#" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregar">Agregar</a>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Hora</th>
                                <th>Atendido</th>
                                <th>Equipos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Iteramos sobre los resultados y los mostramos en la tabla
                            if (isset($resultados) && count($resultados) > 0) {
                                foreach ($resultados as $orden) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($orden['idorden_Servicio']) . "</td>";
                                    echo "<td>" . htmlspecialchars($orden['nombre_cliente']) . "</td>";
                                    echo "<td>" . htmlspecialchars($orden['fecha_recepcion']) . "</td>";
                                    echo "<td>" . htmlspecialchars($orden['usuario_creador']) . "</td>";
                                    echo "<td><a href='dashboard.php?view=detallesequipos&id=" . urlencode($orden['idorden_Servicio']) . "&cliente=" . urlencode($orden['nombre_cliente']) . "' class='btn btn-info btn-sm'>Ver</a></td>";

                                    //echo "<td><a href='../../views/general/detallesequipos.php?id=" . urlencode($orden['idorden_Servicio']) . "&cliente=" . urlencode($orden['nombre_cliente']) . "' class='btn btn-info btn-sm'>Ver</a></td>";

                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No hay órdenes de servicio disponibles.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

</body>
</html>
