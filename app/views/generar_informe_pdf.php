<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../../vendor/setasign/fpdf/fpdf.php';

// Inicia buffer para manejar la salida del PDF
ob_start();

// Verificar si se proporcionó el ID del equipo en la URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Error: No se especificó un equipo válido.');
}

$iddetequipo = intval($_GET['id']); // Convierte el ID del equipo a entero

// Establecer la conexión a la base de datos usando la clase Conexion
$conexion = new Conexion();
$conn = $conexion->conectar(); // Establece la conexión

// Verificar si la conexión se estableció correctamente
if (!$conn) {
    die("Error en la conexión a la base de datos");
}

// Ejecuta el procedimiento almacenado para obtener los datos del equipo
$stmt = $conn->prepare("CALL VerEquipoConFKPorIdEquipo(?)");
$stmt->bindParam(1, $iddetequipo, PDO::PARAM_INT);
$stmt->execute();

// Obtener los resultados
$equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$equipos || count($equipos) === 0) {
    die("Error: Equipo no encontrado.");
}

// Solo usamos el primer registro para generar el informe
$equipo = $equipos[0];
// Verificar que todos los campos requeridos estén completos
$campos_obligatorios = [
    $equipo['cliente_nombre'],
    $equipo['cliente_primer_apellido'],
    $equipo['cliente_segundo_apellido'],
    $equipo['cliente_documento'],
    $equipo['cliente_telefono'],
    $equipo['orden_servicio_fecha'],
    $equipo['categoria_nombre'],
    $equipo['subcategoria_nombre'],
    $equipo['marca_nombre'],
    $equipo['modelo'],
    $equipo['numserie'],
    $equipo['descripcionentrada'],
    $equipo['caracteristicas'],
    $equipo['nombres_servicios'],
    $equipo['precios_sugeridos'],
    $equipo['observaciones_diagnostico']?:'no disponible',
    $equipo['imagenes_tecnicas']
];

foreach ($campos_obligatorios as $campo) {
    if (empty($campo)) {
        die("Error: No se puede generar el informe. Faltan completar todos los campos requeridos.");
    }
}


// Clase personalizada para el PDF
class InformeEquipoPDF extends FPDF {
    function Header() {
        $this->Image(__DIR__ . '/../../logo1.png', 155, -2, 50);
        $this->Image(__DIR__ . '/../../logo2.jpg', 10.5, 6, 60);
        $this->Ln(20);
        $this->SetX(10);
        $this->SetFont('Arial', 'B', 10);

        // IZQUIERDA
        $this->SetXY(10, 30);
        $this->Cell(75, 5, 'RUC: 20611593881', 0, 1, 'L');
        $this->SetX(10);
        $this->Cell(80, 5, utf8_decode('Dirección: Av. Mariscal Benavides N° 785'), 0, 1, 'L');
        $this->SetX(10);
        $this->Cell(80, 5, utf8_decode('Pueblo Nuevo - Chincha Alta - Ica'), 0, 1, 'L');

        // DERECHA
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

// Creación del PDF
$pdf = new InformeEquipoPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(100, 7, 'INFORME TÉCNICO DEL EQUIPO', 0, 0, 'L');
$pdf->Cell(90, 7, utf8_decode('N° EQUIPO: ') . str_pad($equipo['iddetequipo'], 6, '0', STR_PAD_LEFT), 0, 1, 'R');
$pdf->Ln(3);

// DATOS DEL CLIENTE
$pdf->SetFillColor(2, 80, 95);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(190, 7, 'DATOS DEL CLIENTE', 1, 1, 'C', true);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 9);

$cliente = [
    'Cliente' => $equipo['cliente_nombre'] . ' ' . $equipo['cliente_primer_apellido'] . ' ' . $equipo['cliente_segundo_apellido'],
    'DNI/RUC' => $equipo['cliente_documento'],
    'Teléfono' => $equipo['cliente_telefono'],
    'Fecha Recepción' => date('d/m/Y H:i:s', strtotime($equipo['orden_servicio_fecha'])),
    'Fecha Entrega' => $equipo['fechaentrega'] ? date('d/m/Y H:i:s', strtotime($equipo['fechaentrega'])) : 'No disponible'
];

foreach ($cliente as $key => $value) {
    $pdf->Cell(50, 6, utf8_decode($key) . ':', 1);
    $pdf->Cell(140, 6, utf8_decode($value), 1, 1);
}
$pdf->Ln(4);

// DATOS DEL EQUIPO
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(2, 80, 95);
$pdf->SetTextColor(255);
$pdf->Cell(190, 7, 'DATOS DEL EQUIPO', 1, 1, 'C', true);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 9);

$datos_equipo = [
    'Categoría' => $equipo['categoria_nombre'],
    'Subcategoría' => $equipo['subcategoria_nombre'],
    'Marca' => $equipo['marca_nombre'],
    'Modelo' => $equipo['modelo'],
    'Número de Serie' => $equipo['numserie'],
    'Descripción Entrada' => $equipo['descripcionentrada'],
    'Características' => $equipo['caracteristicas'] ?: 'No disponible'
];

foreach ($datos_equipo as $key => $value) {
    $pdf->Cell(50, 6, utf8_decode($key) . ':', 1);
    $pdf->Cell(140, 6, utf8_decode($value), 1, 1);
}
$pdf->Ln(4);

