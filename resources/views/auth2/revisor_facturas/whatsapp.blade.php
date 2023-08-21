@extends('auth2.layouts.app')
@section('title_page', 'Configuración de WhatsApp')
@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-custom card-sticky" id="kt_page_sticky_card">
                            <div class="card-header flex-wrap py-1">
                                <div class="card-title">
                                    <h3 class="card-label"> Configurar WhatsApp </h3>
                                </div>
                                <div class="card-toolbar">
                                    <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                        <div class="btn-group" role="group" aria-label="">
                                            <a href="{{ route('redirect.rol') }}" class="btn btn-secondary btn-icon"
                                                data-toggle="tooltip" title="Volver">
                                                <i class="la la-long-arrow-left"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                @csrf
                                <div class="row">
                                    <div class="col-12 user-select-none mb-5" style="font-size: 17px;">
                                        <div class="d-flex align-items-center">
                                            <strong class="mr-5">Estado: </strong>
                                            <i style="font-size: 11px" @class([
                                                'fas fa-circle',
                                                'text-success' => $estado_whatsapp,
                                                'text-danger' => !$estado_whatsapp,
                                            ])></i>
                                            <span
                                                class="ml-1">{{ $estado_whatsapp ? 'Conectado' : 'No conectado' }}</span>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-8">
                                        <button id="btnIniciar" {{ $estado_whatsapp ? 'disabled' : '' }}
                                            class="btn btn-primary">Iniciar</button>
                                        <button id="btnQR" disabled class="btn btn-success">Obtener QR</button>
                                        <button id="btnCerrar" {{ !$estado_whatsapp ? 'disabled' : '' }}
                                            class="btn btn-danger">Cerrar
                                            Sesión</button>
                                        <button id="btnEliminarToken" class="btn btn-warning">Eliminar token</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const btnIniciar = document.getElementById('btnIniciar');
        const btnQR = document.getElementById('btnQR');
        const btnCerrar = document.getElementById('btnCerrar');
        const btnEliminarToken = document.getElementById('btnEliminarToken');

        btnIniciar.addEventListener('click', iniciar);
        btnQR.addEventListener('click', obtenerQR);
        btnCerrar.addEventListener('click', cerrarSesion);
        btnEliminarToken.addEventListener('click', eliminarToken);

        async function iniciar() {
            try {
                this.setAttribute('disabled', 'true');
                const body = {
                    _token: '{{ csrf_token() }}',
                    apiKey: $('#numerosWhats').val()
                }
                const {
                    data: result
                } = await axios.post("{{ route('facturas.whatsapp.iniciar') }}", body);

                mostrarAlertas(result.sms, "success", 1000);
                btnQR.removeAttribute('disabled');
                btnCerrar.removeAttribute('disabled');

            } catch (error) {
                mostrarAlertas("No se pudo iniciar el servicio", "error");
                this.removeAttribute('disabled');
            }
        }

        async function obtenerQR() {
            try {
                this.setAttribute('disabled', 'true');
                const body = {
                    _token: '{{ csrf_token() }}',
                    apiKey: $('#numerosWhats').val()
                }
                const {
                    data: result
                } = await axios.post("{{ route('facturas.whatsapp.obtener.qr') }}", body);


                if (result.qrcode == null) {
                    mostrarAlertas(`No se pudo obtener el código QR.\n${result?.message || ""}`, "error", 0);
                    this.removeAttribute('disabled');
                    return;
                }

                await Swal.fire({
                    title: "Código QR WhatsApp",
                    text: "Escanea el código para iniciar",
                    imageUrl: result.qrcode,
                    imageWidth: 200,
                    imageHeight: 200,
                    imageAlt: "Código QR WhatsApp",
                    timer: 15000
                });
                location.reload();

                this.removeAttribute('disabled');
            } catch (error) {
                console.log(error);
                mostrarAlertas(`No se pudo obtener el código QR.\n${error?.message || ""}`, "error");
            } finally {
                this.removeAttribute('disabled');
            }
        }

        async function cerrarSesion() {
            try {
                this.setAttribute('disabled', 'true');
                const body = {
                    _token: '{{ csrf_token() }}',
                    apiKey: $('#numerosWhats').val()
                }
                const {
                    data: result
                } = await axios.post("{{ route('facturas.whatsapp.cerrar') }}", body);
                await mostrarAlertas(result.message);
                location.reload();
            } catch (error) {
                console.log(error);
                mostrarAlertas('Hubo un error al cerrar sesión', 'error')
            }
        }

        async function eliminarToken() {
            try {
                this.setAttribute('disabled', 'true');
                const {
                    data: result
                } = await axios.post("{{ route('facturas.whatsapp.eliminar_token') }}");
                await mostrarAlertas(result.message);
                location.reload();
            } catch (error) {
                console.log(error);
                mostrarAlertas('Hubo un error al eliminar el token', 'error')
            }
        }

        function mostrarAlertas(title = "titulo", icon = "success", timer = 2000) {
            return Swal.fire({
                icon: icon,
                title: title,
                showConfirmButton: false,
                timer
            });
        }
    </script>
@endsection
