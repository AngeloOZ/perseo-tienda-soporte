<div id="justificacion-modal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <form method="POST" id="justificacion-modal-form">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h4 class="modal-title h6">Agregar Justificación</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div class="form-group row mb-2">
                            <div class="col-12 mb-4 col-md-6 mb-md-0">
                                <label>Estado:</label>
                                <select class="form-control" id="changeStateSelect2" name="estado_revision">
                                    <option value="1" disabled>Pendiente</option>
                                    <option value="2" disabled>En revisión</option>
                                    <option value="3" selected>Revisado</option>
                                </select>
                            </div>
                            <div class="col-12 mb-4 col-md-6 mb-md-0">
                                <div class="form-group">
                                    <label>Justificado:</label>
                                    <select class="form-control" id="changeJustificadoSelect" name="justificado">
                                        <option value="0">No</option>
                                        <option value="1">Si</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Comentario de revisión:</label>
                            <textarea name="comentario_revision" class="form-control" cols="30" rows="5" required
                                oninput="if(this.value.length > 254) this.value = this.value.slice(0, 254);"></textarea>
                        </div>

                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-link mt-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary mt-2">Registrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
