@extends('soporte.auth.layouts.app')
@section('title_page', 'Tickets en desarrollo')
@section('contenido')
    <style>
        #kt_datatable td {
            padding: 3px;
        }
    </style>
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">

                <!--begin::Card-->
                <div class="card card-custom" id="kt_page_sticky_card">
                    <div class="card-header ">
                        <div class="card-title">
                            <h3 class="card-label"> Listado de tickets en desarrollo </h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-8">
                            <div class="col-lg-3 mb-lg-0 mb-6">
                                <label>Técnicos:</label>
                                <select class="form-control select2 datatable-input" id="filtroTecnicos">
                                    <option value="" selected>Todos</option>
                                    @foreach ($tecnicos as $tecnico)
                                        <option value="{{ $tecnico->usuariosid }}"
                                            {{ Auth::user()->usuariosid == $tecnico->usuariosid ? 'selected' : '' }}>
                                            {{ $tecnico->nombres }}
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

                            <div class="col-lg-3 mb-lg-0 mb-6 pt-7">
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


                        @include('soporte.admin.inc.tabla')
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {

            initDateMonth();

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
                    url: "{{ route('soporte.filtrado.listado_desarrollo') }}",
                    type: 'post',
                    data: function(d) {
                        d.tecnico = $("#filtroTecnicos").val();
                        d.fecha = $("#filtroFecha").val();
                    }

                },
                columns: [{
                        data: 'ticketid',
                        name: 'ticketid',
                        searchable: false,
                        visible: true
                    },
                    {
                        data: 'numero_ticket',
                        name: 'numero_ticket',
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
                        data: 'estado',
                        name: 'estado',
                    },
                    {
                        data: 'fecha_creado',
                        name: 'fecha_creado',
                        type: "date",
                    },
                    {
                        data: 'tiempo_activo',
                        name: 'tiempo_activo',
                        searchable: false,
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

            setInterval(() => {
                table.ajax.reload();
            }, (1000 * 30));

            //Clic en boton buscar
            $('#kt_search').on('click', function(e) {
                e.preventDefault();
                table.draw();
            });

            $('#kt_reset').on('click', function(e) {
                $("#filtroTecnicos").val("{{ Auth::user()->usuariosid }}");
                initDateMonth();
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

            function initDateMonth() {
                $("#filtroFecha").val(
                    `${moment().startOf('month').format('DD-MM-YYYY')} / ${ moment().endOf('month').format('DD-MM-YYYY')}`
                );
            }
        });
    </script>
@endsection
