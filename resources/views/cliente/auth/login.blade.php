<!DOCTYPE html>
<html lang="es">

<head>
    <title>Capacitaciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />

    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

</head>

<body id="kt_body" class="bg-body bg-white">
    <div class="d-flex flex-column flex-root">
        <div class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed"
            style="background-image: url({{ asset('assets/media/fondos/fondo1.png') }})">
            <div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
                <a class="mb-12">
                    <img alt="Logo" src="{{ asset('assets/media/logos/perseologo.png') }}" class="h-45px" />
                </a>
                <div class="w-lg-500px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto bg-white">
                    <form class="form w-100" method="POST" action="{{ route('clientes.post_login') }}">
                        @csrf
                        <div class="text-center mb-10">
                            <h1 class="text-dark mb-3">CAPACITACIONES PERSEO</h1>
                        </div>
                        <div class="fv-row mb-10">
                            <label class="form-label fs-6 fw-bolder text-dark">Usuario: </label>
                            <input
                                class="form-control form-control-lg form-control-solid  {{ $errors->has('identificacion') ? 'is-invalid' : '' }}"
                                type="text" name="identificacion" id="identificacion" autocomplete="off"
                                value="{{ old('identificacion') }}" />
                            @if ($errors->has('identificacion'))
                                <span class=" text-danger">{{ $errors->first('identificacion') }}</span>
                            @endif
                        </div>
                        <div class="fv-row mb-10">
                            <div class="d-flex flex-stack mb-2">
                                <label class="form-label fw-bolder text-dark fs-6 mb-0">Contraseña: </label>
                            </div>
                            <input
                                class="form-control form-control-lg form-control-solid {{ $errors->has('clave') ? 'is-invalid' : '' }}"
                                type="password" name="clave" id="clave" autocomplete="off" />
                            @if ($errors->has('clave'))
                                <span class=" text-danger">{{ $errors->first('clave') }}</span><br>
                            @endif
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-lg btn-primary w-100 mb-5">
                                <span class="indicator-label">INGRESAR</span>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/plugins/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/scripts.bundle.js') }}"></script>
    <script>
        //Notificaciones
        @foreach (session('flash_notification', collect())->toArray() as $message)
            $.notify({
                // options
                message: '{{ $message['message'] }}',
            }, {
                // settings
                showProgressbar: true,
                delay: 2500,
                mouse_over: "pause",
                placement: {
                    from: "top",
                    align: "right",
                },
                animate: {
                    enter: "animated fadeInUp",
                    exit: "animated fadeOutDown",
                },
                type: '{{ $message['level'] }}',
            });
        @endforeach
    </script>
</body>

</html>
