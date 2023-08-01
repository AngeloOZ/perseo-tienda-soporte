@extends('soporte.auth.layouts.app')
@section('title_page', 'Listado de soportes especiales')
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
                                    <h3 class="card-label"> Listado de soportes especiales <small>Supervisor</small></h3>
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
                                </div>

                                <!--begin: Search Form-->
                                <div class="mb-15" id="filtro" style="display: none;">
                                    <div class="row mb-8">
                                        <div class="col-lg-3 mb-lg-2 mb-6">
                                            <label>Tipo plan:</label>
                                            <select class="form-control datatable-input" id="filtroPlan">
                                                <option value="">Todos</option>
                                                <option value="1" selected>WEB</option>
                                                <option value="2">PC</option>
                                                <option value="3">FACTURITO</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-3 mb-lg-2 mb-6">
                                            <label>Técnicos:</label>
                                            <select class="form-control select2 datatable-input" id="filtroTecnicos">
                                                <option value="" selected>Todos</option>
                                                @foreach ($tecnicos as $tecnico)
                                                    <option value="{{ $tecnico->usuariosid }}">{{ $tecnico->nombres }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-3 mb-lg-2 mb-6">
                                            <label>Estado:</label>
                                            <select class="form-control datatable-input" id="filtroEstado">
                                                <option value="" selected>Todos</option>
                                                <option value="1">Asignados</option>
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

                                        <div class="col-lg-3 mb-lg-2 mb-6">
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
                                        <div class="col-lg-3 mb-lg-2 mb-6">
                                            <label>Tipo:</label>
                                            <select class="form-control datatable-input" id="filtroTipo">
                                                <option value="">Todos</option>
                                                <option value="1">Demos</option>
                                                <option value="2" selected>Capacitaciones</option>
                                                <option value="3">LITE</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-9">
                                            <div class="mt-7">
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
                                </div>
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th class="no-export">#</th>
                                            <th>Fecha creación</th>
                                            <th>RUC</th>
                                            <th>Razón social</th>
                                            <th>Plan</th>
                                            <th>Whatsapp</th>
                                            <th>Estado</th>
                                            <th>Fecha de asesoría</th>
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
                    url: "{{ route('especiales.filtrado_supervidor') }}",
                    type: 'POST',
                    data: function(d) {
                        d.tecnico = $("#filtroTecnicos").val();
                        d.plan = $("#filtroPlan").val();
                        d.estado = $("#filtroEstado").val();
                        d.fecha = $("#filtroFecha").val();
                        d.tipo = $("#filtroTipo").val();
                    }
                },
                columns: [{
                        data: 'soporteid',
                        name: 'soporteid',
                        searchable: false,
                        visible: true
                    },
                    {
                        data: 'fecha_creacion',
                        name: 'fecha_creacion',
                    },
                    {
                        data: 'ruc',
                        name: 'ruc',
                    },
                    {
                        data: 'razon_social',
                        name: 'razon_social',
                    },
                    {
                        data: 'plan',
                        name: 'plan',
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
                        data: 'fecha_iniciado',
                        name: 'fecha_iniciado',
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
                table.draw();
            });

            $('#kt_reset').on('click', function(e) {
                $("#filtroTecnicos").val('');
                $("#filtroPlan").val('1');
                $("#filtroEstado").val('1');
                $("#filtroFecha").val('');
                $("#filtroTipo").val('2');
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
    </script>
@endsection
