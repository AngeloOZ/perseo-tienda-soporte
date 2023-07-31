@extends('soporte.auth.layouts.app')
@section('title_page', 'Editar soporte especial')

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form action="{{ route('sop.actualizar_soporte_especial', $soporte->soporteid) }}" method="POST">
                            @method('PUT')
                            @csrf
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
                                                    @include('soporte.admin.tecnico.demos.inc.toolbar')
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
                                        <li class="nav-item">
                                            <a class="nav-link" id="historial-tab-1" data-toggle="tab" href="#historial-1"
                                                aria-controls="historial">
                                                <span class="nav-icon">
                                                    <i class="flaticon-analytics"></i>
                                                </span>
                                                <span class="nav-text">Historial</span>
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
                                            @include('soporte.admin.tecnico.demos.inc.datos_editar')
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="archivos-1" role="tabpanel"
                                        aria-labelledby="archivos-tab-1">
                                        <div class="card-body">
                                            @include('soporte.admin.tecnico.demos.inc.list_actividades')
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="historial-1" role="tabpanel"
                                        aria-labelledby="historial-tab-1">
                                        <div class="card-body">
                                            @include('soporte.admin.inc.historial')
                                        </div>
                                    </div>
                                </div>
                                {{-- Fin Contenido TABS --}}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modal')
    @include('soporte.admin.tecnico.demos.inc.modal')
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        var KTSummernoteDemo = function() {
            var demos = function() {
                $('.summernote').summernote({
                    height: 300,
                    placeholder: "Escribe tu texto aquí",
                });
            }

            return {
                init: function() {
                    demos();
                }
            };
        }();

        jQuery(document).ready(function() {
            KTSummernoteDemo.init();
            const btnSendMail = document.getElementById('btnSendMail');

            btnSendMail.addEventListener('click', async function() {
                this.setAttribute('disabled', 'true');
                const data = validateData();
                if (data) {
                    await sendRequestMail(data);
                    limpiarCampos();
                    $("#closeModal").click();
                }
                this.removeAttribute('disabled');
            });
        });


        function validateData() {
            if ($('#kt_summernote_1').val().length <= 5) {
                Swal.fire("Contenido no válido", "El contenido del mensaje de la actividad es demasiado corta",
                    "error");
                return;
            }

            const data = {
                _token: "{{ csrf_token() }}",
                contenido: $('#kt_summernote_1').summernote('code'),
            };

            return data;
        }

        async function sendRequestMail(data) {
            try {
                const {
                    data: solicitud
                } = await axios.post("{{ route('sop.registrar_actividad_soporte', $soporte->soporteid) }}", data);
                await Swal.fire("Actividad registrada", "La actividad se registro con exito", "success");
                location.reload();
            } catch (error) {
                console.log(error);
                $("#btnSendMail").removeAttr('disabled');
                Swal.fire("Oops...",
                    "No se pudo registrar la actividad, inténtalo de nuevo", "error");
            }
        }

        function limpiarCampos() {
            $('#kt_summernote_1').summernote('code', '');
        }
    </script>
@endsection
