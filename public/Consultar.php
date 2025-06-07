<?php
require_once '../app/config/conexion.php';
$equipos = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idOrden = trim($_POST['idorden_servicio'] ?? '');
    $documento = trim($_POST['documento_cliente'] ?? '');

    if (!empty($idOrden) && is_numeric($idOrden) && !empty($documento)) {
        try {
            $conn = Conexion::conectar();
            $stmt = $conn->prepare("CALL ObtenerOrdenConEquipos(?)");
            $stmt->execute([$idOrden]);
            $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($equipos)) {
                $error = "No se encontraron resultados para la orden ingresada.";
            } else {
                if ($equipos[0]['documento_cliente'] !== $documento) {
                    $equipos = [];
                    $error = "Los datos no coinciden. Verifique el número de documento y la orden.";
                }
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "Debe ingresar un número de orden válido y el número de documento.";
    }
}
?>

<!DOCTYPE html>
<html lang="es" class="light-theme">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Consulta de Estado - Equipo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <style>
/* FUENTES Y FONDO CON PARPADEO */
body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #74ebd5, #ACB6E5);
  background-size: 400% 400%;
  animation: gradientShift 10s ease infinite;
  min-height: 100vh;
  margin: 0;
  padding: 2rem 1rem;
  color: #333;
}

/* CONTENEDOR PRINCIPAL CON BRILLO */
.parallax-container {
  background: #fff;
  max-width: 1300px;
  margin: 0 auto 3rem;
  padding: 2.5rem 3rem;
  border-radius: 1rem;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
  animation: fadeInUp 1.5s ease forwards, float 6s ease-in-out infinite;
}

/* TÍTULO PULSANTE CON SOMBRA */
h1 {
  font-weight: 800;
  text-align: center;
  color: #222;
  margin-bottom: 2.5rem;
  text-shadow: 2px 2px 8px rgba(0,0,0,0.2);
  animation: pulseGlow 3s infinite, bounceIn 2s ease;
}

/* LABELS ANIMADOS */
.form-label {
  font-weight: 600;
  color: #555;
  animation: slideLeft 1s ease-in;
}

/* INPUTS GLOW Y VIBRA CUANDO FOCUS */
.form-control-lg {
  border-radius: 0.6rem;
  border: 2px solid #74b9ff;
  padding: 0.6rem 1rem;
  font-size: 1.1rem;
  transition: all 0.3s ease;
  animation: fadeIn 1.2s ease;
}

.form-control-lg:focus {
  border-color: #0984e3;
  box-shadow: 0 0 12px #0984e3;
  animation: vibrate 0.4s;
  outline: none;
}

/* BOTÓN CON EFECTO LATIDO Y RESPLANDOR */
.btn-primary {
  background: #0984e3;
  border: none;
  font-weight: 700;
  font-size: 1.1rem;
  padding: 0.65rem 1.25rem;
  border-radius: 0.6rem;
  box-shadow: 0 4px 15px rgba(9, 132, 227, 0.4);
  transition: all 0.3s ease;
  animation: heartbeat 2s infinite, fadeInUp 1s;
}

.btn-primary:hover {
  background: #74b9ff;
  transform: scale(1.15);
  box-shadow: 0 0 20px #74b9ff;
}

/* ALERTAS CON SHAKE Y FLASH */
.alert-danger {
  background-color: #ff6b6b;
  color: #fff;
  font-weight: bold;
  animation: shake 0.4s ease, flash 1s infinite;
  border-radius: 0.6rem;
  box-shadow: 0 4px 15px rgba(230, 57, 70, 0.4);
}

/* TARJETAS FLOTANTES */
.card {
  border-radius: 1rem;
  box-shadow: 0 12px 25px rgba(0,0,0,0.15);
  animation: float 6s ease-in-out infinite, fadeInCard 1.3s ease;
  transition: transform 0.3s ease;
}

.card:hover {
  transform: translateY(-10px) scale(1.03);
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

/* CABECERA TARJETA */
.card-header {
  background: linear-gradient(90deg, #0984e3, #74b9ff);
  color: white;
  font-weight: bold;
  animation: slideDown 1s ease-out;
}

/* TABLA CON ENTRADA */
.table {
  box-shadow: 0 0 20px rgba(0,0,0,0.1);
  animation: fadeInUp 1.2s ease;
}

.table tbody tr:hover {
  background-color: #dfe6e9;
  animation: glowRow 1.5s ease-in-out;
}

/* BOTONES OUTLINE CON RESPLANDOR */
.btn-outline-primary, .btn-outline-warning {
  border-radius: 0.5rem;
  font-weight: bold;
  animation: fadeIn 1.5s ease;
  transition: all 0.3s ease;
}

.btn-outline-primary:hover {
  background-color: #74b9ff;
  color: #fff;
  box-shadow: 0 0 12px #74b9ff;
}

.btn-outline-warning:hover {
  background-color: #fdcb6e;
  color: #2d3436;
  box-shadow: 0 0 12px #fdcb6e;
}

/* IMAGEN ZOOM + SPIN LIGERO */
#imagenEvidencia {
  border-radius: 1rem;
  max-height: 70vh;
  object-fit: contain;
  box-shadow: 0 8px 20px rgba(0,0,0,0.2);
  transition: transform 0.3s ease;
  animation: fadeIn 1.5s;
}

#imagenEvidencia:hover {
  transform: scale(1.1) rotate(1deg);
  animation: spinHover 1s ease;
  cursor: zoom-in;
}

