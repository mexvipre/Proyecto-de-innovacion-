<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Formulario Dinámico de Equipos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">

  <div class="container border p-4 rounded">
    <h4 class="mb-4">Especificaciones de Equipos</h4>

    <!-- Select de tipo de equipo -->
    <div class="mb-3">
      <label for="tipoEquipo" class="form-label">Equipo</label>
      <select class="form-select" id="tipoEquipo">
        <option value="">Seleccione un equipo</option>
        <option value="laptop">Laptop</option>
        <option value="pc">PC</option>
        <option value="impresora">Impresora</option>
      </select>
    </div>

    <!-- Grupo de opciones -->
    <div id="especificaciones" class="mb-3 d-none">
      <label class="form-label">ESPECIFICACIONES:</label>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="especificacion" id="ram" value="ram">
        <label class="form-check-label" for="ram">RAM</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="especificacion" id="disco" value="disco">
        <label class="form-check-label" for="disco">Disco Duro</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="especificacion" id="procesador" value="procesador">
        <label class="form-check-label" for="procesador">Procesador</label>
      </div>
    </div>

    <!-- Campos dinámicos -->
    <div id="campoRam" class="mb-3 d-none">
      <label class="form-label">RAM:</label>
      <input type="text" class="form-control" placeholder="Ej: 4 GB">
    </div>

    <div id="campoDisco" class="mb-3 d-none">
      <label class="form-label">Disco Duro:</label>
      <input type="text" class="form-control" placeholder="Ej: 500 GB">
    </div>

    <div id="campoProcesador" class="mb-3 d-none">
      <label class="form-label">Procesador:</label>
      <input type="text" class="form-control" placeholder="Ej: Intel i5">
    </div>

  </div>

  <script>
    const tipoEquipo = document.getElementById('tipoEquipo');
    const especificaciones = document.getElementById('especificaciones');

    const radios = document.getElementsByName('especificacion');
    const campoRam = document.getElementById('campoRam');
    const campoDisco = document.getElementById('campoDisco');
    const campoProcesador = document.getElementById('campoProcesador');

    tipoEquipo.addEventListener('change', () => {
      if (tipoEquipo.value !== "") {
        especificaciones.classList.remove('d-none');
      } else {
        especificaciones.classList.add('d-none');
        ocultarCampos();
      }
    });

    radios.forEach(radio => {
      radio.addEventListener('change', () => {
        ocultarCampos();
        switch (radio.value) {
          case 'ram':
            campoRam.classList.remove('d-none');
            break;
          case 'disco':
            campoDisco.classList.remove('d-none');
            break;
          case 'procesador':
            campoProcesador.classList.remove('d-none');
            break;
        }
      });
    });

    function ocultarCampos() {
      campoRam.classList.add('d-none');
      campoDisco.classList.add('d-none');
      campoProcesador.classList.add('d-none');
    }
  </script>

</body>
</html>
