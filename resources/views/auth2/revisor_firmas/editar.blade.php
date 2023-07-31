@extends('auth2.layouts.app')
@php
    $provincias = App\Models\Provincias::get();
    $nuevoid = str_pad($firma->provinciasid, '2', '0', STR_PAD_LEFT);
    $ciudades = App\Models\Ciudades::where('ciudadesid', 'like', $nuevoid . '%')->get();
@endphp
@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form class="form" action="{{ route('firma.actualizar', $firma->firmasid) }}" method="POST"
                            enctype="multipart/form-data">
                            @method('PUT')
                            <div class="card card-custom" id="kt_page_sticky_card">
                                <div class="card-header flex-wrap py-5" style="position: sticky; background-color: white">
                                    <div class="card-title">
                                        <h3 class="card-label"> Firma </h3>
                                    </div>
                                    <div class="card-toolbar">
                                        <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                            <div class="btn-group" role="group" aria-label="First group">

                                                <a href="{{ route('firma.revisor') }}" class="btn btn-secondary btn-icon"
                                                    data-toggle="tooltip" title="Volver"><i
                                                        class="la la-long-arrow-left"></i></a>

                                                <button type="submit" class="btn btn-success btn-icon"
                                                    data-toggle="tooltip" title="Guardar"><i class="la la-save"></i>
                                                </button>

                                                <a href="{{ route('firma.descarga', [$firma->firmasid, 1]) }}"
                                                    id="foto_ced_anv" class="btn btn-warning btn-icon" data-toggle="popover"
                                                    title="Cédula Anverso" data-html="true" data-placement="right">
                                                    <i class="la la-image"></i>
                                                </a>
                                                <a href="{{ route('firma.descarga', [$firma->firmasid, 2]) }}"
                                                    id="foto_ced_rev" class="btn btn-warning btn-icon" data-toggle="popover"
                                                    title="Cédula Reverso" data-html="true" data-placement="right">
                                                    <i class="la la-image"></i>
                                                </a>
                                                <a href="{{ route('firma.descarga', [$firma->firmasid, 3]) }}"
                                                    id="foto_perfil" class="btn btn-warning btn-icon" data-toggle="popover"
                                                    title="Foto" data-html="true" data-placement="right">
                                                    <i class="la la-user"></i>
                                                </a>

                                                @if ($firma->doc_ruc != '')
                                                    <a href="{{ route('firma.descarga', [$firma->firmasid, 4]) }}"
                                                        class="btn btn-danger btn-icon" data-toggle="tooltip"
                                                        title="RUC"><i class="far fa-file-pdf"></i></a>
                                                @endif
                                                @if ($firma->tipo_persona == 2)
                                                    <a href="{{ route('firma.descarga', [$firma->firmasid, 5]) }}"
                                                        class="btn btn-danger btn-icon" data-toggle="tooltip"
                                                        title="Constitución de la empresa"><i
                                                            class="far fa-file-pdf"></i></a>
                                                    <a href="{{ route('firma.descarga', [$firma->firmasid, 6]) }}"
                                                        class="btn btn-danger btn-icon" data-toggle="tooltip"
                                                        title="Nombramiento del representante legal"><i
                                                            class="far fa-file-pdf"></i></a>

                                                    @if ($firma->doc_aceptacion != '')
                                                        <a href="{{ route('firma.descarga', [$firma->firmasid, 7]) }}"
                                                            class="btn btn-danger btn-icon" data-toggle="tooltip"
                                                            title=" Aceptación del nombramiento "><i
                                                                class="far fa-file-pdf"></i></a>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @csrf
                                    <ul class="nav nav-pills mb-5" id="myTab1" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="datos-tab" data-toggle="tab"
                                                href="#datosTab">
                                                <span class="nav-icon">
                                                    <i class="flaticon-interface-3"></i>
                                                </span>
                                                <span class="nav-text">Datos</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="archivos-tab-1" data-toggle="tab" href="#archivos-1"
                                                aria-controls="archivos">
                                                <span class="nav-icon">
                                                    <i class="flaticon-attachment"></i>
                                                </span>
                                                <span class="nav-text">Archivos</span>
                                            </a>
                                        </li>
                                        @if ($firma->estado >= 2)
                                            <li class="nav-item">
                                                <a class="nav-link" id="solicitudEstado-tab-1" data-toggle="tab"
                                                    href="#solicitudEstado" aria-controls="archivos">
                                                    <span class="nav-icon">
                                                        <i class="flaticon-file-1"></i>
                                                    </span>
                                                    <span class="nav-text">Solicitud</span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                    <div class="tab-content mt-5" id="myTabContent1">
                                        <p style="height: 1px"></p>
                                        <div class="tab-pane fade show active" id="datosTab" role="tabpanel"
                                            aria-labelledby="datos-tab">
                                            @include('auth2.revisor_firmas.datos')
                                        </div>
                                        <div class="tab-pane fade" id="archivos-1" role="tabpanel"
                                            aria-labelledby="archivos-tab-1">
                                            @include('auth2.revisor_firmas.imagenes')
                                        </div>
                                        @if ($firma->estado >= 2)
                                            @include('auth2.revisor_firmas.panel_solicitud')
                                        @endif
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
    </div>
    </div>
