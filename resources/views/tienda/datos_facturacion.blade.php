<form id="form-datos-facturacion" method="POST" action="" enctype="multipart/form-data">
    @csrf
    <div class="card-body p-0">
        <div class="form-group mb-1">
            <label>Cédula/RUC <span class="text-danger">*</span></label>
            <div id="spinner">
                <input type="text" class="form-control" name="ruc" id="inputRuc"
                    oninput="if(this.value.length > 13) this.value = this.value.slice(0, 13);" autocomplete="off"
                    placeholder="1711254789"
                    value="{{ $cliente->ruc ?? '' }}"
                    >
                <span class="form-text text-danger d-none" id="helperTextRuc"></span>
            </div>
        </div>

        <div class="form-group mb-1">
            <label>Nombres/Empresa <span class="text-danger">*</span></label>
            <input type="text" class="form-control" placeholder="Perseo" name="nombre" id="inputEmpresa"  value="{{ $cliente->nombre ?? "" }}" />
            <span class="form-text text-danger d-none" id="helperTextEmpresa"></span>
        </div>

        <div class="form-group mb-1">
            <label>Dirección <span class="text-danger">*</span></label>
            <input type="text" class="form-control" placeholder="Av Tsáfiqui y Tulcán" name="direccion"
                id="inputDireccion" value="{{ $cliente->direccion ?? '' }}" />
            <span class="form-text text-danger d-none" id="helperTextDireccion"></span>
        </div>

        <div class="form-group mb-1">
            <label>Correo electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" placeholder="correo@dominio.com" name="correo"
                id="inputCorreo" value="{{ $cliente->correo ?? ''}}" />
            <span class="form-text text-danger" id="helperTextCorreo"></span>
        </div>
        <div class="form-group mb-1">
            <label>Teléfono <span class="text-danger">*</span></label>
            <input type="tel" class="form-control" placeholder="0987654321" name="telefono" id="inputTelefono" value="{{ $cliente->telefono ?? "" }}" />
            <span class="form-text text-danger d-none" id="helperTextTelefono"></span>
        </div>
        <div class="form-group mb-4">
            <label for="inputObservacion">Observación</span></label>
            <textarea class="form-control" oninput="if(this.value.length > 150) this.value = this.value.slice(0, 150);" style="resize: none" name="observacion" id="inputObservacion" rows="2">{{ $cliente->observacion ?? "" }}</textarea>
        </div>
        <input type="hidden" name="redireccion" id="inputRedireccion" value="false">
        <input type="hidden" name="productos" id="inputProductos">
    </div>
    <div class="row mt-5 d-flex justify-content-lg-start flex-row-reverse">
        <div class="col-12 col-lg-4">
            <button type="submit" id="btnSendMessage" class="btn btn-success mr-2 w-100">Siguiente</button>
        </div>
        <div class="col-12 mt-4 col-lg-4 mt-lg-0">
            <a href="{{ route('tienda', $vendedor->usuariosid) }}" type="button"
                class="btn btn-primary mr-2 w-100">Regresar a la tienda</a>
        </div>
    </div>
</form>
