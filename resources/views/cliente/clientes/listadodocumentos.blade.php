@extends('frontend.layouts.app')
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
                                    <h3 class="card-label">Documentos</h3>
                                </div>
                                

                            </div>
                            <div class="card-body">
                                <!--begin: Datatable-->
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th class="no-exportar">#</th>
                                            <th data-priority="1">Descripción</th>
                                            <th>Extensión</th>
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
                serverSide: true,
                //Peticion ajax que devuelve los registros
                ajax: {
                    url: "{{ route('listadoDocumentos.listado') }}",
                    type: 'GET',
                    data: function(data) {
                        data.estado = $('#estado').val();
                    }
                },
                columns: [{
                        data: 'implementaciones_documentosid',
                        name: 'implementaciones_documentosid',
                        orderable: false,
                        searchable: false,
                        visible: false
                    },

                    
                    {
                        data: 'descripcion',
                        name: 'descripcion',
                    },
                    {
                        data: 'extencion',
                        name: 'extencion'
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
