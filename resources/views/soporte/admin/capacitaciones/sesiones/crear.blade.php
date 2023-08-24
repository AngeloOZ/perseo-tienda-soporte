@extends('soporte.auth.layouts.app')
@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form id="formulario" class="form" action="{{ route('sesiones.guardar') }}" method="POST">
                            <div class="card card-custom" id="kt_page_sticky_card">
                                <div class="card-header flex-wrap py-5">
                                    <div class="card-title">
                                        <h3 class="card-label">Sesiones </h3>
                                    </div>

                                    <div class="card-toolbar">
                                        <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                            <div class="btn-group" role="group" aria-label="">
                                                <a href="{{ route('sesiones.indexVista') }}"
                                                    class="btn btn-secondary btn-icon" data-toggle="tooltip"
                                                    title="Volver"><i class="la la-long-arrow-left"></i></a>

                                                <button type="submit" class="btn btn-success btn-icon" data-toggle="tooltip"
                                                    title="Guardar"><i class="la la-save "></i></button>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="card-body">
                                    @include('soporte.admin.capacitaciones.sesiones._form')
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