/* ==== ANIMACIONES PERSONALIZADAS ==== */
@keyframes fadeInUp {
  from {opacity: 0; transform: translateY(30px);}
  to {opacity: 1; transform: translateY(0);}
}

@keyframes fadeIn {
  from {opacity: 0;}
  to {opacity: 1;}
}

@keyframes fadeInCard {
  from {opacity: 0; transform: scale(0.95);}
  to {opacity: 1; transform: scale(1);}
}

@keyframes pulseGlow {
  0%, 100% {text-shadow: 0 0 5px #fff;}
  50% {text-shadow: 0 0 15px #0984e3;}
}

@keyframes vibrate {
  0% {transform: translateX(0);}
  25% {transform: translateX(-2px);}
  50% {transform: translateX(2px);}
  75% {transform: translateX(-2px);}
  100% {transform: translateX(0);}
}

@keyframes shake {
  0%, 100% {transform: translateX(0);}
  20%, 60% {transform: translateX(-10px);}
  40%, 80% {transform: translateX(10px);}
}

@keyframes heartbeat {
  0%, 100% {transform: scale(1);}
  50% {transform: scale(1.1);}
}

@keyframes glowRow {
  0% {box-shadow: none;}
  50% {box-shadow: 0 0 10px #dfe6e9;}
  100% {box-shadow: none;}
}

@keyframes bounceIn {
  0% {transform: scale(0.5); opacity: 0;}
  60% {transform: scale(1.05);}
  80% {transform: scale(0.95);}
  100% {transform: scale(1); opacity: 1;}
}

@keyframes slideLeft {
  0% {transform: translateX(-50px); opacity: 0;}
  100% {transform: translateX(0); opacity: 1;}
}

@keyframes slideDown {
  0% {transform: translateY(-20px); opacity: 0;}
  100% {transform: translateY(0); opacity: 1;}
}

@keyframes flash {
  0%, 100% {opacity: 1;}
  50% {opacity: 0.3;}
}

@keyframes float {
  0% {transform: translateY(0);}
  50% {transform: translateY(-8px);}
  100% {transform: translateY(0);}
}

@keyframes gradientShift {
  0% {background-position: 0% 50%;}
  100% {background-position: 100% 50%;}
}

@keyframes spinHover {
  0% {transform: rotate(0);}
  100% {transform: rotate(2deg);}
}


    </style>
</head>
<body>

<div class="parallax-container">
    <h1 class="my-4 text-center"><i class="fa-solid fa-magnifying-glass"></i> Consulta el Estado de tu Equipo</h1>

    <section class="form-section" aria-label="Formulario de consulta de estado de equipo">
        <form method="POST" novalidate>
            <div class="row g-3 align-items-end justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <label for="idorden_servicio" class="form-label fs-5">Número de Orden de Servicio</label>
                    <input
                        type="text"
                        id="idorden_servicio"
                        name="idorden_servicio"
                        class="form-control form-control-lg"
                        placeholder="Coloque aquí el número de orden de servicio"
                        required
                        value="<?= htmlspecialchars($_POST['idorden_servicio'] ?? '') ?>"
                        aria-required="true"
                        autofocus
                    />
                </div>

                <div class="col-md-6 col-lg-5">
                    <label for="documento_cliente" class="form-label fs-5">Documento del Cliente</label>
                    <input
                        type="text"
                        id="documento_cliente"
                        name="documento_cliente"
                        class="form-control form-control-lg"
                        placeholder="Ingrese su número de documento"
                        required
                        value="<?= htmlspecialchars($_POST['documento_cliente'] ?? '') ?>"
                        aria-required="true"
                    />
                </div>

                <div class="col-md-4 col-lg-2 d-grid">
                    <button type="submit" class="btn btn-primary btn-lg mt-2" aria-label="Buscar orden de servicio">
                        <i class="fa-solid fa-search me-1"></i> Buscar
                    </button>
                </div>
            </div>
        </form>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center mt-4" role="alert"><?= $error ?></div>
        <?php endif; ?>

        <?php if (!empty($equipos)): ?>
            <div class="card mt-5 shadow-sm rounded-4" role="region" aria-live="polite" aria-label="Resultados de la orden">
                <div class="card-header bg-success text-white rounded-top">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-clipboard-list"></i> Resultados de la Orden #<?= htmlspecialchars($equipos[0]['idorden_Servicio']) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Cliente:</strong> <?= htmlspecialchars($equipos[0]['nombre_cliente']) ?></p>

                    <div class="table-responsive rounded">
                        <table class="table table-bordered table-striped align-middle" role="table">
                            <thead>
                                <tr>
                                    <th>Equipo</th>
                                    <th>Modelo</th>
                                    <th>N° Serie</th>
                                    <th>Marca</th>
                                    <th>Subcategoría</th>
                                    <th>Problema Reportado</th>
                                    <th>Fecha Entrega</th>
                                    <th class="text-center">Ver Evidencia</th>
                                    <th class="text-center">Rastrear Equipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipos as $equipo): ?>
                                    <tr tabindex="0">
                                        <td><?= htmlspecialchars($equipo['NombreCategoria']) ?></td>
                                        <td><?= htmlspecialchars($equipo['modelo']) ?></td>
                                        <td><?= htmlspecialchars($equipo['numserie']) ?></td>
                                        <td><?= htmlspecialchars($equipo['Nombre_Marca']) ?></td>
                                        <td><?= htmlspecialchars($equipo['Nombre_SubCategoria']) ?></td>
                                        <td><?= htmlspecialchars($equipo['descripcionentrada']) ?></td>
                                        <td><?= htmlspecialchars($equipo['fechaentrega']) ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($equipo['ruta_Evidencia_Entrada'])): ?>
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEvidencia" data-img="<?= htmlspecialchars($equipo['ruta_Evidencia_Entrada']) ?>">
                                                    <i class="fa-solid fa-image"></i> Ver
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">No disponible</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-warning btn-sm rastrear-equipo-btn" data-id="<?= htmlspecialchars($equipo['iddetequipo']) ?>">
                                                <i class="fa-solid fa-location-dot"></i> Rastrear
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#modalPDF" data-pdf="http://localhost/andream/app/views/generar_pdf.php?idorden=<?= urlencode($equipos[0]['idorden_Servicio']) ?>">
                            <i class="fa-solid fa-file-pdf me-1"></i> Ver Orden en PDF
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- Modales -->
<div class="modal fade" id="modalEvidencia" tabindex="-1" aria-labelledby="modalEvidenciaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalEvidenciaLabel">Evidencia del Equipo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <img id="imagenEvidencia" src="" alt="Evidencia" class="img-fluid rounded shadow" />
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEstadoEquipo" tabindex="-1" aria-labelledby="modalEstadoEquipoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEstadoEquipoLabel">Estado del Equipo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body p-4" id="contenidoEstadoEquipo">
        <!-- Contenido cargado dinámicamente -->
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalPDF" tabindex="-1" aria-labelledby="modalPDFLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalPDFLabel">Orden de Servicio en PDF</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body p-0">
        <iframe id="pdfFrame" src="" frameborder="0" style="width: 100%; height: 80vh;"></iframe>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('modalEvidencia').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    document.getElementById('imagenEvidencia').src = button.getAttribute('data-img');
});

