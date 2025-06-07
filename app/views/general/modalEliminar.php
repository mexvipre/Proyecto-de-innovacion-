<div class="modal fade" id="modalEliminar<?php echo $id; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">¿Seguro que deseas eliminar?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                Recuerda que si eliminas, ya no podrás recuperar el registro.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btnEliminar" data-id="<?php echo $id; ?>">Eliminar</button>
            </div>
        </div>
    </div>
</div>
