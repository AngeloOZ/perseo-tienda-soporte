@extends('soporte.auth.layouts.app')
@section('title_page', 'Listado de tickets')
@php
    use App\Constants\ConstantesTecnicos;
    $estados = ConstantesTecnicos::obtenerEstadosTickets();
    $listaEstados = ConstantesTecnicos::obtenerEstadosTicketsSelect();

    $disabled = 'disabled';
    $isVisible = false;
    $selected = 'pc';

    $currentUser = Auth::guard('tecnico')->user();

    if ($currentUser->distribuidoresid == 2) {
        $disabled = '';
        $isVisible = true;
        $selected = '';
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
                        <div class="card card-custom">
                            <div class="card-header ">
                                <div class="card-title">
                                    <h3 class="card-label"> Listado de tickets </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card-toolbar text-right mb-5">
                                    <a href="#" class="btn btn-primary font-weight-bolder" id="filtrar">
                                        <span class="svg-icon svg-icon-md">
                                            <i class="la la-filter"></i>
                                        </span>Filtrar
                                    </a>
                                </div>

                                <!--begin: Search Form-->
                                <div class="mb-15" id="filtro" style="display: none;">
                                    <div class="row mb-8">
                                        <div class="col-lg-4 mb-4">
                                            <label>Distribuidores:</label>
                                            <select class="form-control datatable-input" id="filtroDistribuidor"
                                                {{ $disabled }}>
                                                @if ($isVisible)
                                                    <option value="" selected>Todos</option>
                                                @endif
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
                                                <option value="5">OTROS</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-4 mb-4">
                                            <label>Técnicos:</label>
                                            <select class="form-control select2 datatable-input" id="filtroTecnicos">
                                                <option value="" selected>Todos</option>
                                                @foreach ($tecnicos as $tecnico)
                                                    <option value="{{ $tecnico->tecnicosid }}">{{ $tecnico->nombres }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-4 mb-4">
                                            <label>Estado:</label>
                                            <select class="form-control datatable-input" id="filtroEstado">
                                                <option value="">Todos</option>
                                                @foreach ($listaEstados as $estado)
                                                    <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-4 mb-4">
                                            <label>Tickets:</label>
                                            <select class="form-control datatable-input" id="filtroAsignados">
                                                <option value="si" selected>Asignados</option>
                                                <option value="no">Por asignar</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-4 mb-4">
                                            <label>Producto:</label>
                                            <select class="form-control datatable-input" id="filtroProducto"
                                                {{ $disabled }}>
                                                <option value="" {{ $selected == '' ? 'selected' : '' }}>Todos
                                                </option>
                                                <option value="pc" {{ $selected == 'pc' ? 'selected' : '' }}>PC
                                                </option>
                                                <option value="web">Web</option>
                                                <option value="facturito">Facturito</option>
                                                <option value="contafacil">Contafacil</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-4 mb-4">
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

                                @include('soporte.admin.inc.tabla')
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
    @include('soporte.admin.revisor.delete_modal')
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
                    url: "{{ route('soporte.listado_filtrado.revisor') }}",
                    type: 'POST',
                    data: function(d) {
                        //Valores de filtro a enviar a la ruta clientes.index
                        d.distribuidor = $("#filtroDistribuidor").val();
                        d.tecnico = $("#filtroTecnicos").val();
                        d.estado = $("#filtroEstado").val();
                        d.asignados = $("#filtroAsignados").val();
                        d.fecha = $("#filtroFecha").val();
                        d.producto = $("#filtroProducto").val();
                    }
                },
                columns: [{
                        data: 'ticketid',
                        name: 'ticketid',
                        searchable: true,
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
                        searchable: false,
                    },
                    {
                        data: 'fecha_creado',
                        name: 'fecha_creado',
                        type: "date",
                        searchable: false,
                    },
                    {
                        data: 'tiempo_contactado',
                        name: 'tiempo_contactado',
                        searchable: false,
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
                guardarEstadoFiltro();
                table.draw();
            });

            $('#kt_reset').on('click', function(e) {
                resetearEstadoFiltro();
                $("#filtroTecnicos").val('');
                $("#filtroEstado").val('1');
                $("#filtroAsignados").val('si');
                $("#filtroFecha").val('');
                $("#filtroDistribuidor").val('{{ $currentUser->distribuidoresid }}');
                $("#filtroProducto").val('{{ $selected }}');
                $('#filtroTecnicos').trigger('change');
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
        });

        /* -------------------------------------------------------------------------- */
        /*       Funciones para guardar el estado del filtro en el localStorage       */
        /* -------------------------------------------------------------------------- */

        // Se ejecuta al hacer click en el boton buscar
        function guardarEstadoFiltro() {
            const path = 'estado_filtro_az_' + location.pathname.slice(1);
            const filtro = {
                distribuidor: $("#filtroDistribuidor").val(),
                tecnico: $("#filtroTecnicos").val(),
                estado: $("#filtroEstado").val(),
                asignados: $("#filtroAsignados").val(),
                fecha: $("#filtroFecha").val(),
                producto: $("#filtroProducto").val(),
            }
            localStorage.setItem(path, JSON.stringify(filtro));
        }

        // Se ejecuta al cargar la pagina 
        function cargarEstadoFiltro() {
            const path = 'estado_filtro_az_' + location.pathname.slice(1);
            const filtro = JSON.parse(localStorage.getItem(path));
            if (filtro) {
                $("#filtroDistribuidor").val(filtro.distribuidor);
                $("#filtroTecnicos").val(filtro.tecnico);
                $("#filtroEstado").val(filtro.estado);
                $("#filtroAsignados").val(filtro.asignados);
                $("#filtroFecha").val(filtro.fecha);
                $("#filtroProducto").val(filtro.producto);
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
