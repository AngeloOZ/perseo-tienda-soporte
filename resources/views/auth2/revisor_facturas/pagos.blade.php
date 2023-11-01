<div class="container p-8">
    <form action="{{ route('facturas.subir_comprobantes', $factura->facturaid) }}" method="POST" id="form_pagos"
        enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="row mt-0">
            <div class="col-12 mt-5 col-lg-6 mt-md-0">
                <h2 class="font-size-h3 font-weight-bold mb-6">Estado del pago</h2>

                <div class="form-group mt-2">
                    
                </div>
                
                <div class="form-group mt-2">
                    <label for="estado_pago">Estado del pago</label>
                    <select class="form-control form-control" id="estado_pago" name="estado_pago">
                        <option value="0" {{ $factura->estado_pago == 0 ? 'selected' : '' }}>Por pagar</option>
                        <option value="1" {{ $factura->estado_pago == 1 ? 'selected' : '' }}>Pagado</option>
                        <option value="2" {{ $factura->estado_pago == 2 ? 'selected' : '' }}>Pagado y revisado</option>
                    </select>
                    <span class="text-danger d-none" id="mesajeEstado">Seleccione el estado del pago</span>
                </div>
                
                <div class="form-group mt-2">
                    <label for="textObsPago2">Observación del pago vendedor</label>
                    <textarea class="form-control" disabled id="textObsPago2" style="resize: none" rows="2">{{ $factura->observacion_pago_vendedor }}</textarea>
                </div>

                <div class="form-group mt-2">
                    <label for="textObsPago">Observación del pago</label>
                    <textarea class="form-control" name="observacion_pago" id="textObsPago" style="resize: none" rows="4">{{ $factura->observacion_pago }}</textarea>
                </div>

                <div class="form-group mt-2">
                    <button id="btnSubmit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
            @if (isset($factura->comprobante_pago))
                <div class="col-12 mt-5 col-lg-6 mt-md-0">
                    <h2 class="font-size-h3 mb-6">Fotos de comprobantes</h2>
                    <div class="row g-2 d-flex justify-content-center">
                        @foreach (json_decode($factura->comprobante_pago) as $key => $item)
                            <a href="{{ route('factura.descargar_comprobante', ['id_factura' => $factura->facturaid, 'id_comprobante' => $key]) }}"
                                target="_blank" class="col-12 col-md-6 mb-2 text-decoration-none">
                                <img src="data:image/jpeg;base64, {{ $item }}" style="border: 1px solid; width: 100%; height: 200px; object-fit: cover" alt="comprobante">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </form>
</div>
