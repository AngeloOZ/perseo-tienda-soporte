<div
    class="dropdown-menu p-0 m-0 dropdown-menu-right dropdown-menu-anim-up dropdown-menu-lg offcanvas offcanvas-right p-7">
    <div class="offcanvas-content pr-0 mr-n2">

        <div class="d-flex align-items-center mt-5">
            <div class="symbol symbol-75 mr-5">
                <img src="{{ Auth::guard()->user()->getAvatarBase64($size = 300) }}" class="symbol-label">
            </div>
            <div class="d-flex flex-column">
                <div>
                    <a href="#"
                        class="font-weight-bold font-size-5 text-dark-75 ">{{ Auth::guard()->user()->razonsocial }}</a>
                </div>

                <div class="navi mt-4">
                    <form action="{{ route('clientes.logout') }}" method="POST">
                        @csrf
                        <a href="#" class="btn btn-sm btn-light-primary font-weight-bolder py-2 my-2 px-5"
                            onclick="this.closest('form').submit()">Cerrar Sesion</a>
                    </form>

                </div>
            </div>
        </div>

    </div>
    <div class="separator separator-dashed my-1">

    </div>
    <div class="d-flex">
        <div class="col-5">
            <label>Menu Claro: </label>
            <span class="switch switch-sm switch-icon">
                <label>
                    <input type="checkbox" name="menuCliente" id="menuCliente" onchange="cambiarMenuCliente()"
                        @if (Session::get('menuCliente') == 1)
                    checked @endif />
                    <span></span>
                </label>
            </span>
        </div>
        <div class="col-7">
            <a href="{{ route('clientes.cambiarClaveCliente') }}">
                Cambiar Contraseña
            </a>

        </div>
    </div>


</div>
@section('scriptMenuCliente')
    <script>
        function cambiarMenuCliente() {

            var estado;
            if ($('#menuCliente').is(':checked')) {
                estado = 1;
            } else {

                estado = 0;
            }

            $.post('{{ route('cambiarMenuCliente') }}', {
                _token: $('meta[name="csrf-token"]').attr("content"),
                estado: estado
            }, function(data) {
                location.reload();
            });
        }
    </script>
@endsection
