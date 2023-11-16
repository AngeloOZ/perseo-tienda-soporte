<div class="card-toolbar">
    <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
        <div class="btn-group" role="group" aria-label="First group">

            <a href="{{ route('cobros.listado.revisor') }}" class="btn btn-secondary btn-icon" data-toggle="tooltip"
                title="Volver"><i class="la la-long-arrow-left"></i>
            </a>

            <button class="btn btn-success btn-icon" data-toggle="tooltip" title="Guardar">
                <i class="la la-save"></i>
            </button>

            @if (isset($renovacion) && $renovacion->registrado === 1)
                <a href="{{ route('pagos.reactivar', $renovacion->renovacionid) }}" class="btn btn-danger btn-icon"
                    data-toggle="tooltip" title="Reactivar enlace"><i class="la la-undo-alt"></i>
                </a>
            @endif

            @if (isset($renovacion) && $renovacion->registrado === 0)
                <a href="{{ route('pagos.registrar', $renovacion->uuid) }}" target="_blank"
                    class="btn btn-primary btn-icon" data-toggle="tooltip" title="Enlace de cobro"><i
                        class="la la-external-link-alt"></i>
                </a>
            @endif
            
            @if (in_array(Auth::user()->distribuidoresid, [1, 2, 5]))
                @if (isset($cobro->renovacionid) && $noRegistrado)
                    <button id="btnRegistrarCobro" type="button" class="btn btn-primary btn-icon" data-toggle="tooltip"
                        title="Registrar cobro">
                        <i class="la la-cash-register"></i>
                    </button>
                @endif
            @endif
        </div>
    </div>
</div>
