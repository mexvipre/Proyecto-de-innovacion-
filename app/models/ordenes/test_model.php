<?php
require_once '../../models/ordenes/OrdenServicioModel.php';

$modelo = new OrdenServicioModel();
$ordenes = $modelo->obtenerOrden();

echo "<pre>";
print_r($ordenes);
echo "</pre>";
?>
