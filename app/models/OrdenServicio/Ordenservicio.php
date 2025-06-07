<?php
require_once '../../config/conexion.php';

class Ordenservicio {
    private $conn;

    public function __construct() {
        $this->conn = Conexion::conectar(); 
    }

    public function registrarOrden($fecha_recepcion, $idusuario_crea, $idcliente) {
        
        
        try {
            $query = "INSERT INTO orden_de_servicios (fecha_recepcion, idusuario_crea, idcliente)
                      VALUES (:fecha_recepcion, :idusuario_crea, :idcliente)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':fecha_recepcion', $fecha_recepcion);
            $stmt->bindParam(':idusuario_crea', $idusuario_crea);
            $stmt->bindParam(':idcliente', $idcliente);
            $stmt->execute();

            return $this->conn->lastInsertId(); // Devuelve el ID de la orden creada
        } catch (PDOException $e) {
            error_log("Error al registrar orden: " . $e->getMessage(), 3, "errors.log");
            return false;
        }
    }
    // Método para listar las órdenes de servicio
    public function listarUltimaOrden() {
        try {
            // Consulta modificada para obtener solo la última orden
            $query = "SELECT idorden_Servicio, fecha_recepcion FROM orden_de_servicios ORDER BY fecha_recepcion DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); // Devuelve solo la última orden de servicio
        } catch (PDOException $e) {
            error_log("Error al listar la última orden de servicio: " . $e->getMessage(), 3, "errors.log");
            return false;
        }
    }
    }
    
