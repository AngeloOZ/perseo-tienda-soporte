@php
    $vendedores = App\Models\User::select('usuariosid', 'identificacion', 'nombres')
        ->where('rol', 1)
        ->where('estado', 1)
        ->where('distribuidoresid', Auth::user()->distribuidoresid)
        ->get();
@endphp

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
                                    <h3 class="card-label">Listado de firmas enviadas al correo</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-8">
                                    <div class="col-12 col-md-4">
                                        <label>Vendedores:</label>
                                        <select class="form-control select select2" id="filtroVendedores">
                                            <option value="" selected>Todos</option>
                                            @foreach ($vendedores as $vendedor)
                                                <option value="{{ $vendedor->usuariosid }}">{{ $vendedor->nombres }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-4">
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

                                    <div class="col-12 col-md-4">
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
                                            <th>Tipo Persona</th>
                                            <th data-priority="1">Identificacion</th>
                                            <th data-priority="2">Nombres</th>
                                            <th>Correo</th>
                                            <th>Celular</th>
                                            <th>Fecha de creación</th>
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
    @include('modals.delete_modal')
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
                    [0, 'desc']
                ],
                //Guardar pagina, busqueda, etc
                stateSave: true,
                //Trabajar del lado del server
                serverSide: true,
                //Peticion ajax que devuelve los registros
                ajax: {
                    url: "{{ route('firma.filtrado_revisor_correo') }}",
                    type: 'POST',
                    data: function(d) {
                        d.vendedores = $("#filtroVendedores").val();
                        d.fecha = $("#filtroFecha").val();
                    },
                },
                columns: [{
                        data: 'firmasid',
                        name: 'firmasid',
                        searchable: false,
                        visible: false
                    },
                    {
                        data: 'tipo_persona',
                        name: 'tipo_persona',

                    },
                    {
                        data: 'identificacion',
                        name: 'identificacion',

                    },
                    {
                        data: 'nombres',
                        name: 'nombres'
                    },
                    {
                        data: 'correo',
                        name: 'correo',

                    },
                    {
                        data: 'celular',
                        name: 'celular',
                    },
                    {
                        data: 'fecha_creacion',
                        name: 'fecha_creacion',

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
                // guardarEstadoFiltro();
                table.draw();
            });

            $('#kt_reset').on('click', function() {
                $("#filtroEstado").val("");
                $("#filtroVendedores").val("");
                initDateMonth();
                table.draw();
            });

            function initDateMonth() {
                $("#filtroFecha").val(
                    `${moment().startOf('month').format('DD-MM-YYYY')} / ${ moment().endOf('month').format('DD-MM-YYYY')}`
                );
            }
        });
    </script>
@endsection
