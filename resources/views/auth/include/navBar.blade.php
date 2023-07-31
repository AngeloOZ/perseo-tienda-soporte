<div id="kt_header" class="header header-fixed">

    <div class="container-fluid d-flex align-items-stretch justify-content-between">
        <div class="header-menu-wrapper header-menu-wrapper-left" id="kt_header_menu_wrapper">
            @if (Auth::user()->rol == 1)
                <div class="mt-5">
                    <a href="{{ route('inicio', Auth::user()->usuariosid) }}" target="_blank" style="font-size: 20px"
                        data-toggle="tooltip" data-theme="dark" title="Enlace firmas" class="mx-2">
                        <i class="fas fa-globe mt-1">
                        </i>
                    </a>
                    <a href="{{ route('tienda', Auth::user()->usuariosid) }}" target="_blank" style="font-size: 20px"
                        data-toggle="tooltip" data-theme="dark" title="Enlace tienda" class="mx-2">
                        <i class="fab fa-shopify mt-1">
                        </i>
                    </a>

                </div>
            @endif
        </div>

        <div class="topbar">
            <div class="topbar-item">
                @include('auth.usuarios')
                @include('auth.layouts.user_panel')
            </div>
        </div>

    </div>

</div>
