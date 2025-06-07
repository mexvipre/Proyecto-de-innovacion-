 
<?php
require_once '../../../app/config/conexion.php';
$equipos = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Consulta normal por número de orden
    $idOrden = trim($_POST['idorden_servicio'] ?? '');

    if (!empty($idOrden) && is_numeric($idOrden)) {
        try {
            $conn = Conexion::conectar();
            $stmt = $conn->prepare("CALL ObtenerOrdenConEquipos(?)");
            $stmt->execute([$idOrden]);
            $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($equipos)) {
                $error = "No se encontraron equipos para la orden número <strong>" . htmlspecialchars($idOrden) . "</strong>.";
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "Por favor ingrese un número de orden válido.";
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="light-theme">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Consulta de Estado - Equipo</title>
    <!-- Fuentes y CSS de Bootstrap + FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="public/css/estilos.css" />





       
    
</head>
<body>

    <h1 class="my-4 text-center"><i class="fa-solid fa-magnifying-glass"></i> Consulta el Estado de tu Equipo</h1>

    <section class="container form-section" aria-label="Formulario de consulta de estado de equipo">
        <form method="POST" novalidate>
            <div class="row g-3 align-items-end justify-content-center">
                <div class="col-md-8 col-lg-6">
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
                <div class="col-md-4 col-lg-2 d-grid">
                    <button type="submit" class="btn btn-primary btn-lg" aria-label="Buscar orden de servicio">
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
                                    <th scope="col">Equipo</th>
                                    <th scope="col">Modelo</th>
                                    <th scope="col">N° Serie</th>
                                    <th scope="col">Marca</th>
                                    <th scope="col">Subcategoría</th>
                                    <th scope="col">Problema Reportado</th>
                                    <th scope="col">Fecha Entrega</th>
                                    <th scope="col" class="text-center">Ver Evidencia</th>
                                    <th scope="col" class="text-center">Ver Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipos as $equipo): ?>
                                    <tr tabindex="0" aria-label="Equipo <?= htmlspecialchars($equipo['NombreCategoria']) ?> modelo <?= htmlspecialchars($equipo['modelo']) ?>">
                                        <td><?= htmlspecialchars($equipo['NombreCategoria']) ?></td>
                                        <td><?= htmlspecialchars($equipo['modelo']) ?></td>
                                        <td><?= htmlspecialchars($equipo['numserie']) ?></td>
                                        <td><?= htmlspecialchars($equipo['Nombre_Marca']) ?></td>
                                        <td><?= htmlspecialchars($equipo['Nombre_SubCategoria']) ?></td>
                                        <td><?= htmlspecialchars($equipo['descripcionentrada']) ?></td>
                                        <td><?= htmlspecialchars($equipo['fechaentrega']) ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($equipo['ruta_Evidencia_Entrada'])): ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-primary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEvidencia"
                                                    data-img="<?= htmlspecialchars($equipo['ruta_Evidencia_Entrada']) ?>"
                                                    aria-label="Ver evidencia del equipo <?= htmlspecialchars($equipo['NombreCategoria']) ?>"
                                                >
                                                    <i class="fa-solid fa-image"></i> Ver
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">No disponible</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                type="button"
                                               class="btn btn-success btn-sm rastrear-equipo-btn"

                                                data-id="<?= htmlspecialchars($equipo['iddetequipo']) ?>"
                                                aria-label="Rastrear equipo <?= htmlspecialchars($equipo['NombreCategoria']) ?>"
                                            >
                                                <i class="fa-solid fa-arrow-trend-up"></i>

                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                    <button
                    type="button"
                    class="btn btn-danger btn-lg"
                    data-bs-toggle="modal"
                    data-bs-target="#modalPDF"
                    data-pdf="http://localhost/andream/app/views/generar_pdf.php?idorden=<?= urlencode($equipos[0]['idorden_Servicio']) ?>"
                    >
                    <i class="fa-solid fa-file-pdf me-1"></i> Ver Orden en PDF
                    </button>

                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Modal Evidencia -->
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




    <!-- Modal PDF -->
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
        // Modal para evidencia
        const modalEvidencia = document.getElementById('modalEvidencia');
        modalEvidencia.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const imageUrl = button.getAttribute('data-img');
            const img = document.getElementById('imagenEvidencia');
            img.src = imageUrl;
        });
// Modal PDF
const modalPDF = document.getElementById('modalPDF');
if (modalPDF) {
  modalPDF.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    const pdfUrl = button.getAttribute('data-pdf');
    document.getElementById('pdfFrame').src = pdfUrl;
  });

  modalPDF.addEventListener('hidden.bs.modal', () => {
    document.getElementById('pdfFrame').src = '';
  });
}
       // Modal Estado Equipo con barra progreso
const modalEstadoEquipo = new bootstrap.Modal(document.getElementById('modalEstadoEquipo'));
const contenidoEstadoEquipo = document.getElementById('contenidoEstadoEquipo');

document.querySelectorAll('.rastrear-equipo-btn').forEach(button => {
  button.addEventListener('click', () => {
    const idEquipo = button.getAttribute('data-id');
    contenidoEstadoEquipo.innerHTML = '<p class="text-center"><i class="fa-solid fa-spinner fa-spin"></i> Cargando estado...</p>';
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
        const estado = data.data.estado_equipo;
        const fechaRecepcion = data.data.fecha_recepcion;
        const fechaInicio = data.data.fechahorainicio;
        const fechaFin = data.data.fechahorafin;

        let progreso = 0;
        let color = 'bg-secondary';

        if (!fechaInicio) {
          progreso = 25;
          color = 'bg-warning';
        } else if (fechaInicio && !fechaFin) {
          progreso = 60;
          color = 'bg-info';
        } else if (fechaFin) {
          progreso = 100;
          color = 'bg-success';
        }

contenidoEstadoEquipo.innerHTML = `
  <p><strong>Estado actual:</strong> ${estado}</p>
  <p><strong>Fecha recepción:</strong> ${fechaRecepcion || 'No disponible'}</p>
  <p><strong>Inicio:</strong> ${fechaInicio || 'No disponible'}</p>
  <p><strong>Fin:</strong> ${fechaFin || 'No disponible'}</p>
  <div class="progress mt-4" role="progressbar" aria-valuenow="${progreso}" aria-valuemin="0" aria-valuemax="100">
    <div class="progress-bar ${color} progress-bar-striped progress-bar-animated" style="width: ${progreso}%;">
      ${progreso}%
    </div>
  </div>
`;

      } else {
        contenidoEstadoEquipo.innerHTML = `<p class="text-danger text-center">${data.message}</p>`;
      }
    })
    .catch(err => {
      contenidoEstadoEquipo.innerHTML = `<p class="text-danger text-center">Error al obtener el estado del equipo.</p>`;
      console.error(err);
    });
  });
});
    </script>
</body>
</html>
