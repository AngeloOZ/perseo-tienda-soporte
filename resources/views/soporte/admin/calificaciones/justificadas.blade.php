@extends('soporte.auth.layouts.app')
@section('title_page', 'Calificaciones Justificadas')
@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <!--begin::Card-->
                        <div class="card card-custom card-sticky" id="kt_page_sticky_card">
                            <div class="card-header ">
                                <div class="card-title">
                                    <h3 class="card-label">Listado de calificaciones justificadas</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="form-group" style="width: 200px;">
                                        <label for="">Listado de encuestas</label>
                                        <select class="form-control" id="justificados">
                                            <option value="1">Justificados</option>
                                            <option value="0" selected>No justificados</option>
                                        </select>
                                    </div>
                                </div>
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th class="no-exportar">#</th>
                                            <th>Número de ticket</th>
                                            <th>Razón social</th>
                                            <th>Motivo de soporte</th>
                                            <th>Comentario de calificación</th>
                                            <th>Justificación</th>
                                            <th>Técnico</th>
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
@section('script')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
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
                searchDelay: 500,
                deferRender: true,
                paging: true,
                ajax: {
                    url: "{{ route('calificaciones.filtro.justificadas') }}",
                    type: 'POST',
                    data: function(d) {
                        //Valores de filtro a enviar a la ruta clientes.index
                        d.justificadas = $("#justificados").val();
                    }
                },
                columns: [{
                        data: 'encuesta_soporte_id',
                        name: 'encuesta_soporte_id',
                        searchable: false,
                        visible: true
                    },
                    {
                        data: 'numero_ticket',
                        name: 'numero_ticket',
                    },
                    {
                        data: 'razon_social',
                        name: 'razon_social',
                    },
                    {
                        data: 'motivo',
                        name: 'motivo',
                        searchable: false,
                    },
                    {
                        data: 'comentario',
                        name: 'comentario',
                        searchable: false,
                    },
                    {
                        data: 'comentario_revision',
                        name: 'comentario_revision',
                        searchable: false,
                    },
                    {
                        data: 'nombre_tecnico',
                        name: 'nombre_tecnico',
                    },
                    // {
                    //     data: 'action',
                    //     name: 'action',
                    //     orderable: false,
                    //     searchable: false,
                    //     className: "text-center"
                    // },
                ],
            });

            $('#justificados').on('change', function(e) {
                e.preventDefault();
                table.draw();
            });
        });
    </script>
@endsection
