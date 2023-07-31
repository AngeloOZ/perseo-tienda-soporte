<div class="btn-group" role="group" aria-label="First group">
    @if (Auth::user()->rol == 2)
        <a href="{{ route('facturas.revisor_editar', $factura->facturaid) }}" class="btn btn-secondary btn-icon"
            data-toggle="tooltip" title="Volver"><i class="la la-long-arrow-left"></i></a>
    @else
        <a href="{{ route('facturas.editar', $factura->facturaid) }}" class="btn btn-secondary btn-icon"
            data-toggle="tooltip" title="Volver"><i class="la la-long-arrow-left"></i></a>
    @endif


    @if ($factura->liberado == 0 && Auth::user()->liberador == 1)
        @if (!$contador->esContador)
            @if ($licencias != null)
                @if ($licencias->liberar == true)
                    @if ($licencias->accion == 'nuevo')
                        <button type="button" class="btn btn-success btn-icon" data-toggle="tooltip"
                            title="Liberar licencia" id="btnLiberar"><i class="la la-rocket"></i>
                        </button>
                    @elseif($licencias->accion == 'renovar')
                        <button type="button" class="btn btn-success btn-icon" data-toggle="tooltip"
                            title="Renovar licencia" id="btnLiberar"><i class="la la-rocket"></i>
                        </button>
                    @endif
                @endif
            @else
                <button type="button" class="btn btn-success btn-icon" data-toggle="tooltip" title="Liberar licencia"
                    id="btnLiberar"><i class="la la-rocket"></i>
                </button>
            @endif
        @endif


        @if ($contador->esContador && $licencias != null)
            @if ($licencias->liberar == true && $licencias->accion == 'renovar')
                <button type="button" class="btn btn-success btn-icon" data-toggle="tooltip" title="Renovar licencia"
                    id="btnLiberar"><i class="la la-rocket"></i>
                </button>
            @endif
        @endif

        @if ($contador->esContador && $licencias == null)
            <button type="button" class="btn btn-primary btn-icon modal-contador" data-toggle="tooltip"
                title="Confirmar datos"><i class="la la-address-card"></i>
            </button>
        @endif
    @endif



</div>
