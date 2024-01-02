@extends('tienda.layouts.app')
@section('titulo', 'Finalizar compra')
@section('descripcion', 'Productos listos para la compra')
@section('imagen', asset('assets/media/firmas.jpg'))

{{-- @section('navidad')
    <script src="https://app.embed.im/snow.js" defer></script>
@endsection --}}

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid w-100 mx-auto p-0">
        <div class="d-flex flex-column-fluid">
            <div class="container">
                <div class="card card-custom p-0 m-0 mb-8">
                    <div class="d-flex align-items-center px-10" style="height: 60px">
                        <img src="{{ asset('assets/media/logoP.png') }}" width="30px" height="30px" alt="">
                        <h3 class="m-0 ml-3">Resumen de orden</h3>
                    </div>
                    <div class="card-body px-8 pb-0" style="padding-top: 0">
                        <div class="row">
                            <div class="col-12 col-lg-6">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="text-left">Detalle</th>
                                                <th class="text-left">Cantidad</th>
                                                <th class="text-left">Precio Unitario</th>
                                                <th class="text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body_table">
                                            @foreach ($carrito->items as $item)
                                                <tr>
                                                    <td class="d-flex align-items-center ">
                                                        <p class="text-dark">{{ $item->descripcion }}</p>
                                                    </td>
                                                    <td class="text-left align-middle font-size-h5">
                                                        {{ $item->cantidad }}</td>
                                                    <td class="text-left align-middle font-size-h5">
                                                        {{ number_format($item->precio, 2) }}</td>
                                                    <td class="text-right align-middle font-size-h5">
                                                        {{ number_format($item->precio * $item->cantidad, 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2"></td>
                                                <td class="text-left">Subtotal</td>
                                                <td class="text-right">{{ number_format($carrito->subTotal, 2) }}</td>
                                            </tr>
                                            @if ($carrito->descuento != 0)
                                                <tr>
                                                    <td colspan="2"></td>
                                                    <td class="text-left">Descuento</td>
                                                    <td class="text-right">{{ number_format($carrito->descuento, 2) }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td colspan="2"></td>
                                                <td class="text-left">IVA</td>
                                                <td class="text-right">{{ number_format($carrito->iva, 2) }}</td>
                                            </tr>
                                            @if ($carrito->tipo_pago == 'tarjeta' && $vendedor->recargo_pagoplux != 0)
                                                <tr>
                                                    <td colspan="2"></td>
                                                    <td class="text-left">Recargo {{ $vendedor->recargo_pagoplux }}%</td>
                                                    <td class="text-right">{{ number_format($carrito->recargo, 2) }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td colspan="2"></td>
                                                <td class="text-left">Total</td>
                                                <td class="text-right">{{ number_format($carrito->total, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                @include('tienda.datos_compra')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modal')
    @include('tienda.modal_informacion')
@endsection
@section('script')
    {{-- Sandbox:  --}}
    @if ($carrito->tipo_pago == 'tarjeta')
        <script src="https://paybox.pagoplux.com/paybox/index.js"></script>
        <script type="text/javascript">
            var data = {
                PayboxRemail: "{{ $vendedor->correo_pagoplux }}",
                PayboxSendmail: $('#correoCliente').val(),
                PayboxRename: "{{ $vendedor->nombre_pagoplux }}",
                PayboxSendname: $('#nombreCliente').val(),
                PayboxBase0: $('#subTotal').val(),
                PayboxBase12: $('#total ').val(),
                PayboxDescription: "Pago tienda socio-perseo",
                PayboxProduction: true,
                PayboxEnvironment: "prod",
                PayboxLanguage: "es",
                PayboxRequired: [],
                PayboxDirection: $('#direccionCliente').val(),
                PayBoxClientPhone: $('#inputTelefono ').val(),
                PayBoxClientName: $('#nombreCliente ').val(),
                PayBoxClientIdentification: $('#idCliente ').val(),
                PayboxPagoPlux: false,
                PayboxIdElement: 'idHtmlPay'
            };

            var onAuthorize = function(response) {
                if (response.status == 'succeeded') {
                    console.log(response);
                    const form = document.getElementById('form-datos-facturacion');
                    form.id_transaccion.value = response.detail.id_transaccion;
                    form.voucher.value = response.detail.token;
                    form.nombre_tarjeta.value = response.detail.cardIssuer;
                    form.btnSendMessage.click();
                }else{
                    console.error("Error...!!");
                }
            };
        </script>
    @endif

    <script>
        let carritoProductos = {{ Illuminate\Support\Js::from($carrito->items) }};
        contarItemsCarrito();

        $(document).ready(function() {

            @if ($carrito->tipo_pago == 'transferencia')
                init_subir_archivos();
            @endif

            const formInputs = document.getElementById('form-datos-facturacion');
            formInputs.addEventListener('submit', submitFormulario);

            $('#tipoPagoTarjeta').change(function() {
                if ($(this).is(':checked')) {
                    location.href =
                        "{{ route('tienda.finalizar_compra', ['referido' => $vendedor->usuariosid, 'pago' => 'tarjeta']) }}"
                }
            });

            $('#tipoPagoTransferencia').change(function() {
                if ($(this).is(':checked')) {
                    location.href =
                        "{{ route('tienda.finalizar_compra', ['referido' => $vendedor->usuariosid, 'pago' => 'transferencia']) }}"
                }
            });

        });

        /* -------------------------------------------------------------------------- */
        /*                  functiones para validacion de formulario                  */
        /* -------------------------------------------------------------------------- */

        async function submitFormulario(e) {
            e.preventDefault();
            const btnSend = document.getElementById('btnSendMessage');
            btnSend.setAttribute('disabled', 'true');

            if (carritoProductos.length == 0) {
                Swal.fire({
                    title: "Carrito vacío",
                    text: "Primero debes seleccionar un producto",
                    icon: "error",
                    onClose: function() {
                        location.href = "{{ route('tienda', $vendedor->usuariosid) }}"
                    }
                });
                return;
            }

            const comproFirma = carritoProductos.some(item => item.categoria == 2);
            if (comproFirma) {
                const result = await Swal.fire({
                    title: "Firma electrónica",
                    text: "En el carrito se ha detectado un firma electrónica, ¿Desea llenar la solicitud de firmas?",
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonText: "Si",
                    cancelButtonText: "Más tarde",
                    reverseButtons: true
                })
                this.inputRedireccion.value = result.isConfirmed;
            }
            
            const totalText = document.getElementById('total');
            $prod = carritoProductos.map(item => {
                return {
                    productoid: item.productosid,
                    productoid_homo: item.productos_homologados_id,
                    cantidad: item.cantidad,
                    categoria: item.categoria,
                }
            })
            this.inputProductos.value = JSON.stringify($prod);

            sessionStorage.clear();
            Cookies.remove('cxt');
            Cookies.remove('cart_tienda');
            Cookies.remove('cupon_code');
            this.submit();
            btnSend.removeAttribute('disabled');
        }

        /* -------------------------------------------------------------------------- */
        /*                       funciones para items y carrito                       */
        /* -------------------------------------------------------------------------- */

        function contarItemsCarrito() {
            const items = carritoProductos.reduce((sum, curr) => sum + curr.cantidad, 0);
            document.getElementById('cart_items').textContent = items;
            document.getElementById('cart_items_moblie').textContent = items;
        }
        /* -------------------------------------------------------------------------- */
        /*                    funciones para validar peso archivos                    */
        /* -------------------------------------------------------------------------- */
        function init_subir_archivos() {
            const comprobante_pago = document.getElementById('comprobante_pago');

            comprobante_pago.addEventListener('change', function() {
                if (this.files.length > 5) {
                    this.value = "";
                    return Swal.fire({
                        title: "Sobrepasaste el número de archivos",
                        text: "El número máximo de archivos es de 5 y el tamaño máximo de cada archivo es de 2 MB",
                        icon: "warning",
                        confirmButtonText: "OK",
                    })
                }
                this.files.forEach(file => {
                    if (!validar_peso(file)) {
                        this.value = ""
                        return
                    }
                })
            })
        }

        function validar_peso(file, pesoMax = 2097152) {
            if (file.size > pesoMax) {
                Swal.fire({
                    title: "Archivo muy pesado",
                    html: `El archivo: <strong>${file.name}</strong> excede el peso limite de 2MB`,
                    icon: "warning",
                    confirmButtonText: "OK",
                })
                return false;
            }
            return validarExtensionArchivo(file);
        }
        
        function validarExtensionArchivo(file) {
            const extensionesValidas = ['jpg', 'jpeg', 'png', ];

            const extension = file.name.toLowerCase().split('.').pop();
            if (!extensionesValidas.includes(extension)) {
                Swal.fire({
                    title: "Tipo de archivo no válido",
                    html: `Solo se permite imagenes de tipo <strong>${extensionesValidas.join(', ')}</strong>`,
                    icon: "warning",
                    confirmButtonText: "OK",
                })
                return false;
            }
            return true;
        }
    </script>
@endsection
