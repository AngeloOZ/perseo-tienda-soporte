@extends('soporte.layout.app')

@section('titulo', 'Calificar Soporte')
@section('descripcion', 'Ayudanos a mejorar nuestro servicio, calificandonos')
{{-- @section('imagen', asset('assets/media/tienda.jpg')) --}}

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid p-0">
        <div class="d-flex flex-column-fluid">
            <div class="container">
                <div class="card card-custom">
                    <div class="card-header d-flex align-items-center">
                        <h1 class="font-size-h3 m-0 p-0">Calificar Soporte del Ticket: <strong>N°
                                {{ $ticket->numero_ticket }}</strong></h1>
                    </div>
                    <div class="card-body py-2 mb-12">
                        @if ($ticket->estado >= 3 && $ticket->calificado == 1)
                            <div class="d-flex flex-column align-items-center justify-content-between"
                                style="min-height: 350px">
                                <h1 class="mb-5 font-size-h1">Ticket: <strong>N° {{ $ticket->numero_ticket }}</strong></h1>
                                <li class="far fa-check-circle text-success icon-10x "></li>
                                <p class="font-size-h4 text-center mt-8 max-w-500px">Usted ya ha calificado este soporte</p>
                            </div>
                        @elseif ($ticket->estado >= 3)
                            @include('soporte.ticket.encuesta_pregunta')
                        @else
                            <div class="d-flex flex-column align-items-center justify-content-between"
                                style="min-height: 350px">
                                <h1 class="mb-5 font-size-h1">El Ticket: <strong>N° {{ $ticket->numero_ticket }}</strong>
                                    aún está abierto</h1>
                                <li class="far fa-times-circle text-danger icon-10x "></li>
                                <p class="font-size-h4 text-center mt-8 max-w-500px">No se puede registrar una calificación
                                    de un soprte mientras se encuentra abierto</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://kit.fontawesome.com/79af1eae1d.js" crossorigin="anonymous"></script>
    <script>
        const formCalificacion = document.getElementById('formCalificacion');

        formCalificacion?.addEventListener('submit', function(e) {
            try {
                e.preventDefault();
                this.btnSendScore.setAttribute('disabled', 'true');

                const calificaciones = [
                    Number.parseInt(this.elements.pregunta_1.value) || 0,
                    Number.parseInt(this.elements.pregunta_2.value) || 0,
                ];

                if (calificaciones.some(item => item == 0)) {
                    throw new Error("Respuestas en blanco");
                }

                if (calificaciones.some(item => item <= 2 && item > 0)) { 
                    if ($('#ctnComentario').hasClass('d-none')) {
                        $('#ctnComentario').removeClass('d-none');
                        $('#idComentario').focus();
                        this.btnSendScore.removeAttribute('disabled');
                        Swal.fire("Cuentanos un poco más",
                            "Ayúdanos detallando acerca de porque no está satisfecho con el soporte en un mínimo 50 caracteres",
                            "info");
                        return
                    }

                    if (!comentarioIsValid()) {
                        Swal.fire("Faltan detalles",
                            "Por favor detállanos un poco más, recuerda que el mínimo son 50 caracteres",
                            "warning").then(() => $('#idComentario').focus());
                        this.btnSendScore.removeAttribute('disabled');
                        return
                    }
                } else {
                    $('#ctnComentario').addClass('d-none');
                    $('#idComentario').val('');
                }
                this.submit();
            } catch (error) {
                console.log(error);
                this.btnSendScore.removeAttribute('disabled');
                Swal.fire("Respuestas en blanco", "Se debe contestar todas las preguntas", "warning");
            }
        })

        function comentarioIsValid() {
            const comentario = document.getElementById('idComentario');
            const text = comentario.value;

            if (text.length < 50) {
                return false;
            }
            return true;
        }
    </script>
@endsection
