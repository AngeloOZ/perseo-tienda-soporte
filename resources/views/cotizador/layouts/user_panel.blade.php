<div
    class="dropdown-menu p-0 m-0 dropdown-menu-right dropdown-menu-anim-up dropdown-menu-lg offcanvas offcanvas-right p-7">
    <div class="offcanvas-content pr-0 mr-n2">

        <div class="d-flex align-items-center mt-5">
            <div class="symbol symbol-75 mr-5">
                @if (Auth::guard('admin')->check())
                    <img src="{{ Auth::guard('admin')->user()->getAvatarBase64($size = 300) }}" class="symbol-label">
                @elseif(Auth::guard('distribuidor')->check())
                    <img src="{{ Auth::guard('distribuidor')->user()->getAvatarBase64($size = 300) }}"
                        class="symbol-label">
                @elseif(Auth::guard('subdistribuidor')->check())
                    <img src="{{ Auth::guard('subdistribuidor')->user()->getAvatarBase64($size = 300) }}"
                        class="symbol-label">
                @endif

            </div>
            <div class="d-flex flex-column">
                <div>
                    <a href="#" class="font-weight-bold font-size-5 text-dark-75 ">
                        @if (Auth::guard('admin')->check())
                            {{ Auth::guard('admin')->user()->nombres }}
                        @elseif(Auth::guard('distribuidor')->check())
                            {{ Auth::guard('distribuidor')->user()->nombres }}
                        @elseif(Auth::guard('subdistribuidor')->check())
                            {{ Auth::guard('subdistribuidor')->user()->nombres }}
                        @endif
                    </a>
                </div>

                <div class="navi mt-4">

                    @if (Auth::guard('admin')->check())
                        <form action="{{ route('tecnicos.logout') }}" method="POST">
                            @csrf
                            <a href="#" class="btn btn-sm btn-light-primary font-weight-bolder py-2 my-2 px-5"
                                onclick="this.closest('form').submit()">Cerrar Sesion</a>
                        </form>
                    @elseif(Auth::guard('distribuidor')->check())
                        <form action="{{ route('distribuidores.logout') }}" method="POST">
                            @csrf
                            <a href="#" class="btn btn-sm btn-light-primary font-weight-bolder py-2 my-2 px-5"
                                onclick="this.closest('form').submit()">Cerrar Sesion</a>
                        </form>
                    @elseif(Auth::guard('subdistribuidor')->check())
                        <form action="{{ route('distribuidores.logout') }}" method="POST">
                            @csrf
                            <a href="#" class="btn btn-sm btn-light-primary font-weight-bolder py-2 my-2 px-5"
                                onclick="this.closest('form').submit()">Cerrar Sesion</a>
                        </form>
                    @endif


                </div>
            </div>
        </div>

    </div>
    <div class="separator separator-dashed my-1 mb-3">

    </div>

    <div class="d-flex">
        @if (Auth::guard('admin')->check())
            <div class="col-5 ">
                <label>Menu Claro: </label>
                <span class="switch switch-sm switch-icon">
                    <label>
                        <input type="checkbox" name="menu" id="menu" onchange="cambiarMenu();" @if (Session::get('menu') == 1)
                        checked
        @endif />
        <span></span>
        </label>
        </span>
    </div>
    @endif

    @if (Auth::guard('admin')->check())
        <div class="col-7">
            <a href="{{ route('tecnicos.cambiarClave') }}">
                Cambiar Contraseña
            </a>

        </div>
    @elseif (Auth::guard('distribuidor')->check())
        <div class="col-7">
            <a href="{{ route('tecnicosDistribuidor.cambiarClave') }}">
                Cambiar Contraseña
            </a>
        </div>
    @elseif (Auth::guard('subdistribuidor')->check())
        <div class="col-7">
            <a href="{{ route('tecnicosSubdistribuidor.cambiarClave') }}">
                Cambiar Contraseña
            </a>
        </div>


    @endif
</div>


</div>
@section('scriptMenu')
    <script>
        function cambiarMenu() {

            var estado;
            if ($('#menu').is(':checked')) {
                estado = 1;
            } else {

                estado = 0;
            }

            $.post('{{ route('cambiarMenu') }}', {
                _token: $('meta[name="csrf-token"]').attr("content"),
                estado: estado
            }, function(data) {

                location.reload();
            });
        }
    </script>
@endsection
