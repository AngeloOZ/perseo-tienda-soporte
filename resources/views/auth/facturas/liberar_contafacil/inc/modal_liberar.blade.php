<div id="modal-licencia" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">Confirmar datos</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            </div>
            <div class="modal-body">
                <form id="formLicencia" action="#">

                    <div class="form-group mx-2">
                        <label for="tiempoRenovacion">Identificación <small>Cliente</small></label>
                        <input type="text" class="form-control {{ $errors->has('rucCliente') ? 'is-invalid' : '' }}" 
                        id="rucCliente"  name="rucCliente" value="{{ $factura->identificacion }}">
                        @if ($errors->has('rucCliente'))
                            <span class="text-danger">{{ $errors->first('rucCliente') }}</span>
                        @endif
                    </div>

                    <div class="form-group m-2">
                        <label for="tiempoRenovacion">Nombres y Apellidos <small>Cliente</small></label>
                        <input type="text" class="form-control" id="nombresCliente" value="{{ $factura->nombre }}">
                    </div>
                    <div class="form-group m-2">
                        <label for="tiempoRenovacion">Dirección <small>Cliente</small></label>
                        <input type="text" class="form-control" id="direccionCliente"
                            value="{{ $factura->direccion }}">
                    </div>
                    <div class="form-group m-2">
                        <label for="tiempoRenovacion">Teléfono <small>Cliente</small></label>
                        <input type="text" class="form-control" id="telefonoCliente"
                            value="{{ $factura->telefono }}">
                    </div>
                    <div class="form-group m-2">
                        <label for="tiempoRenovacion">Correo <small>Cliente</small></label>
                        <input type="text" class="form-control" id="correoCliente" value="{{ $factura->correo }}">
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-outline-danger mr-2"
                            data-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary" id="btnFormSubmit">Liberar licencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
