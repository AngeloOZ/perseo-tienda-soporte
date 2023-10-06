@extends('auth.layouts.app')
@section('titulo', 'Ver registro: ' . $soporte->soporteid)

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-custom" id="kt_page_sticky_card">
                            {{-- Inicio de tabs buttons --}}
                            <div class="card-header d-block">
                                <div class="d-flex justify-content-between flex-wrap mb-3" style="">
                                    <div class="card-title">
                                        <h3 class="card-label"> Soporte </h3>
                                    </div>
                                    <div class="card-toolbar">
                                        <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                            <div class="btn-group" role="group" aria-label="First group">

                                                <a href="{{ route('demos.listado') }}" class="btn btn-secondary btn-icon"
                                                    data-toggle="tooltip" title="Volver"><i
                                                        class="la la-long-arrow-left"></i></a>

                                                @if ($soporte->tipo == 1 && !$isRegisterLite)
                                                    <a href="{{ route('demos.convertir.lite', $soporte->soporteid) }}"
                                                        class="btn btn-primary btn-icon" data-toggle="tooltip"
                                                        title="Convertir a LITE">
                                                        <i class="la la-sync-alt"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <ul class="nav nav-pills mb-5" id="myTab1" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="datos-tab" data-toggle="tab" href="#datosTab">
                                            <span class="nav-icon">
                                                <i class="flaticon-interface-3"></i>
                                            </span>
                                            <span class="nav-text">Datos del soporte</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="archivos-tab-1" data-toggle="tab" href="#archivos-1"
                                            aria-controls="archivos">
                                            <span class="nav-icon">
                                                <i class="flaticon-piggy-bank"></i>
                                            </span>
                                            <span class="nav-text">Registro de actividad</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            {{-- Fin de tabs buttons --}}
                            {{-- Contenido TABS --}}
                            <div class="tab-content " id="myTabContent1">
                                <div class="tab-pane 1fade show active" id="datosTab" role="tabpanel"
                                    aria-labelledby="datos-tab">
                                    <div class="card-body">
                                        @csrf
                                        @include('auth.demos.inc._form')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="archivos-1" role="tabpanel" aria-labelledby="archivos-tab-1">
                                    <div class="card-body">
                                        @include('soporte.admin.tecnico.demos.inc.list_actividades')
                                    </div>
                                </div>
                            </div>
                            {{-- Fin Contenido TABS --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
