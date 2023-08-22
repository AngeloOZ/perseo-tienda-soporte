@php
    $userRol = Auth::guard('tecnico')->user()->rol;
@endphp
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
                @if (in_array($userRol, [5]))
                    <li class="menu-item {{ areActiveRoutes(['soporte.listado.activos']) }}" aria-haspopup="true">
                        <a href="{{ route('soporte.listado.activos') }}" class="menu-link">
                            <i class="menu-icon fa fa-key"></i>
                            <span class="menu-text">Tickets activos</span>
                        </a>
                    </li>
                @endif

                @if (in_array($userRol, [5]))
                    <li class="menu-item {{ areActiveRoutes(['soporte.listado.desarrollo']) }}" aria-haspopup="true">
                        <a href="{{ route('soporte.listado.desarrollo') }}" class="menu-link">
                            <i class="menu-icon fa fa-key"></i>
                            <span class="menu-text">Tickets en Desarrollo</span>
                        </a>
                    </li>
                @endif

                @if (in_array($userRol, [5]))
                    <li class="menu-item {{ areActiveRoutes(['soporte.listado.cerrados']) }}" aria-haspopup="true">
                        <a href="{{ route('soporte.listado.cerrados') }}" class="menu-link">
                            <i class="menu-icon fa fa-key"></i>
                            <span class="menu-text">Tickets cerrados</span>
                        </a>
                    </li>
                @endif

                @if (in_array($userRol, [5]))
                    <li class="menu-item {{ areActiveRoutes(['sop.listar_soporte_especial']) }}" aria-haspopup="true">
                        <a href="{{ route('sop.listar_soporte_especial') }}" class="menu-link">
                            <i class="menu-icon fas fa-headset"></i>
                            <span class="menu-text">Soporte especial</span>
                        </a>
                    </li>
                @endif

                @if (in_array($userRol, [5]))
                    <li class="menu-item {{ areActiveRoutes(['soporte.mis_calificaciones']) }}" aria-haspopup="true">
                        <a href="{{ route('soporte.mis_calificaciones') }}" class="menu-link">
                            <i class="menu-icon fas fa-balance-scale"></i>
                            <span class="menu-text">Mis calificaciones</span>
                        </a>
                    </li>
                @endif

                @if (in_array($userRol, [5]))
                    <li class="menu-item {{ areActiveRoutes(['soporte.ver.calificaciones.tecnicos']) }}"
                        aria-haspopup="true">
                        <a href="{{ route('soporte.ver.calificaciones.tecnicos') }}" class="menu-link">
                            <i class="menu-icon fas fa-balance-scale-left"></i>
                            <span class="menu-text">Mis calificaciones negativas</span>
                        </a>
                    </li>
                @endif

                @if (in_array($userRol, [6]))
                    <li class="menu-item {{ areActiveRoutes(['soporte.listado.revidor.desarrollo']) }}"
                        aria-haspopup="true">
                        <a href="{{ route('soporte.listado.revidor.desarrollo') }}" class="menu-link">
                            <i class="menu-icon fa fa-key"></i>
                            <span class="menu-text">Tickets en desarrollo</span>
                        </a>
                    </li>
                @endif

                @if (in_array($userRol, [7]))
                    <li class="menu-item {{ areActiveRoutes(['soporte.listado.revisor']) }}" aria-haspopup="true">
                        <a href="{{ route('soporte.listado.revisor') }}" class="menu-link">
                            <i class="menu-icon fas fa-ticket-alt"></i>
                            <span class="menu-text">Listado de tickets</span>
                        </a>
                    </li>
                @endif

                @if (in_array($userRol, [7, 8]))
                    <li class="menu-item menu-item-submenu {{ areActiveRoutesMenu(['soporte.revisor_listar_soporte_especial', 'especiales.listado_supervisor']) }}"
                        aria-haspopup="true" data-menu-toggle="hover">
                        <a href="javascript:;" class="menu-link menu-toggle">
                            <i class="menu-icon fas fa-headset"></i>
                            <span class="menu-text">Soportes especiales</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="menu-submenu">
                            <i class="menu-arrow"></i>
                            <ul class="menu-subnav">
                                @if (in_array($userRol, [7, 8]))
                                    <li class="menu-item {{ areActiveRoutes(['soporte.revisor_listar_soporte_especial']) }}"
                                        aria-haspopup="true">
                                        <a href="{{ route('soporte.revisor_listar_soporte_especial') }}"
                                            class="menu-link">
                                            <i class="menu-icon fas fa-list-ul"></i>
                                            <span class="menu-text">Listado</span>
                                        </a>
                                    </li>
                                @endif

                                @if (in_array($userRol, [7, 8]))
                                    <li class="menu-item {{ areActiveRoutes(['especiales.listado_supervisor']) }}"
                                        aria-haspopup="true">
                                        <a href="{{ route('especiales.listado_supervisor') }}" class="menu-link">
                                            <i class="menu-icon fas fa-chart-line"></i>
                                            <span class="menu-text">Seguimiento</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>

                @endif

                @if (in_array($userRol, [7]))
                    <li class="menu-item {{ areActiveRoutes(['soporte.listado.estado_tecnicos']) }}"
                        aria-haspopup="true">
                        <a href="{{ route('soporte.listado.estado_tecnicos') }}" class="menu-link">
                            <i class="menu-icon fas fa-users-cog"></i>
                            <span class="menu-text">Listado de técnicos</span>
                        </a>
                    </li>
                @endif

                @if (in_array($userRol, [7, 8, 9]))
                    <li class="menu-item menu-item-submenu {{ areActiveRoutesMenu(['soporte.reporte_soporte', 'soporte.reporte_calificaicones']) }}"
                        aria-haspopup="true" data-menu-toggle="hover">
                        <a href="javascript:;" class="menu-link menu-toggle">
                            <i class="menu-icon fas fa-chart-area"></i>
                            <span class="menu-text">Reportes</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="menu-submenu">
                            <i class="menu-arrow"></i>
                            <ul class="menu-subnav">

                                <li class="menu-item {{ areActiveRoutes(['soporte.reporte_soporte']) }}"
                                    aria-haspopup="true">
                                    <a href="{{ route('soporte.reporte_soporte') }}" class="menu-link">
                                        <i class="menu-icon fas fa-chart-bar"></i>
                                        <span class="menu-text">Soportes</span>
                                    </a>
                                </li>

                                <li class="menu-item {{ areActiveRoutes(['soporte.reporte_calificaicones']) }}"
                                    aria-haspopup="true">
                                    <a href="{{ route('soporte.reporte_calificaicones') }}" class="menu-link">
                                        <i class="menu-icon fas fa-chart-pie"></i>
                                        <span class="menu-text">Calificaciones</span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>
                @endif

                @if (in_array($userRol, [7, 8, 9]))
                    <li class="menu-item menu-item-submenu {{ areActiveRoutesMenu(['calificaciones.listado', 'calificaciones.justificadas']) }}"
                        aria-haspopup="true" data-menu-toggle="hover">
                        <a href="javascript:;" class="menu-link menu-toggle">
                            <i class="menu-icon fas fa-star-half-alt"></i>
                            <span class="menu-text">Calificaciones</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="menu-submenu">
                            <i class="menu-arrow"></i>
                            <ul class="menu-subnav">
                                <li class="menu-item {{ areActiveRoutes(['calificaciones.listado']) }}"
                                    aria-haspopup="true">
                                    <a href="{{ route('calificaciones.listado') }}" class="menu-link">
                                        <i class="menu-icon fas fa-list-ul"></i>
                                        <span class="menu-text">Listado</span>
                                    </a>
                                </li>
                                <li class="menu-item {{ areActiveRoutes(['calificaciones.justificadas']) }}"
                                    aria-haspopup="true">
                                    <a href="{{ route('calificaciones.justificadas') }}" class="menu-link">
                                        <i class="menu-icon fas fa-tasks"></i>
                                        <span class="menu-text">Justificadas</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                @if (in_array($userRol, [7]))
                    <li class="menu-item {{ areActiveRoutes(['config.whatsapp']) }} " aria-haspopup="true">
                        <a href="{{ route('config.whatsapp') }}" class="menu-link">
                            <i class="menu-icon fab fa-whatsapp"></i>
                            <span class="menu-text">Configuración WhatsApp</span>
                        </a>
                    </li>
                @endif

                @if (in_array($userRol, [7]))
                    <li class="menu-item menu-item-submenu {{ areActiveRoutesMenu(['asignacion.index', 'categorias.index', 'subcategorias.index', 'temas.index']) }}"
                        aria-haspopup="true" data-menu-toggle="hover">
                        <a href="javascript:;" class="menu-link menu-toggle">
                            <i class="menu-icon fas fa-font"></i>
                            <span class="menu-text">Asignación</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="menu-submenu">
                            <i class="menu-arrow"></i>
                            <ul class="menu-subnav">

                                <li class="menu-item {{ areActiveRoutes(['asignacion.index']) }}"
                                    aria-haspopup="true">
                                    <a href="{{ route('asignacion.index') }}" class="menu-link">
                                        <i class="menu-icon fas fa-font"></i>
                                        <span class="menu-text">Asignación</span>
                                    </a>
                                </li>
                                <li class="menu-item {{ areActiveRoutes(['categorias.index']) }}"
                                    aria-haspopup="true">
                                    <a href="{{ route('categorias.index') }}" class="menu-link">
                                        <i class="menu-icon fas fa-bars"></i>
                                        <span class="menu-text">Categorías</span>
                                    </a>
                                </li>
                                <li class="menu-item {{ areActiveRoutes(['subcategorias.index']) }}"
                                    aria-haspopup="true">
                                    <a href="{{ route('subcategorias.index') }}" class="menu-link">
                                        <i class="menu-icon fa fa-heading"></i>
                                        <span class="menu-text">Subcategorías</span>
                                    </a>
                                </li>
                                <li class="menu-item {{ areActiveRoutes(['temas.index']) }}" aria-haspopup="true">
                                    <a href="{{ route('temas.index') }}" class="menu-link">
                                        <i class="menu-icon fas fa-info"></i>
                                        <span class="menu-text">Temas</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
