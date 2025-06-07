<?php
require_once __DIR__ . '../../../config/conexion.php';

class OrdenServicioModel {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar(); // Se establece correctamente la conexión
    }

    

    public function obtenerOrden() {
        try {
            $sql = "CALL sp_ListarOrdenesServicio()";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC); // Devuelve un array asociativo
            
            $stmt = null; // Cerrar la consulta
            return $resultado;
        } catch (PDOException $e) {
            throw new Exception("Error al obtener la orden de servicio: " . $e->getMessage());
        }
    }
    public function obtenerOrdenPorId($idOrden) {
        try {
            $sql = "CALL ObtenerOrdenServicio(:idorden)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idOrden', $idOrden, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
            $stmt = null; // Cerrar la consulta
            return $resultado;
        } catch (PDOException $e) {
            throw new Exception("Error al obtener la orden de servicio: " . $e->getMessage());
        }
    }
    
}
?>
