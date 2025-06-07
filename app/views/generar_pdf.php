<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../../vendor/setasign/fpdf/fpdf.php';

ob_start(); // Inicia buffer para manejar la salida del PDF

// Verifica si se proporcionó el ID de la orden en la URL
if (!isset($_GET['idorden'])) {
    die('Error: No se especificó una orden.');
}

$idorden = intval($_GET['idorden']); // Convierte el ID de la orden a entero
$conexion = new Conexion(); // Crea una nueva conexión a la base de datos
$conn = $conexion->conectar(); // Establece la conexión

// Ejecuta un procedimiento almacenado para obtener la orden y sus equipos
$stmt = $conn->prepare("CALL ObtenerOrdenConEquipos(?)");
$stmt->bindParam(1, $idorden, PDO::PARAM_INT);
$stmt->execute();
$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene todos los resultados

// Verifica si se encontraron resultados
if (!$ordenes || count($ordenes) === 0) {
    die('Error: Orden no encontrada o sin equipos.');
}

// Usa el primer registro para los datos generales de la orden y cliente
$orden = $ordenes[0];

class OrdenServicioPDF extends FPDF {
    // Define el encabezado del PDF
    function Header() {
        $this->Image('../../logo1.png', 155, -2, 50); // Inserta el primer logo
        $this->Image('../../logo2.jpg', 10.5, 6, 60); // Inserta el segundo logo
        $this->Ln(20); // Salto de línea
        $this->SetX(10); // Establece posición X
        $this->SetFont('Arial', 'B', 10); // Fuente Arial, negrita, tamaño 10
           // --- IZQUIERDA ---
        $this->SetXY(10, 30); // Posición izquierda
        $this->Cell(75, 5, 'RUC: 20611593881', 0, 1, 'L');
        $this->SetX(10);
        $this->Cell(80, 5, utf8_decode('Dirección: Av. Mariscal Benavides N° 785'), 0, 1, 'L');
        $this->SetX(10);
        $this->Cell(80, 5, utf8_decode('Pueblo Nuevo - Chincha Alta - Ica'), 0, 1, 'L');

        // --- DERECHA ---
        $this->SetXY(90, 30); // Posición derecha
        $this->Cell(80, 5, utf8_decode('Teléfono: 961 514 338'), 0, 1, 'L');
        $this->SetX(90);
        $this->Cell(80, 5, utf8_decode('Horarios: Lunes - Sábado (8am a 5pm)'), 0, 1, 'L');

        $this->Ln(5); // Espacio debajo del encabezado // Salto de línea
    }

    // Define el pie de página del PDF
    function Footer() {
        $this->SetY(-15); // Posiciona 15mm desde el fondo
        $this->SetFont('Arial', 'I', 8); // Fuente Arial, cursiva, tamaño 8
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C'); // Imprime número de página
    }

    // Función auxiliar para calcular el número de líneas que ocupará un texto en una celda
    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) $i++;
                } else $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else $i++;
        }
        return $nl;
    }
}

// Crea una nueva instancia del PDF
$pdf = new OrdenServicioPDF();
$pdf->AddPage(); // Agrega una página
$pdf->SetFont('Arial', '', 10); // Establece fuente Arial, normal, tamaño 10

// Título y número de orden
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(100, 7, 'ORDEN DE SERVICIO', 0, 0, 'L'); // Imprime título
$pdf->Cell(90, 7, utf8_decode('N° ORDEN: ') . str_pad($orden['idorden_Servicio'], 6, '0', STR_PAD_LEFT), 0, 1, 'R'); // Imprime número de orden
$pdf->Ln(3); // Salto de línea

// Sección de datos del cliente
$pdf->SetFillColor(2, 80, 95); // Color de fondo azul
$pdf->SetTextColor(255); // Color de texto blanco
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(190, 7, 'DATOS DEL CLIENTE', 1, 1, 'C', true); // Imprime título de sección
$pdf->SetTextColor(0); // Restablece color de texto a negro
$pdf->SetFont('Arial', '', 9);

// Datos del cliente en formato tabla
$cliente = [
    'Cliente' => $orden['nombre_cliente'],
    'DNI/RUC' => $orden['documento_cliente'],
    'Teléfono' => $orden['telefono_cliente'],
    'Fecha Recepción' => date('d/m/Y H:i:s', strtotime($orden['fecha_recepcion'])),
    'Fecha Entrega' => date('d/m/Y H:i:s', strtotime($orden['fechaentrega']))
];

foreach ($cliente as $key => $value) {
    $pdf->Cell(50, 6, utf8_decode($key) . ':', 1); // Imprime clave
    $pdf->Cell(140, 6, utf8_decode($value), 1, 1); // Imprime valor
}
$pdf->Ln(4); // Salto de línea

// Sección de equipos recibidos
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(2, 80, 95);
$pdf->SetTextColor(255);

// Encabezado de la tabla (sin la columna Evidencia)
$widths = [10, 18, 20, 20, 40, 20, 62]; // Ajusta los anchos para ocupar el espacio de la columna eliminada
$headers = ['#', 'Tipo', 'Marca', 'Modelo', 'Descripción', 'Serie', 'Características'];
for ($i = 0; $i < count($headers); $i++) {
    $pdf->Cell($widths[$i], 7, utf8_decode($headers[$i]), 1, 0, 'C', true); // Imprime cada encabezado
}
$pdf->Ln(); // Salto de línea
$pdf->SetTextColor(0);

