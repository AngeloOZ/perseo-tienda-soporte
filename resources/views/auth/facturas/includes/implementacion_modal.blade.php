@php
    $productos = collect(json_decode($factura->productos))->pluck('productoid');
    $soyContadoresIds = [62, 63, 64, 65];
    $interseccion = $productos->intersect($soyContadoresIds);
    $esWebContador = !$interseccion->isEmpty();
@endphp
<div id="implementacion-modal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <form method="POST" id="implementacion-link">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title h6">Registrar Implementación</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                </div>
                <div class="modal-body">
                    <h3 class="h6 mb-3">Datos de quien recibe la capacitación</h3>
                    @if ($esWebContador)
                        <div class="form-group">
                            <label for="identificacion2">Identificacion</label>
                            <input type="text" class="form-control" id="identificacion2" name="identificacion2"
                                value="{{ $factura->identificacion }}">
                            @if ($errors->has('identificacion2'))
                                <span class="text-danger">{{ $errors->first('identificacion2') }}</span>
                            @endif
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="nombre2">Nombre y Apellido</label>
                        <input type="tel" class="form-control" id="nombre2" name="nombre2"
                            value="{{ $factura->nombre }}">
                        @if ($errors->has('nombre2'))
                            <span class="text-danger">{{ $errors->first('nombre2') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="correo2">Correo electrónico</label>
                        <input type="tel" class="form-control {{ $errors->has('correo2') ? 'is-invalid' : '' }}"
                            id="correo2" name="correo2" value="{{ $factura->correo }}">
                        @if ($errors->has('correo2'))
                            <span class="text-danger">{{ $errors->first('correo2') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="whatsapp">Número de teléfono</label>
                        <input type="tel" class="form-control {{ $errors->has('whatsapp') ? 'is-invalid' : '' }}"
                            id="whatsapp" name="whatsapp" value="{{ str_replace(' ', '', $factura->telefono) }}"
                            oninput="validateNumber(this)">
                        @if ($errors->has('whatsapp'))
                            <span class="text-danger">{{ $errors->first('whatsapp') }}</span>
                        @endif
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-link mt-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnSendNumber" class="btn btn-primary mt-2">Registrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