const modalPDF = document.getElementById('modalPDF');
modalPDF.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    document.getElementById('pdfFrame').src = button.getAttribute('data-pdf');
});
modalPDF.addEventListener('hidden.bs.modal', () => {
    document.getElementById('pdfFrame').src = '';
});

const modalEstadoEquipo = new bootstrap.Modal(document.getElementById('modalEstadoEquipo'));
document.querySelectorAll('.rastrear-equipo-btn').forEach(button => {
    button.addEventListener('click', () => {
        const idEquipo = button.getAttribute('data-id');
        const cont = document.getElementById('contenidoEstadoEquipo');
        cont.innerHTML = '<p class="text-center"><i class="fa-solid fa-spinner fa-spin"></i> Cargando estado...</p>';
        modalEstadoEquipo.show();

        fetch("http://localhost/andream/app/views/Tracking/ajax_estado_equipo.php", {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                ajax: 'estado_equipo',
                id_equipo_ajax: idEquipo
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const d = data.data;
                let progreso = 0;
                let color = 'bg-secondary';
                if (!d.fechahorainicio) {
                    progreso = 25; color = 'bg-warning';
                } else if (d.fechahorainicio && !d.fechahorafin) {
                    progreso = 60; color = 'bg-info';
                } else if (d.fechahorafin) {
                    progreso = 100; color = 'bg-success';
                }

                cont.innerHTML = `
                    <p><strong>Estado actual:</strong> ${d.estado_equipo}</p>
                    <p><strong>Fecha recepción:</strong> ${d.fecha_recepcion || 'No disponible'}</p>
                    <p><strong>Inicio:</strong> ${d.fechahorainicio || 'No disponible'}</p>
                    <p><strong>Fin:</strong> ${d.fechahorafin || 'No disponible'}</p>
                    <div class="progress mt-4" role="progressbar" aria-valuenow="${progreso}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar ${color} progress-bar-striped progress-bar-animated" style="width: ${progreso}%;">${progreso}%</div>
                    </div>`;
            } else {
                cont.innerHTML = `<p class="text-danger text-center">${data.message}</p>`;
            }
        })
        .catch(() => {
            cont.innerHTML = `<p class="text-danger text-center">Error al obtener el estado del equipo.</p>`;
        });
    });
});
</script>
</body>
</html>
