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
                @include('soporte.auth.include.icon_menu')
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
                    <li class="menu-item {{ areActiveRoutes(['demos.listado']) }} " aria-haspopup="true">
                        <a href="{{ route('demos.listado') }}" class="menu-link">
                            <i class="menu-icon fa fa-key"></i>
                            <span class="menu-text">Demos y lite</span>
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
                    @endif
                    @if (Auth::user()->usuariosid === 12 || Auth::user()->usuariosid === 13)
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
                @if (in_array(Auth::user()->rol, [2]))
                    <li class="menu-item {{ areActiveRoutes(['facturas.whatsapp.config']) }} " aria-haspopup="true">
                        <a href="{{ route('facturas.whatsapp.config') }}" class="menu-link">
                            <i class="menu-icon fab fa-whatsapp"></i>
                            <span class="menu-text">Configurar Whatsapp</span>
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
