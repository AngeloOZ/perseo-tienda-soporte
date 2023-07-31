<div id="implementacion-modal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <form method="POST" action="{{ route('productos.actualizar.masivo') }}">
                @method('PUT')
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title h6">Aplicar descuentos</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">Distribuidores</label>
                        <select class="form-control" name="distribuidor">
                            <option value="">Todos</option>
                            <option value="1">Perseo Alfa</option>
                            <option value="2">Perseo Matriz</option>
                            <option value="3">Perseo Delta</option>
                            <option value="4">Perseo Omega</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="categoria">Categoria</label>
                        <select class="form-control" name="categoria">
                            <option value="1">FACTURITO</option>
                            <option value="2">FIRMA ELECTRONICA</option>
                            <option value="3">PERSEO PC</option>
                            <option value="4">CONTAFACIL</option>
                            <option value="5">PERSEO WEB</option>
                            <option value="6">WHAPI</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="descuento">Descuento %</label>
                        <input type="number" step="0.1" class="form-control" id="descuento" value="0"
                            name="descuento" oninput="validateDescuento(this)">
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-link mt-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnSendNumber" class="btn btn-primary mt-2">Aplicar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>