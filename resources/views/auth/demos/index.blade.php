@extends('auth.layouts.app')
@section('titulo', 'Listado demos y lites')


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
                                    <h3 class="card-label">Demos y Lites</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-8">
                                    <div class="col-12 col-md-3">
                                        <label>Tipo:</label>
                                        <select class="form-control datatable-input" id="filtroTipo">
                                            <option value="" selected>Todos</option>
                                            <option value="1">Demos</option>
                                            <option value="2">Capacitaciones</option>
                                            <option value="3">Lite</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-3">
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

                                    <div class="col-12 col-md-6 pt-8">
                                        <div class="d-flex justify-content-end">
                                            <button class="btn btn-primary btn-primary--icon" id="kt_search">
                                                <span>
                                                    <i class="la la-search"></i>
                                                    <span>Buscar</span>
                                                </span>
                                            </button>
                                            <button class="btn btn-secondary btn-secondary--icon mx-2" id="kt_reset">
                                                <span>
                                                    <i class="la la-close"></i>
                                                    <span>Reiniciar</span>
                                                </span>
                                            </button>
                                            <a href="{{ route('demos.crear') }}" class="btn btn-success">Registrar nuevo</a>
                                        </div>
                                    </div>
                                </div>

                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th class="no-exportar">#</th>
                                            <th data-priority="1">RUC</th>
                                            <th data-priority="2">Razón Social</th>
                                            <th>Teléfono</th>
                                            <th>Correo</th>
                                            <th>Plan</th>
                                            <th>Tipo</th>
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
    @include('auth.facturas.includes.delete_modal')
@endsection
@section('script')
    <script>
        const tabla = document.getElementById('kt_datatable');
        tabla?.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="autorizar"]')) {
                let btn = e.target;
                if (e.target.tagName == "I") {
                    btn = e.target.parentElement;
                }
                btn.classList.add('disabled-anchor');
            }
        })

        $(document).ready(function() {
            initDateMonth();
            cargarEstadoFiltro();
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
                    url: "{{ route('demos.filtrado.listado') }}",
                    type: 'POST',
                    data: function(d) {
                        d.tipo = $("#filtroTipo").val();
                        d.fecha = $("#filtroFecha").val();
                    },
                },
                columns: [{
                        data: 'soporteid',
                        name: 'soporteid',
                        searchable: false,
                        visible: true,
                    },
                    {
                        data: 'ruc',
                        name: 'ruc',

                    },
                    {
                        data: 'razon_social',
                        name: 'razon_social'
                    },
                    {
                        data: 'whatsapp',
                        name: 'whatsapp',
                    },
                    {
                        data: 'correo',
                        name: 'correo',
                    },
                    {
                        data: 'plan',
                        name: 'plan',
                    },
                    {
                        data: 'tipo',
                        name: 'tipo',
                    },
                    {
                        data: 'estado',
                        name: 'estado',
                    },
                    {
                        data: 'acciones',
                        name: 'acciones',
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

            $('#kt_search').on('click', function(e) {
                e.preventDefault();
                guardarEstadoFiltro();
                table.draw();
            });

            $('#kt_reset').on('click', function(e) {
                resetearEstadoFiltro();
                $("#filtroTipo").val("");
                $("#filtroEstadoLiberado").val("");
                initDateMonth();
                table.draw();
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

            $('#export_csv').on('click', function(e) {
                e.preventDefault();
                table.button(3).trigger();
            });

            $('#export_pdf').on('click', function(e) {
                e.preventDefault();
                table.button(4).trigger();
            });

            function initDateMonth() {
                $("#filtroFecha").val(
                    `${moment().startOf('month').format('DD-MM-YYYY')} / ${ moment().endOf('month').format('DD-MM-YYYY')}`
                );
            }

            /* -------------------------------------------------------------------------- */
            /*       Funciones para guardar el estado del filtro en el localStorage       */
            /* -------------------------------------------------------------------------- */

            // Se ejecuta al hacer click en el boton buscar
            function guardarEstadoFiltro() {
                const path = 'estado_filtro_az_' + location.pathname.slice(1);
                const filtro = {
                    facturado: $("#filtroTipo").val(),
                    fecha: $("#filtroFecha").val(),
                }
                localStorage.setItem(path, JSON.stringify(filtro));
            }

            // Se ejecuta al cargar la pagina 
            function cargarEstadoFiltro() {
                const path = 'estado_filtro_az_' + location.pathname.slice(1);
                const filtro = JSON.parse(localStorage.getItem(path));
                if (filtro) {
                    $("#filtroTipo").val(filtro.facturado);
                    $("#filtroFecha").val(filtro.fecha);
                }
            }

            // Se ejecuta al hacer click en el boton reiniciar
            function resetearEstadoFiltro() {
                const path = 'estado_filtro_az_' + location.pathname.slice(1);
                localStorage.removeItem(path);
            }
        });
    </script>
@endsection
