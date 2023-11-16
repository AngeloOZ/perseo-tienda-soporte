@extends('auth.layouts.app')

@section('contenido')
    @include('auth.facturas.liberar.inc.styles_loader')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-custom" id="kt_page_sticky_card">
                            <div class="card-header flex-wrap py-5" style="position: sticky; background-color: white">
                                <div class="card-title">
                                    <h3 class="card-label"> Liberar licencia Contafácil </h3>
                                </div>
                                <div class="card-toolbar">
                                    <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                        @include('auth.facturas.liberar_contafacil.inc.toolbar')
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
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
    @include('auth.facturas.liberar_contafacil.inc.modal_liberar')
@endsection
@section('script')
    @if ($factura->liberado == 0)
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script>
            $(document).ready(function() {
                const spiner = document.getElementById('spinnerLicenciador');
                const btnFormSubmit = document.getElementById('btnFormSubmit');
                const solicitudLicencidador = {
                    cliente: {
                        identificacion: "{{ $factura->identificacion }}",
                        nombres: "{{ $factura->nombre }}",
                        direccion: "{{ $factura->direccion }}",
                        correos: "{{ $factura->correo }}",
                        telefono2: "{{ $factura->telefono }}",
                        sis_distribuidoresid: Number("{{ $idDas }}"),
                        contador: 0,
                        promocion: 0
                    },
                    licencia: [{
                        producto_id: Number("{{ $contafacilALiberar->cantidad_empresas_ctf }}"),
                        promocion: 0
                    }]
                }

                $('.btn-modal-licencia').on('click', function(e) {
                    e.preventDefault();
                    $("#modal-licencia").modal("show");
                });

                const formLicencia = document.getElementById('formLicencia');

                formLicencia.addEventListener('submit', function(e) {
                    e.preventDefault();
                    this.btnFormSubmit.setAttribute('disabled', 'disabled');

                    solicitudLicencidador.cliente.identificacion = this.rucCliente.value;
                    solicitudLicencidador.cliente.nombres = this.nombresCliente.value;
                    solicitudLicencidador.cliente.direccion = this.direccionCliente.value;
                    solicitudLicencidador.cliente.correos = this.correoCliente.value;
                    solicitudLicencidador.cliente.telefono2 = this.telefonoCliente.value;

                    registrarLicencia(solicitudLicencidador);
                });

                async function registrarLicencia(solicitud) {
                    spiner.classList.remove('d-none');
                    const url = "{{ route('liberar.licencia.contafacil', $factura->facturaid) }}";

                    const body = {
                        _token: '{{ csrf_token() }}',
                        factura_id: "{{ $factura->facturaid }}",
                        licenciador: solicitud,
                        productos: {{ Illuminate\Support\Js::from($productos) }}
                    }

                    try {
                        const {
                            data
                        } = await axios.post(url, body);

                        const mensaje = data?.message || "La licencia se liberó correctamente";
                        const icon = data?.status == 200 ? "success" : "info";

                        Swal.fire({
                            title: "Licencia liberada",
                            text: mensaje,
                            icon: icon,
                        }).then(onCloseAlert);

                    } catch (error) {
                        const mensaje = error?.response?.data?.message ||
                            "Hubo un error al liberar la licencia, intentálo más tarde";

                        Swal.fire({
                            title: "No se pudo liberar",
                            text: mensaje,
                            icon: "error",
                        }).then(onCloseAlert);
                    } finally {
                        btnFormSubmit.removeAttribute('disabled');
                        $("#modal-licencia").modal("hide");
                        spiner.classList.add('d-none');
                    }
                }

                function onCloseAlert() {
                    location.reload();
                }
            })
        </script>
    @endif
@endsection
