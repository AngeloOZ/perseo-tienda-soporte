<div id="delete-modal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <form method="POST" id="delete-link">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h4 class="modal-title h6">Cancelar Factura</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group m-1">
                        <label>Ingrese la nota de credito <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="0000XXX" name="secuencia_nota_credito" id="numeroNotaCredito" />
                        <span class="form-text text-muted">Se debe ingresar una nota de credito para cancelar la factura</span>
                        <input type="hidden" value="{{ $factura->facturaid }}" name="facturaid">
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-link mt-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" disabled id="btnAnularFactura" class="btn btn-primary mt-2">Confirmar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>