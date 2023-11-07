<div class="btn-group" role="group" aria-label="First group">
    <a href="{{ route('facturas.revisor') }}" class="btn btn-secondary btn-icon" data-toggle="tooltip" title="Volver"><i
            class="la la-long-arrow-left"></i></a>

    @if (isset($liberable) && $liberable && Auth::user()->liberador == 1)
        @if ($factura->facturado == 1 && $factura->estado_pago >= 1 && $factura->liberado == 0)
            <button id="btnLiberarManual" type="submit" class="btn btn-warning btn-icon" data-toggle="tooltip"
                title="Marcar como liberado"><i class="la la-hand-pointer"></i>
            </button>
        @endif
        @if ($factura->facturado == 1 && $factura->estado_pago >= 1)
            <a href="{{ route('facturas.ver.liberar', $factura->facturaid) }}" class="btn btn-info btn-icon"
                data-toggle="tooltip" title="Ver productos a liberar"><i class="la la-rocket"></i></a>
        @endif
    @endif
    @if (isset($liberable) && !$liberable)
        @if ($factura->liberado == 0 && $factura->estado_pago >= 1)
            <a href="{{ route('facturas.liberar_producto_manual', $factura->facturaid) }}" id="btnLiberar"
                class="btn btn-warning btn-icon" data-toggle="tooltip" title="Liberar producto manual"><i
                    class="la la-rocket"></i>
            </a>
        @endif
    @endif
    @if ($factura->facturado != 0 && $factura->autorizado == 0)
        <a href="{{ route('factura.autorizar', $factura->facturaid) }}" id="btnAutorizar"
            class="btn btn-success btn-icon" data-toggle="tooltip" title="Autorizar factura"><i
                class="la la-check-circle-o"></i>
        </a>
    @endif

    @if (Auth::user()->distribuidoresid == 1)
        @if ($noRegistrado)
            <button id="btnRegistrarCobro" type="button" class="btn btn-primary btn-icon" data-toggle="tooltip"
                title="Registrar cobro">
                <i class="la la-cash-register"></i>
            </button>
        @endif
    @endif
</div>
