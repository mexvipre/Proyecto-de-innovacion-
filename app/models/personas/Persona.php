<?php
require_once '../../config/conexion.php'; // Asegúrate de que el archivo de conexión esté correcto

class Persona {
    private $conexion;

    public function __construct() {
        $this->conexion = Conexion::conectar();  // Conexión a la base de datos
    }

    public function insertarPersona($nombres, $apellidoP, $apellidoS, $telefono, $tipodoc, $numerodoc, $direccion, $iddistritos, $correo, $status) {
        try {
            // Verificar si el usuario está autenticado y obtener el usuario_id
            if (isset($_SESSION['idpersona'])) {
                $usuario_id = $_SESSION['idpersona'];  // Cambio aquí a 'idpersona'
            } else {
                throw new Exception("El usuario no está autenticado.");
            }

            // Preparar la llamada al procedimiento almacenado
            $sql = "CALL sp_insertar_persona(:nombres, :Primer_Apellido, :Segundo_Apellido, :telefono, :tipodoc, :numerodoc, :correo, :direccion, :iddistrito, :estado, :usuario_id)";
            
            $stmt = $this->conexion->prepare($sql);

            // Asignar los parámetros
            $stmt->bindParam(':nombres', $nombres);
            $stmt->bindParam(':Primer_Apellido', $apellidoP);
            $stmt->bindParam(':Segundo_Apellido', $apellidoS);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':tipodoc', $tipodoc);
            $stmt->bindParam(':numerodoc', $numerodoc);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':iddistrito', $iddistritos);
            $stmt->bindParam(':estado', $status);
            $stmt->bindParam(':usuario_id', $usuario_id);

            // Ejecutar la sentencia
            if ($stmt->execute()) {
                return "Persona registrada correctamente.";
            } else {
                return "Error al registrar la persona.";
            }

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }


public function obtenerPersonas() {
    try {
        // Llamar al procedimiento almacenado
        $sql = "CALL sp_obtener_personas_con_distritos()";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        
        // Obtener los resultados del procedimiento almacenado
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verifica si hay resultados
        if ($resultados) {
            return $resultados;
        } else {
            return "No se encontraron registros.";
        }
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}


}


?>
