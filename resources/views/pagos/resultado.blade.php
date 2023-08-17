@extends('pagos.layouts.app')
@section('titulo', 'Pago registrado')
@section('descripcion', 'Pago registrado según factura {{ $renovacion->uuid }}')
@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid">
        <div class="d-flex flex-column-fluid">
            <div class="container w-75">
                <div class="card card-custom">
                    <div class="card-body p-8">
                        <div class="d-flex flex-column align-items-center justify-content-between" style="min-height: 400px">
                            <h1 class="mb-5 font-size-h1">Comprobante de pago registrado</h1>
                            <p class="font-size-h2 my-5">Número de factura: <strong>{{ $renovacion->uuid }}</strong></p>
                            <li class="far fa-check-circle text-success icon-10x "></li>
                            <p class="font-size-h3 font-weight-bold text-center mt-8 max-w-650px">Hemos registrado tu pago 🎉, actualmente se encuentra en proceso de validación ⌛. Si surge algún inconveniente, nos comunicaremos contigo de inmediato 📞. <br> Agradecemos tu confianza en nuestros servicios 💼.</p>
                        </div>
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
