@extends('tienda.layouts.app')

@section('titulo', 'Tienda')
@section('descripcion', 'Encuentra los mejores productos para tu empresa')
@section('imagen', asset('assets/media/tienda.jpg'))

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="card card-custom">
                    <div class="card-body">
                        <div class="example-preview">
                            <div class="row">
                                <div class="col-12 col-sm-4 col-lg-2">
                                    <ul class="nav flex-column nav-pills">
                                        @foreach ($productos as $key => $item)
                                            <li class="nav-item mb-2">
                                                <a class="nav-link {{ $key === 0 ? 'active' : '' }}"
                                                    id="{{ $item['id'] }}-tab-5" data-toggle="tab"
                                                    href="#{{ $item['id'] }}">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center w-100 mx-3">
                                                        <div class="font-size-lg font-weight-bold">{{ $item['titulo'] }}
                                                        </div>
                                                        <div class="ml-auto font-weight-bold">
                                                            {{ count($item['productos']) }}</div>
                                                    </div>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col-12 col-sm-8 col-lg-10">
                                    @include('tienda.productos')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    @include('tienda.modal_producto')
@endsection

@section('script')
    @if ($cupon['exists'] && !$cupon['activo'])
        <script>
            Swal.fire("Cupón Inválido", "Oops para que el cupón que tratas de usar ya expiro", "warning");
        </script>
    @endif
    <script>
        let carritoProductos = [];
        if (sessionStorage.getItem('cart')) {
            carritoProductos = JSON.parse(sessionStorage.getItem('cart'));
            const items = carritoProductos.reduce((sum, curr) => sum + curr.cantidad, 0);
            document.getElementById('cart_items').textContent = items;
            document.getElementById('cart_items_moblie').textContent = items;
        }

        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-center",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "2500",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        $(document).on('click', '.btn-info-product', function(e) {
            e.preventDefault();
            var content = $(this).data("content-html");
            $("#info-modal").modal("show");
            $("#content-body").html(content)
        });

        const listProductos = {{ Illuminate\Support\Js::from($productos) }};
        $(document).ready(function() {
            agregarQuitarProductos();
        });

        function agregarQuitarProductos() {
            const contenedor = document.getElementById('contenedor_productos');
            contenedor.addEventListener('click', e => {
                if (e.target.matches('[data-action="agregar"]')) {
                    agregarProducto(e.target);
                    toastr.success("Producto agregado");
                } else if (e.target.matches('[data-action="remover"]')) {
                    if (carritoProductos.length > 0) {
                        removerProducto(e.target);
                        toastr.error("Producto removido");
                    }
                }
            })
        }

        function agregarProducto(target) {
            const elementoPadre = target.parentElement;
            const contadorText = elementoPadre.querySelector('.indicador_cantidad');
            let contador = parseInt(contadorText.textContent) + 1;
            const producto = renewProducto(JSON.parse(elementoPadre.dataset.producto), contador);
            contadorText.textContent = contador;
            contadorText.classList.remove('d-none');

            guardarEnCarrito(producto);
        }

        function removerProducto(target) {
            const elementoPadre = target.parentElement;
            const contadorText = elementoPadre.querySelector('.indicador_cantidad');
            let contador = parseInt(contadorText.textContent) - 1;
            const producto = renewProducto(JSON.parse(elementoPadre.dataset.producto), contador);
            if (contador <= 0) {
                contador = 0;
                contadorText.classList.add('d-none');
            }
            contadorText.textContent = contador;
            guardarEnCarrito(producto);
        }

        function renewProducto(producto, cantidad) {
            delete producto.costo;
            delete producto.contenido;
            delete producto.imagen;
            producto.cantidad = cantidad;
            return producto;
        }

        function guardarEnCarrito(producto) {
            if (carritoProductos.length > 0) {
                const list = carritoProductos.filter(prod => prod.productosid != producto.productosid);
                if (producto.cantidad > 0) {
                    carritoProductos = [...list, producto];
                } else {
                    carritoProductos = [...list];
                }
            } else {
                carritoProductos = [producto];
            }
            const items = carritoProductos.reduce((sum, curr) => sum + curr.cantidad, 0);
            document.getElementById('cart_items').textContent = items;
            document.getElementById('cart_items_moblie').textContent = items;
            sessionStorage.setItem('cart', JSON.stringify(carritoProductos));
        }
    </script>
@endsection
