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
                                    <h3 class="card-label">Planificaciones</h3>
                                    <a href="{{ route('planificaciones.crear') }}" class="btn btn-xs btn-primary btn-icon"
                                        data-toggle="tooltip" title="Ingresar Colaborador"><i
                                            class="fa fa-plus fa-xl"></i></a>
                                </div>

                            </div>
                            <div class="card-body">

                                <!--begin: Datatable-->
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th class="no-exportar">#</th>
                                            <th data-priority="1">Identificacion</th>
                                            <th data-priority="2">Cliente</th>
                                            <th>Producto</th>
                                            <th>Plantilla</th>
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
    @include('soporte.admin.capacitaciones.inc.delete_modal')
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
                    url: "{{ route('planificaciones.index') }}",
                    type: 'GET',
                    data: function(data) {
                        data.estado = $('#estado').val();
                    }
                },
                columns: [{
                        data: 'planificacionesid',
                        name: 'planificacionesid',
                        searchable: false,
                        visible: false
                    },
                    {
                        data: 'identificacion',
                        name: 'clientes.identificacion',
                    },
                    {
                        data: 'razonsocial',
                        name: 'clientes.razonsocial'
                    },
                    {
                        data: 'descripcion',
                        name: 'productos.descripcion'
                    },
                    {
                        data: 'plantilla',
                        name: 'planificaciones.descripcion'
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
            //Clic en boton buscar
            $('#kt_search').on('click', function(e) {
                e.preventDefault();
                table.draw();
            });
            //Clic en boton resetear
            $('#kt_reset').on('click', function(e) {
                $("#estado").val('');
                $('#estado').change();
                table.draw();
            });
            //Mostrar div de busqueda
            $('#filtrar').on('click', function(e) {
                $("#filtro").toggle(500);
            });
        });
    </script>
@endsection
