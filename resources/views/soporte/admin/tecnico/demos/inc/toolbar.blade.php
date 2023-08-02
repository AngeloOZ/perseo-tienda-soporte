@switch(Auth::guard('tecnico')->user()->rol)
    @case(5)
        <a href="{{ route('sop.listar_soporte_especial') }}" class="btn btn-secondary btn-icon" data-toggle="tooltip"
            title="Volver"><i class="la la-long-arrow-left"></i></a>
    @break

    @case(7)
        <a href="{{ route('soporte.revisor_listar_soporte_especial') }}" class="btn btn-secondary btn-icon" data-toggle="tooltip"
            title="Volver"><i class="la la-long-arrow-left"></i></a>
    @break

    @case(8)
        <a href="{{ route('especiales.listado_supervisor') }}" class="btn btn-secondary btn-icon"
            data-toggle="tooltip" title="Volver"><i class="la la-long-arrow-left"></i></a>
    @break
@endswitch

<button type="submit" class="btn btn-success btn-icon" data-toggle="tooltip" title="Guardar"><i class="la la-save"></i>
</button>

<button type="button" class="btn btn-primary btn-icon" title="Escribir actividad" data-toggle="modal"
    data-target="#modalEmail"><i class="la la-file-alt"></i>
</button>