@endsection
@section('script')
    <script>
        var foto = new KTImageInput('kt_image_1');
        var cedula1 = new KTImageInput('kt_image_2');
        var cedula2 = new KTImageInput('kt_image_3');

        $(document).ready(function() {
            $('#hombre').change(function() {
                if ($(this).is(':checked')) {
                    $('.hombre').css('background-color', '#babcc3');
                    $('.mujer').css('background-color', '#f8f8ff');
                }
            });
            $('#mujer').change(function() {
                if ($(this).is(':checked')) {
                    $('.hombre').css('background-color', '#f8f8ff');
                    $('.mujer').css('background-color', '#babcc3');
                }
            });

            // $('#por_pagar').change(function() {
            //     if ($(this).is(':checked')) {
            //         $('.por_pagar').css('background-color', '#babcc3');
            //         $('.pagado').css('background-color', '#f8f8ff');
            //     }
            // });
            // $('#pagado').change(function() {
            //     if ($(this).is(':checked')) {
            //         $('.por_pagar').css('background-color', '#f8f8ff');
            //         $('.pagado').css('background-color', '#babcc3');
            //     }
            // });
            actualizarRuc();
            // reenviarCorreo();
            cargarImagenes();
        });

        function cargarImagenes() {
            const cedulaAnverso = "data:image/png;base64,{{ $firma->foto_cedula_anverso }}"
            const cedulaReverso = "data:image/png;base64,{{ $firma->foto_cedula_reverso }}"
            const fotoPerfil = "data:image/png;base64,{{ $firma->foto }}"

            const foto_ced_anv = document.getElementById('foto_ced_anv');
            const foto_ced_rev = document.getElementById('foto_ced_rev');
            const foto_perfil = document.getElementById('foto_perfil');

            foto_ced_anv.setAttribute("data-content",
                `<img src="${cedulaAnverso}" class='img-thumbnail' alt='documentos perseo'>`);

            foto_ced_rev.setAttribute("data-content",
                `<img src="${cedulaReverso}" class='img-thumbnail' alt='documentos perseo'>`);

            foto_perfil.setAttribute("data-content",
                `<img src="${fotoPerfil}" class='img-thumbnail' alt='documentos perseo'>`);
        }

        function cambiarCiudad(id) {

            $.ajax({
                url: '{{ route('firma.recuperarciudades') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id.value
                },
                success: function(data) {
                    $('#ciudadesid').empty();


                    data.map(ciudades =>
                        $('#ciudadesid').append('<option value="' + ciudades.ciudadesid + '"  >' +
                            ciudades
                            .ciudad + '</option>')
                    );
                }
            })
        }

        function convertCedulaToRuc(cedula) {
            if (cedula.length == 13) {
                return cedula;
            } else if (cedula.length == 10) {
                return cedula + "001";
            } else {
                return "Identificación con longitud no válida";
            }
        }

        function actualizarRuc() {
            const inputIdent = document.getElementById('identificacion');
            inputIdent.addEventListener('blur', e => {
                const ruc = convertCedulaToRuc(e.target.value);
                document.getElementById('ruc_hidden').value = ruc;
            })
        }

        function reenviarCorreo() {
            $btnSendEmail = document.getElementById('btnSendEmail');
            $alertaSuccess = document.getElementById('alertaSuccess')
            $alertaError = document.getElementById('alertaError')

            $btnSendEmail.addEventListener('click', e => {
                $btnSendEmail.setAttribute('disabled', 'true');
                const body = {
                    _token: '{{ csrf_token() }}',
                    correo: $btnSendEmail.dataset.userEmail,
                    id_solicitud: $btnSendEmail.dataset.idSolicitudFirma,
                }
                $.ajax({
                    url: '{{ route('reenviar.correo') }}',
                    method: 'POST',
                    data: body,
                    success: function(data) {
                        $btnSendEmail.removeAttribute('disabled');
                        if (data == "enviado") {
                            $alertaSuccess.classList.remove('d-none');
                            setTimeout(() => {
                                $alertaSuccess.classList.add('d-none');
                            }, 3500);
                        } else {
                            $alertaError.classList.remove('d-none');
                            setTimeout(() => {
                                $alertaError.classList.add('d-none');
                            }, 3500);
                        }
                    },
                    error: function(error) {
                        console.error(error);
                        $alertaError.classList.remove('d-none');
                        setTimeout(() => {
                            $alertaError.classList.add('d-none');
                        }, 3500);
                        $btnSendEmail.removeAttribute('disabled');
                    }
                });
                $('body, html').animate({
                    scrollTop: '0px'
                });
            });
        }
    </script>
@endsection
