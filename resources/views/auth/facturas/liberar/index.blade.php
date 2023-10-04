@extends('auth.layouts.app')

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
                            <div class="card-header flex-wrap py-5" style="position: sticky; background-color: white">
                                <div class="card-title">
                                    <h3 class="card-label"> Liberación de productos </h3>
                                </div>
                                <div class="card-toolbar">
                                    <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                        @include('auth.facturas.liberar.inc.toolbar')
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                @include('auth.facturas.liberar.inc.alertas')
                                {{-- TODO: solo renovaciones los admin rol 2 --}}
                                @if ($factura->liberado == 0 && Auth::user()->rol === 2)
                                    <form id="formRenovacion" method="GET">
                                        @csrf
                                        <div class="form-group row">
                                            <div class="col-4">
                                                <label for="ruc">Ingrese un <strong>RUC</strong> en caso sea
                                                    <strong>renovación</strong></label>
                                                <input type="text" onkeypress="return validarNumero(event)"
                                                    maxlength="13" class="form-control" name="ruc" id="ruc"
                                                    value="{{ $ruc_renovacion }}">
                                                <span class="text-danger d-none" id="mensajeCedula">La identificación no es
                                                    válida</span>
                                            </div>
                                            <div class="col-2">
                                                <button class="btn btn-primary mt-8">Consultar</button>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                                @if ($promocion == 1)
                                    <div class="alert alert-custom alert-notice alert-light-primary fade show mb-5"
                                        role="alert">
                                        <div class="alert-icon">
                                            <i class="flaticon2-information"></i>
                                        </div>
                                        <div class="alert-text">Se ha aplicado un cupón de <strong>+3 Meses en planes
                                                anuales</strong></div>
                                        <div class="alert-close">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">
                                                    <i class="ki ki-close"></i>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                @include('auth.facturas.liberar.listado')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modal')
    @include('auth.facturas.liberar.inc.modal_liberar')
    @if ($licencias != null && $licencias->accion == 'renovar')
        @if ($licencias->facturito == true)
            @include('auth.facturas.liberar.inc.modal_facturito')
        @else
            @include('auth.facturas.liberar.inc.modal_normal')
        @endif
    @endif
