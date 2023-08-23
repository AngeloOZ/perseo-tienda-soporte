<div class="aside aside-left aside-fixed d-flex flex-column flex-row-auto" id="kt_aside">
    <div class="brand flex-column-auto" id="kt_brand">
        <a href="#" class="brand-logo">
            @if (session('menuCliente') == 0)
                <img width="200" height="35" alt="Logo"
                    src="{{ asset('assets/media/logos/perseologob2.png') }}" />
            @else
                <img width="200" height="35" alt="Logo"
                    src="{{ asset('assets/media/logos/perseologo.png') }}" />
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

                <li class="menu-item {{ areActiveRoutes(['clientesFront.index']) }} " aria-haspopup="true">
                    <a href="{{ route('clientesFront.index') }}" class="menu-link">
                        <i class="menu-icon fa fa-chalkboard-teacher">
                            <span></span>
                        </i>
                        <span class="menu-text">Implementaciones</span>
                    </a>
                </li>

                <li class="menu-item {{ areActiveRoutes(['listadoDocumentos.listado']) }}" aria-haspopup="true">
                    <a href="{{ route('listadoDocumentos.listado') }}" class="menu-link">
                        <i class="menu-icon fa fa-file">
                            <span></span>
                        </i>
                        <span class="menu-text">Documentos</span>
                    </a>
                </li>
                <li class="menu-item {{ areActiveRoutes(['soportetecnico.index']) }}" aria-haspopup="true">
                    <a href="{{ route('soportetecnico.index') }}" class="menu-link">
                        <i class="menu-icon far fa-sun">
                            <span></span>
                        </i>
                        <span class="menu-text">Soporte Técnico</span>
                    </a>
                </li>

                <li class="menu-item {{ areActiveRoutes(['sesiones.indexVistaCliente']) }}" aria-haspopup="true">
                    <a href="{{ route('sesiones.indexVistaCliente') }}" class="menu-link">
                        <i class="menu-icon far fa-sun">
                            <span></span>
                        </i>
                        <span class="menu-text">Sesiones</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