// SERVICIOS Y DIAGNÓSTICO
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(2, 80, 95);
$pdf->SetTextColor(255);
$pdf->Cell(190, 7, 'SERVICIOS Y DIAGNÓSTICO', 1, 1, 'C', true);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 9);

$servicios = [
    'Nombres de Servicios' => $equipo['nombres_servicios'] ?: 'No disponible',
    'Precios Sugeridos' => $equipo['precios_sugeridos'] ?: 'No disponible',
    'Observaciones Diagnóstico' => $equipo['observaciones_diagnostico'] ?: 'No disponible'
];

foreach ($servicios as $key => $value) {
    $pdf->Cell(50, 6, utf8_decode($key) . ':', 1);
    $pdf->MultiCell(140, 6, utf8_decode($value), 1);
}
$pdf->Ln(4);

// EVIDENCIA TÉCNICA (IMÁGENES)
if (!empty($equipo['imagenes_tecnicas'])) {
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(2, 80, 95);
    $pdf->SetTextColor(255);
    $pdf->Cell(190, 7, 'EVIDENCIA TÉCNICA', 1, 1, 'C', true);
    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Ln(2);

    // Dividir la cadena de URLs en un arreglo
    $rutas_imagenes = explode(' | ', $equipo['imagenes_tecnicas']);
    $index = 0;

    foreach ($rutas_imagenes as $evidenciaPath) {
        $index++;
        $tempImagePath = '';
        $tempImageExt = '';

        if (filter_var($evidenciaPath, FILTER_VALIDATE_URL)) {
            // Descargar imagen remota
            $imageData = @file_get_contents($evidenciaPath);
            if ($imageData !== false) {
                // Obtener info del tipo MIME para saber extensión
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $imageData);
                finfo_close($finfo);

                switch ($mimeType) {
                    case 'image/jpeg':
                        $tempImageExt = '.jpg';
                        break;
                    case 'image/png':
                        $tempImageExt = '.png';
                        break;
                    default:
                        $tempImageExt = ''; // Formato no soportado
                }

                if ($tempImageExt != '') {
                    $tempImagePath = tempnam(sys_get_temp_dir(), 'evidencia_') . $tempImageExt;
                    file_put_contents($tempImagePath, $imageData);
                }
            }
        }

        // Define dimensiones para la imagen
        $maxImageWidth = 90; // Ancho máximo de la imagen en mm
        $maxImageHeight = 90; // Alto máximo de la imagen en mm

        // Ajusta el tamaño de la imagen manteniendo la proporción
        if (!empty($tempImagePath) && file_exists($tempImagePath)) {
            list($imgWidth, $imgHeight, $imgType) = getimagesize($tempImagePath);

            // Verificar que el tipo sea soportado por FPDF (JPG=2, PNG=3)
            if ($imgType == IMAGETYPE_JPEG || $imgType == IMAGETYPE_PNG) {
                $scaleWidth = $maxImageWidth / $imgWidth;
                $scaleHeight = $maxImageHeight / $imgHeight;
                $scale = min($scaleWidth, $scaleHeight);
                $imgDisplayWidth = $imgWidth * $scale;
                $imgDisplayHeight = $imgHeight * $scale;

                // Mostrar título de la imagen
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(190, 6, utf8_decode('Evidencia ' . $index), 0, 1, 'L');
                $pdf->Ln(2);

                // Mostrar la imagen
                $pdf->Image($tempImagePath, 10, $pdf->GetY(), $imgDisplayWidth, $imgDisplayHeight);

                // Eliminar archivo temporal
                unlink($tempImagePath);

                $pdf->Ln($imgDisplayHeight + 5);
            } else {
                // No soportado
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(190, 6, utf8_decode('Formato de imagen no soportado para Evidencia ' . $index . '.'), 0, 1, 'L');
                $pdf->Ln(5);
                if (!empty($tempImagePath) && file_exists($tempImagePath)) {
                    unlink($tempImagePath);
                }
            }
        } else {
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(190, 6, utf8_decode('No se pudo cargar Evidencia ' . $index . '.'), 0, 1, 'L');
            $pdf->Ln(5);
        }
    }
} else {
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(2, 80, 95);
    $pdf->SetTextColor(255);
    $pdf->Cell(190, 7, 'EVIDENCIA TÉCNICA', 1, 1, 'C', true);
    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(190, 6, utf8_decode('No hay imágenes técnicas disponibles.'), 1, 1);
    $pdf->Ln(10);
}

// CONDICIONES GENERALES Y FIRMA
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(2, 80, 95);
$pdf->SetTextColor(255);
$pdf->Cell(190, 7, 'CONDICIONES GENERALES', 1, 1, 'C', true);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 9);

$condiciones_generales = 'Las condiciones generales no están especificadas para este equipo.';
$pdf->MultiCell(190, 6, utf8_decode($condiciones_generales), 1);
$pdf->Ln(10);

$pdf->Cell(190, 6, utf8_decode('Firma del Técnico:'), 0, 1, 'L');
$pdf->Cell(190, 15, '', 1, 1, 'L'); // Espacio para firma

// Cerrar conexión
$conn = null;

// Limpiar el buffer de salida antes de generar el PDF
ob_end_clean();

// Enviar PDF al navegador
$pdf->Output('I', 'informe_tecnico_equipo.pdf');
?>