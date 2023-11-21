@extends('auth.layouts.app')
@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <!--begin::Card-->
                        <form class="form" action="{{ route('detalles.guardar') }}" method="POST">
                            <div class="card card-custom card-sticky" id="kt_page_sticky_card">
                                <div class="card-header flex-wrap py-5">
                                    <div class="card-title">
                                        <h3 class="card-label">Detalles Cotizaciones </h3>
                                    </div>
                                    <div class="card-toolbar">
                                        <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                            <div class="btn-group" role="group" aria-label="">
                                                <a href="{{ route('detalles.listado') }}" class="btn btn-secondary btn-icon"
                                                    data-toggle="tooltip" title="Listado Detalles"><i style="color:#000000"
                                                        class="la la-long-arrow-left "></i></a>

                                                <a href="{{ route('cotizarPlantilla1.index', 0) }}"
                                                    class="btn btn-primary btn-icon " data-toggle="tooltip"
                                                    title="Cotizar"><i class="la la-calculator"></i></a>
                                                <button type="submit" class="btn btn-success btn-icon"
                                                    data-toggle="tooltip" title="Guardar" onMouseMove="detalles()"><i
                                                        class="la la-save"></i></button>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @include('soporte.admin.capacitaciones.cotizar.plantilla1._form')
                                </div>
                            </div>
                            <!--end::Card-->
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
