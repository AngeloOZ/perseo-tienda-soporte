@php
    $tecnicos = App\Models\Tecnicos::select('tecnicosid', 'nombres')
        ->where('estado', 1)
        ->get();
@endphp

@csrf
<div class="form-group row">
    <div class="col-lg-6">
        <label>Identificación:</label>
        <div id="spinner">
            <input type="text" class="form-control {{ $errors->has('identificacion') ? 'is-invalid' : '' }}"
                placeholder="Ingrese su identificacion" name="identificacion" id="identificacion"
                value="{{ old('identificacion', $clientes->identificacion) }}" onkeypress="return validarNumero(event)"
                onblur="validarIdentificacion()" />
        </div>
        <span class="text-danger d-none" id="mensajeBandera">La cédula o Ruc no es válido</span>
        @if ($errors->has('identificacion'))
            <span class="text-danger">{{ $errors->first('identificacion') }}</span>
        @endif
    </div>
    <div class="col-lg-6">
        <label>Razon Social:</label>
        <input type="text" class="form-control {{ $errors->has('razonsocial') ? 'is-invalid' : '' }}"
            placeholder="Ingrese la razon social" name="razonsocial" id="razonsocial"
            value="{{ old('razonsocial', $clientes->razonsocial) }}" />
        @if ($errors->has('razonsocial'))
            <span class="text-danger">{{ $errors->first('razonsocial') }}</span>
        @endif
    </div>
</div>
<div class="form-group row">
    <div class="col-lg-6">
        <label>Nombre Comercial:</label>
        <input type="text" class="form-control {{ $errors->has('nombrecomercial') ? 'is-invalid' : '' }}"
            placeholder="Ingrese el nombre comercial" name="nombrecomercial" id="nombrecomercial"
            value="{{ old('nombrecomercial', $clientes->nombrecomercial) }}" />
        @if ($errors->has('nombrecomercial'))
            <span class="text-danger">{{ $errors->first('nombrecomercial') }}</span>
        @endif
    </div>
    <div class="col-lg-6">
        <label>Correo</label>
        <input type="email" class="form-control {{ $errors->has('correo') ? 'is-invalid' : '' }}"
            placeholder="Ingrese un correo" name="correo" id="correo"
            value="{{ old('correo', $clientes->correo) }}" />
        @if ($errors->has('correo'))
            <span class="text-danger">{{ $errors->first('correo') }}</span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-lg-6">
        <label for="celular">Teléfono:</label>
        <input type="tel" autocomplete="off" class="form-control {{ $errors->has('celular') ? 'is-invalid' : '' }}"
            placeholder="Ingrese un celular" name="celular" aria-label="celular" id="celular"
            value="{{ old('celular', $clientes->celular) }}" onkeypress="return validarNumero(event)" />
        @if ($errors->has('celular'))
            <span class="text-danger">{{ $errors->first('celular') }}</span>
        @endif
    </div>

    <div class="col-lg-6">
        <label>Distribuidor:</label>
        <select class="form-control select2" id="distribuidoresid" name="distribuidoresid">
            <option value="" disabled selected>Escoja un distribuidor</option>
            <option value="1" {{ old('distribuidoresid', $clientes->distribuidoresid) == '1' ? 'Selected' : '' }}>
                Perseo Alfa</option>
            <option value="2" {{ old('distribuidoresid', $clientes->distribuidoresid) == '2' ? 'Selected' : '' }}>
                Perseo Matriz
            </option>
            <option value="3" {{ old('distribuidoresid', $clientes->distribuidoresid) == '3' ? 'Selected' : '' }}>
                Perseo Delta
            </option>
            <option value="4" {{ old('distribuidoresid', $clientes->distribuidoresid) == '4' ? 'Selected' : '' }}>
                Perseo Omega
            </option>
        </select>
        @if ($errors->has('distribuidoresid'))
            <span class="text-danger">{{ $errors->first('distribuidoresid') }}</span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-lg-6">
        <label>Estado:</label>
        <select class="form-control select2" id="estado" name="estado">
            <option value="1" {{ old('estado', $clientes->estado) == '1' ? 'Selected' : '' }}>Activo</option>
            <option value="0" {{ old('estado', $clientes->estado) == '0' ? 'Selected' : '' }}>Inactivo</option>
        </select>
        @if ($errors->has('estado'))
            <span class="text-danger">{{ $errors->first('estado') }}</span>
        @endif
    </div>

    <div class="col-lg-6">
        <label for="clave">Clave:</label>
        <input type="password" autocomplete="off" class="form-control {{ $errors->has('clave') ? 'is-invalid' : '' }}"
            placeholder="Ingrese una clave" name="clave" id="clave" value="{{ old('clave') }}" />
        @if ($clientes->clave)
            <span class="form-text text-muted">La clave se modificará solo si se llena el campo</span>
        @endif
        @if ($errors->has('clave'))
            <span class="text-danger">{{ $errors->first('clave') }}</span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-lg-12">
        <label>Resumen de la empresa</label>
        <textarea rows="3" cols="14" type="text" class="form-control" id="resumen_empresa" name="resumen_empresa"
            maxlength="1000">{{ old('resumen_empresa', $clientes->resumen_empresa) }}</textarea>
        @if ($errors->has('resumen_empresa'))
            <span class="text-danger">{{ $errors->first('resumen_empresa') }}</span>
        @endif
    </div>
</div>

@if ($clientes->fechacreacion != null)
    <div class="form-group row">
        <div class="col-lg-6">
            <label>Fecha Creacion:</label>
            <input type="text" class="form-control" placeholder="" name="fechacreacion" id="fechacreacion"
                value="{{ $clientes->fechacreacion }}" disabled />
        </div>
        <div class="col-lg-6">
            <label>Fecha Modificacion:</label>
            <input type="text" class="form-control" placeholder="" name="fechamodificacion"
                id="fechamodificacion" value="{{ $clientes->fechamodificacion }}" disabled />
        </div>
    </div>
@endif

<script>
    function recuperarInformacion() {
        var cad = document.getElementById('identificacion').value;
        var mostrar = 1;
        $("#spinner").addClass("spinner spinner-success spinner-right");
        if (mostrar == 1) {
            $.post('{{ route('recuperarInformacionPost') }}', {
                _token: '{{ csrf_token() }}',
                cedula: cad
            }, function(data) {
                $("#spinner").removeClass("spinner spinner-success spinner-right");
                if (data.identificacion) {
                    $("#razonsocial").val(data.razon_social);
                    $("#nombrecomercial").val(data.nombre_comercial);
                    $("#correo").val(data.correo);
                    $("#celular").val(data.telefono3);
                }
            });
        }
    }
</script>
