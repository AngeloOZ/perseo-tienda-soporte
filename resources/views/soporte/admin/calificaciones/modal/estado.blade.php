<div id="change-state-modal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form method="POST" id="form-change-state">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h4 class="modal-title h6">Cambiar estado de revisión</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Estado:</label>
                        <select class="form-control" id="changeStateSelect" name="estado_revision">
                            <option value="1">Pendiente</option>
                            <option value="2">En revisión</option>
                            <option value="3">Revisadas</option>
                        </select>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-link mt-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary mt-2">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>