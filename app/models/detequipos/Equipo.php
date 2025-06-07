<?php
// Incluye la clase de conexión
require_once __DIR__ . '../../../config/conexion.php';

class Equipo {
    private $db;

    public function __construct() {
        // Usamos la clase Conexion para obtener la conexión
        $this->db = Conexion::conectar(); // Obtiene la conexión estática
    }

    public function insertarEquipo($idmarcasoc, $modelo, $numserie, $condicionentrada, $descripcionentrada, $fechaentrega, $condicionsalida, $idEvidencia, $id_caracteristica, $idorden_servicio) {
        $sql = "INSERT INTO detequipos (idmarcasoc, modelo, numserie, condicionentrada, descripcionentrada, fechaentrega, condicionsalida, idEvidencia, id_caracteristica, idorden_servicio) 
                VALUES (:idmarcasoc, :modelo, :numserie, :condicionentrada, :descripcionentrada, :fechaentrega, :condicionsalida, :idEvidencia, :id_caracteristica, :idorden_servicio)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':idmarcasoc', $idmarcasoc);
        $stmt->bindParam(':modelo', $modelo);
        $stmt->bindParam(':numserie', $numserie);
        $stmt->bindParam(':condicionentrada', $condicionentrada);
        $stmt->bindParam(':descripcionentrada', $descripcionentrada);
        $stmt->bindParam(':fechaentrega', $fechaentrega);
        $stmt->bindParam(':condicionsalida', $condicionsalida);
        $stmt->bindParam(':idEvidencia', $idEvidencia);
        $stmt->bindParam(':id_caracteristica', $id_caracteristica);
        $stmt->bindParam(':idorden_servicio', $idorden_servicio);

        return $stmt->execute();
    }

    public function obtenerMarcas() {
        $db = Conexion::conectar();
        $sql = "SELECT idmarcasoc, id_marca FROM marcasasoc ORDER BY idmarcasoc DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Devuelve un array asociativo con los datos
    }
}
?>
