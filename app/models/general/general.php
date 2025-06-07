<?php
require_once '../../config/conexion.php';
;

class General {
    private $conn;

    
    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar(); // Se establece correctamente la conexión
    }

    // Método para listar las órdenes de servicio usando el procedimiento almacenado
    public function listarOrdenesServicio() {
        try {
            $query = "CALL ListarOrdenesServicio()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Devuelve todos los resultados como array asociativo
        } catch (PDOException $e) {
            error_log("Error al listar órdenes de servicio: " . $e->getMessage(), 3, "errors.log");
            return false;
        }
    }
    




    public function obtenerOrdenPorId($id) {
        try {
            $query = "CALL ListarOrdenPorID(:id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); // Solo un registro
        } catch (PDOException $e) {
            error_log("Error al obtener orden por ID: " . $e->getMessage(), 3, "errors.log");
            return false;
        }
    }
}
?>
