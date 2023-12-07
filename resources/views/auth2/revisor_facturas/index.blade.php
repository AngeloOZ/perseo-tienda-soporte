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
                                    <h3 class="card-label">Facturas</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-8">
                                    <div class="col-12 col-md-2">
                                        <label>Estado:</label>
                                        <select class="form-control datatable-input" id="filtroEstadoPagado">
                                            <option value="3" selected>Todos</option>
                                            <option value="1">Pagado</option>
                                            <option value="2">Pagado y revisado</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-3">
                                        <label>Estado liberado:</label>
                                        <select class="form-control datatable-input" id="filtroEstadoLiberado">
                                            <option value="" selected>Todos</option>
                                            <option value="1">Por liberar</option>
                                            <option value="2">Liberado</option>
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

                                    <div class="col-12 col-md-4 pt-8">
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
                                        @include('auth.comisiones.inc.buttons')
                                    </div>
                                </div>

                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th class="no-export">#</th>
                                            <th data-priority="1">Identificacion</th>
                                            <th data-priority="2">Nombres</th>
                                            <th>Concepto de factura</th>
                                            <th>Secuencia de factura</th>
                                            <th>Estado del producto</th>
                                            <th>Estado del pago</th>
                                            <th>Origen</th>
                                            <th>Vendedor</th>
                                            <th>Fecha creada</th>
                                            <th>Fecha modificada</th>
                                            <th>Productos</th>
                                            <th>% Descuento</th>
                                            <th>Total factura</th>
                                            <th class="no-export">Acciones</th>
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
        const tabla = document.getElementById('kt_datatable');
        tabla?.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="autorizar"]') ||
                e.target.matches('[data-action="liberar"]')
            ) {
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
                    url: "{{ route('facturas.filtrado_revisor') }}",
                    type: 'POST',
                    data: function(d) {
                        d.pago = $("#filtroEstadoPagado").val();
                        d.liberado = $("#filtroEstadoLiberado").val();
                        d.fecha = $("#filtroFecha").val();
                    },
                },
                columns: [{
                        data: 'facturaid',
                        name: 'facturaid',
                        searchable: false,
                        visible: true,
                    },
                    {
                        data: 'identificacion',
                        name: 'identificacion',

                    },
                    {
                        data: 'nombre',
                        name: 'nombre'
                    },
                    {
                        data: 'concepto',
                        name: 'concepto',

                    },
                    {
                        data: 'secuencia_perseo',
                        name: 'secuencia_perseo',
                    },
                    {
                        data: 'liberado',
                        name: 'liberado',
                    },
                    {
                        data: 'estado',
                        name: 'estado',
                    },
                    {
                        data: 'origen',
                        name: 'origen',
                    },
                    {
                        data: 'vendedor',
                        name: 'vendedor',
                        searchable: false,
                        visible: false,
                    },
                    {
                        data: 'fecha_creacion',
                        name: 'fecha_creacion',
                        searchable: false,
                        visible: false,
                    },
                    {
                        data: 'fecha_actualizado',
                        name: 'fecha_actualizado',
                        searchable: false,
                        visible: false,
                    },
                    {
                        data: 'productos',
                        name: 'productos',
                        searchable: false,
                        visible: false,
                    },
                    {
                        data: 'descuento',
                        name: 'descuento',
                        searchable: false,
                        visible: false,
                    },
                    {
                        data: 'total',
                        name: 'total',
                        searchable: false,
                        visible: false,
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
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    },
                    {
                        extend: 'copyHtml5',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    },
                ],
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

            $('#kt_reset').on('click', function() {
                resetearEstadoFiltro();
                $("#filtroEstadoPagado").val("3");
                $("#filtroEstadoLiberado").val("");
                initDateMonth();
                table.draw();
            });

            function initDateMonth() {
                $("#filtroFecha").val(
                    `${moment().startOf('month').format('DD-MM-YYYY')} / ${ moment().endOf('month').format('DD-MM-YYYY')}`
                );
            }

            /* Botones para exportar datos */
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
        });

        /* -------------------------------------------------------------------------- */
        /*       Funciones para guardar el estado del filtro en el localStorage       */
        /* -------------------------------------------------------------------------- */

        // Se ejecuta al hacer click en el boton buscar
        function guardarEstadoFiltro() {
            const path = 'estado_filtro_az_' + location.pathname.slice(1);
            const filtro = {
                pago: $("#filtroEstadoPagado").val(),
                liberado: $("#filtroEstadoLiberado").val(),
                fecha: $("#filtroFecha").val(),
            }
            localStorage.setItem(path, JSON.stringify(filtro));
        }

        // Se ejecuta al cargar la pagina 
        function cargarEstadoFiltro() {
            const path = 'estado_filtro_az_' + location.pathname.slice(1);
            const filtro = JSON.parse(localStorage.getItem(path));
            if (filtro) {
                $("#filtroEstadoPagado").val(filtro.pago);
                $("#filtroEstadoLiberado").val(filtro.liberado);
                $("#filtroFecha").val(filtro.fecha);
            }
        }

        // Se ejecuta al hacer click en el boton reiniciar
        function resetearEstadoFiltro() {
            const path = 'estado_filtro_az_' + location.pathname.slice(1);
            localStorage.removeItem(path);
        }
    </script>
@endsection
