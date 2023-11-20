@php
    $rutaPrevia = Auth::user()->rol == 2 ? 'facturas.revisor_editar' : 'facturas.editar';
@endphp

<div class="btn-group" role="group" aria-label="First group">

    <a href="{{ route($rutaPrevia, $factura->facturaid) }}" class="btn btn-secondary btn-icon" data-toggle="tooltip"
        title="Volver"><i class="la la-long-arrow-left"></i></a>
    
        @if ($factura->liberado == 0)
        <button type="button" class="btn btn-success btn-icon btn-modal-licencia" data-toggle="tooltip"
            title="Liberar licencia"><i class="la la-rocket"></i>
        </button>
    @endif

    @if ($factura->liberado == 1 && Auth::user()->liberador == 1)
        <form action="{{ route('facturas.reactivar_liberacion', $factura->facturaid) }}" method="POST">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-danger btn-icon" data-toggle="tooltip" title="Reactivar liberación"><i class="la la-undo-alt"></i>
            </button>
        </form>
    @endif
</div>
