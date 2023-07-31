@extends('firma.layouts.app')

@section('titulo',"Estado de la solicitud")
@section('descripcion',"Seguimiento del estado de la solicitud") 
@section('imagen', '')

@section('contenido')
    @php
        $text = 'La solicitud ha sido recibida';
        switch ($estado) {
            case '2':
                $text = 'La solicitud ha sido revisada';
                break;
            case '3':
                $text = 'La solicitud se encuentra en proceso, pronto recibiras un correo';
                break;
            case '4':
                $text = 'La solicitud se encuentra en proceso, pronto recibiras un correo';
                break;
            case '5':
                $text = 'La solicitud ha finalizado, revisa tu correo electrónico';
                break;
            default:
                $text = 'La solicitud ha sido recibida';
                break;
        }
    @endphp
    <div class="content d-flex flex-column flex-column-fluid w-100 w-md-1000px mx-auto">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid mb-5">
                <div class="card card-custom mt-5">
                    <div class="card-header justify-content-center">
                        <h1 class="card-title custom-title-2">
                            Estado de solicitud de firma
                        </h1>
                    </div>
                    @if ($estado <= 5)
                        <div class="card-body">
                            <div class="contenedor_tracking">
                                <div class="item_tracking {{ $estado >= 1 ? 'active' : '' }}" data-title-text="Recbido">
                                    <div class="icon_circle">
                                        <i class="fas fa-envelope-open-text"></i>
                                    </div>
                                    <span class="line"></span>
                                </div>
                                <div class="item_tracking {{ $estado >= 2 ? 'active' : '' }}" data-title-text="Revisado">
                                    <div class="icon_circle">
                                        <i class="flaticon2-writing"></i>
                                    </div>
                                    <span class="line"></span>
                                </div>
                                <div class="item_tracking {{ $estado >= 3 ? 'active' : '' }}" data-title-text="En proceso">
                                    <div class="icon_circle">
                                        <i class="flaticon2-reload"></i>
                                    </div>
                                    <span class="line"></span>
                                </div>
                                {{-- <div class="item_tracking {{ $estado >= 4 ? 'active' : '' }}" data-title-text="Finalizado">
                                    <div class="icon_circle">
                                        <i class="flaticon2-protected"></i>
                                    </div>
                                    <span class="line"></span>
                                </div> --}}
                                <div class="item_tracking {{ $estado >= 5 ? 'active' : '' }}"
                                    data-title-text="Enviado al correo">
                                    <div class="icon_circle">
                                        <i class="far fa-envelope"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="text-desc-1">{{ $text }}</p>
                            <p class="text-desc-2">Una vez revisada tu solicitud tardaremos un máximo de 24 horas laborables en generar tu firma electrónica</p>
                            <div class="container mb-5 mt-md-10 text-justify">
                                <strong class="font-size-h4 font-weight-boldest">NOTA:</strong> PARA LA GENERACION DE LA CONTRASEÑA DE SU CERTIFICADO, USAR NUMEROS Y LETRAS EXCEPTO LA Ñ, SIN CARACTERES ESPECIALES. DE IGUAL MANERA LA FIRMA DEBE SER DESCARGADA UNICAMENTE DESDE COMPUTADORA. <strong>PERSEOSOFT NO SE RESPONSABILIZA</strong> SI TIENE PROBLEMAS AL AUTORIZAR DOCUMENTOS, POR TEMA DE CLAVES NO VALIDAS O MAL DESCARGADAS.
                            </div>
                        </div>
                    @else
                        <div class="card-body">
                            <div class="contenedor-dos text-center">
                               <li class="far fa-times-circle text-danger icon-9x "></li>
                               <p class="text-desc-1" style="margin-top: 20px; font-size: 25px">Oops... <br>Tu solicitud ha sido anulada</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script></script>
@endsection
