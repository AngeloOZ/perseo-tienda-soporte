@extends('tienda.layouts.app')
@section('titulo', 'Resumen compra')
@section('descripcion', 'Productos listos para la compra')
@section('imagen', asset('assets/media/firmas.jpg'))

{{-- @section('navidad')
    <script src="https://app.embed.im/snow.js" defer></script>
@endsection --}}

@section('contenido')
<div class="content d-flex flex-column flex-column-fluid w-100 mx-auto p-0">
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom">
                <div class="card-header" style="min-height: 50px">
                    <h3 class="card-title">
                        Datos de Facturación
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            @if ($cupon['exists'])
                            @if ($cupon['isValid'])
                            <div class="alert alert-custom alert-notice alert-light-primary fade show mb-5" role="alert">
                                <div class="alert-icon">
                                    <i class="flaticon2-information"></i>
                                </div>
                                @if ($cupon['cupon']->tipo == 1)
                                <div class="alert-text"><strong>¡Genial!</strong> Has aplicado un cupón por
                                    un <strong>{{ $cupon['cupon']->descuento }}% de descuento</strong>, te
                                    recomendamos concretar la compra lo más pronto posible, ya que el cupón
                                    puede expirar o agotarse</div>
                                @else
                                <div class="alert-text"><strong>¡Genial!</strong> Has aplicado un cupón de
                                    <strong>+3 Meses en planes anuales</strong>, te recomendamos concretar
                                    la compra lo más pronto posible, ya que el cupón puede expirar o
                                    agotarse
                                </div>
                                @endif
                                <div class="alert-close">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">
                                            <i class="ki ki-close"></i>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-custom alert-notice alert-light-danger fade show mb-5" role="alert">
                                <div class="alert-icon">
                                    <i class="flaticon2-warning"></i>
                                </div>
                                <div class="alert-text">El cupón que estás tratando de aplicar no es válido,
                                    esto se debe a que el cupón ha cumplido el stock máximo o ya expiro</div>
                                <div class="alert-close">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">
                                            <i class="ki ki-close"></i>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            @endif
                            @endif
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="text-left">Detalle</th>
                                            <th class="text-left">Cantidad</th>
                                            <th class="text-left">Precio Unitario</th>
                                            <th class="text-left">Total</th>
                                            <th class="text-left">Eliminar</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body_table">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2"></td>
                                            <td class="font-weight-bolder text-left">Subtotal</td>
                                            <td class="font-weight-bolder text-right" id="subTotal"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"></td>
                                            <td class="font-weight-bolder text-left">Descuento</td>
                                            <td class="font-weight-bolder text-right" id="descuento"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"></td>
                                            <td class="font-weight-bolder text-left">IVA</td>
                                            <td class="font-weight-bolder text-right" id="iva"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"></td>
                                            <td class="font-weight-bolder text-left">Total</td>
                                            <td class="font-weight-bolder text-right" id="total"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            @include('tienda.datos_facturacion')
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
<script>
    let carritoProductos = [];

    const campos = {
        ruc: false,
        empresa: false,
        direccion: false,
        correo: false,
        telefono: false
    }

    if (sessionStorage.getItem('cart')) {
        carritoProductos = JSON.parse(sessionStorage.getItem('cart'));
        contarItemsCarrito();
    }

    $(document).ready(function() {
        cargarItems();
        removerProductos();
        recuperarInformacion();
        const formInputs = document.getElementById('form-datos-facturacion');
        formInputs.addEventListener('submit', submitFormulario);
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

        validarFormulario();
        if (campos.telefono && campos.correo) {
            await validarCorreoTelefono();
        }
        if (campos.ruc &&
            campos.empresa &&
            campos.direccion &&
            campos.correo &&
            campos.telefono
        ) {
            const usuario = {
                ruc: this.inputRuc.value,
                nombre: this.inputEmpresa.value,
                direccion: this.inputDireccion.value,
                correo: this.inputCorreo.value,
                telefono: this.inputTelefono.value,
                observacion: this.inputObservacion.value,
            }
            Cookies.set("cxt", JSON.stringify(usuario));
            Cookies.set('cart_tienda', JSON.stringify(carritoProductos));
            location.href = "{{ route('tienda.finalizar_compra', $vendedor->usuariosid) }}"
            btnSend.removeAttribute('disabled');
        } else {
            btnSend.removeAttribute('disabled');
        }
    }

    async function validarCorreoTelefono() {
        const formInputs = document.getElementById('form-datos-facturacion');
        const helperTextPhone = document.getElementById('helperTextTelefono');
        const helperTextCorreo = document.getElementById('helperTextCorreo');

        const datasend = {
            _token: '{{ csrf_token() }}',
            correo: formInputs.inputCorreo.value,
            celular: formInputs.inputTelefono.value
        };

        const rest = await fetch("{{ route('admin.verificaremailcelular') }}", {
            method: 'POST',
            body: JSON.stringify(datasend),
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const json = await rest.json();
        const [valor1, valor2] = json;

        if (valor1 == 1) {
            helperTextCorreo.classList.add('d-none');
            campos.correo = true;
        } else {
            helperTextCorreo.textContent = "El correo ingresado no es válido";
            helperTextCorreo.classList.remove('d-none')
            campos.correo = false;
        }

        if (valor2 == 1) {
            helperTextPhone.classList.add('d-none');
            campos.telefono = true;
        } else {
            helperTextPhone.textContent = "El número de teléfono ingresado no es válido";
            helperTextPhone.classList.remove('d-none')
            campos.telefono = false;
        }
    }

    function validarFormulario() {

        if ($("#inputRuc").val().trim().length < 1) {
            $('#helperTextRuc').text("Este campo no puede estár vacío")
            $('#helperTextRuc').removeClass("d-none");
            campos.ruc = false;
        } else {
            $('#helperTextRuc').addClass("d-none");
            campos.ruc = true;
        }

        if ($("#inputEmpresa").val().trim().length < 1) {
            $('#helperTextEmpresa').text("Este campo no puede estár vacío")
            $('#helperTextEmpresa').removeClass("d-none");
            campos.empresa = false;
        } else {
            $('#helperTextEmpresa').addClass("d-none");
            campos.empresa = true;
        }

        if ($("#inputDireccion").val().trim().length < 1) {
            $('#helperTextDireccion').text("Este campo no puede estár vacío")
            $('#helperTextDireccion').removeClass("d-none");
            campos.direccion = false;
        } else {
            $('#helperTextDireccion').addClass("d-none");
            campos.direccion = true;
        }

        if ($("#inputCorreo").val().trim().length < 1) {
            $('#helperTextCorreo').text("Este campo no puede estár vacío")
            $('#helperTextCorreo').removeClass("d-none");
            campos.correo = false;
        } else {
            $('#helperTextCorreo').addClass("d-none");
            campos.correo = true;
        }

        if ($("#inputTelefono").val().trim().length < 1) {
            $('#helperTextTelefono').text("Este campo no puede estár vacío")
            $('#helperTextTelefono').removeClass("d-none");
            campos.telefono = false;
        } else {
            $('#helperTextTelefono').addClass("d-none");
            campos.telefono = true;
        }


    }

    async function recuperarInformacion() {
        const inputEmpresa = document.getElementById('inputRuc');
        const spiner = document.getElementById('spinner');
        const helperText = inputRuc.nextElementSibling;

        inputRuc.addEventListener('blur', function() {
            const text = this.value;
            if (text != "") {
                var extraer = text.substr(10, 3);
                if (text.length === 13 && extraer == "001") {
                    helperText.classList.add('d-none');
                    campos.ruc = true;
                } else if (text.length == 10) {
                    var cad = document.getElementById('inputRuc').value.trim();
                    var total = 0;
                    var longitud = cad.length;
                    var longcheck = longitud - 1;
                    var digitos = cad.split('').map(Number);
                    var codigo_provincia = digitos[0] * 10 + digitos[1];
                    if (cad !== "" && longitud === 10) {

                        if (cad != '2222222222' && codigo_provincia >= 1 && (codigo_provincia <= 24 ||
                                codigo_provincia == 30)) {
                            for (i = 0; i < longcheck; i++) {
                                if (i % 2 === 0) {
                                    var aux = cad.charAt(i) * 2;
                                    if (aux > 9) aux -= 9;
                                    total += aux;
                                } else {
                                    total += parseInt(cad.charAt(i));
                                }
                            }
                            total = total % 10 ? 10 - total % 10 : 0;

                            if (cad.charAt(longitud - 1) == total) {
                                campos.ruc = true;

                            } else {
                                $("#inputRuc").val("");
                                $('#helperTextRuc').text("El RUC ingresado no es válida")
                                $('#helperTextRuc').removeClass("d-none");
                                campos.ruc = false;
                            }
                        } else {
                            $("#inputRuc").val("");
                            $('#helperTextRuc').text("La cédula ingresada no es válida")
                            $('#helperTextRuc').removeClass("d-none");
                            campos.ruc = false;
                        }
                    }
                } else {
                    $("#inputRuc").val("");
                    $('#helperTextRuc').text("La cédula o RUC ingresado no es válido")
                    $('#helperTextRuc').removeClass("d-none");
                    campos.ruc = false;
                }

                if (campos.ruc) {
                    $('#helperTextRuc').addClass("d-none");

                    spiner.classList.add('spinner', 'spinner-success', 'spinner-right');
                    $.ajax({
                        url: "{{ route('firma.index') }}",
                        headers: {
                            'usuario': 'perseo',
                            'clave': 'Perseo1232*'
                        },
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            identificacion: text
                        },
                        success: function(data) {
                            spiner.classList.remove('spinner', 'spinner-success',
                                'spinner-right');
                            if (data.identificacion) {
                                $("#inputEmpresa").val(data.razon_social);
                                $("#inputCorreo").val(data.correo);
                                $("#inputDireccion").val(data.direccion);
                                $("#inputTelefono").val(data.telefono2);
                            }
                        }
                    });
                }
            }
        })
    }

    /* -------------------------------------------------------------------------- */
    /*                       funciones para items y carrito                       */
    /* -------------------------------------------------------------------------- */
    function cargarItems() {
        const bodyTable = document.getElementById('body_table');
        const fragmanet = document.createDocumentFragment();
        carritoProductos.forEach(item => {
            const TR = document.createElement('TR');
            TR.innerHTML = `
                <td class="d-flex align-items-center ">
                    <p class="text-dark ">${item.descripcion}</p>
                </td>
                <td class="text-left align-middle font-weight-bolder font-size-h5">${item.cantidad}</td>
                <td class="text-left align-middle font-weight-bolder font-size-h5">${item.precio.toFixed(2)}</td>
                <td class="text-left align-middle font-weight-bolder font-size-h5">${(item.precio * item.cantidad).toFixed(2)}</td>
                <td class="text-left align-middle">
                    <button data-action="remover" data-id-producto="${item.productosid}" class="btn btn-danger font-weight-bolder font-size-sm">X</button>
                </td>
                `;
            fragmanet.appendChild(TR);
        });
        bodyTable.innerHTML = '';
        bodyTable.appendChild(fragmanet);
        calcularTotal();
    }

    function calcularTotal() {
        const subTotalText = document.getElementById('subTotal');
        const ivaText = document.getElementById('iva');
        const totalText = document.getElementById('total');
        const descuentoText = document.getElementById('descuento');
        const descuentoValue = {{ $cupon['cupon']->descuento ?? 0 }};

        let subTotal = 0,
            descuento = 0,
            iva = 0,
            total = 0,
            total2 = 0;
        carritoProductos.forEach(item => {
            subTotal += item.cantidad * item.precio;
            // iva += item.cantidad * ((item.precio * 12) / 100).toFixed(2);
            // total += item.cantidad * item.precioiva.toFixed(2)
        })
        if (subTotal > 0) {
            descuento = (subTotal * descuentoValue) / 100;
            descuento = parseFloat(descuento.toFixed(2));

            iva = ((subTotal - descuento) * 12) / 100;
            iva = parseFloat(iva.toFixed(3));

            total = subTotal - descuento + iva;
        }

        subTotalText.textContent = "" + subTotal.toFixed(2);
        descuentoText.textContent = "" + descuento.toFixed(2);
        ivaText.textContent = "" + iva.toFixed(2);
        totalText.textContent = "" + total.toFixed(2);
    }

    function removerProductos() {
        const bodyTable = document.getElementById('body_table');
        bodyTable.addEventListener('click', e => {
            if (e.target.matches('[data-action="remover"]')) {
                const id = e.target.dataset.idProducto;
                const productos = carritoProductos.filter(items => items.productosid != id);
                carritoProductos = [...productos];
                sessionStorage.setItem('cart', JSON.stringify(carritoProductos));
                contarItemsCarrito();
                cargarItems();
            }
        })
    }

    function contarItemsCarrito() {
        const items = carritoProductos.reduce((sum, curr) => sum + curr.cantidad, 0);
        document.getElementById('cart_items').textContent = items;
        document.getElementById('cart_items_moblie').textContent = items;
    }
</script>
@endsection