@php
    if (isset($readOnly) && $readOnly == true) {
        $readOnly = 'readonly';
        $disabled = 'disabled';
    } else {
        $disabled = '';
        $readOnly = '';
    }
@endphp

@if (isset($soporte->nombreTecnico))
    <div class="form-group row">
        <div class="col-12 mb-4 col-md-6 mb-md-0">
            <label>Técnico </label>
            <input type="text" disabled class="form-control" value="{{ $soporte->nombreTecnico ?? 'Sin asignar' }}" />
        </div>
        <div class="col-12 mb-4 col-md-6 mb-md-0">
            <label>Fecha de agendamiento </label>
            <input type="text" disabled class="form-control" value="{{ $soporte->fecha_agendado ?? 'Sin asignar' }}" />
        </div>
    </div>
@endif

<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Plan <span class="text-danger">*</span>
        </label>
        <select {{ $disabled }} class="form-control {{ $errors->has('plan') ? 'is-invalid' : '' }}" name="plan">
            <option value="" disabled selected>Seleccionar tipo</option>
            <option value="1" {{ old('plan', $soporte->plan) == 1 ? 'selected' : '' }}>WEB</option>
            <option value="2" {{ old('plan', $soporte->plan) == 2 ? 'selected' : '' }}>PC</option>
            @if ($soporte->plan == 3)
                <option value="3" selected>FACTURITO</option>
            @endif
        </select>
        @error('plan')
            <span class="text-danger">{{ $errors->first('plan') }}</span>
        @enderror
    </div>
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Tipo</span>
        </label>
        <select {{ $disabled }} class="form-control {{ $errors->has('tipo') ? 'is-invalid' : '' }}" name="tipo">
            <option value="" disabled selected>Seleccionar tipo</option>
            <option value="1" {{ old('tipo', $soporte->tipo) == 1 ? 'selected' : '' }}>Demo</option>
            <option value="3" {{ old('tipo', $soporte->tipo) == 3 ? 'selected' : '' }}>LITE</option>
            @if ($soporte->tipo == 2)
                <option value="2" selected>Capacitación</option>
            @endif
        </select>
        @error('tipo')
            <span class="text-danger">{{ $errors->first('tipo') }}</span>
        @enderror
    </div>
</div>


<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>RUC <span class="text-danger">*</span></label>
        <div id="spinner">
            <input type="text" {{ $readOnly }} class="form-control {{ $errors->has('ruc') ? 'is-invalid' : '' }}" name="ruc" id="ruc"
                oninput="if(this.value.length > 13) this.value = this.value.slice(0, 13);" autocomplete="off"
                placeholder="23XXXXXXXX001" value="{{ old('ruc', $soporte->ruc) }}">
            <span class="form-text text-danger d-none" id="helperTextRuc"></span>
        </div>
        @error('ruc')
            <span class="text-danger">{{ $errors->first('ruc') }}</span>
        @enderror
    </div>
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label for="razon_social">Razón Social <span class="text-danger">*</span>
        </label>
        <input type="text" {{ $readOnly }}
            class="form-control {{ $errors->has('razon_social') ? 'is-invalid' : '' }}" id="razon_social" name="razon_social"
            value="{{ old('razon_social', $soporte->razon_social) }}" placeholder="PerseoSoft" />
        @error('razon_social')
            <span class="text-danger">{{ $errors->first('razon_social') }}</span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label for="whatsapp">Whatsapp <span class="text-danger">*</span>
        </label>
        <input type="text" {{ $readOnly }}
            class="form-control {{ $errors->has('whatsapp') ? 'is-invalid' : '' }}" id="whatsapp" name="whatsapp"
            value="{{ old('whatsapp', $soporte->whatsapp) }}" placeholder="0987654321" />
        @error('whatsapp')
            <span class="text-danger">{{ $errors->first('whatsapp') }}</span>
        @enderror
    </div>

    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label for="correo">Correo <span class="text-danger">*</span>
        </label>
        <input type="text" {{ $readOnly }}
            class="form-control {{ $errors->has('correo') ? 'is-invalid' : '' }}" id="correo" name="correo"
            value="{{ old('correo', $soporte->correo) }}" placeholder="correo@dominio.com" />
        @error('correo')
            <span class="text-danger">{{ $errors->first('correo') }}</span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label for="">Actividad principal de la empresa <span class="text-danger">*</span></label>
        <textarea name="actividad_empresa" {{ $readOnly }} class="form-control" cols="30" rows="3">{{ old('actividad_empresa', $soporte->actividad_empresa) }}</textarea>
        @error('actividad_empresa')
            <span class="text-danger">{{ $errors->first('actividad_empresa') }}</span>
        @enderror
    </div>
</div>
