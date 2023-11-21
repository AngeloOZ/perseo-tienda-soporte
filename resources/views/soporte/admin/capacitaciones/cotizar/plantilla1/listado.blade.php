@extends('auth.layouts.app')
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
                        <div class="card card-custom card-sticky" id="kt_page_sticky_card">
                            <div class="card-header ">
                                <div class="card-title">
                                    <h3 class="card-label">Detalles Cotizaciones</h3>
                                    <a href="{{ route('detalles.crear') }}" class="btn btn-xs btn-primary btn-icon"
                                        data-toggle="tooltip" title="Ingresar Detalle"><i class="fa fa-plus fa-xl"></i></a>
                                    <a href="{{ route('cotizarPlantilla1.index', 0) }}"
                                        class="ml-1 btn btn-xs btn-primary btn-icon" data-toggle="tooltip"
                                        title="Cotizar"><i class="fa fa-calculator fa-xl"></i></a>
                                </div>

                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th data-priority="1">Detalle</th>
                                            <th data-priority="2">Precio</th>
                                            <th class="no-exportar">Acciones</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
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
                serverSide: true,
                //Peticion ajax que devuelve los registros
                ajax: {
                    url: "{{ route('detalles.listado') }}",
                    type: 'GET'
                },
                columns: [{
                        data: 'detalle',
                        name: 'detalle',
                    },
                    {
                        data: 'precio',
                        name: 'precio'
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
