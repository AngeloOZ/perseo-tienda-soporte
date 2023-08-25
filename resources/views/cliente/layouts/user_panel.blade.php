<div
    class="dropdown-menu p-0 m-0 dropdown-menu-right dropdown-menu-anim-up dropdown-menu-lg offcanvas offcanvas-right p-5">
    <div class="offcanvas-content pr-0 mr-n2">

        <div class="d-flex align-items-center mt-5">
            <div class="symbol symbol-75 mr-5">
                <img src="{{ Auth::guard('cliente')->user()->getAvatarBase64($size = 300) }}" class="symbol-label">
            </div>
            <div class="d-flex flex-column">
                <div>
                    <a href="#"
                        class="font-weight-bold font-size-5 text-dark-75 ">{{ Auth::guard('cliente')->user()->razonsocial }}</a>
                </div>

                <div class="mt-4">
                    <a href="{{ route('clientes.cambiarClaveCliente') }}">
                        Cambiar Contraseña
                    </a>
                </div>
            </div>
        </div>

    </div>
    <div class="separator separator-dashed my-3"></div>
    <div class="d-flex justify-content-end">
        <div class="">
            <form action="{{ route('clientes.logout') }}" method="POST">
                @csrf
                <a href="#" class="btn btn-sm btn-light-primary font-weight-bolder"
                    onclick="this.closest('form').submit()">Cerrar Sesion</a>
            </form>
        </div>
    </div>
</div>
