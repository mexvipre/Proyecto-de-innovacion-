<?php
require_once '../../config/conexion.php';
require_once '../../models/general/general.php';
require_once '../../models/personas/Persona.php';
require_once '../../models/empresas/empresasModel.php';


class GeneralController {
    private $general;
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar(); // Se establece correctamente la conexión
        $this->general = new General(); // Instanciar la clase General
    }

    // Método para manejar la solicitud de listar las órdenes de servicio
    public function listarOrdenesServicio() {
        // Llamamos al modelo para obtener los datos
        $resultados = $this->general->listarOrdenesServicio();
        // Verificar si hay resultados
        if ($resultados && count($resultados) > 0) {
            return $resultados;
        } else {
            // Si no hay resultados, devolver un array vacío o mensaje
            return [];
        }
    }

    public function obtenerOrdenPorId($id) {
        $resultado = $this->general->obtenerOrdenPorId($id); // Este método ya lo creamos en el modelo
        if ($resultado) {
            return $resultado;
        } else {
            return null;
        }
    }

    
}
