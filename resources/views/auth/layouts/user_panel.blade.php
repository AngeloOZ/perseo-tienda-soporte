
<div
    class="dropdown-menu p-0 m-0 dropdown-menu-right dropdown-menu-anim-up dropdown-menu-lg offcanvas offcanvas-right p-7">
    <div class="offcanvas-content pr-0 mr-n2">

        <div class="d-flex align-items-center mt-5">
            <div class="symbol symbol-75 mr-5">

                {{-- Dibujito --}}
            </div>
            <div class="d-flex flex-column">
                <div>
                    <a href="#" class="font-weight-bold font-size-5 text-dark-75 ">
                        {{ Auth::user()->nombres }}
                    </a>        
                </div>
                
                <div class="navi mt-4">
                    <small>{{ Auth::user()->correo }}</small>
                </div>
                <div class="navi my-3">
                    <small>{{ Auth::user()->telefono }}</small>
                </div>
            </div>
        </div>
    </div>
    <div class="separator separator-dashed my-1 mb-3">

    </div>

    <div class="d-flex">

        <div class="col-6">
            <form action="" method="POST">
                @csrf
                <a href="{{route('logout_usuarios')}}" class="btn btn-sm btn-light-primary font-weight-bolder py-2 my-2 px-5"
                    onclick="">Cerrar Sesion</a>
            </form>
        </div>


        <div class="col-6">
            <a href="{{route('usuarios.clave')}}">
                Cambiar Contraseña
            </a>

        </div>

    </div>


</div>
@section('scriptMenu')
    <script></script>
@endsection
