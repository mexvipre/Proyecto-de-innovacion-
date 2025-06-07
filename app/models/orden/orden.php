<?php
// models/orden/Orden.php
require_once '../../config/conexion.php';

class Orden
{
    public function obtenerEquiposConFK()
    {
        try {
            // Establecer la conexión a la base de datos
            $db = Conexion::conectar();

            // Llamada al procedimiento almacenado 'VerEquiposConFK'
            $sql = "CALL VerEquiposConFK();";

            // Preparar la consulta
            $stmt = $db->prepare($sql);

            // Ejecutar la consulta
            $stmt->execute();

            // Obtener los resultados
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Verificar si se obtuvieron resultados
            if ($resultados) {
                // Mostrar los resultados como un array
                //echo "<pre>";
                //var_dump($resultados); // Muestra el array asociativo con los resultados
                //echo "</pre>";
            } else {
                echo "No se encontraron resultados.\n";
            }

            // Retornar los resultados
            return $resultados;

        } catch (PDOException $e) {
            // En caso de error, mostrar el mensaje de excepción
            echo "Error al ejecutar la consulta: " . $e->getMessage();
        }
    }
}

// Crear una instancia y llamar el método
$orden = new Orden();
$orden->obtenerEquiposConFK();
?>
