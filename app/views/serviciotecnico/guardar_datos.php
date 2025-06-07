<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = new mysqli('localhost', 'root', '', 'compuservic');

    // Obtener los valores del formulario
    $orden_servicio = $_POST['orden_servicio'];
    $categoria_id = $_POST['categoria'];
    $subcategoria_id = $_POST['subcategoria'];
    $marca_id = $_POST['marca'];
    $especificaciones = $_POST['especificaciones'];
    $caracteristicas = $_POST['caracteristicas'];
    $evidencia_entrada = $_FILES['evidencia_entrada']['name']; // Nombre del archivo subido
    $detequipos = $_POST['detequipos'];

    // Subir archivo
    if ($evidencia_entrada) {
        move_uploaded_file($_FILES['evidencia_entrada']['tmp_name'], "uploads/" . $evidencia_entrada);
    }

    // Insertar en la tabla orden_de_servicios
    $sqlOrden = "INSERT INTO orden_de_servicios (orden_servicio) VALUES ('$orden_servicio')";
    if ($conexion->query($sqlOrden) === TRUE) {
        $orden_id = $conexion->insert_id; // Obtener el ID del último registro insertado

        // Insertar en las otras tablas asociadas
        $sqlCategoriaSubcategoria = "INSERT INTO categorias_subcategorias (orden_id, categoria_id, subcategoria_id) 
                                    VALUES ('$orden_id', '$categoria_id', '$subcategoria_id')";
        $sqlMarca = "INSERT INTO marcas (orden_id, marca_id) VALUES ('$orden_id', '$marca_id')";
        $sqlEspecificaciones = "INSERT INTO especificaciones (orden_id, especificaciones) 
                                VALUES ('$orden_id', '$especificaciones')";
        $sqlCaracteristicas = "INSERT INTO caracteristicas (orden_id, caracteristicas) 
                               VALUES ('$orden_id', '$caracteristicas')";
        $sqlEvidencia = "INSERT INTO evidencia_entrada (orden_id, evidencia_entrada) 
                         VALUES ('$orden_id', '$evidencia_entrada')";
        $sqlDetequipos = "INSERT INTO detequipos (orden_id, detequipos) 
                          VALUES ('$orden_id', '$detequipos')";

        // Ejecutar las inserciones
        $conexion->query($sqlCategoriaSubcategoria);
        $conexion->query($sqlMarca);
        $conexion->query($sqlEspecificaciones);
        $conexion->query($sqlCaracteristicas);
        $conexion->query($sqlEvidencia);
        $conexion->query($sqlDetequipos);

        echo "Datos guardados exitosamente.";
    } else {
        echo "Error al guardar los datos: " . $conexion->error;
    }

    $conexion->close();
}
?>
