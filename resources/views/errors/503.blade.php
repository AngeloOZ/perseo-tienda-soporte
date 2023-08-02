@extends('errors.layouts.errors')
@section('contenido')
    <!--begin::Main-->
    <div class="d-flex flex-column flex-root">
        <div class="error error-5 d-flex flex-row-fluid bgi-size-cover bgi-position-center"
            style="background-image: url({{ asset('assets/media/bg5.jpg') }});">
            <!--begin::Content-->
            <div class="container d-flex flex-row-fluid flex-column justify-content-md-center p-12">
                <h1 class="error-title font-weight-boldest text-info mt-10 mt-md-0 mb-12">Oops..!</h1>
                <p class="font-weight-boldest display-4">Sitio en mantenimineto</p>
                <p class="font-size-h3">Los servicios se encuentran en mantenimiento, reintentalo más tarde</p>
            </div>
            <!--end::Content-->
        </div>
        <!--end::Error-->
    </div>
@endsection
