<?php
require_once '../../controllers/roles/RolController.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rol = $_POST['rol'];
    $descripcion = $_POST['descripcion'];

    $controller = new RolController();
    $controller->insertarRol($rol, $descripcion);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Insertar Rol</title>
</head>
<body>

    <h2>Insertar Nuevo Rol</h2>
    <form method="POST">
        <label for="rol">Rol:</label>
        <input type="text" name="rol" required><br>

        <label for="descripcion">Descripción:</label>
        <textarea name="descripcion" required></textarea><br>

        <button type="submit">Insertar Rol</button>
    </form>

</body>
</html>
