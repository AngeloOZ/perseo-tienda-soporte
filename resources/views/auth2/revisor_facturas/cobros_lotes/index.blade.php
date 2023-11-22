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
                                    <form action="{{ route('pagos.lotes.post') }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <div class="d-flex">
                                            <div class="form-group">
                                                <label for="">Plantilla CSV</label>
                                                <input type="file" class="form-control" name="csv" accept=".csv">
                                            </div>
                                            <button class="btn btn-primary align-self-center ml-3">Subir archivo</button>
                                        </div>
                                    </form>
                                </div>
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Secuencia</th>
                                            <th>Fecha</th>
                                            <th>Banco Origen</th>
                                            <th>Banco Destino</th>
                                            <th>Comprobante</th>
                                            <th>Monto</th>
                                            <th>Origen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cobros as $cobro)
                                            <tr data-id-factura="{{ $cobro->facturaid }}">
                                                <td>{{ $cobro->facturaid }}</td>
                                                <td>{{ $cobro->secuencia }}</td>
                                                <td>{{ $cobro->fecha }}</td>
                                                <td>
                                                    {{ $bancosOrigen->where('bancocid', $cobro->banco_destino)->first()->descripcion }}
                                                </td>
                                                <td>
                                                    {{ $bancosDestino->where('bancoid', $cobro->banco_destino)->first()->descripcion }}
                                                </td>
                                                <td>{{ $cobro->numero_comprobante }}</td>
                                                <td>{{ $cobro->monto }}</td>
                                                <td>{{ $cobro->origen }}</td>
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
