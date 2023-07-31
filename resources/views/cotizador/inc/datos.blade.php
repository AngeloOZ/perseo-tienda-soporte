@php
        $plantillas = App\Models\Plantilla::all();
    
@endphp
<div class="form-group row">
    <div class="col-lg-6">
        <label>Fecha:</label>
        <input type="text" class="form-control" placeholder="Ingrese fecha" name="fecha" id="fecha"
            value="{{ old('fecha') }}" autocomplete="off" />
        <span class="text-danger d-none" id="mensajeFecha">Ingrese una fecha</span>
        @if ($errors->has('fecha'))
            <span class="text-danger">{{ $errors->first('fecha') }}</span>
        @endif
    </div>
    @if (isset($cliente) && $cliente == 2)
        <div class="col-lg-6">
            <label>Clientes</label>
            <select class="form-control select2" id="clientesid" name="clientesid">
                <option value="">
                    Escoja un cliente
                </option>

                {{-- @if (count($clientes) > 0)
                    @foreach ($clientes as $clientesD)
                        <option value="{{ $clientesD->clientesid }}"
                            {{ old('clientesid') == $clientesD->clientesid ||  $clientesD->clientesid  == $base ? 'selected' : '' }} >
                            {{ $clientesD->razonsocial }}
                        </option>
                    @endforeach
            @endif --}}

            </select>
            <span class="text-danger d-none" id="mensajeCliente">Escoja un
                cliente
            </span>
        </div>
    @else
        <div class="col-lg-6">
            <label>Prospecto</label>
            <select class="form-control select2" id="prospectosid" name="prospectosid">
                <option value="">
                    Escoja un prospecto
                </option>
                <option value="Cliente 1">Cliente 1</option>
                <option value="Cliente 2">Cliente 2</option>
                <option value="Cliente 3">Cliente 3</option>
                {{-- @if (count($prospectos) > 0)
                @foreach ($prospectos as $prospectosD)
                    <option value="{{ $prospectosD->prospectosid }}"
                        {{ old('prospectosid') == $prospectosD->prospectosid || $prospectosD->prospectosid == $base ? 'selected' : '' }}>

                        {{ $prospectosD->razonsocial }}
                    </option>
                @endforeach
            @endif --}}
            </select>
            <span class="text-danger d-none" id="mensajeProspecto">Escoja un
                prospecto
            </span>
        </div>
    @endif
</div>

<div class="form-group row">
    <div class="col-lg-6">
        <label>Plantilla</label>
        <select class="form-control select2" id="tipo_plantilla" name="tipo_plantilla">
            <option value="">
                Escoja una plantilla
            </option>
            @foreach ($plantillas as $plantilla)
                <option value="{{ $plantilla->plantillasid }}"
                    {{ old('tipo_plantilla') == $plantilla->plantillasid ? 'selected' : '' }}>
                    {{ $plantilla->detalle }}
                </option>
            @endforeach 

        </select>
        <span class="text-danger d-none" id="mensajePlantilla">Escoja una plantilla</span>
    </div>
    <div class="col-lg-6">
        <label>Detalle de Pago</label>
        <select class="form-control select2" id="forma_pagoid" name="forma_pagoid">
            <option value="">
                Escoja número de pago
            </option>
            <option value="1" {{ old('forma_pagoid') == 1 ? 'selected' : '' }}>
                1 Pago
            </option>
            <option value="2" {{ old('forma_pagoid') == 2 ? 'selected' : '' }}>
                2 Pagos
            </option>
            <option value="3" {{ old('forma_pagoid') == 3 ? 'selected' : '' }}>
                3 Pagos
            </option>
            <option value="4" {{ old('forma_pagoid') == 4 ? 'selected' : '' }}>
                4 Pagos
            </option>
        </select>
        <span class="text-danger d-none" id="mensajePago">Escoja una pago</span>
    </div>
</div>
