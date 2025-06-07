<?php
require_once __DIR__ . '../../../models/ordenTecnico/ordenTecnicoModel.php';

class OrdenTecnicoController {
    private $model;

    public function __construct() {
        $this->model = new OrdenTecnicoModel();
    }

    public function listarTecnicos() {
        return $this->model->obtenerTecnicos();
    }
   

    public function asignarTecnico() {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['asignar'])) {
            $idOrdenServicio = $_POST['idorden_servicio'];
            $idUsuario = $_POST['idusuario'];

            if ($this->model->asignarTecnico($idOrdenServicio, $idUsuario)) {
                echo "Técnico asignado correctamente.";
            } else {
                echo "Error al asignar técnico.";
            }
        }
    }
}

// Si se envió el formulario, procesarlo
$controller = new OrdenTecnicoController();
$controller->asignarTecnico();
?>
