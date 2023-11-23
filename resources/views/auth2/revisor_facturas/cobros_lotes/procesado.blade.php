@php
    $bancosDestino = collect($bancos->destino);
    $bancosOrigen = collect($bancos->origen);
@endphp
@extends('auth2.layouts.app')
@section('title', 'Listado de cobros')
@section('contenido')
    <style>
        #kt_datatable td {
            padding: 3px;
        }
    </style>
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <!--begin::Card-->
                        <div class="card card-custom card-sticky" id="kt_page_sticky_card">
                            <div class="card-header ">
                                <div class="card-title">
                                    <h3 class="card-label"> Procesar cobros por lotes <small>(Revisor)</small></h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-5">
                                    <div class="text-right">
                                        <button id="btnIniciarRegistro" class="btn btn-primary">Iniciar registro de
                                            cobros</button>
                                    </div>
                                    <div class="progress d-none" id="progressBar">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                            role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Secuencia</th>
                                            <th>Fecha</th>
                                            <th>Banco Destino</th>
                                            <th>Comprobante</th>
                                            <th>Monto</th>
                                            <th>Tipo</th>
                                            <th>Origen</th>
                                            <th>Número cobro</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cobros as $cobro)
                                            <tr data-id-factura="{{ $cobro->facturaid }}">
                                                <td>{{ $cobro->facturaid }}</td>
                                                <td>{{ $cobro->secuencia }}</td>
                                                <td>{{ $cobro->fecha }}</td>
                                                <td>
                                                    {{ $bancosDestino->where('bancoid', $cobro->banco_destino)->first()->descripcion }}
                                                </td>
                                                <td>{{ $cobro->numero_comprobante }}</td>
                                                <td>{{ $cobro->monto }}</td>
                                                <td>{{ $cobro->tipo }}</td>
                                                <td>{{ $cobro->origen }}</td>
                                                <td class="numero-cobro"></td>
                                                <td>
                                                    <span class="badge badge-warning">Pendiente</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!--end::Card-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        $(document).ready(function() {
            const cobros = {{ Illuminate\Support\Js::from($cobros) }};
            $btnIniciar = document.getElementById('btnIniciarRegistro');
            $progressBar = document.getElementById('progressBar');

            $btnIniciar.addEventListener('click', iniciarRegistroCobros);


            async function iniciarRegistroCobros() {
                this.classList.add('d-none');
                $progressBar.classList.remove('d-none');
                let numeroProcesados = 0;

                for (const cobro of cobros) {
                    actualizarValorProgressBar(numeroProcesados * 100 / cobros.length);
                    const result = await registrarCobro(cobro);
                    actualizarEstadoCobro(cobro.facturaid, result.data, result.error);
                    numeroProcesados++;
                }

                $progressBar.classList.add('d-none');
            }

            async function registrarCobro(cobro) {
                try {
                    const body = {
                        _token: '{{ csrf_token() }}',
                        ...cobro,
                    }

                    const {
                        data: peticion
                    } = await axios.post("{{ route('cobros.registro.lotes') }}", body);

                    return {
                        data: peticion.data.codigo_nuevo,
                        error: null
                    }
                } catch (error) {
                    const response = {
                        data: null,
                        error: error.message
                    }

                    if (error.response) {
                        // El servidor respondió con un estado fuera del rango 2xx
                        response.error = error.response.data.message;
                    } else if (error.request) {
                        // La petición fue hecha pero no se recibió respuesta
                        response.error = error.request;
                    }

                    console.log(response);
                    return response;
                }
            }

            function actualizarEstadoCobro(facturaId, codigoCobro = null, messageError = null) {
                const $fila = document.querySelector(`tr[data-id-factura="${facturaId}"]`);
                const $numeroCobro = $fila.querySelector('.numero-cobro');
                const $badge = $fila.querySelector('td:last-child span');

                $badge.classList.remove('badge-warning');
                $badge.classList.add(codigoCobro ? 'badge-success' : 'badge-danger');
                $badge.textContent = codigoCobro ? 'Registrado' : 'No registrado';

                if (messageError) {
                    $badge.setAttribute('data-toggle', 'tooltip');
                    $badge.setAttribute('data-placement', 'top');
                    $badge.setAttribute('title', messageError);
                }

                $numeroCobro.textContent = codigoCobro;
            }

            function actualizarValorProgressBar(valor) {
                if (valor > 100) valor = 100;
                $progressBar.querySelector('.progress-bar').style.width = `${valor}%`;
                $progressBar.querySelector('.progress-bar').setAttribute('aria-valuenow', valor);
            }
        });
    </script>
@endsection
