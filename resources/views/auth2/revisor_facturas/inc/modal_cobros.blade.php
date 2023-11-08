@php
    $monto = 0;
    if (isset($cobro->cobrosid)) {
        $datos = json_decode($renovacion->datos);
        $monto = $datos->factura->total_facturado;
    } elseif (isset($factura->facturaid)) {
        $monto = $factura->total_venta;
    }
@endphp
<div id="modalCobros" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form method="POST" id="modalFormCobros">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title h6">Registrar cobro</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label for="">Forma de pago:</label>
                        <select name="forma_pago" class="form-control">
                            <option value="" disabled selected>Seleccione</option>
                            <option value="5">Deposito</option>
                            <option value="6">Transferencia</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label for="">Monto:</label>
                        <input type="text" name="monto" id="monto" class="form-control" placeholder="Monto"
                            value="{{ $monto }}">
                    </div>
                    <div class="form-group mb-2">
                        <label for="">Fecha:</label>
                        <input type="date" name="fecha" class="form-control" placeholder="Fecha"
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <input type="hidden" name="facturaid" value="{{ $factura->facturaid ?? null }}">
                    <input type="hidden" name="cobrosid" value="{{ $cobro->cobrosid ?? null }}">
                </div>
                <div class="modal-footer p-0 pt-4 pr-4">
                    <button type="button" class="btn btn-link" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@section('modal_script')
    <script>
        $("#btnRegistrarCobro").click(function() {
            $("#modalCobros").modal("show");
            $("#modalFormCobros").attr("action", "{{ route('cobros.registrar.sistema') }}");
        });

        // deshabilitar el boton de registrar al enviar el formulario
        $("#modalFormCobros").submit(function() {
            $("#modalFormCobros button[type=submit]").attr("disabled", true);
        });

        // sanitizar el monto event input y solo permitir 1 punto
        $("#monto").on("input", function() {
            var monto = $(this).val();
            monto = monto.replace(/[^0-9.]/g, "");
            monto = monto.replace(/(\..*)\./g, "$1");
            $(this).val(monto);
        });
    </script>
@endsection
