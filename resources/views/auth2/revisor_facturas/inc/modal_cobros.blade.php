<div id="modalCobros" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form method="POST" id="modalFormCobros">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title h6">Registrar cobro</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">Forma de pago:</label>
                        <select name="forma_pago" class="form-control">
                            <option value="" disabled selected>Seleccione</option>
                            <option value="5">Deposito</option>
                            <option value="6">Transferencia</option>
                        </select>
                    </div>
                    <input type="hidden" name="facturaid" value="{{ $factura->facturaid ?? null }}">
                    <input type="hidden" name="cobrosid" value="{{ $cobro->cobrosid ?? null }}">
                </div>
                <div class="modal-footer p-0 pt-4 pr-4">
                    <button type="button" class="btn btn-link" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>
