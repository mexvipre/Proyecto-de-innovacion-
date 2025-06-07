<?php
require_once '../../models/orden/Ordenservicio.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_orden'])) {
    
    $idusuario_crea = 1;

    // Datos necesarios
    $fecha_recepcion = date('Y-m-d H:i:s');
    $idcliente = $_POST['idcliente']; 

    $orden = new Ordenservicio();
    $resultado = $orden->registrarOrden($fecha_recepcion, $idusuario_crea, $idcliente);

    if ($resultado) {
        header("Location: generarOrden.php?success=1");
        exit();
    } else {
        header("Location: generarOrden.php?error=1");
        exit();
    }
}
