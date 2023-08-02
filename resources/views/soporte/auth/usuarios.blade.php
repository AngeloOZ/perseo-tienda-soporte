@php
    $array_nombnres = explode(' ', Auth::guard('tecnico')->user()->nombres);
    $iniciales = substr($array_nombnres[0],0,1).substr($array_nombnres[1],0,1);
@endphp
<div class="topbar-item" data-toggle="dropdown" data-offset="10px,0px">
    <div class="btn btn-icon btn-icon-mobile w-auto btn-clean d-flex align-items-center btn-lg px-2"
        id="kt_quick_user_toggle">
        <span class="text-muted font-weight-bold font-size-base d-md-inline mr-1">Bienvenid@,</span>
        <span class="text-dark-50 font-weight-bolder font-size-base d-md-inline mr-3">
            {{ Auth::guard('tecnico')->user()->nombres }}
        </span>
        <span class="symbol symbol-lg-35 symbol-25 symbol-light-success">
            <span class="symbol-label font-size-h5 font-weight-bold">
                {{ $iniciales }}
            </span>
        </span>
    </div>
    <span class="pulse-ring"></span>

</div>
