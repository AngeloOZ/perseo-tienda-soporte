<div class="topbar-item" data-toggle="dropdown" data-offset="10px,0px">

    <div class="btn btn-icon btn-icon-mobile w-auto btn-clean d-flex align-items-center btn-lg px-2"
        id="kt_quick_user_toggle">
        <span class="text-muted font-weight-bold font-size-base d-none d-md-inline mr-1">Bienvenido,</span>
        <span
            class="text-dark-50 font-weight-bolder font-size-base d-none d-md-inline mr-3">{{ Auth::guard('cliente')->user()->razonsocial }}</span>
        <span class="symbol symbol-lg-35 symbol-25 symbol-light-success">
            <span
                class="symbol-label font-size-h5 font-weight-bold">{{ substr(Auth::guard('cliente')->user()->razonsocial, 0, 1) }}</span>
        </span>
    </div>
    <span class="pulse-ring"></span>

</div>