foreach ($ordenes as $index => $item) {
    $pdf->SetFont('Arial', '', 9);

    // Prepara los datos para cada celda de la fila (sin Evidencia)
    $data = [
        ($index + 1),
        isset($item['NombreCategoria']) ? $item['NombreCategoria'] : 'Impresora',
        $item['Nombre_Marca'],
        $item['modelo'],
        $item['descripcionentrada'],
        $item['numserie'],
        $item['caracteristicas']
    ];

    // Calcula el número de líneas para cada celda
    $lines = [];
    for ($i = 0; $i < count($data); $i++) {
        $lines[$i] = $pdf->NbLines($widths[$i], utf8_decode($data[$i]));
    }
    $maxLines = max($lines); // Máximo número de líneas para celdas de texto
    $rowHeight = $maxLines * 6; // Altura base de la fila según el texto

    // Guarda la posición inicial de la fila
    $xStart = $pdf->GetX();
    $yStart = $pdf->GetY();

    // Dibuja cada celda con texto usando MultiCell
    $x = $xStart;
    for ($i = 0; $i < count($data); $i++) {
        $pdf->SetXY($x, $yStart);
        $pdf->Cell($widths[$i], $rowHeight, '', 1, 0); // Dibuja celda vacía con borde
        $pdf->SetXY($x, $yStart);
        $pdf->MultiCell($widths[$i], 6, utf8_decode($data[$i]), 0, 'L'); // Añade texto dentro de la celda
        $x += $widths[$i]; // Avanza a la siguiente columna
    }

    // Avanza a la siguiente fila
    $pdf->SetXY($xStart, $yStart + $rowHeight);
}

$pdf->Ln(4); // Salto de línea

// Nueva sección: Evidencias de los Equipos
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(2, 80, 95);
$pdf->SetTextColor(255);
$pdf->Cell(190, 7, 'EVIDENCIAS DE LOS EQUIPOS', 1, 1, 'C', true); // Título de la sección
$pdf->SetTextColor(0);
$pdf->Ln(4);

foreach ($ordenes as $index => $item) {
    // Obtiene la ruta de la imagen
    $evidenciaPath = $item['ruta_Evidencia_Entrada'];
    $tempImagePath = '';

    // Verifica si la ruta es una URL o un archivo local y prepara la imagen
    if (filter_var($evidenciaPath, FILTER_VALIDATE_URL)) {
        // Descarga la imagen desde la URL a un archivo temporal
        $tempImagePath = tempnam(sys_get_temp_dir(), 'evidencia_') . '.jpg';
        file_put_contents($tempImagePath, file_get_contents($evidenciaPath));
    } elseif (file_exists($evidenciaPath)) {
        $tempImagePath = $evidenciaPath; // Usa la ruta directamente si es un archivo local
    }

    // Imprime el título del equipo
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(190, 6, utf8_decode('Equipo ' . ($index + 1) . ': ' . $item['Nombre_Marca'] . ' ' . $item['modelo']), 0, 1, 'L');
    $pdf->Ln(2);

    // Define dimensiones para la imagen
    $maxImageWidth = 100; // Ancho máximo de la imagen en mm (más grande)
    $maxImageHeight = 100; // Alto máximo de la imagen en mm

    // Ajusta el tamaño de la imagen manteniendo la proporción
    if (!empty($tempImagePath) && file_exists($tempImagePath)) {
        list($imgWidth, $imgHeight) = getimagesize($tempImagePath); // Obtiene dimensiones originales
        $scaleWidth = $maxImageWidth / $imgWidth; // Escala según el ancho
        $scaleHeight = $maxImageHeight / $imgHeight; // Escala según el alto
        $scale = min($scaleWidth, $scaleHeight); // Usa la escala más pequeña para que quepa
        $imgDisplayWidth = $imgWidth * $scale; // Ancho final de la imagen
        $imgDisplayHeight = $imgHeight * $scale; // Alto final de la imagen

        // Inserta la imagen
        $pdf->Image($tempImagePath, 10, $pdf->GetY(), $imgDisplayWidth, $imgDisplayHeight);
        unlink($tempImagePath); // Elimina el archivo temporal
        $pdf->Ln($imgDisplayHeight + 5); // Salto de línea después de la imagen
    } else {
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(190, 6, utf8_decode('No se encontró evidencia para este equipo.'), 0, 1, 'L');
        $pdf->Ln(5);
    }
}

$pdf->Ln(4); // Salto de línea

// Nota importante
$pdf->SetFillColor(220, 220, 220); // Color de fondo gris claro
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(190, 7, 'IMPORTANTE', 1, 1, 'C', true); // Imprime título de sección
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(190, 6, utf8_decode('Las cláusulas del contrato de adhesión se encuentran en el anverso. Revise cuidadosamente antes de aceptar los términos del servicio.'), 1); // Imprime mensaje
$pdf->Ln(6); // Salto de línea

// Mensaje final
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 8, utf8_decode('¡Gracias por su confianza!'), 0, 1, 'C'); // Imprime mensaje de agradecimiento

// Genera y muestra el PDF en el navegador (cambio clave aquí)
$pdf->Output('I', 'OrdenServicio_' . $idorden . '.pdf');
ob_end_flush(); // Finaliza buffer
?>
message.txt
11 KB