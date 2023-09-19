@php
    $guardado = isset($estado) ? $estado : false;
    $mostrarPopUp = false;
    
    $horaEntrada = env('HORA_ENTRADA') ?? '08:10';
    $horaSalida = env('HORA_SALIDA') ?? '16:55';
    
    $current = strtotime(date('G:i'));
    $entrada = strtotime($horaEntrada);
    $salida = strtotime($horaSalida);
    
    if ($current <= $entrada && $current >= $salida) {
        $mostrarPopUp = true;
    }
@endphp

@extends('soporte.layout.app')
@section('titulo', 'Soporte - Perseo')
@section('descripcion', 'Encuentra la solución a todos tus problemas')

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid">
        <div class="d-flex flex-column-fluid">
            <div class="container w-75">
                <div class="card card-custom">
                    <div class="card-body p-8">
                        @if ($guardado)
                            <div class="d-flex flex-column align-items-center justify-content-between"
                                style="min-height: 400px">
                                <h1 class="mb-5 font-size-h1">Solicitud de soporte técnico creada</h1>
                                <p class="font-size-h4 my-5">Número de Ticket: <strong>{{ $numero_ticker }}</strong></p>
                                <li class="far fa-check-circle text-success icon-10x "></li>
                                <a href="{{ route('soporte.crear.ticket') }}" class="btn btn-primary mt-6">Regresar al
                                    inicio</a>
                                <p class="font-size-h2 font-weight-bold text-center mt-8 max-w-500px">Por favor mantente
                                    atento al correo electrónico que proporcionaste, pronto un asesor se contactará por ese
                                    medio</p>
                            </div>
                        @else
                            <div class="d-flex flex-column align-items-center justify-content-between"
                                style="min-height: 350px">
                                <h1 class="mb-5 font-size-h1">No se pudo procesar tu solicitud, inténtalo de nuevo</h1>
                                <li class="far fa-times-circle text-danger icon-10x "></li>
                                <a href="{{ route('soporte.crear.ticket') }}" class="btn btn-primary mt-6">Regresar al
                                    inicio</a>
                                <p class="font-size-h4 text-center mt-8 max-w-500px">Hubo un error al crear el ticket, por
                                    favor vuelve a intentarlo</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@if ($mostrarPopUp)
    @section('modal')
        <div class="modal fade" id="modalHorario" data-backdrop="static" tabindex="-1" role="dialog"
            aria-labelledby="staticBackdrop" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header d-none d-sm-flex py-3">
                        <h5 class="modal-title">Ticket No: <strong>{{ $numero_ticker }}</strong></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <div class="modal-body p-1">
                        <div class="w-100">
                            <img src="{{ asset('assets/media/ticket-perseo.jpeg') }}" class="w-100" alt="perseo horario">
                        </div>
                    </div>
                    <div class="modal-footer p-2 pr-4">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endsection
@endif
@section('script')
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href)
        }
    </script>
    @if ($mostrarPopUp)
        <script>
            $(document).ready(function() {
                $('#modalHorario').modal('show');
            });
        </script>
    @endif
@endsection
