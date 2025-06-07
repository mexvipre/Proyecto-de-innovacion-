<?php
require_once 'MarcaSoc.php';

$marca = new MarcaSoc();
$resultados = $marca->obtenerMarcas();

echo "Total registros encontrados: " . count($resultados) . "<br><br>";

foreach ($resultados as $fila) {
    echo "ID: " . $fila['idmarcasoc'] . "<br>";
}
?>
