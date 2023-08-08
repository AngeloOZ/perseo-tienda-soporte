@extends('soporte.auth.layouts.app')
@section('title_page', 'Editar ticket')

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form action="{{ route('soporte.actualizar_estado_ticket', $ticket->ticketid) }}" method="POST">
                            <div class="card card-custom" id="kt_page_sticky_card">
                                {{-- Inicio de tabs buttons --}}
                                <div class="card-header d-block">
                                    <div class="d-flex justify-content-between flex-wrap mb-3" style="">
                                        <div class="card-title">
                                            <h3 class="card-label"> Ticket N° {{ $ticket->numero_ticket }}</h3>
                                        </div>
                                        <div class="card-toolbar">
                                            <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                                <div class="btn-group" role="group" aria-label="First group">
                                                    <a href="{{ route('soporte.listado.activos') }}"
                                                        class="btn btn-secondary btn-icon" data-toggle="tooltip"
                                                        title="Volver"><i class="la la-long-arrow-left"></i></a>
                                                    @if ($ticket->estado <= 2)
                                                        <button type="submit" class="btn btn-success btn-icon"
                                                            data-toggle="tooltip" title="Guardar"><i class="la la-save"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-primary btn-icon"
                                                            title="Escribir correo" data-toggle="modal"
                                                            data-target="#modalEmail"><i
                                                                class="la la-envelope-open-text"></i>
                                                        </button>
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
                                                <span class="nav-text">Datos ticket</span>
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
                                            @method('PUT')
                                            <input type="hidden" name="liberar_tecnico" value="true">
                                            <div class="form-group row ">
                                                <div class="col-12 mb-3 col-md-6 mb-md-0">
                                                    <label><span class="font-size-h6 font-weight-bold">Estado del
                                                            ticket<span>
                                                    </label>
                                                    <select class="form-control select2"
                                                        {{ $ticket->estado > 2 ? 'disabled' : '' }} name="estado">
                                                        <option value="1"
                                                            {{ $ticket->estado == '1' ? 'Selected' : '' }}>
                                                            Abierto</option>
                                                        <option value="2"
                                                            {{ $ticket->estado == '2' ? 'Selected' : '' }}>En
                                                            progreso</option>
                                                        <option value="3"
                                                            {{ $ticket->estado == '3' ? 'Selected' : '' }}>
                                                            Desarrollo</option>
                                                        <option value="4"
                                                            {{ $ticket->estado == '4' ? 'Selected' : '' }}>
                                                            Cerrado</option>
                                                        <option value="5"
                                                            {{ $ticket->estado == '5' ? 'Selected' : '' }}>
                                                            Cerrado (Sin respuesta)</option>
                                                        <option value="6"
                                                            {{ $ticket->estado == '6' ? 'Selected' : '' }}>
                                                            Cerrado (Problema general)</option>
                                                    </select>
                                                </div>

                                                <div class="col-12 mb-3 col-md-6 mb-md-0">
                                                    <label>Actividad de la empresa</label>
                                                    <input type="text" class="form-control" name="actividad_empresa"
                                                        value="{{ $ticket->actividad_empresa }}" />
                                                </div>
                                            </div>
                                            @include('soporte.admin.inc.datos_ticket')
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="archivos-1" role="tabpanel"
                                        aria-labelledby="archivos-tab-1">
                                        <div class="card-body">
                                            @include('soporte.admin.registro-actividades.registro')
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
    @include('soporte.admin.inc.modal_email')
@endsection
@section('script')
    <script>
        var KTSummernoteDemo = function() {
            var demos = function() {
                $('.summernote').summernote({
                    height: 250,
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
                Swal.fire("Contenido de correo no válido", "El contenido del mensaje de correo es demasiado corta",
                    "error");
                return;
            }

            const cliente = document.getElementById('rolCliente').value;
            const desarrollo = document.getElementById('rolDesarrollador').value;
            const supervisor = document.getElementById('rolSupervisor').value;
            const sendMail = document.getElementById('checkEnviarCorreo').checked;

            const data = {
                ticketid: "{{ $ticket->ticketid }}",
                contenido: $('#kt_summernote_1').summernote('code'),
                enviar_mail: sendMail,
            };

            if (sendMail && cliente == "" && tecnico == "" && supervisor == "") {
                Swal.fire("Destinatarios no seleccionado", "No ha seleccionado a quien enviar el correo", "error");
                return;
            }

            if (cliente != "") {
                data.cliente = cliente;
            }
            if (desarrollo != "") {
                data.desarrollo = desarrollo
            }
            if (supervisor != "") {
                data.supervisor = supervisor
            }
            return data;
        }

        async function sendRequestMail(data) {
            try {
                const sendMail = document.getElementById('checkEnviarCorreo').checked;
                let title = "Correo enviado";
                let sms = "El correo fue enviado correctamente";

                if (!sendMail) {
                    title = "Anotación registrada";
                    sms = "La anotación se ha guardado correctamente";
                }

                const {
                    data: solicitud
                } = await axios.post("{{ route('soporte.enviar_correo_cliente') }}", data);

                await Swal.fire(title, sms, "success");
                location.reload();

            } catch (error) {

                console.log(error);
                $("#btnSendMail").removeAttr('disabled');
                Swal.fire("Oops... parece que hubo un error",
                    "No se pudo enviar el correo o registrar la nota, inténtalo de nuevo", "error");

            }
        }

        function limpiarCampos() {
            $('#kt_summernote_1').summernote('code', '');
            $("#rolCliente").val('');
            $("#rolDesarrollador").val('');
            $("#rolSupervisor").val('');
        }
    </script>
    @include('soporte.admin.registro-actividades.script')
@endsection
