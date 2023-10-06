@php
    $disabled = 'disabled';
    $disabledTecnico = 'disabled';
    $readOnly = 'readonly';
    
    if (in_array(Auth::guard('tecnico')->user()->rol, [7, 8])) {
        $disabled = '';
        $readOnly = '';
        $disabledTecnico = '';
    }
    
    if (isset($bloquearTecnico) && $bloquearTecnico) {
        $disabledTecnico = 'disabled';
    }
    
    $estados = [(object) ['id' => 1, 'nombre' => 'Asignado', 'permisos' => [7]], (object) ['id' => 3, 'nombre' => 'Contactado', 'permisos' => [5, 7]], (object) ['id' => 2, 'nombre' => 'Agendado', 'permisos' => [5, 7]], (object) ['id' => 4, 'nombre' => 'Implementacion', 'permisos' => [5, 7]], (object) ['id' => 5, 'nombre' => 'Revisado 1', 'permisos' => [7, 8, 9]], (object) ['id' => 6, 'nombre' => 'Finalizado', 'permisos' => [5, 7]], (object) ['id' => 7, 'nombre' => 'Reagendado', 'permisos' => [7, 8, 9]], (object) ['id' => 8, 'nombre' => 'Revisado 2', 'permisos' => [7, 8, 9]], (object) ['id' => 9, 'nombre' => 'Aprobado', 'permisos' => [7, 8, 9]], (object) ['id' => 10, 'nombre' => 'Rechazado', 'permisos' => [7, 8, 9]], (object) ['id' => 11, 'nombre' => 'Sin Respuesta', 'permisos' => [5, 7]], (object) ['id' => 12, 'nombre' => 'Autoimplementado', 'permisos' => [5, 7, 8, 9]]];
    
    // Buscar vendedor
    $vendedor = 'Desconocido';
    if ($soporte->vededorid) {
        $vendedor = App\Models\User::find($soporte->vededorid, ['nombres']);
        $vendedor = $vendedor->nombres ?? 'Desconocido';
    }
    
@endphp

<div class="form-group row">
    <div class="col-12 mb-4 mb-md-0">
        <label>Vendedor</label>
        <input type="text" class="form-control" disabled value="{{ $vendedor }}" />
    </div>
</div>
<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Técnico asignado <span class="text-danger">*</span>
        </label>
        <select class="form-control select2 @error('tecnico') is-invalid @enderror" name="tecnico" {{ $disabledTecnico }}>
            <option value="" disabled selected>Seleccionar técnico</option>
            @foreach ($tecnicos as $item)
                <option value="{{ $item->tecnicosid }}"
                    {{ $soporte->tecnicoid == $item->tecnicosid ? 'selected' : '' }}>
                    {{ $item->nombres }}</option>
            @endforeach
        </select>
        @error('tecnico')
            <span class="text-danger">{{ $errors->first('tecnico') }}</span>
        @enderror
    </div>

    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Plan <span class="text-danger">*</span>
        </label>
        <select class="form-control @error('plan') is-invalid @enderror" name="plan" {{ $disabled }}>
            <option value="" disabled {{ !$soporte->plan ? 'selected' : '' }}>Sin asignar</option>
            <option value="1" {{ $soporte->plan == 1 ? 'selected' : '' }}>WEB</option>
            <option value="2" {{ $soporte->plan == 2 ? 'selected' : '' }}>PC</option>
            <option value="3" {{ $soporte->plan == 3 ? 'selected' : '' }}>FACTURITO</option>
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
        <input type="text" class="form-control @error('ruc') is-invalid @enderror" name="ruc"
            value="{{ $soporte->ruc }}" {{ $readOnly }} />
        @error('ruc')
            <span class="text-danger">{{ $errors->first('ruc') }}</span>
        @enderror
    </div>

    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Razón Social <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control @error('razon_social') is-invalid @enderror" name="razon_social"
            value="{{ $soporte->razon_social }}" {{ $readOnly }} />
        @error('razon_social')
            <span class="text-danger">{{ $errors->first('razon_social') }}</span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Whatsapp <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control @error('whatsapp') is-invalid @enderror" name="whatsapp"
            value="{{ $soporte->whatsapp }}" {{ $readOnly }} />
        @error('whatsapp')
            <span class="text-danger">{{ $errors->first('whatsapp') }}</span>
        @enderror
    </div>

    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Correo <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control @error('correo') is-invalid @enderror" name="correo"
            value="{{ $soporte->correo }}" {{ $readOnly }} />
        @error('correo')
            <span class="text-danger">{{ $errors->first('correo') }}</span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Estado</span>
        </label>
        <select class="form-control select2 @error('estado') is-invalid @enderror" name="estado">
            @foreach ($estados as $estado)
                <option value="{{ $estado->id }}" {{ $soporte->estado == $estado->id ? 'selected' : '' }}
                    {{ !in_array(Auth::guard('tecnico')->user()->rol, $estado->permisos) ? 'disabled' : '' }}>
                    {{ $estado->nombre }}
                </option>
            @endforeach
        </select>
        @error('estado')
            <span class="text-danger">{{ $errors->first('estado') }}</span>
        @enderror
    </div>

    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Tipo</span>
        </label>
        <select class="form-control @error('tipo') is-invalid @enderror" name="tipo" {{ $disabled }}>
            <option value="" disabled selected>Seleccionar tipo</option>
            <option value="1" {{ $soporte->tipo == 1 ? 'selected' : '' }}>Demo</option>
            <option value="2" {{ $soporte->tipo == 2 ? 'selected' : '' }}>Capacitación</option>
            <option value="3" {{ $soporte->tipo == 3 ? 'selected' : '' }}>LITE</option>
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
        <input type="datetime-local" class="form-control @error('fecha_agendado') is-invalid @enderror"
            name="fecha_agendado" value="{{ $soporte->fecha_agendado }}" />
        @error('fecha_agendado')
            <span class="text-danger">{{ $errors->first('fecha_agendado') }}</span>
        @enderror
    </div>

    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Número de reagendaciones</label>
        <input type="text" class="form-control" disabled value="{{ $soporte->veces_reagendado }}" />
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Fecha de creación</label>
        <input type="text" class="form-control" disabled value="{{ $soporte->fecha_creacion }}" />
    </div>

    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Fecha de edición</label>
        <input type="text" class="form-control" disabled value="{{ $soporte->fecha_actualizado }}" />
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Fecha de inicio de sesión</label>
        <input type="text" class="form-control" disabled value="{{ $soporte->fecha_iniciado }}" />
    </div>

    <div class="col-12 mb-4 col-md-6 mb-md-0">
        <label>Fecha de fin de sesión</label>
        <input type="text" class="form-control" disabled value="{{ $soporte->fecha_finalizado }}" />
    </div>
</div>
