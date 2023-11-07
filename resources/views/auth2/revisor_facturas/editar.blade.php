@php
    $detallePagos = json_decode($factura->detalle_pagos);
    $noRegistrado = !isset($detallePagos->cobros_id_perseo);
@endphp
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
                                            @include('auth2.revisor_facturas.inc.toolbar_facturas')
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
@if ($noRegistrado)
    @section('modal')
        @include('auth2.revisor_facturas.inc.modal_cobros')
    @endsection
@endif
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

        $("#btnRegistrarCobro").click(function() {
            $("#modalCobros").modal("show");
            $("#modalFormCobros").attr("action", "{{ route('cobros.registrar.sistema') }}");
        });
    </script>
@endsection
