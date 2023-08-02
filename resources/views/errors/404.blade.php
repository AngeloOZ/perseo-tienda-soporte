@extends('errors.layouts.errors')
@section('contenido')
    <!--begin::Main-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Error-->


        {{-- <h1 class="font-weight-boldest text-dark-75 mt-15" style="font-size: 10rem">404</h1>
        <p class="font-size-h3 text-muted font-weight-normal">OOPS! Algo salió mal</p>
        <p class="font-size-h1 font-weight-boldest text-dark-75">Lo sentimos, parece que no podemos encontrar la
            página que estás buscando.</p>
        <p class="font-size-h4 line-height-md">Es posible que haya un error ortográfico en la URL ingresada o que la
            página que está buscando ya no exista.</p> --}}


        <div class="error error-5 d-flex flex-row-fluid bgi-size-cover bgi-position-center"
            style="background-image: url({{ asset('assets/media/bg5.jpg') }});">
            <!--begin::Content-->
            <div class="container d-flex flex-row-fluid flex-column justify-content-md-center p-12">
                <h1 class="error-title font-weight-boldest text-info mt-10 mt-md-0 mb-12">404</h1>
                <p class="font-weight-boldest display-4">Oops! Algo salió mal</p>
                <p class="font-size-h3">Es posible que haya un error ortográfico en la URL ingresada <br> o que la página que está buscando ya no exista.</p>
            </div>
            <!--end::Content-->
        </div>
        <!--end::Error-->
    </div>
@endsection
