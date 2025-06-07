<div class="modal fade" id="modalRevision<?php echo $id; ?>" tabindex="-1" aria-labelledby="modalRevisionLabel<?php echo $id; ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #02505F; color: white;">
                <h5 class="modal-title" id="modalRevisionLabel<?php echo $id; ?>">
                    <i class="fa-solid fa-toolbox"></i> Revisión del Equipo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <h6><strong>Detalles del Servicio (ID: <?php echo $id; ?>):</strong></h6>

                <?php
                // Inicializamos el totala
                $totalPrecio = 0;

                // Procesar los servicios_realizados
                if (!empty($servicios)) {
                    echo '<div class="list-group">';

                    // Separar los servicios por coma
                    $items = explode(',', $servicios);

                    foreach ($items as $item) {
                        // Quitar espacios al inicio y al final
                        $item = trim($item);

                        // Usamos regex para extraer nombre y precio
                        if (preg_match('/^(.*?)\s*\(S\/\s*(\d+(\.\d+)?)\)$/', $item, $matches)) {
                            $nombre = htmlspecialchars(trim($matches[1]));
                            $precio = (float)$matches[2];
                            $totalPrecio += $precio;

                            echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                            echo "<div><strong>{$nombre}</strong></div>";
                            echo "<div><span class='badge bg-success text-white'>Precio: S/ " . number_format($precio, 2, ',', '.') . "</span></div>";
                            echo '</div>';
                        } else {
                            // Para depuración: Mostrar si el item no coincide con el regex
                            echo "<div class='alert alert-warning' role='alert'>Formato inválido para: {$item}</div>";
                        }
                    }

                    echo '</div>';
                } else {
                    echo "<div class='alert alert-warning' role='alert'>No se encontraron servicios para esta revisión.</div>";
                }
                ?>

                <hr>
                <div class="d-flex justify-content-between">
                    <strong>Total de Servicios:</strong>
                    <span class="badge bg-danger text-white">S/ <?php echo number_format($totalPrecio, 2, ',', '.'); ?></span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>