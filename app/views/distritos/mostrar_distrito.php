<?php
require_once '../../models/distritos/Distrito.php';

$distrito = new distrito();
$distritos = $distrito->obtenerDistritos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Distritos</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
</head>
<body>
    <h2>Lista de Distritos</h2>

    <?php if (!empty($distritos)): ?>
        <table id="tablaDistritos" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Provincia</th>
                    <th>Departamento</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($distritos as $distrito): ?>
                    <tr>
                        <td><?= htmlspecialchars($distrito['iddistrito']) ?></td>
                        <td><?= htmlspecialchars($distrito['nombre']) ?></td>
                        <td><?= htmlspecialchars($distrito['provincia']) ?></td>
                        <td><?= htmlspecialchars($distrito['departamento']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay distritos registrados.</p>
    <?php endif; ?>

    <!-- jQuery y DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#tablaDistritos').DataTable({
                dom: 'Bfrtip', // Para mostrar botones
                buttons: [
                    'copy', 'excel' //'print' // Botones para exportar
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' // Traducción al español
                }
            });
        });
    </script>
</body>
</html>