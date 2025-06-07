<?php
require_once 'app/config/conexion.php'; 

class PersonaModel {

    private $conn;

    // Constructor donde pasamos la conexión
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getDatosPersona($idpersona) {
        if (is_null($idpersona)) {
            return null;
        }

        // Consultar los datos de la persona
        $query = "
        SELECT 
            p.idpersona, p.nombres, p.Primer_Apellido, p.Segundo_Apellido, r.rol
        FROM 
            PERSONAS p
        JOIN 
            CONTRATOS c ON p.idpersona = c.idpersona
        JOIN 
            ROLES r ON c.idrol = r.idrol
        WHERE 
            p.idpersona = :idpersona";

        // Usamos la conexión para preparar la consulta
        $stmt = $this->conn->prepare($query);

        if ($stmt === false) {
            die('Error en la preparación de la consulta: ' . implode(', ', $this->conn->errorInfo()));
        }

        // Asociar el parámetro a la consulta usando bindValue
        $stmt->bindValue(':idpersona', $idpersona, PDO::PARAM_INT);

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener los resultados
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si hay resultados
        if ($result) {
            return $result;  // Devuelve los datos de la persona
        }

        return null;  // Si no hay resultados
    }
}
