<?php
require_once __DIR__ . '../../../config/conexion.php';

class EmpresasModel {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar(); // Se establece correctamente la conexión
    }

    // Obtener todas las empresas
    public function obtenerEmpresas() {
        try {
            $sql = "CALL sp_mostrar_empresas()"; // Llamamos al procedimiento almacenado
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC); // Devuelve un array asociativo

            $stmt = null; // Cerrar la consulta
            return $empresas;
        } catch (PDOException $e) {
            error_log("Error al obtener las empresas: " . $e->getMessage());
            throw new Exception("Error al obtener las empresas: " . $e->getMessage());
        }
    }

    // Agregar una nueva empresa
    public function agregarEmpresa($ruc, $razon_social, $telefono, $email, $direccion, $iddistrito,$usuario_id) {
        try {
            // Verificar si el usuario está autenticado y obtener el usuario_id
            if (isset($_SESSION['idpersona'])) {
                $usuario_id = $_SESSION['idpersona'];  // Cambio aquí a 'idpersona'
            } else {
                throw new Exception("El usuario no está autenticado.");
            }

            // Llamamos al procedimiento almacenado con solo 6 parámetros
            $sql = "CALL sp_agregar_empresa(:ruc, :razon_social, :telefono, :email, :direccion, :iddistrito, :usuario_id)";
            $stmt = $this->conn->prepare($sql);
    
            // Vinculamos los parámetros con las variables recibidas
            $stmt->bindParam(":ruc", $ruc, PDO::PARAM_STR);
            $stmt->bindParam(":razon_social", $razon_social, PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $telefono, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $direccion, PDO::PARAM_STR);
            $stmt->bindParam(":iddistrito", $iddistrito, PDO::PARAM_INT);
            $stmt->bindParam(':usuario_id', $usuario_id);
    
            // Ejecutamos la consulta
            $resultado = $stmt->execute();
            $stmt = null; // Cerrar la consulta
    
            return $resultado; // Retorna si la inserción fue exitosa
        } catch (PDOException $e) {
            error_log("Error al agregar empresa: " . $e->getMessage());
            throw new Exception("No se pudo agregar la empresa: " . $e->getMessage());
        }
    }    
    
    // Obtener los distritos disponibles
    public function obtenerDistritos() {
        try {
            $sql = "SELECT iddistrito, nombre FROM distritos"; // Solo distritos activos
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $distritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $distritos;
        } catch (PDOException $e) {
            error_log("Error al obtener los distritos: " . $e->getMessage());
            throw new Exception("Error al obtener los distritos: " . $e->getMessage());
        }
    }
    public function obtenerEmpresaPorId($idempresa) {
        try {
            $query = "SELECT * FROM empresas WHERE idempresa = :idempresa";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":idempresa", $idempresa, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
    public function actualizarEmpresa($idempresa, $ruc, $razon_social, $telefono, $email, $direccion, $iddistrito, $usuario_id) {
        try {
            if (isset($_SESSION['idpersona'])) {
                $usuario_id = $_SESSION['idpersona'];
            } else {
                throw new Exception("El usuario no está autenticado.");
            }
    
            $sql = "CALL sp_actualizar_empresa(:idempresa, :ruc, :razon_social, :telefono, :email, :direccion, :iddistrito, :modificado_por)";
            $stmt = $this->conn->prepare($sql);
    
            $stmt->bindParam(":idempresa", $idempresa, PDO::PARAM_INT);
            $stmt->bindParam(":ruc", $ruc, PDO::PARAM_STR);
            $stmt->bindParam(":razon_social", $razon_social, PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $telefono, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $direccion, PDO::PARAM_STR);
            $stmt->bindParam(":iddistrito", $iddistrito, PDO::PARAM_INT);
            $stmt->bindParam(":modificado_por", $usuario_id, PDO::PARAM_INT);
    
            $resultado = $stmt->execute();
            $stmt = null;
    
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al actualizar empresa: " . $e->getMessage());
            throw new Exception("No se pudo actualizar la empresa: " . $e->getMessage());
        }
    }
    
}
?>