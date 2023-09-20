@extends('soporte.auth.layouts.app')
@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form id="formulario" class="form" action="{{ route('planificaciones.actualizar', $planificaciones->planificacionesid) }}"
                            method="POST">
                            @method('PUT')
                            <div class="card card-custom">
                                <div class="card-header flex-wrap py-5" style="position: sticky; background-color: white">
                                    <div class="card-title">
                                        <h3 class="card-label"> Planificaciones </h3>
                                    </div>
                                    <div class="card-toolbar">
                                        <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                            <div class="btn-group" role="group" aria-label="First group">

                                                <a href="{{ route('planificaciones.index') }}" class="btn btn-secondary btn-icon"
                                                    data-toggle="tooltip" title="Volver"><i
                                                        class="la la-long-arrow-left"></i></a>

                                                <button type="submit" class="btn btn-success btn-icon"
                                                    data-toggle="tooltip" title="Guardar"><i
                                                        class="la la-save"></i></button>

                                                <a href="{{ route('planificaciones.crear') }}" class="btn btn-warning btn-icon"
                                                    data-toggle="tooltip" title="Nuevo"><i class="la la-user-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @include('soporte.admin.capacitaciones.planificaciones._form')
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
