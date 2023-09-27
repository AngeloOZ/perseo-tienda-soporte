@extends('soporte.auth.layouts.app')
@section('title_page', 'Listado de soportes especiales')
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
                                    <h3 class="card-label"> Listado de soportes especiales <small>Revisor</small></h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card-toolbar text-right mb-5">
                                    <a href="#" class="btn btn-primary font-weight-bolder" id="filtrar">
                                        <span class="svg-icon svg-icon-md">
                                            <i class="la la-filter"></i>
                                        </span>Filtrar
                                    </a>

                                    @include('auth.comisiones.inc.buttons')

                                    <a href="{{ route('sop.agregar_soporte_especial') }}" class="btn btn-success">Registrar
                                        nuevo</a>
                                </div>

                                <!--begin: Search Form-->
                                <div class="mb-15" id="filtro" style="display: none;">
                                    <div class="row mb-8">
                                        <div class="col-lg-4 mb-lg-2 mb-6">
                                            <label>Asignados:</label>
                                            <select class="form-control datatable-input" id="filtroAsignados">
                                                <option value="1">Asignados</option>
                                                <option value="" selected>No asignados</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-4 mb-lg-2 mb-6">
                                            <label>Distribuidor:</label>
                                            <select class="form-control datatable-input" id="filtroDistribuidor" {{ $disabled }}>
                                                <option value="">Todos</option>
                                                <option value="1" {{ $currentUser->distribuidoresid == 1 ? 'selected' : '' }}>Perseo Alfa</option>
                                                <option value="2" {{ $currentUser->distribuidoresid == 2 ? 'selected' : '' }}>Perseo Matriz</option>
                                                <option value="3" {{ $currentUser->distribuidoresid == 3 ? 'selected' : '' }}>Perseo Delta</option>
                                                <option value="4" {{ $currentUser->distribuidoresid == 4 ? 'selected' : '' }}>Perseo Omega</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-4 mb-lg-2 mb-6">
                                            <label>Técnicos:</label>
                                            <select class="form-control select2 datatable-input" id="filtroTecnicos">
                                                <option value="" selected>Todos</option>
                                                @foreach ($tecnicos as $tecnico)
                                                    <option value="{{ $tecnico->tecnicosid }}">{{ $tecnico->nombres }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-4 mb-lg-2 mb-6">
                                            <label>Estado:</label>
                                            <select class="form-control datatable-input" id="filtroEstado">
                                                <option value="">Todos</option>
                                                <option value="1" selected>Asignados</option>
                                                <option value="3">Contactados</option>
                                                <option value="2">Agendados</option>
                                                <option value="4">Implementados</option>
                                                <option value="5">Revisados 1</option>
                                                <option value="6">Finalizados</option>
                                                <option value="7">Reagendados</option>
                                                <option value="8">Revisados 2</option>
                                                <option value="9">Aprobados</option>
                                                <option value="10">Rechazados</option>
                                                <option value="11">Sin Respuesta</option>
                                                <option value="12">Autoimplementado</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-4 mb-lg-2 mb-6">
                                            <label>Tipo:</label>
                                            <select class="form-control datatable-input" id="filtroTipo" name="periodo">
                                                <option value="" selected>Todos</option>
                                                <option value="1">Demos</option>
                                                <option value="2">Capacitaciones</option>
                                                <option value="3">LITE</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-4 mb-lg-2 mb-6">
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

                                    <div class="row">
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
                                            <th class="no-export">#</th>
                                            <th>RUC</th>
                                            <th>Razón social</th>
                                            <th>Correo</th>
                                            <th>Whatsapp</th>
                                            <th>Estado</th>
                                            <th>Tipo</th>
                                            <th>Distribuidor</th>
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
@section('modal')
    @include('modals.delete_modal')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
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
                ],
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
                searchDelay: 500,
                deferRender: true,
                paging: true,
                ajax: {
                    url: "{{ route('soporte.filtrado_soporte_especial') }}",
                    type: 'POST',
                    data: function(d) {
                        //Valores de filtro a enviar a la ruta clientes.index
                        d.asignados = $("#filtroAsignados").val();
                        d.tecnico = $("#filtroTecnicos").val();
                        d.estado = $("#filtroEstado").val();
                        d.tipo = $("#filtroTipo").val();
                        d.fecha = $("#filtroFecha").val();
                        d.distribuidor = $("#filtroDistribuidor").val();
                    }
                },
                columns: [{
                        data: 'soporteid',
                        name: 'soporteid',
                        searchable: false,
                        visible: true
                    },
                    {
                        data: 'ruc',
                        name: 'ruc',

                    }, {
                        data: 'razon_social',
                        name: 'razon_social',
                    },
                    {
                        data: 'correo',
                        name: 'correo',
                    },
                    {
                        data: 'whatsapp',
                        name: 'whatsapp',
                    },
                    {
                        name: 'estado',
                        data: 'estado',
                    },
                    {
                        data: 'tipo',
                        name: 'tipo',
                    },
                    {
                        data: 'distribuidor',
                        name: 'distribuidor',
                    },
                    {
                        data: 'acciones',
                        name: 'acciones',
                        orderable: false,
                        searchable: false,
                        className: "text-center"
                    },
                ],
            });

            //Clic en boton buscar
            $('#kt_search').on('click', function(e) {
                e.preventDefault();
                guardarEstadoFiltro();
                table.draw();
            });

            $('#kt_reset').on('click', function() {
                resetearEstadoFiltro();
                $("#filtroTecnicos").val('');
                $("#filtroEstado").val('1');
                $("#filtroTipo").val('');
                $("#filtroFecha").val('');
                $("#filtroDistribuidor").val('{{ $currentUser->distribuidoresid }}');
                $("#filtroAsignados").val('');
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
        });

        /* -------------------------------------------------------------------------- */
        /*       Funciones para guardar el estado del filtro en el localStorage       */
        /* -------------------------------------------------------------------------- */

        // Se ejecuta al hacer click en el boton buscar
        function guardarEstadoFiltro() {
            const path = 'estado_filtro_az_' + location.pathname.slice(1);
            const filtro = {
                asignados: $("#filtroAsignados").val(),
                tecnico: $("#filtroTecnicos").val(),
                estado: $("#filtroEstado").val(),
                tipo: $("#filtroTipo").val(),
                fecha: $("#filtroFecha").val(),
                distribuidor: $("#filtroDistribuidor").val(),
            }
            localStorage.setItem(path, JSON.stringify(filtro));
        }

        // Se ejecuta al cargar la pagina 
        function cargarEstadoFiltro() {
            const path = 'estado_filtro_az_' + location.pathname.slice(1);
            const filtro = JSON.parse(localStorage.getItem(path));
            if (filtro) {
                $("#filtroAsignados").val(filtro.asignados);
                $("#filtroTecnicos").val(filtro.tecnico);
                $("#filtroEstado").val(filtro.estado);
                $("#filtroTipo").val(filtro.tipo);
                $("#filtroFecha").val(filtro.fecha);
                $("#filtroDistribuidor").val(filtro.distribuidor);
                $('#filtroTecnicos').trigger('change');
            }
        }

        // Se ejecuta al hacer click en el boton reiniciar
        function resetearEstadoFiltro() {
            const path = 'estado_filtro_az_' + location.pathname.slice(1);
            localStorage.removeItem(path);
        }
    </script>
@endsection
