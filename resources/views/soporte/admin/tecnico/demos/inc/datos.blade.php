<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Técnico <span class="text-danger">*</span>
        </label>
        <select class="form-control select2 {{ $errors->has('tecnico') ? 'is-invalid' : '' }}"
            name="tecnico">
            <option value="" disabled selected>Seleccionar tecnico</option>
            @foreach ($tecnicos as $item)
                <option value="{{ $item->usuariosid }}">{{ $item->nombres }}</option>
            @endforeach
        </select>
        @error('tecnico')
            <span class="text-danger">{{ $errors->first('tecnico') }}</span>
        @enderror
    </div>

    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Plan <span class="text-danger">*</span>
        </label>
        <select class="form-control {{ $errors->has('plan') ? 'is-invalid' : '' }}" name="plan">
            <option value="" disabled>Sin asignar</option>
            <option value="1">WEB</option>
            <option value="2">PC</option>
            <option value="3">FACTURITO</option>
        </select>
        @error('plan')
            <span class="text-danger">{{ $errors->first('plan') }}</span>
        @enderror
    </div>
</div>


<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>RUC <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control {{ $errors->has('ruc') ? 'is-invalid' : '' }}" name="ruc"
            id="ruc" value="{{ old('ruc') }}" />
        <span class="text-danger d-none" id="mensajeCedula">Identificación no
            válida</span>
        @error('ruc')
            <span class="text-danger">{{ $errors->first('ruc') }}</span>
        @enderror
    </div>
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Razón Social <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control {{ $errors->has('razon_social') ? 'is-invalid' : '' }}"
            name="razon_social" value="{{ old('razon_social') }}" />
        @error('razon_social')
            <span class="text-danger">{{ $errors->first('razon_social') }}</span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Whatsapp <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control {{ $errors->has('whatsapp') ? 'is-invalid' : '' }}" name="whatsapp"
            value="{{ old('whatsapp') }}" />
        @error('whatsapp')
            <span class="text-danger">{{ $errors->first('whatsapp') }}</span>
        @enderror
    </div>

    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Correo <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control {{ $errors->has('correo') ? 'is-invalid' : '' }}" name="correo"
            value="{{ old('correo') }}" />
        @error('correo')
            <span class="text-danger">{{ $errors->first('correo') }}</span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Estado</span>
        </label>
        <select class="form-control {{ $errors->has('estado') ? 'is-invalid' : '' }}" name="estado">
            <option value="1" {{ old('estado') == 1 ? 'selected' : '' }}>Asignado</option>
            <option value="2" {{ old('estado') == 2 ? 'selected' : '' }}>Agendado</option>
        </select>
        @error('estado')
            <span class="text-danger">{{ $errors->first('estado') }}</span>
        @enderror
    </div>
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Tipo</span>
        </label>
        <select class="form-control {{ $errors->has('tipo') ? 'is-invalid' : '' }}" name="tipo">
            <option value="" disabled selected>Seleccionar tipo</option>
            <option value="1" {{ old('tipo') == 1 ? 'selected' : '' }}>Demo</option>
            <option value="2" {{ old('tipo') == 2 ? 'selected' : '' }}>Capacitación</option>
            <option value="3" {{ old('tipo') == 3 ? 'selected' : '' }}>LITE</option>
        </select>
        @error('tipo')
            <span class="text-danger">{{ $errors->first('tipo') }}</span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Fecha de agendamiento <span class="text-danger">*</span>
        </label>
        <input type="datetime-local" class="form-control {{ $errors->has('fecha_agendado') ? 'is-invalid' : '' }}"
            name="fecha_agendado" value="{{ old('fecha_agendado') }}" min="{{ date('Y-m-d\TH:i') }}" />
        @error('fecha_agendado')
            <span class="text-danger">{{ $errors->first('fecha_agendado') }}</span>
        @enderror
    </div>
</div>
