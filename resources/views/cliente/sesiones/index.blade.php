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
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="card-label">Sesiones</h3>

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
                                <div class="mb-15" id="filtro" style="display: none;">
                                    <div class="row mb-8">

                                        <div class="col-lg-6 mb-lg-0 mb-6">
                                            <label>Tipo:</label>
                                            <select class="form-control select2" id="tipo">
                                                <option value="">Seleccione</option>
                                                <option value="1">Fecha Inicio</option>
                                                <option value="2">Fecha Fin</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-6 mb-lg-0 mb-6">
                                            <label>Fecha:</label>
                                            <div class="input-group" id='kt_fecha'>
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="la la-calendar-check-o"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control" autocomplete="off"
                                                    placeholder="Rango de Fechas" id="fecha">
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
                                <!--begin: Datatable-->
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th class="no-exportar">id</th>
                                            <th data-priority="1">Técnico</th>
                                            <th>Producto</th>
                                            <th>Planificación</th>
                                            <th data-priority="3">Descripción</th>
                                            <th>Fecha Hora Inicio</th>
                                            <th>Fecha Hora Fin</th>
                                            <th>Enlace</th>
                                            <th class="no-exportar">Acción</th>
                                            <th>Tiempo Ocupado</th>


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
    @include('modals.verificar_planificacion')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            var start = moment();
            var end = moment();

            function cb(start, end) {
                $('#kt_fecha .form-control').val(start.format('DD-MM-YYYY') + ' / ' + end.format(
                    'DD-MM-YYYY'));
            };
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
                startDate: start,
                endDate: end,
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

            }, cb);
            cb(start, end);


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
                ajax: {
                    url: "{{ route('sesiones.indexCliente') }}",
                    type: 'POST',
                    data: function(d) {
                        d.fecha = $('#fecha').val();
                        d.tipo = $('#tipo').val();

                    }
                },


                columns: [{
                        data: 'sesionesid',
                        name: 'sesionesid',
                        searchable: false,
                        visible: false
                    },
                    {
                        data: 'creador',
                        name: 'creador',

                    },

                    {
                        data: 'productosid',
                        name: 'productosid',

                    },
                    {
                        data: 'planificacionesid',
                        name: 'planificacionesid',
                        visible: false,
                        orderable: false,
                        searchable: false

                    },
                    {
                        data: 'descripcion',
                        name: 'descripcion',
                        orderable: false,
                        searchable: false

                    },
                    {
                        data: 'fechainicio',
                        name: 'fechainicio',
                    },

                    {
                        data: 'fechafin',
                        name: 'fechafin',

                    },
                    {
                        data: 'enlace',
                        name: 'enlace',
                        visible: false,
                        orderable: false,
                        searchable: false

                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'suma',
                        name: 'suma',
                        searchable: false,
                        orderable: false,
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
                cb;
                $("#tipo").val('');
                $('#tipo').change();
                table.draw();
            });

            //Mostrar div de busqueda
            $('#filtrar').on('click', function(e) {
                $("#filtro").toggle(500);
            });
            $(document).on('click', '.confirm-sesion', function(e) {
                e.preventDefault();
                var sesionid = $(this).data('href');
                $("#sesion-verificar").val(sesionid);
                $("#sesion-modal").modal("show");
                temas();
            });

        });
    </script>
@endsection

