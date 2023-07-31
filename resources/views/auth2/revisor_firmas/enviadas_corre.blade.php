@extends('auth2.layouts.app')

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
                                    <h3 class="card-label">Listado de firmas enviadas al correo</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th class="no-exportar">#</th>
                                            <th>Tipo Persona</th>
                                            <th data-priority="1">Identificacion</th>
                                            <th data-priority="2">Nombres</th>
                                            <th>Correo</th>
                                            <th>Celular</th>
                                            <th>Fecha de creación</th>
                                            <th>Estado</th>
                                            <th class="no-exportar">Acciones</th>
                                        </tr>
                                    </thead>
                                </table>
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
                    url: "{{ route('firma.revisor_correo') }}",
                    type: 'GET'

                },
                columns: [{
                        data: 'firmasid',
                        name: 'firmasid',
                        searchable: false,
                        visible: false
                    },
                    {
                        data: 'tipo_persona',
                        name: 'tipo_persona',

                    },
                    {
                        data: 'identificacion',
                        name: 'identificacion',

                    },
                    {
                        data: 'nombres',
                        name: 'nombres'
                    },
                    {
                        data: 'correo',
                        name: 'correo',

                    },
                    {
                        data: 'celular',
                        name: 'celular',
                    },
                    {
                        data: 'fecha_creacion',
                        name: 'fecha_creacion',

                    },
                    {
                        data: 'estado',
                        name: 'estado',

                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: "text-center"
                    },
                ],
                buttons: [{
                        extend: 'print',
                        title: 'Usuarios',
                        exportOptions: {
                            columns: ':not(.no-exportar)'
                        }
                    },
                    {
                        extend: 'copyHtml5',
                        title: 'Usuarios',
                        exportOptions: {
                            columns: ':not(.no-exportar)'
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        title: 'Usuarios',
                        exportOptions: {
                            columns: ':not(.no-exportar)'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        title: 'Usuarios',
                        exportOptions: {
                            columns: ':not(.no-exportar)'
                        }
                    },
                ]

            });


            $('#export_print').on('click', function(e) {
                e.preventDefault();
                table.button(0).trigger();
            });

            $('#export_copy').on('click', function(e) {
                e.preventDefault();
                table.button(1).trigger();
            });

            $('#export_excel').on('click', function(e) {
                e.preventDefault();
                table.button(2).trigger();
            });

            $('#export_pdf').on('click', function(e) {
                e.preventDefault();
                table.button(3).trigger();
            });

        });
    </script>
@endsection
