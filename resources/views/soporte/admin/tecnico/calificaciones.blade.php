@extends('soporte.auth.layouts.app')
@section('title_page', 'Listado de técnicos')
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
                                    <h3 class="card-label"> Listado de tickets activos </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-10" style="font-size: 14px;">
                                    <p><strong>Pregunta 1: </strong> ¿Cómo calificaría la amabilidad y cordialidad del servicio de atención al cliente que recibió en su última interacción con nuestro equipo de soporte?</p>
                                    <p><strong>Pregunta 2: </strong> ¿Qué tan satisfecho está con la solución que recibió para su problema en su última interacción con nuestro equipo de soporte?</p>
                                </div>
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th class="no-exportar">#</th>
                                            <th class="width_pregunta">Calificacion <br />pregunta<br/>1</th>
                                            <th class="width_pregunta">Calificacion <br />pregunta<br/>2</th>
                                            <th class="width_test">Problema a resolver</th>
                                            <th class="width_test">Comentario</th>
                                            <th>WhatsApp</th>
                                            <th>Fecha de<br/>calificaicon</th>
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
                    [0, 'asc']
                ],
                //Guardar pagina, busqueda, etc
                stateSave: true,
                //Trabajar del lado del server
                serverSide: true,
                //Peticion ajax que devuelve los registros
                ajax: {
                    url: "{{ route('soporte.ver.calificaciones.tecnicos') }}",
                    type: 'GET'

                },
                columns: [{
                        data: 'encuesta_soporte_id',
                        name: 'encuesta_soporte_id',
                        searchable: false,
                        // visible: false,
                    },
                    {
                        data: 'pregunta_1',
                        name: 'pregunta_1',
                        className: 'width_pregunta',

                    }, {
                        data: 'pregunta_2',
                        name: 'pregunta_2',
                    },
                    {
                        data: 'motivo',
                        name: 'motivo',
                        className: 'text-left',
                        width: '100px',
                    },
                    {
                        data: 'comentario',
                        name: 'comentario',
                        className: 'text-left',
                        width: '100px',
                    },
                    {
                        data: 'contacto',
                        name: 'contacto',
                    },
                    {
                        data: 'fecha_creacion',
                        name: 'fecha_creacion',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: "text-center"
                    },
                ],
            });

        });
    </script>
@endsection
