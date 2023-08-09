<div class="btn-group" role="group" aria-label="First group">
    @if (Auth::user()->rol == 2)
        <a href="{{ route('facturas.revisor_editar', $factura->facturaid) }}" class="btn btn-secondary btn-icon"
            data-toggle="tooltip" title="Volver"><i class="la la-long-arrow-left"></i></a>
    @else
        <a href="{{ route('facturas.editar', $factura->facturaid) }}" class="btn btn-secondary btn-icon"
            data-toggle="tooltip" title="Volver"><i class="la la-long-arrow-left"></i></a>
    @endif


    @if ($factura->liberado == 0 && Auth::user()->liberador == 1)

        @if ($licencias != null)
            @if ($licencias->liberar == true)
                <button type="button" class="btn btn-success btn-icon" data-toggle="tooltip"
                    title="{{ $licencias->accion == 'nuevo' ? 'Liberar' : 'Renovar' }} licencia" id="btnLiberar"><i
                        class="la la-rocket"></i>
                </button>
            @endif
        @else
            <button type="button" class="btn btn-success btn-icon modal-contador" data-toggle="tooltip"
                title="Confirmar datos btn"><i class="la la-rocket"></i>
            </button>
        @endif

        @if ($contador->esContador && $licencias != null)
            @if ($licencias->liberar == true && $licencias->accion == 'renovar')
                <button type="button" class="btn btn-success btn-icon" data-toggle="tooltip" title="Renovar licencia"
                    id="btnLiberar"><i class="la la-rocket"></i>
                </button>
            @endif
        @endif
    @endif
</div>
