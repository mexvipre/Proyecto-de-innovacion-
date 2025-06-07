<?php
require_once '../../controllers/empresas/empresasController.php';

$empresa = new EmpresaController();
$empresas = $empresa->obtenerEmpresas();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lista de Empresas</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Agrega tu archivo de CSS para el sidebar si tienes -->
    <link rel="stylesheet" href="path/to/your/sidebar.css">

    <!-- Otros archivos CSS que puedas necesitar -->
</head>
<body class="bg-light">

    <!-- Sidebar (Ejemplo de Sidebar, adapta según tu estructura) -->


    <div class="container mt-5">
        <h2 class="text-center mb-4">Lista de Empresas</h2>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>RUC</th>
                        <th>Razón Social</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th>Fecha de Creación</th>
                        <th>Fecha de Modificación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($empresas)): ?>
                        <?php foreach ($empresas as $empresa): ?>
                            <tr>
                                <td><?= htmlspecialchars($empresa['idempresa']) ?></td>
                                <td><?= htmlspecialchars($empresa['ruc']) ?></td>
                                <td><?= htmlspecialchars($empresa['razon_social']) ?></td>
                                <td><?= htmlspecialchars($empresa['telefono']) ?></td>
                                <td><?= htmlspecialchars($empresa['email']) ?></td>
                                <td><?= htmlspecialchars($empresa['direccion']) ?></td>
                                <td><?= htmlspecialchars($empresa['fecha_creacion']) ?></td>
                                <td><?= htmlspecialchars($empresa['fecha_modificacion']) ?></td>
                                <td>
                                <a href="dashboard.php?view=editar_empresas&idempresa=<?php echo $empresa['idempresa']; ?>" class="btn btn-warning">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-danger">No hay empresas registradas o ocurrió un error.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS (incluye dependencias necesarias para funcionalidades interactivas como dropdowns y modales) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Agrega tu archivo de JavaScript para el sidebar si tienes -->
    <script src="path/to/your/sidebar.js"></script>

    <!-- Otros scripts que puedas necesitar -->
</body>
</html>
