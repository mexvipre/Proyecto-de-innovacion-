<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../../vendor/setasign/fpdf/fpdf.php';

ob_start();

if (!isset($_GET['idorden'])) {
    die('Error: No se especificó una orden.');
}

$idorden = intval($_GET['idorden']);
$conexion = new Conexion();
$conn = $conexion->conectar();

$stmt = $conn->prepare("CALL ObtenerOrdenConEquipos(?)");
$stmt->bindParam(1, $idorden, PDO::PARAM_INT);
$stmt->execute();
$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$ordenes || count($ordenes) === 0) {
    die('Error: Orden no encontrada o sin equipos.');
}

$orden = $ordenes[0];

class OrdenServicioPDF extends FPDF {
    function Header() {
        $this->Image('../../logo1.png', 155, -2, 50);
        $this->Image('../../logo2.jpg', 10.5, 6, 60);
        $this->Ln(20);
        $this->SetFont('Arial', 'B', 10);
        $this->SetXY(10, 30);
        $this->Cell(75, 5, 'RUC: 20611593881', 0, 1, 'L');
        $this->SetX(10);
        $this->Cell(80, 5, utf8_decode('Dirección: Av. Mariscal Benavides N° 785'), 0, 1, 'L');
        $this->SetX(10);
        $this->Cell(80, 5, utf8_decode('Pueblo Nuevo - Chincha Alta - Ica'), 0, 1, 'L');
        $this->SetXY(90, 30);
        $this->Cell(80, 5, utf8_decode('Teléfono: 961 514 338'), 0, 1, 'L');
        $this->SetX(90);
        $this->Cell(80, 5, utf8_decode('Horarios: Lunes - Sábado (8am a 5pm)'), 0, 1, 'L');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }

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
                $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue;
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

$pdf = new OrdenServicioPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(100, 7, 'ORDEN DE SERVICIO', 0, 0, 'L');
$pdf->Cell(90, 7, utf8_decode('N° ORDEN: ') . str_pad($orden['idorden_Servicio'], 6, '0', STR_PAD_LEFT), 0, 1, 'R');
$pdf->Ln(3);

// Datos del cliente
$pdf->SetFillColor(2, 80, 95);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(190, 7, 'DATOS DEL CLIENTE', 1, 1, 'C', true);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 9);

$cliente = [
    'Cliente' => $orden['nombre_cliente'],
    'DNI/RUC' => $orden['documento_cliente'],
    'Teléfono' => $orden['telefono_cliente'],
    'Fecha Recepción' => date('d/m/Y H:i:s', strtotime($orden['fecha_recepcion'])),
    'Fecha Entrega' => date('d/m/Y H:i:s', strtotime($orden['fechaentrega']))
];

foreach ($cliente as $key => $value) {
    $pdf->Cell(50, 6, utf8_decode($key) . ':', 1);
    $pdf->Cell(140, 6, utf8_decode($value), 1, 1);
}
$pdf->Ln(4);

// Equipos
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(2, 80, 95);
$pdf->SetTextColor(255);
$widths = [10, 18, 20, 20, 40, 20, 62];
$headers = ['#', 'Tipo', 'Marca', 'Modelo', 'Descripción', 'Serie', 'Características'];
for ($i = 0; $i < count($headers); $i++) {
    $pdf->Cell($widths[$i], 7, utf8_decode($headers[$i]), 1, 0, 'C', true);
}
$pdf->Ln();
$pdf->SetTextColor(0);

foreach ($ordenes as $index => $item) {
    $pdf->SetFont('Arial', '', 9);
    $data = [
        ($index + 1),
        $item['NombreCategoria'] ?? 'Impresora',
        $item['Nombre_Marca'],
        $item['modelo'],
        $item['descripcionentrada'],
        $item['numserie'],
        $item['caracteristicas']
    ];

    $lines = [];
    for ($i = 0; $i < count($data); $i++) {
        $lines[$i] = $pdf->NbLines($widths[$i], utf8_decode($data[$i]));
    }
    $maxLines = max($lines);
    $rowHeight = $maxLines * 6;

    $xStart = $pdf->GetX();
    $yStart = $pdf->GetY();
    $x = $xStart;
    for ($i = 0; $i < count($data); $i++) {
        $pdf->SetXY($x, $yStart);
        $pdf->Cell($widths[$i], $rowHeight, '', 1, 0);
        $pdf->SetXY($x, $yStart);
        $pdf->MultiCell($widths[$i], 6, utf8_decode($data[$i]), 0, 'L');
        $x += $widths[$i];
    }
    $pdf->SetXY($xStart, $yStart + $rowHeight);
}
$pdf->Ln(4);

// Evidencias
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(2, 80, 95);
$pdf->SetTextColor(255);
$pdf->Cell(190, 7, 'EVIDENCIAS DE LOS EQUIPOS', 1, 1, 'C', true);
$pdf->SetTextColor(0);
$pdf->Ln(4);

foreach ($ordenes as $index => $item) {
    $evidenciaPath = $item['ruta_Evidencia_Entrada'];
    $tempImagePath = '';

    // Procesar URL o archivo local
    if (filter_var($evidenciaPath, FILTER_VALIDATE_URL)) {
        $imageContent = @file_get_contents($evidenciaPath);
        if ($imageContent !== false) {
            $tempImagePath = tempnam(sys_get_temp_dir(), 'evidencia_') . '.jpg';
            file_put_contents($tempImagePath, $imageContent);
            $imgInfo = @getimagesize($tempImagePath);
            if ($imgInfo === false || $imgInfo[2] !== IMAGETYPE_JPEG) {
                unlink($tempImagePath);
                $tempImagePath = '';
            }
        }
    } elseif (file_exists($evidenciaPath)) {
        $imgInfo = @getimagesize($evidenciaPath);
        if ($imgInfo !== false && $imgInfo[2] === IMAGETYPE_JPEG) {
            $tempImagePath = $evidenciaPath;
        }
    }

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(190, 6, utf8_decode('Equipo ' . ($index + 1) . ': ' . $item['Nombre_Marca'] . ' ' . $item['modelo']), 0, 1, 'L');
    $pdf->Ln(2);

    if (!empty($tempImagePath)) {
        list($imgWidth, $imgHeight) = getimagesize($tempImagePath);
        $scale = min(100 / $imgWidth, 100 / $imgHeight);
        $pdf->Image($tempImagePath, 10, $pdf->GetY(), $imgWidth * $scale, $imgHeight * $scale);
        if (strpos($tempImagePath, sys_get_temp_dir()) !== false) {
            unlink($tempImagePath);
        }
        $pdf->Ln(($imgHeight * $scale) + 5);
    } else {
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(190, 6, utf8_decode('No se encontró evidencia válida para este equipo.'), 0, 1, 'L');
        $pdf->Ln(5);
    }
}

// Nota final
$pdf->Ln(4);
$pdf->SetFillColor(220, 220, 220);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(190, 7, 'IMPORTANTE', 1, 1, 'C', true);
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(190, 6, utf8_decode('Las cláusulas del contrato de adhesión se encuentran en el anverso. Revise cuidadosamente antes de aceptar los términos del servicio.'), 1);
$pdf->Ln(6);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 8, utf8_decode('¡Gracias por su confianza!'), 0, 1, 'C');

$pdf->Output('I', 'OrdenServicio_' . $idorden . '.pdf');
ob_end_flush();
