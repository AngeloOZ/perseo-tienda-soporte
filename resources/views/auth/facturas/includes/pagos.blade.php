@php
    $disabled = '';
    if ($factura->estado_pago >= 1) {
        $disabled = 'disabled';
    }

    $client = new App\Http\Controllers\CobrosClientesController();
    $bancos = $client->obtener_bancos(Auth::user());
    $detallePago = json_decode($factura->detalle_pagos);
    $bancoDestino = $detallePago->banco_destino ?? null;
    $bancoOrigen = $detallePago->banco_origen ?? null;
    $numeroComprobante = $detallePago->numero_comprobante ?? null;
@endphp
<div class="container p-8">
    <form action="{{ route('facturas.subir_comprobantes', $factura->facturaid) }}" method="POST" id="form_pagos"
        enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="row mt-0">
            <div class="col-12 mt-5 col-lg-6 mt-md-0">
                <h2 class="font-size-h3 font-weight-bold mb-6">Subir comprobante</h2>

                <div class="form-group row">
                    <div class="col-12 mb-2 col-md-6 mb-md-0">
                        <label>Banco de Origen <span class="text-danger">*</span></label>
                        <select name="banco_origen" class="form-control select2" {{ $disabled }}>
                            @foreach ($bancos->origen as $banco)
                                <option value="{{ $banco->bancocid }}"
                                    {{ $bancoOrigen == $banco->bancocid ? 'selected' : '' }}>
                                    {{ $banco->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-2 col-md-6 mb-md-0">
                        <label>Banco de Destino <span class="text-danger">*</span></label>
                        <select name="banco_destino" class="form-control select2" {{ $disabled }}>
                            @foreach ($bancos->destino as $banco)
                                <option value="{{ $banco->bancoid }}" {{ $bancoDestino == $banco->bancoid ? 'selected' : '' }}>
                                    {{ $banco->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-12 mb-2 col-md-6 mb-md-0">
                        <label>Número de comprobante <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="numero_comprobante" name="numero_comprobante"
                            {{ $disabled }} placeholder="XXXXXXX"
                            value="{{ $numeroComprobante }}" />
                        <p class="text-danger d-none" id="mensajeComprobante"></p>
                    </div>

                    <div class="col-12 mb-2 col-md-6 mb-md-0">
                        <label for="estado_pago">Estado del pago</label>
                        <select class="form-control form-control" id="estado_pago" name="estado_pago"
                            {{ $disabled }}>
                            <option value="0" {{ $factura->estado_pago == 0 ? 'selected' : '' }}>Por pagar
                            </option>
                            <option value="1" {{ $factura->estado_pago == 1 ? 'selected' : '' }}>Pagado</option>
                            @if ($factura->estado_pago == 2)
                                <option value="2" selected>Pagado y revisado</option>
                            @endif
                        </select>
                        <span class="text-danger d-none" id="mesajeEstado">Seleccione el estado del pago</span>
                    </div>
                </div>

                <div class="form-group mt-2">
                    <label for="comprobante_pago" class="customFile">Subir comprobantes:</label>
                    <input type="file" multiple class="form-control" name="comprobante_pago[]" {{ $disabled }}
                        id="comprobante_pago" accept=".jpg, .jpeg, .png">
                    <span class="text-muted" id="">El número máximo de archivos es de 5 y el tamaño máximo de
                        cada archivo es de 2 MB</span>
                    <p class="text-danger d-none" id="mensajeArchios">Debe selecionar al menos un archivo</p>
                </div>

                <div class="form-group mt-2">
                    <label for="textObsPago2">Observación del pago vendedor</label>
                    <textarea class="form-control" id="textObsPago2" name="observacion_pago_vendedor" style="resize: none" rows="2"
                        {{ $disabled }}>{{ $factura->observacion_pago_vendedor }}</textarea>
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
                                <img src="data:image/jpeg;base64, {{ $item }}"
                                    style="border: 1px solid; width: 100%; height: 200px; object-fit: cover"
                                    alt="comprobante">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </form>
</div>

@section('script-pagos')
    <script>
        $(document).ready(function() {
            init_subir_archivos();
        });

        /* -------------------------------------------------------------------------- */
        /*                            funciones para pagos                            */
        /* -------------------------------------------------------------------------- */

        function init_subir_archivos() {
            const mensajeArchios = document.getElementById('mensajeArchios');
            const mesajeEstado = document.getElementById('mesajeEstado');

            const form_pagos = document.getElementById('form_pagos');
            const comprobante_pago = document.getElementById('comprobante_pago');
            const btnSubmit = document.getElementById('btnSubmit');

            comprobante_pago.addEventListener('change', function() {
                if (this.files.length > 5) {
                    this.value = "";
                    return Swal.fire({
                        title: "Demasiados archivos",
                        text: "El número máximo de archivos es de 5 y el tamaño máximo de cada archivo es de 2 MB",
                        icon: "warning",
                        confirmButtonText: "OK",
                    })
                }
                this.files.forEach(file => {
                    if (!validar_peso(file)) {
                        this.value = ""
                        return
                    }
                })
            })

            form_pagos.addEventListener('submit', function(event) {
                event.preventDefault();
                btnSubmit.setAttribute('disabled', 'true');

                if (this.numero_comprobante.value.length < 6) {
                    $('#mensajeComprobante').text('El número de comprobante debe tener al menos 6 caracteres');
                    $('#mensajeComprobante').removeClass('d-none');
                    btnSubmit.removeAttribute('disabled');
                    return;
                } else {
                    $('#mensajeComprobante').addClass('d-none');
                }

                @if (!isset($factura->comprobante_pago))
                    if (this.comprobante_pago.files.length == 0) {
                        mensajeArchios.classList.remove('d-none');
                        btnSubmit.removeAttribute('disabled');
                        Swal.fire({
                            title: "Seleccione un archivo",
                            text: "Debe seleccionar un archivo para poder realizar el pago",
                            icon: "warning",
                            confirmButtonText: "OK",
                        });
                        return;
                    } else {
                        mensajeArchios.classList.add('d-none');
                    }
                @endif

                this.submit();
                btnSubmit.removeAttribute('disabled');
            });
        }

        function validar_peso(file, pesoMax = 2097152) {
            if (file.size > pesoMax) {
                Swal.fire({
                    title: "Archivo muy pesado",
                    html: `El archivo: <strong>${file.name}</strong> excede el peso limite de 2MB`,
                    icon: "warning",
                    confirmButtonText: "OK",
                })
                return false;
            }
            return validarExtensionArchivo(file);
        }

        function validarExtensionArchivo(file) {
            const extensionesValidas = ['jpg', 'jpeg', 'png', ];

            const extension = file.name.toLowerCase().split('.').pop();
            if (!extensionesValidas.includes(extension)) {
                Swal.fire({
                    title: "Tipo de archivo no válido",
                    html: `Solo se permite imagenes de tipo <strong>${extensionesValidas.join(', ')}</strong>`,
                    icon: "warning",
                    confirmButtonText: "OK",
                })
                return false;
            }
            return true;
        }
    </script>
@endsection
