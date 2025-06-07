<?php
require_once __DIR__ . '../../../config/conexion.php';
$conn = Conexion::conectar();

$sql = "SELECT d.iddetequipo, d.modelo, d.numserie, d.problema_reportado, d.salida, ma.idmarcasoc, m.Nombre_Marca AS marca
        FROM detequipos d
        LEFT JOIN marcasasoc ma ON d.idmarcasoc = ma.idmarcasoc
        LEFT JOIN marcas m ON ma.id_marca = m.id_marca";
$stmt = $conn->prepare($sql);
$stmt->execute();
$equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Equipos por Orden de Servicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            overflow: auto !important;
            position: relative;
        }
        .modal {
            z-index: 1050 !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Detalles de Equipos por Orden de Servicio</h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Serie</th>
                    <th>Problema Reportado</th>
                    <th>Salida</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipos as $equipo): ?>
                    <tr>
                        <td><?= htmlspecialchars($equipo['iddetequipo']) ?></td>
                        <td><?= htmlspecialchars($equipo['marca'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($equipo['modelo'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($equipo['numserie'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($equipo['problema_reportado'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($equipo['salida'] ?? 'N/A') ?></td>
                        <td>
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalCaracteristicas<?= $equipo['iddetequipo'] ?>">
                                Ver Características
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Incluir los modales fuera de la tabla -->
        <?php foreach ($equipos as $equipo): ?>
            <?php
            $id = $equipo['iddetequipo'];
            ob_start();
            include 'modal_caracteristicas.php';
            $modalHtml = ob_get_clean();
            echo $modalHtml;
            ?>
        <?php endforeach; ?>
    </div>
</body>
</html>