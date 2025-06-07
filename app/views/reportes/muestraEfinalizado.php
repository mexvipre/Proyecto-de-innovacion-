<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Equipos Finalizados</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
<style>
  thead {
    background-color: #2395e2 !important;
    color: white !important;
  }
  body {
    font-family: Arial, sans-serif;
    font-size: 12px;
  }
  h2 {
    text-align: center;
    margin: 10px 0;
    font-weight: normal;
  }
  table.dataTable {
    width: 95% !important;
    margin: auto !important;
    border-collapse: collapse;
    font-size: 12px;
  }
  table.dataTable th,
  table.dataTable td {
    padding: 4px 6px !important;
    white-space: nowrap;
    text-align: left;
  }
  table.dataTable tbody tr {
    height: 28px;
  }
  div.dataTables_wrapper {
    width: 95%;
    margin: auto;
  }
</style>
</head>
<body>

<h2>Equipos Finalizados</h2>

<table id="tablaDatos" class="display compact" style="width:100%">
    <thead>
        <tr>
            <th>Cliente</th>
            <th>Técnico</th>
            <th>Modelo</th>
            <th>N° Serie</th>
            <th>Observaciones</th>
            <th>Inicio</th>
            <th>Fin</th>
            <th>Recepción</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    $('#tablaDatos').DataTable({
        ajax: {
            url: 'http://localhost/andream/app/views/reportes/proceso_listar_E_finalizado.php',
            dataSrc: 'data'
        },
        columns: [
            { data: 'cliente' },
            { data: 'tecnico' },
            { data: 'equipo_modelo' },
            { data: 'numero_serie' },
            { data: 'observaciones' },
            { data: 'fechahorainicio' },
            {
                data: 'fechahorafin',
                render: function (data) {
                    return data ? data : '—';
                }
            },
            { data: 'fecha_recepcion' }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        pageLength: 10,
        lengthChange: false,
        searching: true
    });
});
</script>

</body>
</html>
