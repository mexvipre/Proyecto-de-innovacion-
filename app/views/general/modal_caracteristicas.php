<?php
require_once __DIR__ . '../../../config/conexion.php';

// Evitar redefinición de clase
if (!class_exists('EquipmentModal')) {
    /**
     * Clase EquipmentModal
     * Maneja la visualización de detalles y características del equipo en un modal con buscador.
     */
    class EquipmentModal {
        private $conn;          // Conexión a la base de datos
        private $id;            // ID del equipo (iddetequipo)
        private $equipo;        // Detalles del equipo
        private $caracteristicas; // Características asociadas al equipo
        private $modalId;       // ID único para el modal

        public function __construct($id) {
            $this->conn = Conexion::conectar();
            $this->id = (int)$id;
            $this->modalId = "modalCaracteristicas{$this->id}";
            $this->loadEquipmentData();
            $this->loadCharacteristicsData();
        }

        private function loadEquipmentData() {
            try {
                $stmt = $this->conn->prepare("CALL GetEquipmentDetails(?)");
                $stmt->execute([$this->id]);
                $this->equipo = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
                error_log("Detalles del equipo ID {$this->id}: " . json_encode($this->equipo));
            } catch (PDOException $e) {
                error_log("Error al cargar detalles del equipo ID {$this->id}: " . $e->getMessage());
                $this->equipo = [];
            }
        }

        private function loadCharacteristicsData() {
            try {
                $stmt = $this->conn->prepare("
                    SELECT de.iddetequipo, c.id_caracteristica, c.id_especificacion, c.valor AS caracteristica_valor, e.especificacion AS especificacion
                    FROM detequipos de
                    JOIN caracteristicas c ON de.iddetequipo = c.iddetequipo
                    JOIN especificaciones e ON c.id_especificacion = e.id_especificacion
                    WHERE de.iddetequipo = ?
                    ORDER BY de.iddetequipo ASC
                ");
                $stmt->execute([$this->id]);
                $this->caracteristicas = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
                error_log("Características cargadas para equipo ID {$this->id}: " . json_encode($this->caracteristicas));
            } catch (PDOException $e) {
                error_log("Error al cargar características del equipo ID {$this->id}: " . $e->getMessage());
                $this->caracteristicas = [];
            }
        }

        private function getSpecifications() {
            $result = [];
            $tipo_equipo = $this->equipo['tipo_equipo'] ?? '';
            if (empty($tipo_equipo)) {
                error_log("Tipo de equipo vacío para ID {$this->id}");
                return $result;
            }
            try {
                $stmt = $this->conn->prepare("CALL obtener_especificaciones_por_categoria(?)");
                $stmt->execute([$tipo_equipo]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $result[] = [
                        'id_especificacion' => $row['id_especificacion'],
                        'especificacion' => $row['especificacion']
                    ];
                }
                error_log("Especificaciones cargadas para tipo {$tipo_equipo}: " . json_encode($result));
            } catch (PDOException $e) {
                error_log("Error al cargar especificaciones para tipo {$tipo_equipo}: " . $e->getMessage());
            }
            return $result;
        }

        private function getCharacteristicsWithValues() {
            $result = [];
            foreach ($this->caracteristicas as $car) {
                $spec_name = $car['especificacion'] ?? '';
                if (!empty($spec_name)) {
                    $result[$spec_name] = [
                        'idcaracteristica' => $car['id_caracteristica'],
                        'id_especificacion' => $car['id_especificacion'],
                        'valor' => $car['caracteristica_valor'] ?? ''
                    ];
                }
            }
            return $result;
        }

        public function render() {
            $caracteristicas = $this->getCharacteristicsWithValues();
            $tipo_equipo = $this->equipo['tipo_equipo'] ?? 'N/A';
            $specifications = $this->getSpecifications();
?>

<div class="modal fade" id="<?= $this->modalId ?>" tabindex="-1" aria-labelledby="<?= $this->modalId ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="<?= $this->modalId ?>Label">Características del Equipo #<?= $this->id ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Tipo:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($tipo_equipo) ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Especificaciones:</label>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" placeholder="Ej: Voltaje" id="specSearch<?= $this->id ?>" list="specList<?= $this->id ?>">
                            <datalist id="specList<?= $this->id ?>">
                                <?php foreach ($specifications as $spec): ?>
                                    <option value="<?= htmlspecialchars($spec['especificacion']) ?>" data-id="<?= $spec['id_especificacion'] ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <button type="button" class="btn btn-outline-secondary add-spec">+</button>
                        </div>
                        <div id="errorMessage<?= $this->id ?>" class="text-danger" style="display: none;"></div>
                        <form id="formCaracteristica<?= $this->id ?>" action="/AndreaM/app/views/general/guardar_caracteristica.php?id=<?= urlencode($_GET['id'] ?? '') ?>&cliente=<?= urlencode($_GET['cliente'] ?? '') ?>" method="POST">
                            <input type="hidden" name="iddetequipo" value="<?= $this->id ?>">
                            <input type="hidden" name="tipo_equipo" value="<?= htmlspecialchars($tipo_equipo) ?>">
                            <div id="specContainer<?= $this->id ?>">
                                <?php 
                                $index = 0;
                                foreach ($caracteristicas as $spec_name => $spec_data): 
                                ?>
                                    <div class="input-group mb-2 spec-group">
                                        <span class="input-group-text"><?= htmlspecialchars($spec_name) ?>: </span>
                                        <input type="text" class="form-control" 
                                               name="caracteristicas[<?= $index ?>][valor]" 
                                               value="<?= htmlspecialchars($spec_data['valor']) ?>" 
                                               placeholder="Ingrese el valor">
                                        <input type="hidden" name="caracteristicas[<?= $index ?>][idcaracteristica]" value="<?= $spec_data['idcaracteristica'] ?>">
                                        <input type="hidden" name="caracteristicas[<?= $index ?>][id_especificacion]" value="<?= $spec_data['id_especificacion'] ?>">
                                        <input type="hidden" name="caracteristicas[<?= $index ?>][especificacion]" value="<?= htmlspecialchars($spec_name) ?>">
                                    </div>
                                <?php 
                                    $index++;
                                endforeach; 
                                ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const modalId = "<?= $this->modalId ?>";
    const tipoEquipo = "<?= htmlspecialchars($tipo_equipo) ?>";
    const specData = {
        availableSpecs: <?= json_encode(array_map(function($spec) { return $spec['especificacion']; }, $specifications), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        specIdMap: <?= json_encode(array_column($specifications, 'id_especificacion', 'especificacion'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        existingSpecs: <?= json_encode(array_map(function($car) { return $car['especificacion']; }, $this->caracteristicas), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
    };

    document.addEventListener('DOMContentLoaded', function() {
        console.log('Modal inicializado para equipo ID <?= $this->id ?>, tipo: ' + tipoEquipo);
        console.log('Especificaciones disponibles:', specData.availableSpecs);

        const specSearch = document.getElementById('specSearch<?= $this->id ?>');
        const addButton = document.querySelector('#' + modalId + ' .add-spec');
        const specContainer = document.getElementById('specContainer<?= $this->id ?>');
        const errorMessage = document.getElementById('errorMessage<?= $this->id ?>');
        const availableSpecs = specData.availableSpecs || [];
        const availableSpecsLower = availableSpecs
            .filter(spec => spec != null && typeof spec === 'string')
            .map(spec => spec.toLowerCase());
        const specIdMap = specData.specIdMap || {};
        const existingSpecs = specData.existingSpecs || [];
        const existingSpecsLower = existingSpecs
            .filter(spec => spec != null && typeof spec === 'string')
            .map(spec => spec.toLowerCase());

        function showError(message) {
            console.log('Error mostrado:', message);
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
            setTimeout(() => {
                errorMessage.style.display = 'none';
                errorMessage.textContent = '';
            }, 5000);
        }

        addButton.addEventListener('click', function() {
            const searchInput = specSearch.value.trim();
            console.log('Intento de agregar especificación:', searchInput);
            if (!searchInput) {
                showError('Por favor, ingrese una especificación.');
                specSearch.focus();
                return;
            }

            const searchInputLower = searchInput.toLowerCase();

            if (existingSpecsLower.includes(searchInputLower)) {
                showError('Esta especificación ya está agregada. Elija otra.');
                specSearch.value = '';
                specSearch.focus();
                return;
            }

            const matchedSpec = availableSpecs.find(spec => spec.toLowerCase() === searchInputLower);
            if (matchedSpec) {
                const specId = specIdMap[matchedSpec] || null;
                console.log('Especificación válida encontrada, ID:', specId);
                if (!specId) {
                    showError('Error interno: ID de especificación no encontrado.');
                    specSearch.value = '';
                    specSearch.focus();
                    return;
                }
                addNewSpec(searchInput, specId);
            } else {
                showError('No es una especificación válida para este equipo. Seleccione una opción del listado.');
                specSearch.value = '';
                specSearch.focus();
            }
        });

        function addNewSpec(searchInput, specId) {
            console.log('Añadiendo nueva especificación:', searchInput, 'ID:', specId);
            const specElements = Array.from(specContainer.querySelectorAll('.spec-group'));
            const newIndex = specElements.length;
            const newSpec = specElements.length > 0 ? specElements[0].cloneNode(true) : createNewSpec();

            console.log('newSpec HTML:', newSpec.outerHTML);

            const textSpan = newSpec.querySelector('.input-group-text');
            if (textSpan) {
                textSpan.textContent = searchInput + ': ';
            } else {
                console.error('No se encontró .input-group-text en newSpec');
            }

            const valorInput = newSpec.querySelector('input[name$="[valor]"]');
            if (valorInput) {
                valorInput.value = '';
                valorInput.name = `caracteristicas[${newIndex}][valor]`;
            } else {
                console.error('No se encontró input[name$="[valor]"] en newSpec');
            }

            const idCaracteristicaInput = newSpec.querySelector('input[name$="[idcaracteristica]"]');
            if (idCaracteristicaInput) {
                idCaracteristicaInput.value = '';
                idCaracteristicaInput.name = `caracteristicas[${newIndex}][idcaracteristica]`;
            } else {
                console.error('No se encontró input[name$="[idcaracteristica]"] en newSpec');
            }

            const idEspecificacionInput = newSpec.querySelector('input[name$="[id_especificacion]"]');
            if (idEspecificacionInput) {
                idEspecificacionInput.value = specId || '';
                idEspecificacionInput.name = `caracteristicas[${newIndex}][id_especificacion]`;
            } else {
                console.error('No se encontró input[name$="[id_especificacion]"] en newSpec');
            }

            const especificacionInput = newSpec.querySelector('input[name$="[especificacion]"]');
            if (especificacionInput) {
                especificacionInput.value = searchInput;
                especificacionInput.name = `caracteristicas[${newIndex}][especificacion]`;
            } else {
                console.error('No se encontró input[name$="[especificacion]"] en newSpec');
            }

            specContainer.appendChild(newSpec);
            existingSpecs.push(searchInput);
            existingSpecsLower.push(searchInput.toLowerCase());
            specSearch.value = '';
            specSearch.focus();
        }

        function createNewSpec() {
            console.log('Creando nuevo elemento de especificación');
            const div = document.createElement('div');
            div.className = 'input-group mb-2 spec-group';
            div.innerHTML = `
                <span class="input-group-text"></span>
                <input type="text" class="form-control" name="caracteristicas[0][valor]" placeholder="Ingrese el valor">
                <input type="hidden" name="caracteristicas[0][idcaracteristica]" value="">
                <input type="hidden" name="caracteristicas[0][id_especificacion]" value="">
                <input type="hidden" name="caracteristicas[0][especificacion]" value="">
            `;
            return div;
        }
    });
})();
</script>
<?php
        }
    }
}

if (!isset($id) || $id <= 0) {
    echo "<p>ID de equipo no válido.</p>";
} else {
    $modal = new EquipmentModal($id);
    $modal->render();
}
?>