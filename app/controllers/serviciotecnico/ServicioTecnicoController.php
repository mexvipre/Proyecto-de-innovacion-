<?php
require_once __DIR__ . '/../../models/serviciotecnico/ServicioTecnico.php';

class ServicioTecnicoController {

    // Mostrar formulario
    public function crear() {
        require __DIR__ . '/../../views/serviciotecnico/crear.php';
    }

    // Procesar el formulario y guardar en la base de datos
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'idcliente' => $_POST['idcliente'],
                'createdBy' => $_POST['createdBy'],
                'stType' => $_POST['stType'],
                'dateCreated' => $_POST['dateCreated'],
                'dateModificate' => $_POST['dateModificate'],
                'dateCitaInit' => $_POST['dateCitaInit'],
                'dateCitaEnd' => $_POST['dateCitaEnd'],
                'observations' => $_POST['observations'],
                'modifiedBy' => $_POST['modifiedBy']
            ];

            $servicio = new ServicioTecnico();
            if ($servicio->crear($data)) {
                header('Location: /serviciotecnico/listar');
                exit();
            } else {
                echo "Error al registrar el servicio técnico.";
            }
        }
    }
}
