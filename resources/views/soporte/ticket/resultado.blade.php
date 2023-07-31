@extends('soporte.layout.app')

@section('titulo', 'Soporte - Perseo')
@section('descripcion', 'Encuentra la solución a todos tus problemas')
{{-- @section('imagen', asset('assets/media/tienda.jpg')) --}}

@php
    $guardado = isset($estado) ? $estado : false;
@endphp

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
@section('script')
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href)
        }
    </script>
@endsection
