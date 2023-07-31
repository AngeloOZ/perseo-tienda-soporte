<form id="form-datos-facturacion" method="POST" action="{{ route('tienda.guardarcompra') }}" enctype="multipart/form-data">
    @csrf
    <div class="card-body p-0">
        <div class="form-group mb-3 row">
            <div class="col-12 mb-4 col-md-6 mb-md-0">
                <label>Cédula/RUC</label>
                <div id="spinner">
                    <input type="text" class="form-control {{ $errors->has('identificacion') ? 'is-invalid' : '' }}"
                        name="identificacion" readonly value="{{ $cliente->ruc ?? '' }}" id="idCliente">
                </div>
                @error('identificacion')
                    <span class="text-danger">{{ $errors->first('identificacion') }}</span>
                @enderror
            </div>

            <div class="col-12 mb-4 col-md-6 mb-md-0">
                <label>Nombres/Empresa</label>
                <input type="text" class="form-control {{ $errors->has('nombre') ? 'is-invalid' : '' }}"
                    name="nombre" readonly value="{{ $cliente->nombre ?? '' }}" id="nombreCliente" />
                @error('nombre')
                    <span class="text-danger">{{ $errors->first('nombre') }}</span>
                @enderror
            </div>
        </div>
        <div class="form-group mb-3 row">
            <div class="col-12 mb-4 col-md-6 mb-md-0">
                <label>Dirección</label>
                <input type="text" class="form-control {{ $errors->has('direccion') ? 'is-invalid' : '' }} "
                    name="direccion" readonly value="{{ $cliente->direccion ?? '' }}" id="direccionCliente" />
                @error('direccion')
                    <span class="text-danger">{{ $errors->first('direccion') }}</span>
                @enderror
            </div>
            <div class="col-12 mb-4 col-md-6 mb-md-0">
                <label>Correo electrónico</label>
                <input type="email" class="form-control {{ $errors->has('correo') ? 'is-invalid' : '' }}"
                    name="correo" readonly value="{{ $cliente->correo ?? '' }}" id="correoCliente" />
                @error('correo')
                    <span class="text-danger">{{ $errors->first('correo') }}</span>
                @enderror
            </div>

        </div>
        <div class="form-group mb-3 row">
            <div class="col-12 mb-4 col-md-6 mb-md-0">
                <label>Teléfono</label>
                <input type="tel" class="form-control {{ $errors->has('telefono') ? 'is-invalid' : '' }} "
                    name="telefono" id="inputTelefono" readonly value="{{ $cliente->telefono ?? '' }}" />
                @error('telefono')
                    <span class="text-danger">{{ $errors->first('telefono') }}</span>
                @enderror
            </div>
            <div class="col-12 mb-4 col-md-6 mb-md-0">
                <label for="inputObservacion">Observación</span></label>
                <textarea class="form-control {{ $errors->has('observacion') ? 'is-invalid' : '' }}" style="resize: none" readonly
                    name="observacion" rows="1">{{ $cliente->observacion ?? '' }}</textarea>
                @error('observacion')
                    <span class="text-danger">{{ $errors->first('observacion') }}</span>
                @enderror
            </div>
        </div>
        <div class="form-group mb-3">
            <label>Forma de pago</label>
            <div class="row">
                <div class="col-lg-6">
                    <label class="option">
                        <span class="option-control">
                            <span class="radio">
                                <input type="radio" name="tipo_pago" id="tipoPagoTransferencia"
                                    {{ $carrito->tipo_pago == 'transferencia' ? 'checked' : '' }} value="1" />
                                <span></span>
                            </span>
                        </span>
                        <span class="option-label">
                            <span class="option-head">
                                <span class="option-title">
                                    Transferencia bancaria
                                </span>
                            </span>
                            <span class="option-body">Pago con transferencia o deposito no tiene recargo</span>
                        </span>
                    </label>
                </div>
                <div class="col-lg-6">
                    <label class="option">
                        <span class="option-control">
                            <span class="radio">
                                <input type="radio" {{ $vendedor->correo_pagoplux == null ? 'disabled' : '' }}
                                    name="tipo_pago" id="tipoPagoTarjeta"
                                    {{ $carrito->tipo_pago == 'tarjeta' ? 'checked' : '' }} value="2" />
                                <span></span>
                            </span>
                        </span>
                        <span class="option-label">
                            <span class="option-head">
                                <span class="option-title">
                                    Tarjeta de credito/debito
                                </span>
                            </span>
                            <span class="option-body">
                                @if ($vendedor->correo_pagoplux)
                                    @if ($vendedor->recargo_pagoplux != 0)
                                        El pago con tarjeta de credito tiene un recargo adicional
                                    @else
                                        El pago con tarjeta de credito no tiene recargo adicional
                                    @endif
                                @else
                                    El pago con tarjeta no está habilitado para tu distribuidor
                                @endif
                            </span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        @if ($carrito->tipo_pago == 'transferencia')
            <div class="form-group mb-1">
                <div class="d-flex align-items-center" style="height: 50px">
                    <label for="btnInfoPago"
                        class="customFile font-weight-bolder font-size-h6 text-uppercase p-0 m-0">Comprobante de
                        pago</label>
                    <button type="button" class="btn btn-info btn-sm ml-4 confirm-delete" href="javascript:void(0)"
                        data-href="" data-toggle="tooltip" id="btnInfoPago">Ver cuentas de banco</button>
                </div>
                <input type="file" multiple class="form-control" name="comprobante_pago[]" id="comprobante_pago"
                    accept=".jpg, .jpeg, .png">
                <span class="text-dark-50" id="">El número máximo de archivos es de 5 y el tamaño máximo de
                    cada archivo es de 2 MB</span>
                <p class="text-danger d-none" id="mensajeArchios">Debe selecionar al menos un archivo</p>
            </div>
        @endif
        @if ($carrito->tipo_pago == 'tarjeta')
            <div id="ButtonPaybox" style="text-align: right"></div>
        @endif
        <input type="hidden" name="redireccion" id="inputRedireccion" value="false">
        <input type="hidden" name="productos" id="inputProductos">
        <input type="hidden" id="subTotal" value="0">
        <input type="hidden" id="total" value="{{ $carrito->total }}">
        <input type="hidden" name="referido" value="{{ $vendedor->usuariosid }}">
        @if (isset($_COOKIE['cupon_code']))
            <input type="hidden" name="cupon_code" value="{{ $_COOKIE['cupon_code'] }}">
        @endif
        @if ($carrito->tipo_pago == 'tarjeta')
            <input type="hidden" name="" id="idHtmlPay">
            <input type="hidden" name="id_transaccion" id="id_transaccion">
            <input type="hidden" name="voucher" id="voucher">
            <input type="hidden" name="nombre_tarjeta" id="nombre_tarjeta">
        @endif
    </div>

    <div class="row mt-5 d-flex justify-content-lg-start flex-row-reverse">
        <div class="col-12 col-lg-4">
            <button type="submit" id="btnSendMessage" {{ !$carrito->tipo_pago ? 'disabled' : '' }}
                class="btn btn-success mr-2 w-100">Finalizar orden</button>
        </div>
        <div class="col-12 mt-4 col-lg-4 mt-lg-0">
            <a href="{{ route('tienda.checkout', $vendedor->usuariosid) }}" type="button"
                class="btn btn-primary mr-2 w-100">Regresar</a>
        </div>
    </div>
</form>
