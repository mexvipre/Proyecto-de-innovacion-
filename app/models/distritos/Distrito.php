<?php
require_once __DIR__ . '../../../config/conexion.php';


class Distrito {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar(); // Se establece correctamente la conexión
    }

    public function obtenerDistritos() {
        try {
            $sql = "CALL sp_mostrar_distritos()";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $distritos = $stmt->fetchAll(PDO::FETCH_ASSOC); // Devuelve un array asociativo
            
            $stmt = null; // Cerrar la consulta
            return $distritos;
        } catch (PDOException $e) {
            throw new Exception("Error al obtener los distritos: " . $e->getMessage());
        }
    }

    public function agregarDistrito($nombre, $provincia, $departamento) {
        try {
            $sql = "CALL agregarDistrito(:nombre, :provincia, :departamento)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":provincia", $provincia, PDO::PARAM_STR);
            $stmt->bindParam(":departamento", $departamento, PDO::PARAM_STR);
    
            $resultado = $stmt->execute();
    
            $stmt = null; // Cerrar la consulta
    
            if (!$resultado) {
                throw new Exception("Error al ejecutar la consulta de inserción.");
            }
    
            return $resultado;
        } catch (PDOException $e) {
            throw new Exception("Error al agregar el distrito: " . $e->getMessage());
        }
    }
    
}
?>
