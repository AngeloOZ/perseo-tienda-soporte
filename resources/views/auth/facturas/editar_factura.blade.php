@extends('auth.layouts.app')

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-custom" id="kt_page_sticky_card">
                            {{-- Inicio de tabs buttons --}}
                            <div class="card-header d-block">
                                <div class="d-flex justify-content-between flex-wrap mb-3" style="">
                                    <div class="card-title">
                                        <h3 class="card-label"> Factura</h3>
                                    </div>
                                    @include('auth.facturas.includes.toolbar')
                                </div>
                                <ul class="nav nav-pills mb-5" id="myTab1" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="datos-tab" data-toggle="tab" href="#datosTab">
                                            <span class="nav-icon">
                                                <i class="flaticon-interface-3"></i>
                                            </span>
                                            <span class="nav-text">Datos factura</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="archivos-tab-1" data-toggle="tab" href="#archivos-1"
                                            aria-controls="archivos">
                                            <span class="nav-icon">
                                                <i class="flaticon-piggy-bank"></i>
                                            </span>
                                            <span class="nav-text">Pagos</span>
                                        </a>
                                    </li>
                                    @if ($soporte)
                                        <li class="nav-item">
                                            <a class="nav-link" id="implementacion-tab-1" data-toggle="tab"
                                                href="#implementacion-1" aria-controls="implementacion">
                                                <span class="nav-icon">
                                                    <i class="flaticon-imac"></i>
                                                </span>
                                                <span class="nav-text">Implementaciones</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            {{-- Fin de tabs buttons --}}
                            {{-- Contenido TABS --}}
                            <div class="tab-content " id="myTabContent1">
                                <div class="tab-pane fade show active" id="datosTab" role="tabpanel"
                                    aria-labelledby="datos-tab">
                                    <form class="form" action="{{ route('facturas.actualizar', $factura->facturaid) }}"
                                        id="formFactura" method="POST" enctype="multipart/form-data">
                                        @method('PUT')
                                        <div class="card-body">
                                            @csrf
                                            @include('auth.facturas.includes.datos')
                                            @include('auth.facturas.includes.productos')
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="archivos-1" role="tabpanel" aria-labelledby="archivos-tab-1">
                                    @include('auth.facturas.includes.pagos')
                                </div>
                                @if ($soporte)
                                    @include('auth.facturas.includes.implementacion')
                                @endif
                            </div>
                            {{-- Fin Contenido TABS --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    @include('auth.facturas.includes.anular_modal')
    @include('auth.facturas.includes.implementacion_modal')
@endsection

@section('script')
    <script>
        var comprobantePago = new KTImageInput('kt_image_1');

        let listaProductos = {{ Illuminate\Support\Js::from($factura->productos2) }}
        $(document).ready(function() {
            const btnSubmit = document.getElementById('buttonSave')
            const form = document.getElementById('formFactura');

            btnSubmit?.addEventListener('click', _ => {
                form.submit();
            })

            form?.addEventListener('submit', submitFormulario);
            cancelar_factura();
            removerProductos();
            init_subir_archivos()
        });

        async function submitFormulario(e) {
            e.preventDefault();
            if (listaProductos.length == 0) {
                Swal.fire({
                    title: "No hay productos",
                    text: "No se puede generar una factura sin productos",
                    icon: "error",
                });
                return;
            } else {
                this.submit();
            }
        }

        function removerProductos() {
            const bodyTable = document.getElementById('body_table');
            bodyTable.addEventListener('click', e => {
                if (e.target.matches('[data-action="remover"]')) {
                    const id = e.target.dataset.idProducto;
                    const productos = listaProductos.filter(items => items.productosid != id);
                    listaProductos = [...productos];
                    cargarItems();
                    actualizarProductos();
                }
            })
        }

        function cargarItems() {
            const bodyTable = document.getElementById('body_table');
            const fragmanet = document.createDocumentFragment();
            listaProductos.forEach(item => {
                const TR = document.createElement('TR');
                TR.innerHTML = `
                <td class="d-flex align-items-center ">
                    <p class="text-dark ">${item.descripcion}</p>
                </td>
                <td class="text-right align-middle font-weight-bolder font-size-h5">${item.cantidad}</td>
                <td class="text-right align-middle font-weight-bolder font-size-h5">$${item.precio.toFixed(2)}</td>
                <td class="text-right align-middle font-weight-bolder font-size-h5">$${(item.precio * item.cantidad).toFixed(2)}</td>
                <td class="text-right align-middle">
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
            const descuentoValue = {{ $cupon->descuento ?? 0 }};

            let subTotal = 0,
                descuento = 0,
                iva = 0,
                total = 0,
                total2 = 0;
            listaProductos.forEach(item => {
                subTotal += item.cantidad * item.precio;
            })
            if (subTotal > 0) {
                descuento = (subTotal * descuentoValue) / 100;
                descuento = parseFloat(descuento.toFixed(2));

                iva = ((subTotal - descuento) * 12) / 100;
                iva = parseFloat(iva.toFixed(3));
                total = subTotal - descuento + iva;
            }

            subTotalText.textContent = "$" + subTotal.toFixed(2);
            descuentoText.textContent = "$" + descuento.toFixed(2);
            ivaText.textContent = "$" + iva.toFixed(2);
            totalText.textContent = "$" + total.toFixed(2);
        }

        function actualizarProductos() {
            const input = document.getElementById('inputProductos');
            $prod = listaProductos.map(item => {
                return {
                    productoid: item.productosid,
                    cantidad: item.cantidad,
                }
            })
            input.value = JSON.stringify($prod);
        }

        function cancelar_factura() {
            const numeroNotaCredito = document.getElementById('numeroNotaCredito');
            const btnAnularFactura = document.getElementById('btnAnularFactura');

            numeroNotaCredito?.addEventListener('keyup', function() {
                if (this.value.length >= 2) {
                    btnAnularFactura?.removeAttribute('disabled');
                } else {
                    btnAnularFactura?.setAttribute('disabled', 'true');
                }
            })
        }

        /* -------------------------------------------------------------------------- */
        /*                      functiones para implementaciones                      */
        /* -------------------------------------------------------------------------- */
        $(document).on('click', '.modal-implementacion', function(e) {
            e.preventDefault();
            var url = $(this).data("href");
            $("#implementacion-modal").modal("show");
            $("#implementacion-link").attr("action", url);
        });

        const formModalImplementacion = document.getElementById('implementacion-link');

        formModalImplementacion?.addEventListener('submit', function(event) {
            event.preventDefault();
            const btnSubmit = this.btnSendNumber;
            btnSubmit.setAttribute('disabled', 'true');
            this.submit();
        })

        function validateNumber(text) {

            if (text.value.length > 10) {
                text.value = text.value.slice(0, 10);
            }
            if (text.value.length == 10) {
                $('#btnSendNumber').removeAttr('disabled')
            }
        }
    </script>
    @yield('script-pagos')
@endsection
