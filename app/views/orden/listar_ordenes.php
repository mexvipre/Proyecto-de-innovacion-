<?php
require_once '../../models/orden/Orden.php';
$orden = new Orden();
$equipos = $orden->obtenerEquiposConFK();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Equipos</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-4">
    <h2>Lista de Equipos </h2>
    <table id="equiposTabla" class="display table table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>ID Equipo</th>
                <th>Modelo</th>
                <th>N° Serie</th>
                <th>Reporte del Cliente </th>
                <th>Fecha Entrega</th>
                <th>Condición Salida</th>
                <th>Cliente</th>
                <th>Documento</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Marca</th>
                <th>Categoría</th>
                <th>Subcategoría</th>
                <th>Especificación</th>
            </tr>
        </thead>
        <tbody>
<?php
if ($equipos) {
    foreach ($equipos as $equipo) {
        echo "<tr>";
        echo "<td>{$equipo['iddetequipo']}</td>";
        echo "<td>{$equipo['modelo']}</td>";
        echo "<td>{$equipo['numserie']}</td>";
        echo "<td>{$equipo['descripcionentrada']}</td>";
        echo "<td>{$equipo['fechaentrega']}</td>";
        echo "<td>{$equipo['condicionsalida']}</td>";
        echo "<td>{$equipo['cliente_nombre']}</td>";
        echo "<td>{$equipo['cliente_documento']}</td>";
        echo "<td>{$equipo['cliente_telefono']}</td>";
        echo "<td>{$equipo['cliente_correo']}</td>";
        echo "<td>{$equipo['marca_nombre']}</td>";
        echo "<td>{$equipo['categoria_nombre']}</td>";
        echo "<td>{$equipo['subcategoria_nombre']}</td>";
        echo "<td>{$equipo['especificacion_nombre']}</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='14'>No se encontraron equipos</td></tr>";
}
?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#equiposTabla').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.12.1/i18n/Spanish.json"
        }
    });
});

function confirmarFinalizacion() {
    return confirm("¿Estás seguro que deseas finalizar esta orden?");
}
</script>

</body>
</html>
