<div id="facturar-modal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form method="POST" id="facturar-link">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title h6">Confirmar Facturación</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mt-1">¿Está seguro de generar la factura? no podrá revertir esta acción</p>
                    <button type="button" class="btn btn-link mt-2" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary mt-2">Facturar</button>
                </div>
            </form>
        </div>
    </div>
</div>