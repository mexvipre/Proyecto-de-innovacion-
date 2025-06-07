<?php
require_once '../../models/empresas/empresasModel.php';

class EmpresaController {
    private $model;

    public function __construct() {
        $this->model = new EmpresasModel();
    }

    // Obtener la lista de empresas
    public function obtenerEmpresas() {
        try {
            $empresas = $this->model->obtenerEmpresas();
            if ($empresas) {
                return $empresas;
            } else {
                throw new Exception("No se encontraron empresas.");
            }
        } catch (Exception $e) {
            error_log("Error al obtener empresas: " . $e->getMessage());
            return [];
        }
    }

    // Agregar una nueva empresa
    public function agregarEmpresa($ruc, $razon_social, $telefono, $email, $direccion, $iddistrito,$usuario_id) {
        try {
            $resultado = $this->model->agregarEmpresa($ruc, $razon_social, $telefono, $email, $direccion, $iddistrito, $usuario_id);
            if ($resultado) {
                return ["success" => true, "message" => "Empresa agregada correctamente."];
            } else {
                throw new Exception("No se pudo agregar la empresa.");
            }
        } catch (Exception $e) {
            error_log("Error al agregar empresa: " . $e->getMessage());
            return ["success" => false, "message" => "Error al agregar la empresa."];
        }
    }

    // Obtener la lista de distritos
    public function obtenerDistritos() {
        try {
            return $this->model->obtenerDistritos();
        } catch (Exception $e) {
            error_log("Error al obtener distritos: " . $e->getMessage());
            return [];
        }
    }
    public function obtenerEmpresaPorId($idempresa) {
        // Llamamos al modelo para obtener los datos de la empresa
        $empresa = $this->model->obtenerEmpresaPorId($idempresa);
        
        if ($empresa) {
            return $empresa;
        } else {
            // Si no se encuentra la empresa, se puede manejar el error, como redirigir o mostrar un mensaje
            echo "No se encontró la empresa.";
            exit;
        }
    }
    public function actualizarEmpresa($idempresa, $ruc, $razon_social, $telefono, $email, $direccion, $iddistrito, $usuario_id) {
        try {
            $resultado = $this->model->actualizarEmpresa($idempresa, $ruc, $razon_social, $telefono, $email, $direccion, $iddistrito, $usuario_id);
            if ($resultado) {
                return ["success" => true, "message" => "Empresa actualizada correctamente."];
            } else {
                throw new Exception("No se pudo actualizar la empresa.");
            }
        } catch (Exception $e) {
            error_log("Error al actualizar empresa: " . $e->getMessage());
            return ["success" => false, "message" => "Error al actualizar la empresa."];
        }
    }
    

    
  
}
?>