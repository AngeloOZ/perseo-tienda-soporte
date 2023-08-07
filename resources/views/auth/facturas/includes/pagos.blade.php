@php
    $disabled = '';
    if ($factura->estado_pago >= 1) {
        $disabled = 'disabled';
    }
@endphp
<div class="container p-8">
    <form action="{{ route('facturas.subir_comprobantes', $factura->facturaid) }}" method="POST" id="form_pagos" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="row mt-0">
            <div class="col-12 mt-5 col-lg-6 mt-md-0">
                <h2 class="font-size-h3 font-weight-bold mb-6">Subir comprobante</h2>
                <div class="form-group"> 
                    <label for="estado_pago">Estado del pago</label>
                    <select class="form-control form-control" id="estado_pago" name="estado_pago" {{ $disabled }}>
                        <option value="0" {{ $factura->estado_pago == 0 ? 'selected' : '' }}>Por pagar</option>
                        <option value="1" {{ $factura->estado_pago == 1 ? 'selected' : '' }}>Pagado</option>
                        @if ($factura->estado_pago == 2)
                            <option value="2" selected>Pagado y revisado</option>
                        @endif
                    </select>
                    <span class="text-danger d-none" id="mesajeEstado">Seleccione el estado del pago</span>
                </div>

                <div class="form-group mt-2">
                    <label for="comprobante_pago" class="customFile">Subir comprobantes:</label>
                    <input type="file" multiple class="form-control" name="comprobante_pago[]" {{ $disabled }} id="comprobante_pago" accept=".jpg, .jpeg, .png">
                    <span class="text-muted" id="">El número máximo de archivos es de 5 y el tamaño máximo de
                        cada archivo es de 2 MB</span>
                    <p class="text-danger d-none" id="mensajeArchios">Debe selecionar al menos un archivo</p>
                </div>

                <div class="form-group mt-2">
                    <label for="textObsPago2">Observación del pago vendedor</label>
                    <textarea class="form-control" id="textObsPago2" name="observacion_pago_vendedor" style="resize: none" rows="2" {{ $disabled }}>{{ $factura->observacion_pago_vendedor }}</textarea>
                </div>

                <div class="form-group mt-2">
                    <label for="textObsPago">Observación del pago revisor</label>
                    <textarea class="form-control" disabled id="textObsPago" style="resize: none" rows="2">{{ $factura->observacion_pago }}</textarea>
                </div>

                <div class="form-group mt-2">
                    <button id="btnSubmit" class="btn btn-primary" {{ $disabled }}>Guardar pago</button>
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
