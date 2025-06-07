<?php
if (isset($_POST['categoria_id'])) {
    $conexion = new mysqli('localhost', 'root', '', 'compuservic');
    $categoria_id = $_POST['categoria_id'];

    // Consulta corregida
    $stmt = $conexion->prepare("SELECT id_subcategoria, Nombre_SubCategoria FROM subcategoria WHERE id_categoria = ?");
    $stmt->bind_param("i", $categoria_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    echo '<option value="">-- Selecciona una subcategoría --</option>';
    while ($fila = $resultado->fetch_assoc()) {
        echo "<option value='" . $fila['id_subcategoria'] . "'>" . $fila['Nombre_SubCategoria'] . "</option>";
    }

    $stmt->close();
    $conexion->close();
}
?>
