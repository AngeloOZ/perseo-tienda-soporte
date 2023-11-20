@php
    $concepto = $factura->concepto;
    $nomenclaturaNuevos = ['FT', 'NPC', 'PNW', 'P_PNW', 'P_NPC', 'P_FT'];
    $concepto = explode(' ', $concepto)[0];
    $concepto = strtoupper($concepto);
    $esNuevo = in_array($concepto, $nomenclaturaNuevos);
@endphp
<div class="card-toolbar">
    <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
        <div class="btn-group" role="group" aria-label="First group">
            <a href="{{ route('facturas.listado') }}" class="btn btn-secondary btn-icon" data-toggle="tooltip"
                title="Volver"><i class="la la-long-arrow-left"></i></a>

            @if ($esNuevo && $factura->capacitacionid == null && $factura->facturado == 1)
                <a class="btn btn-warning btn-icon modal-implementacion" href="javascript:void(0)"
                    data-href="{{ route('soporte.registrar_capacitacion_ventas', $factura->facturaid) }} }}"
                    title="Crear implementación"> <i class="la la-headset"></i>
                </a>
            @endif

            @if ($factura->facturado == 0)
                <button type="button" class="btn btn-success btn-icon" id="buttonSave" data-toggle="tooltip"
                    title="Guardar"><i class="la la-save"></i>
                </button>
                <a href="{{ route('factura.generar', $factura->facturaid) }}" id="btnFacturar"
                    class="btn btn-danger btn-icon" data-toggle="tooltip" title="Facturar"><i
                        class="la la-file-invoice-dollar"></i>
                </a>
            @endif
            @if ($factura->facturado == 1 && $factura->autorizado == 0)
                <a href="{{ route('factura.autorizar', $factura->facturaid) }}" id="btnAutorizar"
                    class="btn btn-success btn-icon" data-toggle="tooltip" title="Autorizar factura"><i
                        class="la la-check-circle-o"></i>
                </a>
            @endif
            @if ($factura->facturado == 1)
                <a class="btn btn-danger btn-icon confirm-delete" href="javascript:void(0)"
                    data-href="{{ route('facturas.cancelar') }}" data-toggle="tooltip" title="Cancelar factura">
                    <i class="la la-times-circle-o"></i>
                </a>
            @endif
            @if (isset($liberable) && $liberable && Auth::user()->liberador == 1)
                @if ($factura->facturado == 1 && $factura->estado_pago >= 1)
                    <a href="{{ route('liberarlicencias', $factura->facturaid) }}" class="btn btn-info btn-icon"
                        data-toggle="tooltip" title="Ver productos a liberar"><i class="la la-rocket"></i></a>
                @endif
            @endif

        </div>
    </div>
</div>
