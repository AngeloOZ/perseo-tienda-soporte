@extends('auth.layouts.app')

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form class="form" method="POST" enctype="multipart/form-data">
                            <div class="card card-custom" id="kt_page_sticky_card">
                                <div class="card-header flex-wrap py-5" style="position: sticky; background-color: white">
                                    <div class="card-title">
                                        <h3 class="card-label"> Factura </h3>
                                    </div>
                                    <div class="card-toolbar">
                                        <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                            <div class="btn-group" role="group" aria-label="First group">
                                                <a href="{{ route('facturas.listado') }}" class="btn btn-secondary btn-icon" data-toggle="tooltip"
                                                    title="Volver"><i class="la la-long-arrow-left"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <embed src="data:application/pdf;base64,{{ $pdf }}" type="application/pdf" width="100%" height="650px"/>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
