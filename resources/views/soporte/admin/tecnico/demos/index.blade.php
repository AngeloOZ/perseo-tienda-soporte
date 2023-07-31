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
                                    <h3 class="card-label"> Listado de soportes </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-8">
                                    <div class="col-md-4 mb-lg-2 mb-6">
                                        <label>Plan:</label>
                                        <select class="form-control datatable-input" id="filtroPlan">
                                            <option value="" selected>Todos</option>
                                            <option value="1">WEB</option>
                                            <option value="2">PC</option>
                                            <option value="3">FACTURITO</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-lg-2 mb-6">
                                        <label>Tipo:</label>
                                        <select class="form-control datatable-input" id="filtroTipo">
                                            <option value="" selected>Todos</option>
                                            <option value="1">Demos</option>
                                            <option value="2">Capacitaciones</option>
                                            <option value="3">LITE</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-lg-2 mb-6">
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

                                    <div class="col-md-4 mb-lg-2 mb-6">
                                        <label>Fecha:</label>
                                        <div class="input-group" id='kt_fecha'>
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="la la-calendar-check-o"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control" autocomplete="off"
                                                placeholder="DD-MM-AAAA / DD-MM-AAAA" id="filtroFecha">
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-lg-2 mb-6">
                                        <div class="pt-8">
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
                                            <th>RUC</th>
                                            <th>Razón social</th>
                                            <th>Correo</th>
                                            <th>Whatsapp</th>
                                            <th>Estado</th>
                                            <th>Tipo</th>
                                            <th>Plan</th>
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
                    [0, 'asc']
                ],
                //Guardar pagina, busqueda, etc
                stateSave: true,
                //Trabajar del lado del server
                serverSide: true,
                //Peticion ajax que devuelve los registros
                ajax: {
                    url: "{{ route('soporte.filtrado.tec') }}",
                    type: 'POST',
                    data: function(d) {
                        d.plan = $("#filtroPlan").val();
                        d.tipo = $("#filtroTipo").val();
                        d.estado = $("#filtroEstado").val();
                        d.fecha = $("#filtroFecha").val();
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
                        data: 'plan',
                        name: 'plan',
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

            $('#kt_search').on('click', function(e) {
                e.preventDefault();
                table.draw();
            });

            $('#kt_reset').on('click', function(e) {
                $("#filtroEstado").val('1');
                $("#filtroFecha").val('');
                $("#filtroPlan").val('');
                $("#filtroTipo").val('');
                table.draw();
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
        });
    </script>
@endsection
