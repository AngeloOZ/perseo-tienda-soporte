@php
    $tecnicoActividad = App\Models\Tecnicos::where('tecnicosid', $sesiones->tecnicosid)->first();
    
    $tecnico = App\Models\Tecnicos::where('tecnicosid', Auth::guard('admin')->user()->tecnicosid)
        ->where('distribuidoresid', $tecnicoActividad->distribuidoresid)
        ->where('subdistribuidoresid', $tecnicoActividad->subdistribuidoresid)
        ->first();
    
@endphp
@if ($tecnico)
    @extends('backend.layouts.app')
    @section('contenido')
        <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
            <div class="d-flex flex-column-fluid">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">

                            <form id="formulario" class="form" method="POST" enctype="multipart/form-data"
                                action="{{ route('sesiones.actualizar', $sesiones->sesionesid) }}">
                                @method('PUT')
                                <div class="card card-custom" id="kt_page_sticky_card">
                                    <div class="card-header flex-wrap py-5"
                                        style="position: sticky; background-color: white">
                                        <div class="card-title">
                                            <h3 class="card-label"> Sesiones </h3>
                                        </div>
                                        <div class="card-toolbar">
                                            <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                                <div class="btn-group" role="group" aria-label="First group">

                                                    <a href="{{ route('sesiones.indexVista') }}"
                                                        class="btn btn-secondary btn-icon" data-toggle="tooltip"
                                                        title="Volver"><i class="la la-long-arrow-left"></i></a>

                                                    <button id="botonSubmit" type="submit" class="btn btn-success btn-icon"
                                                        data-toggle="tooltip" title="Guardar"><i
                                                            class="la la-save"></i></button>



                                                    {{-- <a href="{{ route('sesiones.enviarCorreo', $sesiones->sesionesid) }}"
                                                        id="enviarCorreo"
                                                        @if ($sesiones->fechahorafin != null) class="btn btn-secondary btn-icon" @else class="btn btn-secondary btn-icon d-none" @endif
                                                        data-toggle="tooltip" title="Enviar Correo"><i
                                                            class="far fa-envelope-open"></i></a> --}}

                                                    <a href="{{ route('sesiones.crear') }}" class="btn btn-warning btn-icon"
                                                        data-toggle="tooltip" title="Nuevo"><i
                                                            class="la la-user-plus"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-content">
                                        <div class="card-body  tab-pane fade  active show" id="sesionesDiv">
                                            @include('soporte.admin.capacitaciones.sesiones._form')
                                        </div>

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
    @section('modal')
        @include('soporte.admin.capacitaciones.inc.delete_modal')
    @endsection
@else
    <script>
        location.href = "{{ route('tecnicosVista.404') }}"
    </script>

@endif
