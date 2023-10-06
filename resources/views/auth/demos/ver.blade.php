@extends('auth.layouts.app')
@section('titulo', 'Ver registro: ' . $soporte->soporteid)

@section('contenido')
    <style>
        .loader-licenciador {
            width: 175px;
            height: 80px;
            display: block;
            margin: auto;
            background-image: radial-gradient(circle 25px at 25px 25px, #FFF 100%, transparent 0), radial-gradient(circle 50px at 50px 50px, #FFF 100%, transparent 0), radial-gradient(circle 25px at 25px 25px, #FFF 100%, transparent 0), linear-gradient(#FFF 50px, transparent 0);
            background-size: 50px 50px, 100px 76px, 50px 50px, 120px 40px;
            background-position: 0px 30px, 37px 0px, 122px 30px, 25px 40px;
            background-repeat: no-repeat;
            position: relative;
            box-sizing: border-box;
        }

        .loader-licenciador::after {
            content: '';
            left: 0;
            right: 0;
            margin: auto;
            bottom: 20px;
            position: absolute;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 5px solid transparent;
            border-color: #FF3D00 transparent;
            box-sizing: border-box;
            animation: rotation-licenciador 1s linear infinite;
        }

        @keyframes rotation-licenciador {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .contenedor-loader {
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 99999;
        }
    </style>
    <div class="contenedor-loader d-none" id="spinnerLicenciador">
        <span class="loader-licenciador"></span>
    </div>
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
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

                                                <a href="{{ route('demos.listado') }}" class="btn btn-secondary btn-icon"
                                                    data-toggle="tooltip" title="Volver"><i
                                                        class="la la-long-arrow-left"></i></a>

                                                @if ($soporte->tipo == 1 && !$isRegisterLite)
                                                    <a href="{{ route('demos.convertir.lite', $soporte->soporteid) }}"
                                                        class="btn btn-primary btn-icon" data-toggle="tooltip"
                                                        title="Convertir a LITE">
                                                        <i class="la la-sync-alt"></i>
                                                    </a>
                                                @endif

                                                @if ($soporte->lite_liberado == 0 && $soporte->tipo == 3)
                                                    <button id="btnLiberar" type="button" class="btn btn-info btn-icon"
                                                        data-toggle="tooltip" title="Liberar lite"><i
                                                            class="la la-rocket"></i></button>
                                                @endif
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
                                </ul>
                            </div>
                            {{-- Fin de tabs buttons --}}
                            {{-- Contenido TABS --}}
                            <div class="tab-content " id="myTabContent1">
                                <div class="tab-pane 1fade show active" id="datosTab" role="tabpanel"
                                    aria-labelledby="datos-tab">
                                    <div class="card-body">
                                        @csrf
                                        @include('auth.demos.inc._form')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="archivos-1" role="tabpanel" aria-labelledby="archivos-tab-1">
                                    <div class="card-body">
                                        @include('soporte.admin.tecnico.demos.inc.list_actividades')
                                    </div>
                                </div>
                            </div>
                            {{-- Fin Contenido TABS --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($soporte->lite_liberado == 0)
    @section('script')
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script>
            const spiner = document.getElementById('spinnerLicenciador');
            const btnLiberar = document.getElementById('btnLiberar');
            const solicitudLicencidador = {
                cliente: {
                    identificacion: "{{ $soporte->ruc }}",
                    nombres: "{{ $soporte->razon_social }}",
                    direccion: "Quito",
                    correos: "{{ $soporte->correo }}",
                    telefono2: "{{ $soporte->whatsapp }}",
                    sis_distribuidoresid: "{{ $vendedorSIS->sis_distribuidoresid ?? null }}",
                    sis_vendedoresid: "{{ $vendedorSIS->sis_revendedoresid ?? null }}",
                    contador: "0",
                    promocion: 0,
                },
                // COMMENT: 1000 es LITE
                licencia: [{
                    producto_id: 1000
                }],
            }

            btnLiberar?.addEventListener('click', function() {
                this.setAttribute('disabled', 'true');
                spiner.classList.remove('d-none');

                let url = "{{ route('demos.liberar.lite', 'cad') }}";
                url = url.replace('cad', "{{ $soporte->soporteid }}")

                peticionLiberarProducto(url);
            })

            function peticionLiberarProducto(url) {
                axios.post(url, {
                        _token: '{{ csrf_token() }}',
                        soporteid: "{{ $soporte->soporteid }}",
                        licenciador: solicitudLicencidador,
                        productos: [{
                            producto_id: 1000
                        }]
                    })
                    .then(data => {
                        if (data.data.status == 200) {
                            Swal.fire({
                                title: "Licencias liberadas",
                                text: data.data.message,
                                icon: "success",
                                timer: 2500
                            }).then(onCloseAlert)
                        } else {
                            Swal.fire({
                                title: "Licencias liberadas con errores",
                                text: data.data.message,
                                icon: "info",
                                timer: 2500
                            }).then(onCloseAlert)
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        const mensaje = error?.response?.data?.message ||
                            "Hubo un error al liberar la licencia, intentálo más tarde";
                        Swal.fire({
                            title: "No se pudo liberar",
                            text: mensaje,
                            icon: "error",
                        }).then(onCloseAlert)
                    })
                    .finally(() => {
                        spiner.classList.add('d-none');
                        btnLiberar.removeAttribute('disabled')
                    });
            }

            function onCloseAlert() {
                location.reload();
            }
        </script>
    @endsection
@endif
