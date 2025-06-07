<?php
require_once '../../../app/config/conexion.php';
$estadisticas = [];
$tareasTecnicos = [];
$ultimaActualizacion = date('d/m/Y H:i:s');

try {
    $conn = Conexion::conectar();

    // Obtener conteo estados general
    $stmt = $conn->prepare("CALL ObtenerConteoEstadosGeneral()");
    $stmt->execute();
    $estadisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor(); // importante para liberar resultados

    // Obtener resumen tareas técnicos
    $stmt2 = $conn->prepare("CALL ObtenerResumenTareasTecnicos()");
    $stmt2->execute();
    $tareasTecnicos = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    $stmt2->closeCursor();

} catch (PDOException $e) {
    die("Error al obtener datos: " . $e->getMessage());
}

// Calcular total equipos y porcentajes
$totalEquipos = array_sum(array_column($estadisticas, 'cantidad'));
foreach ($estadisticas as &$item) {
    $item['porcentaje'] = $totalEquipos > 0 ? round(($item['cantidad'] / $totalEquipos) * 100, 1) : 0;
}
unset($item);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - Estados y Técnicos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .estado-card {
            max-width: 950px;
            margin: auto;
        }
        .canvas-wrapper {
            position: relative;
            max-width: 450px;
            margin: auto;
            overflow: visible; /* Permite hoverOffset sobresalir */
        }
        canvas {
            max-width: 450px;
            margin: auto;
            display: block;
            position: relative;
            z-index: 1;
            opacity: 1;
            transition: opacity 0.4s ease;
        }
        .badge-lg {
            font-size: 1rem;
            padding: 0.6em 1em;
        }
        .actualizacion {
            font-size: 0.85rem;
            color: #666;
            font-style: italic;
            text-align: center;
            margin-top: 12px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container my-5">

    <!-- Título -->
    <h2 class="text-center mb-4">
        <i class="fa-solid fa-chart-pie me-2"></i>Estado General de Equipos
    </h2>

    <!-- Card Estado General -->
<div class="card shadow estado-card border-0 mt-5">
    <div class="card-body">
        <h5 class="mb-3"><i class="fa-solid fa-clipboard-list me-2"></i>Resumen General de Tareas</h5>
        <div class="row g-4">

                <!-- Gráfico Doughnut -->
                <div class="col-md-5 text-center">
                    <div class="canvas-wrapper">
                        <canvas id="graficoGeneral"></canvas>
                    </div>
                    <p class="actualizacion">Última actualización: <?= $ultimaActualizacion ?></p>
                </div>

                <!-- Resumen detallado -->
                <div class="col-md-7">
                    <h5 class="mb-3"><i class="fa-solid fa-list-check me-2"></i>Resumen Detallado</h5>
                    <div class="list-group shadow-sm">
                        <?php
                        $colores = ['#ffc107', '#0dcaf0', '#198754', '#6c757d'];
                        foreach ($estadisticas as $i => $estado): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge rounded-pill" style="background-color: <?= $colores[$i % count($colores)] ?>;">&nbsp;</span>
                                    <strong class="ms-2"><?= htmlspecialchars($estado['estado_equipo']) ?></strong>
                                </div>
                                <div>
                                    <span class="badge bg-secondary badge-lg me-2"><?= $estado['cantidad'] ?> equipos</span>
                                    <span class="badge bg-dark badge-lg"><?= $estado['porcentaje'] ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="list-group-item bg-light d-flex justify-content-between align-items-center fw-bold">
                            <span><i class="fa-solid fa-boxes-stacked me-2"></i>Total</span>
                            <span><?= $totalEquipos ?> equipos registrados</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Card Técnicos y tareas -->
<!-- Card Técnicos y tareas -->
<div class="card shadow estado-card border-0 mt-5">
    <div class="card-body">
        <h5 class="mb-3"><i class="fa-solid fa-tools me-2"></i>Resumen de Técnicos y Tareas Actuales</h5>
        <div class="row g-4">

            <!-- Gráfico de barras -->
            <div class="col-md-7">
                <canvas id="graficoTecnicos"></canvas>
            </div>

            <!-- Resumen lateral -->
            <div class="col-md-5">
                <div class="list-group shadow-sm">
                    <?php
                    // Paleta de colores y filtrado
                    $coloresBarras = ['#0dcaf0', '#ffc107', '#198754', '#dc3545', '#6610f2', '#fd7e14', '#20c997', '#6f42c1'];
                    $tareasTecnicos = array_filter($tareasTecnicos, fn($t) => intval($t['tareas_actuales']) > 0);
                    $totalTareas = array_sum(array_column($tareasTecnicos, 'tareas_actuales'));

                    foreach ($tareasTecnicos as $i => $tecnico):
                        $porcentaje = $totalTareas > 0 ? round(($tecnico['tareas_actuales'] / $totalTareas) * 100, 1) : 0;
                        $color = $coloresBarras[$i % count($coloresBarras)];
                    ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge rounded-pill" style="background-color: <?= $color ?>;">&nbsp;</span>
                                <strong class="ms-2"><?= htmlspecialchars($tecnico['nombre_tecnico']) ?></strong>
                            </div>
                            <div>
                                <span class="badge bg-secondary badge-lg me-2"><?= $tecnico['tareas_actuales'] ?> tareas</span>
                        
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="list-group-item bg-light d-flex justify-content-between align-items-center fw-bold">
                        <span><i class="fa-solid fa-layer-group me-2"></i>Total</span>
                        <span><?= $totalTareas ?> tareas actuales</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>



</div>

<script>
    // Plugin sombra para doughnut
    const shadowPlugin = {
        id: 'shadowPlugin',
        beforeDraw(chart) {
            const ctx = chart.ctx;
            const activeElements = chart.getActiveElements();
            if (activeElements.length) {
                const element = activeElements[0].element;
                ctx.save();
                ctx.shadowColor = 'rgba(0,0,0,0.3)';
                ctx.shadowBlur = 15;
                ctx.shadowOffsetX = 0;
                ctx.shadowOffsetY = 0;
                element.draw(ctx);
                ctx.restore();
            }
        }
    };

    // Datos gráfico general
    const ctx = document.getElementById('graficoGeneral').getContext('2d');
    const colores = ['#ffc107', '#0dcaf0', '#198754', '#6c757d'];

    const data = {
        labels: <?= json_encode(array_column($estadisticas, 'estado_equipo')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($estadisticas, 'cantidad')) ?>,
            backgroundColor: colores,
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 70
        }]
    };

    const config = {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 1200
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const label = ctx.label || '';
                            const value = ctx.raw;
                            const total = <?= $totalEquipos ?>;
                            const percent = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} equipos (${percent}%)`;
                        }
                    }
                }
            },
            elements: {
                arc: {
                    borderWidth: 2,
                    borderColor: '#fff'
                }
            },
            hover: {
                mode: 'nearest',
                intersect: true
            }
        },
        plugins: [shadowPlugin],
    };

    new Chart(ctx, config);

    // Gráfico barras técnicos
// Gráfico barras técnicos (horizontal)
const ctxTecnicos = document.getElementById('graficoTecnicos').getContext('2d');
const nombresTecnicos = <?= json_encode(array_column($tareasTecnicos, 'nombre_tecnico')) ?>;
const tareasTecnicosData = <?= json_encode(array_column($tareasTecnicos, 'tareas_actuales')) ?>;

const coloresBarras = <?= json_encode(array_slice($coloresBarras, 0, count($tareasTecnicos))) ?>;

new Chart(ctxTecnicos, {
    type: 'bar',
    data: {
        labels: nombresTecnicos,
        datasets: [{
            label: 'Tareas actuales',
            data: tareasTecnicosData,
            backgroundColor: coloresBarras
        }]
    },
    options: {
        indexAxis: 'y', // 👈 Esto cambia la orientación a horizontal
        responsive: true,
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            },
            y: {
                ticks: {
                    autoSkip: false
                }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => `${ctx.parsed.x} tareas` // x porque es horizontal
                }
            }
        }
    }
});


</script>
</body>
</html>
