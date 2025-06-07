<?php
require_once '../../config/conexion.php'; // Conexión PDO
require_once '../../models/clientes/Clientes.php';

$clienteModel = new ClienteModel();

if (isset($_GET['dni'])) {
    $documento = $_GET['dni'];

    if (strlen($documento) == 8) {
        // Buscar por DNI (persona natural)
        $cliente = $clienteModel->buscarClientePorDNI($documento);

        if ($cliente) {
            echo json_encode([
                'success' => true,
                'nombre' => $cliente['nombres'] . ' ' . $cliente['Primer_Apellido'] . ' ' . $cliente['Segundo_Apellido'],
                'tipo' => 'persona'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Persona no encontrada.'
            ]);
        }
    } elseif (strlen($documento) == 11) {
        // Buscar por RUC (empresa)
        $empresa = $clienteModel->buscarClientePorRUC($documento);

        if ($empresa) {
            echo json_encode([
                'success' => true,
                'nombre' => $empresa['razon_social'],
                'tipo' => 'empresa'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Empresa no encontrada.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Documento inválido.'
        ]);
    }
}
