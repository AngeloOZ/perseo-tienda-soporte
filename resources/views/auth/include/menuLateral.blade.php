<div class="aside aside-left aside-fixed d-flex flex-column flex-row-auto aside" id="kt_aside">

    <div class="brand flex-column-auto " id="kt_brand">
        <a href="#" class="brand-logo">
            @if (session('menu') == 0)
                <img width="200" height="35" alt="Logo" src="{{ asset('assets/media/perseologob2.png') }}" />
            @else
                <img width="200" height="35" alt="Logo" src="{{ asset('assets/media/perseologo.png') }}" />
            @endif
        </a>
        <button class="brand-toggle btn btn-sm px-0" id="kt_aside_toggle">
            <span class="svg-icon svg-icon svg-icon-xl">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                    height="24px" viewBox="0 0 24 24" version="1.1">
                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <polygon points="0 0 24 0 24 24 0 24" />
                        <path
                            d="M5.29288961,6.70710318 C4.90236532,6.31657888 4.90236532,5.68341391 5.29288961,5.29288961 C5.68341391,4.90236532 6.31657888,4.90236532 6.70710318,5.29288961 L12.7071032,11.2928896 C13.0856821,11.6714686 13.0989277,12.281055 12.7371505,12.675721 L7.23715054,18.675721 C6.86395813,19.08284 6.23139076,19.1103429 5.82427177,18.7371505 C5.41715278,18.3639581 5.38964985,17.7313908 5.76284226,17.3242718 L10.6158586,12.0300721 L5.29288961,6.70710318 Z"
                            fill="#000000" fill-rule="nonzero"
                            transform="translate(8.999997, 11.999999) scale(-1, 1) translate(-8.999997, -11.999999)" />
                        <path
                            d="M10.7071009,15.7071068 C10.3165766,16.0976311 9.68341162,16.0976311 9.29288733,15.7071068 C8.90236304,15.3165825 8.90236304,14.6834175 9.29288733,14.2928932 L15.2928873,8.29289322 C15.6714663,7.91431428 16.2810527,7.90106866 16.6757187,8.26284586 L22.6757187,13.7628459 C23.0828377,14.1360383 23.1103407,14.7686056 22.7371482,15.1757246 C22.3639558,15.5828436 21.7313885,15.6103465 21.3242695,15.2371541 L16.0300699,10.3841378 L10.7071009,15.7071068 Z"
                            fill="#000000" fill-rule="nonzero" opacity="0.3"
                            transform="translate(15.999997, 11.999999) scale(-1, 1) rotate(-270.000000) translate(-15.999997, -11.999999)" />
                    </g>
                </svg>
            </span>
        </button>
    </div>
    <div class="aside-menu-wrapper flex-column-fluid" id="kt_aside_menu_wrapper">
        <div id="kt_aside_menu" class="aside-menu my-4" data-menu-vertical="1" data-menu-scroll="1"
            data-menu-dropdown-timeout="500">
            <ul class="menu-nav">
                @if (in_array(Auth::user()->rol, [1]))
                    <li class="menu-item {{ areActiveRoutes(['firma.listado']) }} " aria-haspopup="true">
                        <a href="{{ route('firma.listado') }}" class="menu-link">
                            <i class="menu-icon fa fa-key"></i>
                            <span class="menu-text">Firmas</span>
                        </a>
                    </li>
                @endif
                @if (in_array(Auth::user()->rol, [1]))
                    <li class="menu-item {{ areActiveRoutes(['facturas.listado']) }} " aria-haspopup="true">
                        <a href="{{ route('facturas.listado') }}" class="menu-link">
                            <i class="menu-icon fas fa-file-invoice"></i>
                            <span class="menu-text">Facturas</span>
                        </a>
                    </li>
                @endif
                @if (in_array(Auth::user()->rol, [1]))
                    <li class="menu-item {{ areActiveRoutes(['cupones.listado']) }} " aria-haspopup="true">
                        <a href="{{ route('cupones.listado') }}" class="menu-link">
                            <i class="menu-icon fas fa-ticket-alt"></i>
                            <span class="menu-text">Listado de cupones</span>
                        </a>
                    </li>
                @endif
                @if (in_array(Auth::user()->rol, [1]))
                    <li class="menu-item {{ areActiveRoutes(['cobros.listado.vendedor']) }} " aria-haspopup="true">
                        <a href="{{ route('cobros.listado.vendedor') }}" class="menu-link">
                            <i class="menu-icon fas fa-dollar-sign"></i>
                            <span class="menu-text">Listado de cobros</span>
                        </a>
                    </li>
                @endif
                @if (in_array(Auth::user()->rol, [1]))
                    @if (Auth::user()->usuariosid === 12)
                        <li class="menu-item {{ areActiveRoutes(['comisiones.listado_tecnicos']) }} "
                            aria-haspopup="true">
                            <a href="{{ route('comisiones.listado_tecnicos') }}" class="menu-link">
                                <i class="menu-icon fas fa-file-invoice"></i>
                                <span class="menu-text">Comisiones soporte</span>
                            </a>
                        </li>
                        <li class="menu-item {{ areActiveRoutes(['comisiones.listado']) }} " aria-haspopup="true">
                            <a href="{{ route('comisiones.listado') }}" class="menu-link">
                                <i class="menu-icon fas fa-file-invoice"></i>
                                <span class="menu-text">Comisiones vendedores</span>
                            </a>
                        </li>
                        <li class="menu-item {{ areActiveRoutes(['comisiones.mi_listado']) }} " aria-haspopup="true">
                            <a href="{{ route('comisiones.mi_listado') }}" class="menu-link">
                                <i class="menu-icon fas fa-file-invoice"></i>
                                <span class="menu-text">Mis comisiones</span>
                            </a>
                        </li>
                    @endif
                @endif
                @if (in_array(Auth::user()->rol, [2]))
                    <li class="menu-item {{ areActiveRoutes(['facturas.revisor']) }} " aria-haspopup="true">
                        <a href="{{ route('facturas.revisor') }}" class="menu-link">
                            <i class="menu-icon fas fa-file-invoice-dollar"></i>
                            <span class="menu-text">Listado de facturas</span>
                        </a>
                    </li>
                @endif
                @if (in_array(Auth::user()->rol, [2]))
                    <li class="menu-item {{ areActiveRoutes(['cobros.listado.revisor']) }} " aria-haspopup="true">
                        <a href="{{ route('cobros.listado.revisor') }}" class="menu-link">
                            <i class="menu-icon fas fa-dollar-sign"></i>
                            <span class="menu-text">Listado de cobros</span>
                        </a>
                    </li>
                @endif
                @if (in_array(Auth::user()->rol, [3]))
                    <li class="menu-item {{ areActiveRoutes(['productos.listado']) }} " aria-haspopup="true">
                        <a href="{{ route('productos.listado') }}" class="menu-link">
                            <i class="menu-icon fas fa-cubes"></i>
                            <span class="menu-text">Listado de productos</span>
                        </a>
                    </li>
                @endif
                @if (in_array(Auth::user()->rol, [4]))
                    <li class="menu-item {{ areActiveRoutes(['firma.revisor']) }} " aria-haspopup="true">
                        <a href="{{ route('firma.revisor') }}" class="menu-link">
                            <i class="menu-icon fa fa-key"></i>
                            <span class="menu-text">Listado de firmas</span>
                        </a>
                    </li>
                @endif
                @if (in_array(Auth::user()->rol, [4]))
                    <li class="menu-item {{ areActiveRoutes(['firma.revisor_correo']) }} " aria-haspopup="true">
                        <a href="{{ route('firma.revisor_correo') }}" class="menu-link">
                            <i class="menu-icon fa fa-key"></i>
                            <span class="menu-text">Firmas enviadas al correo</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
