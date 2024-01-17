@php
    $monto = 0;
    if (isset($cobro->cobrosid)) {
        if (isset($renovacion->datos)) {
            $datos = json_decode($renovacion->datos);
            $monto = $datos->factura->total_facturado;
        }
    } elseif (isset($factura->facturaid)) {
        $monto = $factura->total_venta;
    }
@endphp
<div id="modalContrato" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form method="POST" id="formContrato">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title h6">Generar contrato</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>

                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label for="">Dirección:</label>
                        <input type="text" name="direccion" required id="direccion" class="form-control" placeholder="Dirección">
                    </div>
                    <div class="form-group mb-2">
                        <label for="">Fecha:</label>
                        <input type="date" name="fecha" class="form-control" placeholder="Fecha"
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <!--//aqui cambio-->
                    <div class="form-group mb-2">
                        <label for="tipo">Tipo:</label>
                        <select id="tipo" name="tipo" class="form-control">
                            <option value="anual">Anual</option>
                            <option value="mensual">Mensual</option>
                            <option value="extra">Extra</option>
                        </select>
                    </div>
                    
                    <!--////termina cambio-->
                </div>
                <div class="modal-footer p-0 pt-4 pr-4">
                    <button type="button" class="btn btn-link" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@section('script')
    <script>
        $("#btnContrato").click(function() {
            $("#modalContrato").modal("show");
            $("#formContrato").attr("action", "{{ route('generarContrato.index', $cotizaciones->cotizacionesid) }}");
        });

        // deshabilitar el boton de registrar al enviar el formulario
        $("#formContrato").submit(function() {
            // ocultar modal
            $("#modalContrato").modal("hide");
        });
    </script>
@endsection
