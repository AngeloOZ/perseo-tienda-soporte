@php
    $horaSalida = env('HORA_SALIDA') ?? "16:50";

    $currentUser = Auth::guard('tecnico')->user();

    $hora = date('G:i');
    $hora1 = strtotime($hora);
    $hora2 = strtotime($horaSalida);
    $showButton = $hora1 > $hora2;
@endphp
<div
    class="dropdown-menu p-0 m-0 dropdown-menu-right dropdown-menu-anim-up dropdown-menu-lg offcanvas offcanvas-right p-7">
    <div class="offcanvas-content pr-0 mr-n2">

        <div class="d-flex align-items-center mt-5">
            <div class="symbol symbol-75 mr-5">
            </div>
            <div class="d-flex flex-column">
                <div>
                    <a href="#" class="font-weight-bold font-size-5 text-dark-75 ">
                        {{ $currentUser->nombres }}
                    </a>
                </div>

                <div class="navi mt-4">
                    <small>{{ $currentUser->correo }}</small>
                </div>
                <div class="navi my-3">
                    <small>{{ $currentUser->telefono }}</small>
                </div>
            </div>

        </div>
    </div>
    <div class="separator separator-dashed my-1 mb-3"></div>

    <div class="d-flex">
        <div class="col-6">
            <form action="" method="POST">
                @csrf
                <a href="{{ route('soporte.logout') }}"
                    class="btn btn-sm btn-light-primary font-weight-bolder py-2 my-2 px-5" onclick="">Cerrar
                    Sesion</a>
            </form>
        </div>
        <div class="col-6">
            <a href="{{ route('soporte.clave') }}">Cambiar Contraseña</a>
        </div>
    </div>
    @if ($currentUser->rol == 5)
        <div class="separator separator-dashed my-1 mb-3"></div>
        <div class="d-flex justify-content-between px-5">
            <p class="d-flex align-items-center m-0" style="height: 30px">
                <strong class="mr-3">Estado: </strong>
                <i style="font-size: 10px" @class([
                    'fas fa-circle',
                    'text-success' => $currentUser->activo == 1,
                    'text-danger' => $currentUser->activo == 0,
                ])></i>
                <span class="ml-1">{{ $currentUser->activo == 1 ? 'Disponible' : 'Desconectado' }}</span>
            </p>

            @if ($showButton)
                <a href="{{ route('soporte.cambiar.disponibilidad') }}" @class([
                    'btn btn-icon btn-circle btn-sm ml-8',
                    'btn-success' => $currentUser->activo == 0,
                    'btn-danger' => $currentUser->activo == 1,
                ])>
                    <i class="fas fa-power-off"></i>
                </a>
            @endif
        </div>
    @endif
</div>
