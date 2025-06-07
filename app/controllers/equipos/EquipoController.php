<?php
require_once '../../models/equipos/EquipoModel.php';

class EquipoController {
    private $equipoModel;

    public function __construct() {
        // Instanciamos el modelo
        $this->equipoModel = new EquipoModel();
    }

    // Agregar equipox
    
    public function agregarEquipo() {
        // Recibimos los datos del formulario
        if (isset($_POST['NomEquipo'], $_POST['idTipoEquipo'])) {
            $nomEquipo = $_POST['NomEquipo'];
            $idTipoEquipo = $_POST['idTipoEquipo'];
            
            // Si el tipo de equipo es computadora o laptop, recibimos también el ID de la computadora
            $idComputer = null;
            if ($idTipoEquipo == 'computadora' || $idTipoEquipo == 'laptop') {
                if (isset($_POST['idComputer'])) {
                    $idComputer = $_POST['idComputer'];
                }
            }

            // Llamamos al modelo para agregar el equipo
            $resultado = $this->equipoModel->agregarEquipo($nomEquipo, $idTipoEquipo, $idComputer);
            
            if ($resultado) {
                header("Location: index.php?controller=equipo&action=listarEquipos");
            } else {
                echo "Error al agregar el equipo.";
            }
        }
    }

    // Listar equipos
    public function listarEquipos() {
        $equipos = $this->equipoModel->obtenerEquipos();
        require_once 'views/equipos/listarEquipos.php';
    }

    // Agregar marca
    public function agregarMarca() {
        if (isset($_POST['idEquipo'], $_POST['NomMarca'], $_POST['idTipoEquipo'])) {
            $idEquipo = $_POST['idEquipo'];
            $nomMarca = $_POST['NomMarca'];
            $idTipoEquipo = $_POST['idTipoEquipo'];

            // Llamamos al modelo para agregar la marca
            $resultado = $this->equipoModel->agregarMarca($idEquipo, $nomMarca, $idTipoEquipo);
            
            if ($resultado) {
                header("Location: index.php?controller=equipo&action=listarMarcas");
            } else {
                echo "Error al agregar la marca.";
            }
        }
    }
}
?>
