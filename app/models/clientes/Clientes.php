<?php
// Incluimos la clase de conexión para poder usarla
require_once __DIR__ . '../../../config/conexion.php';

// Definimos la clase ClienteModel que se encargará de interactuar con la base de datos
class ClienteModel {
    private $conn;

    // Constructor para establecer la conexión a la base de datos
    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar(); // Establece la conexión
    }

    // Método para obtener los clientes utilizando el procedimiento almacenado
    public function obtenerClientes($tipo = '') {
        try {
            // Ejecutamos el procedimiento almacenado 'obtenerClientesV4'
            $sql = "CALL obtenerClientesV4()"; // Llamamos al procedimiento almacenado

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();  // Ejecutamos la consulta

            // Obtenemos los resultados
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC); 

            // Filtramos los resultados según el tipo
            if ($tipo === 'persona') {
                return array_filter($clientes, function ($cliente) {
                    return !empty($cliente['persona_apellidos']); // Solo personas
                });
            } elseif ($tipo === 'empresa') {
                return array_filter($clientes, function ($cliente) {
                    return !empty($cliente['empresa_razon_social']); // Solo empresas
                });
            }

            return $clientes;  // Si no se pasa ningún tipo, retornamos todos los clientes
        } catch (PDOException $e) {
            // Si ocurre un error en la ejecución, lo capturamos
            throw new Exception("Error al obtener los clientes: " . $e->getMessage());
        }
    }
    public function buscarClientePorDNI($dni) {
        $conexion = Conexion::conectar();
        $query = "SELECT c.idcliente, c.idpersona, p.numerodoc, p.tipodoc, p.nombres, p.Primer_Apellido, p.Segundo_Apellido
                  FROM clientes c
                  INNER JOIN personas p ON c.idpersona = p.idpersona
                  WHERE p.numerodoc = :dni";
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':dni', $dni);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Devuelve un solo cliente
    }
    public function buscarClientePorRUC($ruc) {
        try {
            $conexion = Conexion::conectar();
            $sql = "SELECT e.razon_social
                    FROM empresas e
                    INNER JOIN clientes c ON c.idempresa = e.idempresa
                    WHERE e.ruc = :ruc
                    LIMIT 1";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':ruc', $ruc, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Manejo de errores (opcional)
            error_log("Error en buscarClientePorRUC: " . $e->getMessage());
            return false;
        }
    }
    


}
?>
