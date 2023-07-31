<div id="kt_header" class="header  bg-dark" style="top: 0;  position: fixed; width:100%">
    <div class="container-fluid d-flex align-items-stretch justify-content-between">
        <div class="header-menu-wrapper header-menu-wrapper-left" id="kt_header_menu_wrapper">
            <div class="mt-4">
                <img width="200" height="35" alt="Logo" src="{{ asset('assets/media/perseologob2.png') }}" />
            </div>
        </div>
        <a href="{{ route('tienda.checkout', $vendedor->usuariosid) }}" class="btn btn-primary align-self-center">
            <i class="la la-shopping-cart icon-2x"></i>
            <span class="label label-lg label-light m-0" id="cart_items">0</span>
        </a>
    </div>
</div>
