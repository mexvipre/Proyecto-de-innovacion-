<?php
require_once '../../models/roles/Rol.php';

class RolController {
    private $model;

    public function __construct() {
        $this->model = new Rol();
    }

    public function insertarRol($rol, $descripcion) {
        if (!empty($rol)) {
            $resultado = $this->model->insertarRol($rol, $descripcion);
            if ($resultado) {
                echo "Rol insertado correctamente";
            } else {
                echo "Error al insertar rol";
            }
        } else {
            echo "El campo 'rol' es obligatorio";
        }
    }

    public function listarRoles() {
        return $this->model->listarRoles();
    }
}
