@if ($licencias != null)
    <div class="mb-6">
        @if ($licencias->liberar == true)
            @if ($licencias->accion == 'renovar')
                <div class="alert alert-success" role="alert">
                    <p style="font-size: 15px; margin: 0">La renovación de la licencia esta
                        asociada al contrato No:
                        <strong>{{ $licencias->numerocontrato }}</strong>
                    </p>
                </div>
            @elseif($licencias->accion == 'nuevo')
                <div class="alert alert-info" role="alert">
                    <p style="font-size: 15px; margin: 0">El RUC:
                        {{ $ruc_renovacion ?? '' }} no posee licencias a renovar, pero aún
                        se puede liberar</p>
                </div>
            @else
                <div class="alert alert-warning" role="alert">
                    <p style="font-size: 15px; margin: 0">El RUC:
                        {{ $ruc_renovacion ?? '' }} no posee licencias a renovar, y el
                        tampoco coincide coincide con la identificación:
                        {{ $factura->identificacion }} que fue ingresada en la factura
                        actual, se recomienda liberar directamente en el licenciador</p>
                </div>
            @endif
        @else
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">No se puede renovar está licencia</h4>
                <p style="font-size: 14px;">No es posible renovar esta licencia por este
                    medio, esto se debe a que al cliente tiene más de una licencia activa o
                    el producto a renovar no coincide con el registrado en el licenciador
                </p>
                <hr>
                <p style="font-size: 14px;" class="mb-0">Para renovar esta licencia debe
                    contactarse directamente con el administrador</p>
            </div>
        @endif
    </div>
@endif