@endsection
@if ($factura->liberado == 0)
    @section('script')
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        
        <script>
            const formRenovacion = document.getElementById('formRenovacion');
            const mensajeCedula = document.getElementById('mensajeCedula');

            formRenovacion?.addEventListener('submit', function(e) {
                e.preventDefault();
                if (this.ruc.value.length < 13) {
                    mensajeCedula.classList.remove('d-none');
                    return;
                }

                let url = "{{ route('facturas.ver.liberar', ['factura' => $factura->facturaid, 'ruc' => 'cad']) }}";
                url = url.replace('cad', this.ruc.value);
                location.href = url;
            })
        </script>

        @if ($contador->esContador)
            <script>
                @if (!$contador->error)
                    Swal.fire({
                        icon: 'info',
                        title: 'Atención',
                        text: 'Se ha detectado que estas tratando de liberar un plan soy contador, por favor verifique los datos de la licencia antes de liberar',
                        onClose: function() {
                            $("#modal-contador").modal("show");
                        }

                    })
                @else
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Parece que estás tratando de liberar varias licencias, entre ellas un plan soy contador, esto puede causar un conflicto con los datos a quien se libera las licencias, le recomendamos que lo realice manualmente en el administrador.',
                        onClose: function() {
                            $("#modal-contador").modal("show");
                        }
                    })
                @endif
            </script>
        @endif

        {{-- Liberaciones de planes nuevos --}}
        @if ($licencias == null || ($licencias != null && $licencias->accion == 'nuevo'))
            <script>
                $(document).on('click', '.modal-contador', function(e) {
                    e.preventDefault();
                    $("#modal-contador").modal("show");
                });

                const spiner = document.getElementById('spinnerLicenciador');
                const btnLiberar = document.getElementById('btnLiberar');
                const modalContador = document.getElementById('formContador');
                const listaProductos = {{ Illuminate\Support\Js::from($productos) }};
                const listaProductosLiberables = {{ Illuminate\Support\Js::from($productos_liberables) }};
                const solicitudLicencidador = {
                    cliente: {
                        identificacion: "{{ $factura->identificacion }}",
                        nombres: "{{ $factura->nombre }}",
                        direccion: "{{ $factura->direccion }}",
                        correos: "{{ $factura->correo }}",
                        telefono2: "{{ $factura->telefono }}",
                        sis_distribuidoresid: "{{ $vendedorSIS->sis_distribuidoresid }}",
                        sis_vendedoresid: "{{ $vendedorSIS->sis_revendedoresid }}",
                        contador: "0",
                        promocion: {{ $promocion }},
                    },
                    licencia: listaProductosLiberables,
                }

                modalContador?.addEventListener('submit', function(e) {
                    e.preventDefault();
                    solicitudLicencidador.cliente.identificacion = this.rucCliente.value;
                    solicitudLicencidador.cliente.nombres = this.nombresCliente.value;
                    solicitudLicencidador.cliente.direccion = this.direccionCliente.value;
                    solicitudLicencidador.cliente.correos = this.correoCliente.value;
                    solicitudLicencidador.cliente.telefono2 = this.telefonoCliente.value;
                    if (this.rucContador) {
                        solicitudLicencidador.cliente.contador = this.rucContador.value;
                    }
                    $("#btnLiberar").click();
                });

                btnLiberar?.addEventListener('click', function() {
                    this.setAttribute('disabled', 'true');
                    spiner.classList.remove('d-none');

                    let url = "{{ route('facturas.liberar_producto', 'cad') }}";
                    url = url.replace('cad', "{{ $factura->facturaid }}")

                    peticionLiberarProducto(url);
                })

                function peticionLiberarProducto(url) {
                    axios.post(url, {
                            _token: '{{ csrf_token() }}',
                            factura_id: "{{ $factura->facturaid }}",
                            licenciador: solicitudLicencidador,
                            productos: listaProductos
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
                    let url = "{{ route('facturas.ver.liberar', $factura->facturaid) }}";
                    location.href = url;
                }
            </script>

            {{-- Liberaciones para renovar --}}
        @elseif($licencias != null && $licencias->accion == 'renovar')
            <script>
                const spiner = document.getElementById('spinnerLicenciador');
                const btnLiberar = document.getElementById('btnLiberar');
                const formModalRenovacion = document.getElementById('formModalRenovacion');
                const listaProductosLiberables = {{ Illuminate\Support\Js::from($productos_liberables) }};
                const listaProductos = {{ Illuminate\Support\Js::from($productos) }};


                btnLiberar?.addEventListener('click', function() {
                    $("#renovacion-modal").modal("show");
                })

                formModalRenovacion.addEventListener('submit', function(e) {
                    e.preventDefault();
                    spiner.classList.remove('d-none');
                    const solicitudRenovacion = {
                        id_producto: Number.parseInt("{{ $licencias->id_producto }}"),
                        id_licencia: Number.parseInt("{{ $licencias->id_licencia }}"),
                        id_servidor: Number.parseInt("{{ $licencias->id_servidor }}"),
                        renovar: Number.parseInt(this.tiempoRenovacion.value),
                        sis_vendedoresid: "{{ $vendedorSIS->sis_revendedoresid }}",
                    }

                    if (this.recargaDocumentos) {
                        solicitudRenovacion.recarga = this.recargaDocumentos.value;
                    }

                    let url = "{{ route('facturas.renovar_licencia_producto', 'cad') }}";
                    url = url.replace('cad', "{{ $factura->facturaid }}");

                    preticionRenovarLicencia(url, solicitudRenovacion);
                    $("#renovacion-modal").modal("hide");
                });

                async function preticionRenovarLicencia(url, solicitud) {
                    try {
                        const response = await axios.post(url, {
                            _token: '{{ csrf_token() }}',
                            factura_id: "{{ $factura->facturaid }}",
                            renovacion: solicitud,
                            productos: listaProductos
                        });

                        spiner.classList.add('d-none');

                        const mensaje = response.data?.message || "Licencias renovada correctamente";

                        switch (response.status) {
                            case 200:
                                await Swal.fire({
                                    title: "Licencia renovadas",
                                    text: mensaje,
                                    icon: "success",
                                    // timer: 2500
                                });
                                break;
                            case 201:
                                await Swal.fire({
                                    title: "Licencia renovadas con errores",
                                    text: mensaje,
                                    icon: "warning",
                                    // timer: 2500
                                });
                                break;
                        }

                    } catch (error) {
                        console.error(error);
                        const mensaje = error?.response?.data?.message ||
                            "Hubo un error al renovar la licencia, intentálo más tarde o realice la renovación directamente en el licenciador";
                        await Swal.fire({
                            title: "No se pudo renovar",
                            text: mensaje,
                            icon: "error",
                            // timer: 2500
                        });
                    } finally {
                        spiner.classList.add('d-none');
                        onCloseAlert();
                    }
                }

                function onCloseAlert() {
                    let url = "{{ route('facturas.ver.liberar', $factura->facturaid) }}";
                    location.href = url;
                }
            </script>
        @endif
    @endsection
@endif
