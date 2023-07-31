<div class="modal fade" id="modalEmail" tabindex="-1" role="dialog" aria-labelledby="modalEmail" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Registrar actividad </h5>
                <button type="button" id="closeModal" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close"></i>
                </button>
            </div>
            <div class="modal-body p-0">
                <textarea class="summernote" id="kt_summernote_1"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" onclick="limpiarCampos()">Limpiar campos</button>
                <button type="button" class="btn btn-light-primary font-weight-bold"
                    data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary font-weight-bold" id="btnSendMail">Enviar</button>
            </div>
        </div>
    </div>
</div>
