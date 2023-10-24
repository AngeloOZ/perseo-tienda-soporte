<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>@yield('title','Revisor facturas')</title>
    <meta name="description" content="Firma" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />

    <link href="{{ asset('assets/plugins/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/header/light.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/custom/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/brand/dark.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/badge-color.css') }}" rel="stylesheet" type="text/css" />

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/logoP.png') }}">
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Firmas" />
    <meta property="og:description" content="=Solicitud de Firmas Electrónicas" />
    <meta property="og:image" content="" />
    <style>
        .disabled-anchor {
            opacity: 0.7 !important;
            pointer-events: none !important;
        }
    </style>
</head>

<body id="kt_body"
    class="header-fixed header-mobile-fixed aside-enabled aside-fixed aside-minimize-hoverable page-loading">
    {{-- NavBar Responsive --}}
    <div id="kt_header_mobile" class="header-mobile align-items-center header-mobile-fixed">
        @include('auth2.include.navBarMobile')
    </div>
    <div class="d-flex flex-column flex-root">

        <div class="d-flex flex-row flex-column-fluid page">
            @include('auth.include.menuLateral')

            <div class="d-flex flex-column flex-row-fluid wrapper">
                @include('auth2.include.navBar')
                @yield('contenido')
                @include('auth.include.footer')
            </div>
        </div>
    </div>



    @yield('modal')
    <script>
        var KTAppSettings = {
            "breakpoints": {
                "sm": 576,
                "md": 768,
                "lg": 992,
                "xl": 1200,
                "xxl": 1400
            },
            "colors": {
                "theme": {
                    "base": {
                        "white": "#ffffff",
                        "primary": "#3699FF",
                        "secondary": "#E5EAEE",
                        "success": "#1BC5BD",
                        "info": "#8950FC",
                        "warning": "#FFA800",
                        "danger": "#F64E60",
                        "light": "#E4E6EF",
                        "dark": "#181C32"
                    },
                    "light": {
                        "white": "#ffffff",
                        "primary": "#E1F0FF",
                        "secondary": "#EBEDF3",
                        "success": "#C9F7F5",
                        "info": "#EEE5FF",
                        "warning": "#FFF4DE",
                        "danger": "#FFE2E5",
                        "light": "#F3F6F9",
                        "dark": "#D6D6E0"
                    },
                    "inverse": {
                        "white": "#ffffff",
                        "primary": "#ffffff",
                        "secondary": "#3F4254",
                        "success": "#ffffff",
                        "info": "#ffffff",
                        "warning": "#ffffff",
                        "danger": "#ffffff",
                        "light": "#464E5F",
                        "dark": "#ffffff"
                    }
                },
                "gray": {
                    "gray-100": "#F3F6F9",
                    "gray-200": "#EBEDF3",
                    "gray-300": "#E4E6EF",
                    "gray-400": "#D1D3E0",
                    "gray-500": "#B5B5C3",
                    "gray-600": "#7E8299",
                    "gray-700": "#5E6278",
                    "gray-800": "#3F4254",
                    "gray-900": "#181C32"
                }
            },
            "font-family": "Poppins"
        };
    </script>
    <script src="{{ asset('assets/plugins/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/scripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        //Notificaciones
        @foreach (session('flash_notification', collect())->toArray() as $message)
            @if ($message['level'] == 'info')
                Swal.fire({
                    title: "WhatsApp Desconectado",
                    text: "Parace que el servicio de WhatsApp está desconfigurado",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Configurar ahora",
                    cancelButtonText: "Despues",
                    reverseButtons: true,
                }).then(function(result) {
                    if (result.value) {
                        location.href = "{{ route('facturas.whatsapp.config') }}"
                    }
                });
                @php continue; @endphp
            @endif

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
    <script>
        bloquearBotones();

        function disabledButton(event) {
            this.classList.add('disabled-anchor');
            this.setAttribute('disabled', 'true');
        }

        function bloquearBotones() {
            const btnAutorizar = document.getElementById("btnAutorizar");
            const btnLiberar = document.getElementById("btnLiberar");

            btnAutorizar?.addEventListener('click', disabledButton)
            btnLiberar?.addEventListener('click', disabledButton)
        }
    </script>

    @yield('scriptMenu')
    @yield('script')
</body>


</html>
