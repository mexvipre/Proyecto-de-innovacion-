<?php
require_once '../../controllers/roles/RolController.php';

$controller = new RolController();
$roles = $controller->listarRoles();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Roles</title>
    <style>
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            text-align: left;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .acciones {
            display: flex;
            gap: 10px;
        }
        .btn-editar, .btn-eliminar {
            padding: 5px 10px;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            text-decoration: none;
        }
        .btn-editar {
            background-color: #4CAF50;
        }
        .btn-eliminar {
            background-color: #f44336;
        }
    </style>
</head>
<body>

<h2 style="text-align: center;">Lista de Roles</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Rol</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($roles)): ?>
            <?php foreach ($roles as $rol): ?>
                <tr>
                    <td><?= htmlspecialchars($rol['idrol']) ?></td>
                    <td><?= htmlspecialchars($rol['rol']) ?></td>
                    <td><?= htmlspecialchars($rol['descripcion']) ?></td>
                    <td class="acciones">
                        <a href="editar_rol.php?id=<?= $rol['idrol'] ?>" class="btn-editar">Editar</a>
                       
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" style="text-align: center;">No hay roles registrados</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
