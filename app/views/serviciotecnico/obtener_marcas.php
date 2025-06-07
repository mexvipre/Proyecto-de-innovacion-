<?php
if (isset($_POST['categoria_id'])) {
    $conexion = new mysqli('localhost', 'root', '', 'compuservic');
    $categoria_id = $_POST['categoria_id'];

    $stmt = $conexion->prepare("
        SELECT DISTINCT m.id_marca, m.Nombre_Marca
        FROM marcasasoc ma
        JOIN marcas m ON ma.id_marca = m.id_marca
        JOIN subcategoria s ON ma.id_subcategoria = s.id_subcategoria
        WHERE s.id_categoria = ?
    ");
    $stmt->bind_param("i", $categoria_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    echo '<option value="">-- Selecciona una marca --</option>';
    while ($fila = $resultado->fetch_assoc()) {
        echo "<option value='" . $fila['id_marca'] . "'>" . $fila['Nombre_Marca'] . "</option>";
    }

    $stmt->close();
    $conexion->close();

    
}
?>
