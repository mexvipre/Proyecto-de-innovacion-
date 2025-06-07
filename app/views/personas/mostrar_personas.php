<?php
require_once '../../models/personas/Persona.php';
$modelo = new Persona();

$personas = $modelo->obtenerPersonas(); // Llamada al método que ejecuta el procedimiento almacenado
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Personas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
    <style>
body {
    font-size: 14px;
}

.table th, .table td {
    padding: 4px;
    font-size: 12px;
    white-space: normal;  /* Cambiado a normal para que las palabras largas puedan romperse */
    word-wrap: break-word;  /* Asegura que las palabras largas se dividan */
    overflow-wrap: break-word; /* Asegura que las palabras largas no desborden */
}

.dt-buttons .btn {
    padding: 2px 8px;
    font-size: 12px;
    margin-bottom: 5px;
}

h2 {
    font-size: 18px;
    margin-bottom: 15px;
}

.table {
    table-layout: fixed; /* Establece el layout de la tabla para evitar que se expanda */
}

    </style>
</head>
<body>

<div class="container mt-3">
    <h2>Lista de Personas</h2>
    <table id="tablaPersonas" class="table table-sm table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>ID</th> 
                <th>Nombres</th> 
                <th>Primer Apellido</th> 
                <th>Segundo Apellido</th> 
                <th>Teléfono</th> 
                <th>Tipo Doc.</th> 
                <th>Número Doc.</th> 
                <th>Correo</th>
                <th>Dirección</th> 
                <th>Distrito</th> 

            </tr>
        </thead>
        <tbody>
            <?php foreach ($personas as $p) : ?>
                <tr>
                    <td><?= $p['idpersona'] ?></td> 
                    <td><?= $p['nombres'] ?></td>
                    <td><?= $p['Primer_Apellido'] ?></td> <!-- Mostrar primer apellido -->
                    <td><?= $p['Segundo_Apellido'] ?></td> <!-- Mostrar segundo apellido -->
                    <td><?= $p['telefono'] ?></td> 
                    <td><?= $p['tipodoc'] ?></td>
                    <td><?= $p['numerodoc'] ?></td> 
                    <td><?= $p['correo'] ?></td>  <!-- Agregar el correo -->
                    <td><?= $p['direccion'] ?></td>
                    <td><?= $p['distrito'] ?></td>  <!-- Mostrar el nombre del distrito -->

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tablaPersonas').DataTable({
            responsive: true,
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            dom: 'Bfrtip',
            buttons: [{ extend: 'excelHtml5', text: 'Exportar a Excel', className: 'btn btn-success btn-sm' }],
            pageLength: 10 // 👈 Muestra solo 10 filas por página
        });
    });
</script>

</body>
</html>
