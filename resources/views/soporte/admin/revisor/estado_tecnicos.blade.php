@extends('soporte.auth.layouts.app')
@section('title_page', 'Listado de técnicos')
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
                                    <h3 class="card-label"> Listado de técnicos </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Identificación</th>
                                                <th scope="col">Nombres</th>
                                                <th scope="col">Estado</th>
                                                <th scope="col">Hora de ingreso</th>
                                                <th scope="col">Hora de salida</th>
                                                <th scope="col" class="text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tecnicos as $key => $item)
                                                <tr>
                                                    <th>{{ $key + 1 }}</th>
                                                    <td>{{ $item->identificacion }}</td>
                                                    <td>{{ $item->nombres }}</td>
                                                    <td>
                                                        @if ($item->activo == 1)
                                                            <span class="label label-inline label-success font-weight-bold">
                                                                Conectado </span>
                                                        @else
                                                            <span
                                                                class="label label-inline label-light-danger font-weight-bold">
                                                                Desconectado </span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->fecha_de_ingreso }}</td>
                                                    <td>{{ $item->fecha_de_salida }}</td>
                                                    <td class="text-center">
                                                        <a href="{{ route('soporte.cambiar.disponibilidad', $item->tecnicosid) }}"
                                                            @class([
                                                                'btn btn-icon btn-circle btn-sm ml-8',
                                                                'btn-success' => $item->estado == 0,
                                                                'btn-danger' => $item->estado == 1,
                                                            ])>
                                                            <i class="fas fa-power-off"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!--end::Card-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
