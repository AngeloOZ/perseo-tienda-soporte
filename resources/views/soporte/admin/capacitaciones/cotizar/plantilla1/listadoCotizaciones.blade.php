@extends('soporte.auth.layouts.app')
@section('contenido')
    <style>
        #kt_datatable td {
            padding: 3px;
        }
    </style>
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <!--begin::Card-->
                        <div class="card card-custom card-sticky" id="kt_page_sticky_card">
                            <div class="card-header ">
                                <div class="card-title">
                                    <h3 class="card-label">Listado Cotizaciones</h3>
                                    <a href="{{ route('cotizarPlantilla1.index', 0) }}"
                                        class="btn btn-xs btn-primary btn-icon" data-toggle="tooltip"
                                        title="Ingresar Cotizacion"><i class="fa fa-plus fa-xl"></i></a>
                                </div>
                            </div>
                            <div class="card-body">
                                <!--begin: Datatable-->
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th data-priority="1">Fecha</th>
                                            <th data-priority="2">Prospecto</th>
                                            <th>Plantilla</th>
                                            <th> # Pagos</th>
                                            <th> Subtotal</th>
                                            <th> Iva</th>
                                            <th> Total</th>
                                            <th> Asesor</th>

                                            <th class="no-exportar">Acciones</th>
                                        </tr>
                                    </thead>
                                </table>
                                <!--end: Datatable-->
                            </div>
                        </div>
                        <!--end::Card-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var table = $('#kt_datatable').DataTable({
                dom: "<'row'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6'l>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                responsive: true,
                processing: true,
                //Combo cantidad de registros a mostrar por pantalla
                lengthMenu: [
                    [15, 25, 50, -1],
                    [15, 25, 50, 'Todos']
                ],
                //Registros por pagina
                pageLength: 15,
                //Orden inicial
                order: [
                    [0, 'desc']
                ],
                //Guardar pagina, busqueda, etc
                stateSave: true,
                //Trabajar del lado del server

                //Peticion ajax que devuelve los registros
                ajax: {
                    url: "{{ route('listadoCotizaciones.listado') }}",
                    type: 'GET'
                },
                columns: [{
                        data: 'fecha',
                        name: 'fecha',
                    },
                    {
                        data: 'prospectosnombres',
                        name: 'prospectos.razonsocial'
                    },
                    {
                        data: 'plantilla',
                        name: 'plantillasdescarga.detalle'
                    },
                    {
                        data: 'detalle_pago',
                        name: 'detalle_pago'
                    }, {
                        data: 'subtotal',
                        name: 'subtotal'
                    }, {
                        data: 'iva',
                        name: 'iva'
                    }, {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'usuariocreacion',
                        name: 'usuariocreacion'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
            table.search('').draw();
        });
    </script>
@endsection
