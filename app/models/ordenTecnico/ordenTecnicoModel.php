<?php
require_once __DIR__ . '../../../config/conexion.php';

class OrdenTecnicoModel {
    private $conn;

    public function __construct() {
      $conexion = new Conexion();
      $this->conn = $conexion->conectar(); // Se establece correctamente la conexión
  }
    // Obtener lista de técnicos
    public function obtenerTecnicos() {
        $query = "SELECT idusuario, namuser FROM usuarios 
                  JOIN contratos ON usuarios.idcontrato = contratos.idcontrato
                  JOIN roles ON contratos.idrol = roles.idrol
                  WHERE roles.rol = 'Tecnico'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    // Asignar técnico a una orden de servicio
    public function asignarTecnico($idOrdenServicio, $idUsuario) {
        $query = "INSERT INTO orden_tecnico (idorden_servicio, idusuario) VALUES (:idOrden, :idUsuario)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idOrden', $idOrdenServicio);
        $stmt->bindParam(':idUsuario', $idUsuario);
        return $stmt->execute();
    }
}
?>
