<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Firma</title>
    <meta name="description" content="Firma" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />

    <link href="{{ asset('assets/css/pages/error/error-5.css') }}" rel="stylesheet" type="text/css" />

    <link href="{{ asset('assets/plugins/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/header/light.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/custom/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/logoP.png') }}">
    <link href="{{ asset('assets/css/brand/dark.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .error.error-5 .error-title {
            font-size: 7rem !important;
            color: #1a1a27 !important;
        }

        @media (min-width: 768px) {
            .error.error-5 .error-title {
                font-size: 14rem !important;
            }
        }
    </style>

</head>

<body id="kt_body"
    class="header-fixed header-mobile-fixed aside-enabled aside-fixed aside-minimize-hoverable page-loading">

    @yield('contenido')

    <script src="{{ asset('assets/plugins/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/scripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
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
