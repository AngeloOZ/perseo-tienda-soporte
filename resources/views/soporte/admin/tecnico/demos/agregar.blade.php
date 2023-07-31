@extends('soporte.auth.layouts.app')
@section('title_page', 'Agregar soporte especial')

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form action="{{ route('sop.registrar_soporte_especial') }}" method="POST">
                            <div class="card card-custom" id="kt_page_sticky_card">
                                {{-- Inicio de tabs buttons --}}
                                <div class="card-header d-block">
                                    <div class="d-flex justify-content-between flex-wrap my-3" style="">
                                        <div class="card-title">
                                            <h3 class="card-label"> Soporte </h3>
                                        </div>
                                        <div class="card-toolbar">
                                            <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                                <div class="btn-group" role="group" aria-label="First group">

                                                    <a href="{{ route('soporte.revisor_listar_soporte_especial') }}"
                                                        class="btn btn-secondary btn-icon" data-toggle="tooltip"
                                                        title="Volver"><i class="la la-long-arrow-left"></i></a>

                                                    <button type="submit" class="btn btn-success btn-icon"
                                                        data-toggle="tooltip" title="Guardar"><i class="la la-save"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @csrf
                                    @include('soporte.admin.tecnico.demos.inc.datos')
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
