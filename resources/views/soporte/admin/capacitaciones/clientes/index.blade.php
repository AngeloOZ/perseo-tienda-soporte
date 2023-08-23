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
                                    <h3 class="card-label">Clientes</h3>
                                    <a href="{{ route('clientes.crear') }}" class="btn btn-xs btn-primary btn-icon"
                                        data-toggle="tooltip" title="Ingresar Clientes"><i class="fa fa-plus fa-xl"></i></a>
                                </div>
                                <div class="card-toolbar">

                                    <a href="#" class="btn btn-primary font-weight-bolder" id="filtrar">
                                        <span class="svg-icon svg-icon-md">
                                            <i class="la la-filter"></i>
                                        </span>Filtrar
                                    </a>
                                </div>

                            </div>
                            <div class="card-body">
                                <!--begin: Search Form-->
                                <div class="mb-15" id="filtro" style="display: none;">
                                    <div class="row mb-8">
                                        <div class="col-lg-12 col-xs-12  d-xs-block d-md-flex">
                                            <div class="col-lg-7 col-md-5 col-xs-10 mb-lg-0 mb-6">
                                                <label>Estado:</label>
                                                <select class="form-control select2" id="estado" name="estado">
                                                    <option value="">Todos</option>
                                                    <option value="1">Activo</option>
                                                    <option value="0">Inactivo</option>
                                                </select>

                                            </div>
                                            <div class="col-lg-5 col-md-7 col-xs-2 mt-5 text-right">
                                                <div class="mt-2">
                                                    <button class="btn btn-primary btn-primary--icon  mb-0" id="kt_search">
                                                        <span>
                                                            <i class="la la-search"></i>
                                                            <span>Buscar</span>
                                                        </span>
                                                    </button>&#160;&#160;
                                                    <button class="btn btn-secondary btn-secondary--icon " id="kt_reset">
                                                        <span>
                                                            <i class="la la-close"></i>
                                                            <span>Reiniciar</span>
                                                        </span>
                                                    </button>
                                                </div>

                                            </div>

                                        </div>

                                    </div>
                                </div>
                                <!--begin: Datatable-->
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th class="no-exportar">#</th>
                                            <th data-priority="1">Identificacion</th>
                                            <th data-priority="2">Razon Social</th>
                                            <th data-priority="3">Nombre Comercial</th>
                                            <th>Correo</th>
                                            <th>Celular</th>
                                            <th>Estado</th>
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

            $('.form-control').val('');
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
                    url: "{{ route('clientes.index') }}",
                    type: 'GET',
                    data: function(data) {
                        data.estado = $('#estado').val();
                    }
                },
                columns: [{
                        data: 'clientesid',
                        name: 'clientesid',
                        searchable: false,
                        visible: false
                    },
                    {
                        data: 'identificacion',
                        name: 'identificacion',
                        orderable: false,
                    },
                    {
                        data: 'razonsocial',
                        name: 'razonsocial'
                    },
                    {
                        data: 'nombrecomercial',
                        name: 'nombrecomercial'
                    },
                    {
                        data: 'correo',
                        name: 'correo'
                    },
                    {
                        data: 'celular',
                        name: 'celular'
                    },
                    {
                        data: 'estado',
                        name: 'estado',
                        orderable: false,

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
