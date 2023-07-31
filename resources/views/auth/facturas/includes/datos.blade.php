@php
    $abreviaturas = App\Models\ConceptosFactura::all();
    
    $concepto = $factura->concepto;
    $concepto = explode(' ', $concepto)[0] . " - ";
    
    if (old('concepto_abv')) {
        $concepto = old('concepto_abv');
    }
@endphp
<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Identificación:</label>
        <div id="spinner">
            <input type="text" class="form-control {{ $errors->has('identificacion') ? 'is-invalid' : '' }}"
                placeholder="Ingrese su identificacion" name="identificacion" autocomplete="off"
                {{ $factura->facturado == 0 ? '' : 'readonly' }} value="{{ $factura->identificacion }}"
                onkeypress="return validarNumero(event)" onblur="validarIdentificacion()" />
        </div>
        <span class="text-danger d-none" id="mensajeBandera">La cédula o Ruc no es
            válido</span>
        @if ($errors->has('identificacion'))
            <span class="text-danger">{{ $errors->first('identificacion') }}</span>
        @endif
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Nombres:</label>
        <input type="text" class="form-control {{ $errors->has('nombre') ? 'is-invalid' : '' }}"
            placeholder="Ingrese los nombres" name="nombre" autocomplete="off"
            {{ $factura->facturado == 0 ? '' : 'readonly' }} value="{{ $factura->nombre }}" />
        @if ($errors->has('nombre'))
            <span class="text-danger">{{ $errors->first('nombre') }}</span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label for="">Calle principal, secundaria. N.
            casa/departamento</label>
        <input type="text" class="form-control" name="direccion" {{ $factura->facturado == 0 ? '' : 'readonly' }}
            value="{{ $factura->direccion }}" autocomplete="off">
        <span class="text-danger d-none" id="mensajeDireccion"> Ingrese una
            Dirección</span>
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Correo</label>
        <input type="email" class="form-control {{ $errors->has('correo') ? 'is-invalid' : '' }}"
            placeholder="Ingrese un correo" name="correo" autocomplete="off"
            {{ $factura->facturado == 0 ? '' : 'readonly' }} value="{{ $factura->correo }}" />
        @if ($errors->has('correo'))
            <span class="text-danger">{{ $errors->first('correo') }}</span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Teléfono:</label>
        <input type="text" class="form-control {{ $errors->has('telefono') ? 'is-invalid' : '' }}"
            placeholder="Ingrese un teléfono" name="telefono" autocomplete="off"
            {{ $factura->facturado == 0 ? '' : 'readonly' }} value="{{ $factura->telefono }}"
            onkeypress="return validarNumero(event)" />

        @if ($errors->has('telefono'))
            <span class="text-danger">{{ $errors->first('telefono') }}</span>
        @endif
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label for="">Observación</label>
        <input type="text" class="form-control" name="observacion" {{ $factura->facturado == 0 ? '' : 'readonly' }}
            value="{{ $factura->observacion }}" autocomplete="off"
            oninput="if(this.value.length > 150) this.value = this.value.slice(0, 150);">
    </div>
    <input type="hidden" name="productos" id="inputProductos" value="{{ $factura->productos }}">
</div>

<div class="form-group row">
    {{-- <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Concepto de la factura:</label>
        <input type="text" class="form-control {{ $errors->has('concepto') ? 'is-invalid' : '' }}" name="concepto" autocomplete="off"
            {{ $factura->facturado == 0 ? '' : 'readonly' }} value="{{ $factura->concepto }}"
            oninput="if(this.value.length > 75) this.value = this.value.slice(0, 75);" />
        @if ($errors->has('concepto'))
            <span class="text-danger">{{ $errors->first('concepto') }}</span>
        @endif
    </div> --}}

    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Concepto de la factura:</label>
        <div class="d-flex">
            @if ($factura->facturado == 0)
                <select class="form-control select2 {{ $errors->has('concepto_abv') ? 'is-invalid' : '' }}"
                    name="concepto_abv">
                    <option value="" >Seleccionar uno</option>
                    @foreach ($abreviaturas as $abv)
                        <option 
                            value="{{ $abv->id ."@".$abv->abreviatura }}"
                            {{ $factura->id_concepto == $abv->id ? 'selected' : '' }}
                        >
                            {{ $abv->producto }}
                        </option>
                    @endforeach
                </select>
            @endif
            <input type="text" class="form-control {{ $errors->has('concepto') ? 'is-invalid' : '' }}"
                name="concepto" autocomplete="off" {{ $factura->facturado == 0 ? '' : 'readonly' }}
                value="{{ $factura->concepto }}"
                oninput="if(this.value.length > 75) this.value = this.value.slice(0, 75);" />

        </div>
        @if ($errors->has('concepto_abv'))
            <span class="text-danger d-block">{{ $errors->first('concepto_abv') }}</span>
        @endif
        @if ($errors->has('concepto'))
            <span class="text-danger">{{ $errors->first('concepto') }}</span>
        @endif
    </div>

    @if ($factura->secuencia_perseo)
        <div class="col-12 mt-2 col-lg-6 mt-lg-0">
            <label>Secuencia de factura</label>
            <input type="text" class="form-control" autocomplete="off"
                {{ $factura->facturado == 0 ? '' : 'readonly' }} value="{{ $factura->secuencia_perseo }}" />
        </div>
    @endif
    @if ($factura->secuencia_nota_credito)
        <div class="col-12 mt-2 col-lg-6 mt-lg-4">
            <label>Número de nota de credito</label>
            <input type="text" class="form-control" autocomplete="off"
                {{ $factura->facturado == 0 ? '' : 'readonly' }} value="{{ $factura->secuencia_nota_credito }}" />
        </div>
    @endif

</div>