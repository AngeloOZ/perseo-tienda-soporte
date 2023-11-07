@php
    $noRegistrado = !isset($cobro->cobros_id_perseo);
@endphp
@extends('auth2.layouts.app')
@section('title', 'Editar cobro')
@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form action="{{ route('cobros.actualizar_revisor', $cobro->cobrosid) }}" method="POST"
                            id="formCobros">
                            <div class="card card-custom" id="kt_page_sticky_card">
                                {{-- Toolbar --}}
                                <div class="card-header d-block">
                                    <div class="d-flex justify-content-between flex-wrap pt-2">
                                        <div class="card-title">
                                            <h3 class="card-label"> Editar cobro <small>(Revisor)</small></h3>
                                        </div>
                                        @include('auth2.revisor_facturas.inc.toolbar')
                                    </div>
                                </div>

                                <div class="card-body">
                                    @method('PUT')
                                    @csrf
                                    <div class="row mt-0">
                                        <div class="col-12 mt-3 col-lg-6 mt-md-0">
                                            <div class="form-group">
                                                <label style="flex-basis: 100%;">Ingresado por:</label>
                                                <div class="d-flex">
                                                    <input disabled class="form-control" value="{{ $vendedor->nombres }}" />
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label style="flex-basis: 100%;">Números de facturas</label>
                                                <div class="d-flex">
                                                    <input id="secuencias" readonly class="form-control"
                                                        value="{{ $cobro->secuencias }}" />
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-12 mb-2 col-md-6 mb-md-0">
                                                    <label>Banco de Origen <span class="text-danger">*</span></label>
                                                    <select name="banco_origen" class="form-control select2">
                                                        @foreach ($bancos->origen as $banco)
                                                            <option value="{{ $banco->bancocid }}"
                                                                {{ old('banco_origen', $cobro->banco_origen) == $banco->bancocid ? 'selected' : '' }}>
                                                                {{ $banco->descripcion }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-12 mb-2 col-md-6 mb-md-0">
                                                    <label>Banco de Destino <span class="text-danger">*</span></label>
                                                    <select name="banco_destino" class="form-control select2">
                                                        @foreach ($bancos->destino as $banco)
                                                            <option value="{{ $banco->bancoid }}"
                                                                {{ old('banco_destino', $cobro->banco_destino) == $banco->bancoid ? 'selected' : '' }}>
                                                                {{ $banco->descripcion }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-12 mb-2 col-md-6 mb-md-0">
                                                    <label>Número de comprobante <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control {{ $errors->has('numero_comprobante') ? 'is-invalid' : '' }}"
                                                        id="numero_comprobante" name="numero_comprobante"
                                                        placeholder="XXXXXXX"
                                                        value="{{ old('numero_comprobante', $cobro->numero_comprobante) }}" />
                                                    @error('numero_comprobante')
                                                        <span
                                                            class="text-danger">{{ $errors->first('numero_comprobante') }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-12 mb-2 col-md-6 mb-md-0">
                                                    <label>Estado del pago</label>
                                                    <select name="estado"
                                                        class="form-control {{ $errors->has('estado') ? 'is-invalid' : '' }}">
                                                        <option value="1" {{ $cobro->estado == 1 ? 'selected' : '' }}>
                                                            Registrado
                                                        </option>
                                                        <option value="2" {{ $cobro->estado == 2 ? 'selected' : '' }}>
                                                            Verificado
                                                        </option>
                                                        <option value="3" {{ $cobro->estado == 3 ? 'selected' : '' }}>
                                                            Rechazado
                                                        </option>
                                                    </select>
                                                    @error('estado')
                                                        <span class="text-danger">{{ $errors->first('estado') }}</span>
                                                    @enderror

                                                </div>

                                            </div>

                                            @if ($cobro->obs_vendedor != null)
                                                <div class="form-group">
                                                    <label for="">Obseración del pago vendedor</label>
                                                    <textarea class="form-control" readonly style="resize: none" rows="3">{{ $cobro->obs_vendedor }}</textarea>
                                                </div>
                                            @endif

                                            <div class="form-group">
                                                <label for="">Obseración del pago vendedor</label>
                                                <textarea class="form-control {{ $errors->has('obs_revisor') ? 'is-invalid' : '' }}" name="obs_revisor"
                                                    style="resize: none" rows="3">{{ $cobro->obs_revisor }}</textarea>
                                                @error('obs_revisor')
                                                    <span class="text-danger">{{ $errors->first('obs_revisor') }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        @if (isset($cobro->comprobante))
                                            <div class="col-12 mt-5 col-lg-6 mt-md-0">
                                                <h2 class="font-size-h3 mb-6">Fotos de comprobantes</h2>
                                                <div class="row g-2 d-flex justify-content-center">
                                                    @foreach (json_decode($cobro->comprobante) as $key => $item)
                                                        <a href="{{ route('cobros.descargar_comprobante', ['cobroid' => $cobro->cobrosid, 'id_comprobante' => $key]) }}"
                                                            target="_blank"
                                                            class="col-12 col-md-6 mb-2 text-decoration-none">
                                                            <img src="data:image/jpeg;base64, {{ $item }}"
                                                                style="border: 1px solid; width: 100%; height: 200px; object-fit: cover"
                                                                alt="comprobante">
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@if ($noRegistrado)
    @section('modal')
        @include('auth2.revisor_facturas.inc.modal_cobros')
    @endsection
@endif
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    <script>
        $(document).ready(function() {
            initTagify();
            init_subir_archivos();
        });

        $("#btnRegistrarCobro").click(function() {
            $("#modalCobros").modal("show");
            $("#modalFormCobros").attr("action", "{{ route('cobros.registrar.sistema') }}");
        });

        function initTagify() {
            var input = document.getElementById('secuencias');
            var tagify = new Tagify(input);
        }
    </script>
@endsection
