@extends('auth2.layouts.app')

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
                                    <div class="card-toolbar">
                                        <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                            <div class="btn-group" role="group" aria-label="First group">
                                                <a href="{{ url()->previous() ?? route('facturas.revisor') }}"
                                                    class="btn btn-secondary btn-icon" data-toggle="tooltip"
                                                    title="Volver"><i class="la la-long-arrow-left"></i></a>

                                                @if (isset($liberable) && $liberable && Auth::user()->liberador == 1)
                                                    @if ($factura->facturado == 1 && $factura->estado_pago >= 1 && $factura->liberado == 0)
                                                        <button id="btnLiberarManual" type="submit"
                                                            class="btn btn-warning btn-icon" data-toggle="tooltip"
                                                            title="Marcar como liberado"><i class="la la-hand-pointer"></i>
                                                        </button>
                                                    @endif
                                                    @if ($factura->facturado == 1 && $factura->estado_pago >= 1)
                                                        <a href="{{ route('facturas.ver.liberar', $factura->facturaid) }}"
                                                            class="btn btn-info btn-icon" data-toggle="tooltip"
                                                            title="Ver productos a liberar"><i class="la la-rocket"></i></a>
                                                    @endif
                                                @endif
                                                @if (isset($liberable) && !$liberable)
                                                    @if ($factura->liberado == 0 && $factura->estado_pago >= 1)
                                                        <a href="{{ route('facturas.liberar_producto_manual', $factura->facturaid) }}"
                                                            id="btnLiberar" class="btn btn-warning btn-icon"
                                                            data-toggle="tooltip" title="Liberar producto manual"><i
                                                                class="la la-rocket"></i>
                                                        </a>
                                                    @endif
                                                @endif
                                                @if ($factura->facturado != 0 && $factura->autorizado == 0)
                                                    <a href="{{ route('factura.autorizar', $factura->facturaid) }}"
                                                        id="btnAutorizar" class="btn btn-success btn-icon"
                                                        data-toggle="tooltip" title="Autorizar factura"><i
                                                            class="la la-check-circle-o"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
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
                                            @include('auth2.revisor_facturas.datos')
                                            @include('auth2.revisor_facturas.productos')
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="archivos-1" role="tabpanel" aria-labelledby="archivos-tab-1">
                                    @include('auth2.revisor_facturas.pagos')
                                </div>
                            </div>
                            {{-- Fin Contenido TABS --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        const btnLib = document.getElementById('btnLiberarManual');

        btnLib?.addEventListener('click', () => {
            Swal.fire({
                title: "Cambiar estado",
                text: "Esta acción solo cambiará, ha estado \"LIBERADO\" internamente, no afectará nada en el licenciador",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Cambiar estado",
                cancelButtonText: "cancelar",
                reverseButtons: true,
            }).then(function(result) {
                if (result.value) {
                    location.href =
                        "{{ route('facturas.liberar_producto_manual', ['factura' => $factura->facturaid]) }}"
                }
            });
        })
    </script>
@endsection
