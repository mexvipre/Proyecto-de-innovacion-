<?php
require_once __DIR__ . '../../../config/conexion.php'; // Ruta correcta

$conn = Conexion::conectar();

// Consulta para verificar si hay imágenes asociadas al equipo
$stmt = $conn->prepare("SELECT idEvidencia, ruta_Evidencia_Entrada FROM evidencias_entrada WHERE idEvidencia IN (SELECT idEvidencia FROM detequipos WHERE iddetequipo = ?)");
$stmt->execute([$id]); // $id es el ID del equipo (iddetequipo)

$imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<?php
$modalId = "modalEvidencias{$id}";
?>
<!-- Modal -->
<div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="<?= $modalId ?>Label">Evidencia del equipo #<?= $id ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <h2>Tomar o Subir una Foto</h2>
        
        <!-- Mostrar la foto existente, si existe -->
        <div id="fotoExistente<?= $id ?>" style="margin-bottom: 20px;">
          <?php if (!empty($imagenes)): ?>
            <h3>📸 Evidencia existente:</h3>
            <div style="text-align: center;">
              <img id="imgEvidencia<?= $id ?>" src="<?= htmlspecialchars($imagenes[0]['ruta_Evidencia_Entrada']) ?>" alt="Evidencia" style="width: 100%; border-radius: 10px; cursor: pointer;" onclick="zoomFoto(<?= $id ?>)">
            </div>
          <?php endif; ?>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px;">
          <input type="file" id="archivoImagen<?= $id ?>" accept="image/*" style="width: 150px;">
          <button id="activarCamara<?= $id ?>">Usar Cámara</button>
          <button id="capturar<?= $id ?>" style="display:none;">Tomar Foto</button>
        </div>

        <video id="video<?= $id ?>" autoplay style="display:none;"></video>
        <canvas id="canvas<?= $id ?>" style="display: none;"></canvas>

        <div id="modalPreview<?= $id ?>" style="display:none;">
          <div class="modal-content">
            <h3>Previsualización</h3>
            <img id="fotoModal<?= $id ?>" src="" alt="Foto" />
            <input type="text" id="nombreImagen<?= $id ?>" placeholder="Nombre de la imagen" />
            <div class="modal-buttons">
              <button id="guardar<?= $id ?>">Guardar</button>
              <button onclick="cerrarModal<?= $id ?>()">Volver a Tomar foto</button>
            </div>
          </div>
        </div>

        <input type="hidden" id="iddetequipo<?= $id ?>" value="<?= $id ?>" />
      </div>
    </div>
  </div>
</div>

<!-- Estilos para el modal -->
<style>
  button {
    background: #28a745;
    color: white;
    border: none;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
  }

  .modal-content img {
    width: 10%;
    border-radius: 10px;
    margin-bottom: 10px;
  }

  button:hover {
    background: #218838;
  }

  input[type="file"],
  input[type="text"] {
    padding: 8px;
    margin-top: 10px;
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 5px;
  }

  video {
    width: 43%;
    margin-top: 15px;
    border-radius: 10px;
    display: block;
    margin: 0 auto;
  }

  .modal-content img {
    width: 100%;
    border-radius: 10px;
    margin-bottom: 10px;
  }

  .modal-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
  }

  .modal-buttons button {
    width: 48%;
  }

  /* Estilo para el zoom */
  .zoom-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    cursor: pointer;
  }

  .zoom-container img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 10px;
  }

  .close-zoom {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 18px;
    padding: 10px;
    cursor: pointer;
  }

</style>

<!-- Scripts para abrir y capturar la imagen -->
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const id = <?= $id ?>;
    const video = document.getElementById(`video${id}`);
    const canvas = document.getElementById(`canvas${id}`);
    const context = canvas.getContext('2d');
    const activarCamara = document.getElementById(`activarCamara${id}`);
    const capturar = document.getElementById(`capturar${id}`);
    const archivoImagen = document.getElementById(`archivoImagen${id}`);
    const fotoModal = document.getElementById(`fotoModal${id}`);
    const nombreImagen = document.getElementById(`nombreImagen${id}`);
    const modal = document.getElementById(`modalPreview${id}`);
    const imagenBase64Input = document.createElement('input');
    imagenBase64Input.type = 'hidden';
    document.body.appendChild(imagenBase64Input);

    // Función para abrir el modal con la imagen capturada
    function abrirModal(imagenBase64) {
      fotoModal.src = imagenBase64;
      imagenBase64Input.value = imagenBase64;
      modal.style.display = 'flex';
    }

    window[`cerrarModal${id}`] = function () {
      modal.style.display = 'none';
      nombreImagen.value = '';
    }

    // Activar cámara
    activarCamara.addEventListener('click', () => {
      navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
          video.srcObject = stream;
          video.style.display = 'block';
          capturar.style.display = 'inline-block';
        })
        .catch(err => console.error("Error al acceder a la cámara:", err));
    });

    // Capturar la imagen
    capturar.addEventListener('click', () => {
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      context.drawImage(video, 0, 0, canvas.width, canvas.height);
      const imagenBase64 = canvas.toDataURL("image/png");
      abrirModal(imagenBase64);
    });

    // Subir imagen desde archivo
    archivoImagen.addEventListener('change', function () {
      const archivo = this.files[0];
      if (archivo) {
        const lector = new FileReader();
        lector.onload = function (e) {
          abrirModal(e.target.result);
        };
        lector.readAsDataURL(archivo);
      }
    });

// Guardar la imagen
document.getElementById(`guardar${id}`).addEventListener('click', function () {
  const nombre = nombreImagen.value.trim();
  const imagen = imagenBase64Input.value;
  const iddetEquipo = document.getElementById(`iddetequipo${id}`).value;

  if (!nombre || !imagen || !iddetEquipo) {
    alert("⚠️ Debes ingresar nombre e imagen, y asegurarte que el equipo esté definido.");
    return;
  }

  fetch('../imagenes/subir.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      nombre_imagen: nombre,
      imagen: imagen,
      iddetequipo: iddetEquipo
    })
  })
  .then(res => res.text())
  .then(data => {
    // Cerrar el modal inmediatamente después de guardar la imagen
    window[`cerrarModal${id}`]();

    Swal.fire({
      title: '¡Operación completada!',
      text: `✅ Imagen guardada como: ${nombre}`,
      icon: 'success',
      confirmButtonText: 'Listo',
      confirmButtonColor: '#00BF63',
    }).then((result) => {
      if (result.isConfirmed) {
        // Recargar la página después de que el usuario confirme
        window.location.reload();
      }
    });
  })
  .catch(err => console.error("Error al guardar imagen:", err));
});


    // Función para hacer zoom en la imagen al hacer clic
    window.zoomFoto = function (id) {
      const img = document.getElementById(`imgEvidencia${id}`);
      const modalZoom = document.createElement('div');
      modalZoom.classList.add('zoom-container');
      modalZoom.innerHTML = `
        <img src="${img.src}" alt="Zoom Imagen" />
        <button class="close-zoom" onclick="this.parentElement.remove();">✖</button>
      `;
      document.body.appendChild(modalZoom);
    };
  });
</script>
