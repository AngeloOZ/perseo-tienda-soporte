@extends('soporte.auth.layouts.app')
@section('title_page', 'Listado de calificaciones')
@php
    $currentUser = Auth::guard('tecnico')->user();
    $disabled = 'disabled';
    if ($currentUser->distribuidoresid == 2) {
        $disabled = '';
    }
@endphp
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
                                    <h3 class="card-label"> Listado de calificaciones </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div>
                                    <p class="font-size-h6"><strong>Pregunta 1:</strong> ¿Cómo calificaría la amabilidad y
                                        cordialidad del servicio de atención al cliente que recibió en su última interacción
                                        con nuestro equipo de soporte?</p>
                                    <p class="font-size-h6"><strong>Pregunta 2:</strong> ¿Qué tan satisfecho está con la
                                        solución que recibió para su problema en su última interacción con nuestro equipo de
                                        soporte?</p>
                                </div>

                                <div class="mb-5 mt-10">
                                    <div class="row mb-8">
                                        <div class="col-lg-2 mb-lg-0 mb-6">
                                            <label>Distribuidor:</label>
                                            <select class="form-control datatable-input" id="filtroDistribuidor"
                                                {{ $disabled }}>
                                                <option value="">Todos</option>
                                                <option value="1"
                                                    {{ $currentUser->distribuidoresid == 1 ? 'selected' : '' }}>Perseo Alfa
                                                </option>
                                                <option value="2"
                                                    {{ $currentUser->distribuidoresid == 2 ? 'selected' : '' }}>Perseo
                                                    Matriz
                                                </option>
                                                <option value="3"
                                                    {{ $currentUser->distribuidoresid == 3 ? 'selected' : '' }}>Perseo Delta
                                                </option>
                                                <option value="4"
                                                    {{ $currentUser->distribuidoresid == 4 ? 'selected' : '' }}>Perseo Omega
                                                </option>
                                            </select>

                                        </div>

                                        <div class="col-lg-2 mb-lg-0 mb-6">
                                            <label>Calificación:</label>
                                            <select class="form-control datatable-input" id="filtroCalificacion">
                                                <option value="">Todas</option>
                                                <option value="1">Positivas</option>
                                                <option value="2" selected>Negativas</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-2 mb-lg-0 mb-6">
                                            <label>Estado:</label>
                                            <select class="form-control datatable-input" id="filtroEstado">
                                                <option value="">Todas</option>
                                                <option value="1" selected>Pendiente</option>
                                                <option value="2">En revisión</option>
                                                <option value="3">Revisadas</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-3 mb-lg-0 mb-6">
                                            <label>Técnicos:</label>
                                            <select class="form-control select2 datatable-input" id="filtroTecnicos">
                                                <option value="" selected>Todos</option>
                                                @foreach ($tecnicos as $tecnico)
                                                    <option value="{{ $tecnico->tecnicosid }}">{{ $tecnico->nombres }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-3 mb-lg-0 mb-6">
                                            <label>Fecha:</label>
                                            <div class="input-group" id='kt_fecha'>
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="la la-calendar-check-o"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control" autocomplete="off"
                                                    placeholder="Rango de Fechas" id="filtroFecha">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row ">
                                        <div class="col-lg-12">
                                            <button class="btn btn-primary btn-primary--icon" id="kt_search">
                                                <span>
                                                    <i class="la la-search"></i>
                                                    <span>Buscar</span>
                                                </span>
                                            </button>&#160;&#160;
                                            <button class="btn btn-secondary btn-secondary--icon" id="kt_reset">
                                                <span>
                                                    <i class="la la-close"></i>
                                                    <span>Reiniciar</span>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th class="no-exportar">#</th>
                                            <th>Número de ticket</th>
                                            <th>Razón social</th>
                                            <th>Contáctos</th>
                                            <th>Pregunta 1</th>
                                            <th>Pregunta 2</th>
                                            <th>Comentario</th>
                                            <th>Técnico</th>
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
    @include('soporte.admin.calificaciones.modal.estado')
    @include('soporte.admin.calificaciones.modal.justificacion')
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
                    url: "{{ route('calificaciones.filtro.listado') }}",
                    type: 'POST',
                    data: function(d) {
                        //Valores de filtro a enviar a la ruta clientes.index
                        d.distribuidor = $("#filtroDistribuidor").val();
                        d.tecnico = $("#filtroTecnicos").val();
                        d.calificacion = $("#filtroCalificacion").val();
                        d.estado = $("#filtroEstado").val();
                        d.fecha = $("#filtroFecha").val();
                    }
                },
                columns: [{
                        data: 'encuesta_soporte_id',
                        name: 'encuesta_soporte_id',
                        searchable: false,
                        visible: true,
                    },
                    {
                        data: 'numero_ticket',
                        name: 'numero_ticket',
                        width: '100px',
                    },
                    {
                        data: 'razon_social',
                        name: 'razon_social',
                        width: '150px',
                    },
                    {
                        data: 'contacto',
                        name: 'contacto',
                        width: '150px',
                        className: "text-left",
                    },
                    {
                        data: 'pregunta_1',
                        name: 'pregunta_1',
                    },
                    {
                        data: 'pregunta_2',
                        name: 'pregunta_2',
                    },
                    {
                        data: 'comentario',
                        name: 'comentario',
                        width: '150px'
                    },
                    {
                        data: 'nombre_tecnico',
                        name: 'nombre_tecnico',
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

            });

            //Clic en boton buscar
            $('#kt_search').on('click', function(e) {
                e.preventDefault();
                table.draw();
            });

            $('#kt_reset').on('click', function(e) {
                $("#filtroTecnicos").val('');
                $("#filtroEstado").val('1');
                $("#filtroAsignados").val('');
                $("#filtroFecha").val('');
                $("#filtroDistribuidor").val('{{ $currentUser->distribuidoresid }}');
                $("#filtroTecnicos").trigger('change');
                table.draw();
            });

            $('#filtrar').on('click', function(e) {
                $("#filtro").toggle(500);
            });

            //Inicializar rango de fechas
            $('#kt_fecha').daterangepicker({
                autoUpdateInput: false,
                format: "DD-MM-YYYY",
                locale: {
                    "separator": " - ",
                    "applyLabel": "Aplicar",
                    "cancelLabel": "Cancelar",
                    "fromLabel": "DE",
                    "toLabel": "HASTA",
                    "customRangeLabel": "Personalizado",
                    "daysOfWeek": [
                        "Dom",
                        "Lun",
                        "Mar",
                        "Mie",
                        "Jue",
                        "Vie",
                        "Sáb"
                    ],
                    "monthNames": [
                        "Enero",
                        "Febrero",
                        "Marzo",
                        "Abril",
                        "Mayo",
                        "Junio",
                        "Julio",
                        "Agosto",
                        "Septiembre",
                        "Octubre",
                        "Noviembre",
                        "Diciembre"
                    ],
                    "firstDay": 1
                },
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ultimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Ultimos 30 días ': [moment().subtract(29, 'days'), moment()],
                    'Mes Actual': [moment().startOf('month'), moment().endOf('month')],
                    'Mes Anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    'Año Actual': [moment().startOf('year'), moment().endOf('year')],
                    'Año Anterior': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1,
                        'year').endOf('year')],
                },
                buttonClasses: ' btn',
                applyClass: 'btn-primary',
                cancelClass: 'btn-secondary',
                alwaysShowCalendars: true,
                showDropdowns: true,
            }, function(start, end, label) {
                $('#kt_fecha .form-control').val(start.format('DD-MM-YYYY') + ' / ' + end.format(
                    'DD-MM-YYYY'));
            });


            $(document).on('click', '.change-state-modal', function(e) {
                e.preventDefault();
                const url = $(this).data("href");
                const estado = $(this).data("estado");
                $("#change-state-modal").modal("show");
                $("#form-change-state").attr("action", url);
                $("#changeStateSelect").val(estado);
            });

            $(document).on('click', '.justificacion-modal', function(e) {
                e.preventDefault();
                const url = $(this).data("href");
                $("#justificacion-modal").modal("show");
                $("#justificacion-modal-form").attr("action", url);
                // $("#changeStateSelect").val(estado);
            });

        });
    </script>
@endsection
