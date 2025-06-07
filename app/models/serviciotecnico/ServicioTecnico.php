<?php
require_once __DIR__ . '/../../../config/Conexion.php';

class ServicioTecnico {
    private $conn;

    public function __construct() {
        $this->conn = Conexion::conectar();
    }

    // Insertar nuevo servicio técnico
    public function crear($data) {
        $query = "INSERT INTO serviciotecnico 
            (idcliente, createdBy, stType, dateCreated, dateModificate, dateCitaInit, dateCitaEnd, observations, modifiedBy)
            VALUES 
            (:idcliente, :createdBy, :stType, :dateCreated, :dateModificate, :dateCitaInit, :dateCitaEnd, :observations, :modifiedBy)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idcliente', $data['idcliente']);
        $stmt->bindParam(':createdBy', $data['createdBy']);
        $stmt->bindParam(':stType', $data['stType']);
        $stmt->bindParam(':dateCreated', $data['dateCreated']);
        $stmt->bindParam(':dateModificate', $data['dateModificate']);
        $stmt->bindParam(':dateCitaInit', $data['dateCitaInit']);
        $stmt->bindParam(':dateCitaEnd', $data['dateCitaEnd']);
        $stmt->bindParam(':observations', $data['observations']);
        $stmt->bindParam(':modifiedBy', $data['modifiedBy']);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
