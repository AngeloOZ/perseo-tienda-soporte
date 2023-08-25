@extends('cliente.layouts.app')
@section('contenido') 

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <form class="form" action="{{ route('clientes.guardarClaveCliente') }}" method="POST">
                        @csrf
                        <div class="card card-custom card-sticky" id="kt_page_sticky_card">
                            <div class="card-header flex-wrap py-5">
                                <div class="card-title">
                                    <h3 class="card-label">Cambiar Contraseña </h3>
                                </div>
                                <div class="card-toolbar">
                                    <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                        <div class="btn-group" role="group" aria-label="">
                                            <a href="{{ route('sesiones.indexVistaCliente') }}" class="btn btn-secondary btn-icon" data-toggle="tooltip"
                                                title="Volver"><i class="la la-long-arrow-left"></i></a>

                                            <button type="submit" class="btn btn-success btn-icon" data-toggle="tooltip"
                                                title="Guardar"><i class="la la-save"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                @csrf
                                <div class="form-group row">
                                    <div class="col-lg-6">
                                        <label>Ingrese Nueva Contraseña:</label>
                                        <input type="password" class="form-control" placeholder="Ingrese Contraseña"
                                            name="de_clave" autocomplete="off" id="de_clave" />
                                        @if ($errors->has('de_clave'))
                                            <span class="text-danger">{{ $errors->first('de_clave') }}</span>
                                        @endif
                                    </div>
                                    <div class="col-lg-6">
                                        <label>Confirmar Nueva Contraseña:</label>
                                        <input type="password" class="form-control" placeholder="Ingrese Contraseña"
                                            name="de_clave_confirmation" autocomplete="off" id="de_clave_confirmation">
                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
