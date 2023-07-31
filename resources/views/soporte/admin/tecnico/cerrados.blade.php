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
                                    <h3 class="card-label"> Listado de tickets en cerrados </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                @include('soporte.admin.inc.tabla')
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
    @include('soporte.admin.inc.script_tabla', ['name' => 'soporte.listado.cerrados'])
@endsection
