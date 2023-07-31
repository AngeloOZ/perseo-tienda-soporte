@extends('auth.layouts.app')
@section('titulo', 'Reporte de comisiones')

@php
    $vendedores = App\Models\User::where('estado', 1)
        ->where('rol', 1)
        ->whereIn('distribuidoresid', [1, 2])
        ->get();
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

                                    <div class="dropdown dropdown-inline">
                                        <button type="button" class="btn btn-secondary font-weight-bold"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="la la-download"></i>Exportar
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                            <ul class="navi flex-column navi-hover py-2">
                                                <li
                                                    class="navi-header font-weight-bolder text-uppercase font-size-xs text-primary pb-2">
                                                    Opciones</li>
                                                <li class="navi-item">
                                                    <a href="#" class="navi-link" id="export_print">
                                                        <span class="navi-icon">
                                                            <i class="la la-print"></i>
                                                        </span>
                                                        <span class="navi-text">Imprimir</span>
                                                    </a>
                                                </li>
                                                <li class="navi-item">
                                                    <a href="#" class="navi-link" id="export_copy">
                                                        <span class="navi-icon">
                                                            <i class="la la-copy"></i>
                                                        </span>
                                                        <span class="navi-text">Copiar</span>
                                                    </a>
                                                </li>
                                                <li class="navi-item">
                                                    <a href="#" class="navi-link" id="export_excel">
                                                        <span class="navi-icon">
                                                            <i class="la la-file-excel-o"></i>
                                                        </span>
                                                        <span class="navi-text">Excel</span>
                                                    </a>
                                                </li>
                                                <li class="navi-item">
                                                    <a href="#" class="navi-link" id="export_csv">
                                                        <span class="navi-icon">
                                                            <i class="la la-file-text-o"></i>
                                                        </span>
                                                        <span class="navi-text">CSV</span>
                                                    </a>
                                                </li>
                                                <li class="navi-item">
                                                    <a href="#" class="navi-link" id="export_pdf">
                                                        <span class="navi-icon">
                                                            <i class="la la-file-pdf-o"></i>
                                                        </span>
                                                        <span class="navi-text">PDF</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                                <!--begin: Search Form-->
                                <div class="mb-15" id="filtro" style="display: none;">
                                    <div class="row mb-8">
                                        <div class="col-lg-3 mb-lg-0 mb-6">
                                            <label>Distribuidor:</label>
                                            <select class="form-control select2 datatable-input" id="filtroDistribuidor">
                                                <option value="" selected>Todos</option>
                                                <option value="1">Perseo Alfa</option>
                                                <option value="2">Perseo Matriz</option>
                                                <option value="3">Perseo Delta</option>
                                                <option value="4">Perseo Omega</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-3 mb-lg-0 mb-6">
                                            <label>Vendedores:</label>
                                            <select class="form-control select2 datatable-input" id="filtroVendedores">
                                                <option value="" selected>Todos</option>
                                                @foreach ($vendedores as $vendedor)
                                                    <option value="{{ $vendedor->usuariosid }}">{{ $vendedor->nombres }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="col-lg-3 mb-lg-0 mb-6">
                                            <label>Estado de comisión:</label>
                                            <select class="form-control datatable-input" id="filtroEstado" name="periodo">
                                                <option value="">Todos</option>
                                                <option value="si">Pagados</option>
                                                <option value="no" selected>Por pagar</option>
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
                                <!--begin: Search Form-->
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th class="no-exportar">#</th>
                                            <th>Vendedor</th>
                                            <th>Secuencia de Factura</th>
                                            <th>Fecha de venta</th>
                                            <th>Valor de venta</th>
                                            <th>Planes nuevos</th>
                                            <th>Renovaciones</th>
                                            <th>Firmas</th>
                                            <th>Total Comisiones</th>
                                            {{-- <th class="no-exportar">Acciones</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <tfoot>
                                        <tr>
                                            <th class="text-right"></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                    </tbody>
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
    @include('soporte.admin.revisor.delete_modal')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            initDateMonth();

            var table = $('#kt_datatable').DataTable({
                dom: "<'row'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6'l>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                responsive: true,
                buttons: [
                    'print',
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdfHtml5',
                ],
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
                    url: "{{ route('comisiones.listado.ajax') }}",
                    type: 'POST',
                    data: function(d) {
                        d.distribuidor = $("#filtroDistribuidor").val();
                        d.vendedor = $("#filtroVendedores").val();
                        d.estado = $("#filtroEstado").val();
                        d.fecha = $("#filtroFecha").val();
                    }
                },
                columns: [{
                        data: 'id_comision',
                        name: 'id_comision',
                        searchable: false,
                        visible: false
                    },
                    {
                        data: 'vendedor',
                        name: 'vendedor',
                    },
                    {
                        data: 'secuencia_perseo',
                        name: 'secuencia_perseo',
                    },
                    {
                        data: 'fecha_registro',
                        name: 'fecha_registro',
                    },
                    {
                        data: 'total_venta',
                        name: 'total_venta',
                        searchable: false,
                    },
                    {
                        data: 'nuevos',
                        name: 'nuevos',
                        searchable: false,
                    },
                    {
                        data: 'renovaciones',
                        name: 'renovaciones',
                        searchable: false,
                    },
                    {
                        data: 'firmas',
                        name: 'firmas',
                        searchable: false,
                    },
                    {
                        data: 'total',
                        name: 'total',
                        searchable: false,
                    },
                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();
                    const opt = {
                        page: 'current'
                    }

                    // Obtiene los valores de la columna "Total Ventas" de todas las filas visibles
                    var totalVentas = api.column(4, opt).data().reduce((acc, val) => (acc +
                        convertStrToFloat(val)), 0);
                    var totalNuevos = api.column(5, opt).data().reduce((acc, val) => (acc +
                        convertStrToFloat(val)), 0);
                    var totalRenovaciones = api.column(6, opt).data().reduce((acc, val) => (acc +
                        convertStrToFloat(val)), 0);
                    var totalFirmas = api.column(7, opt).data().reduce((acc, val) => (acc +
                        convertStrToFloat(val)), 0);
                    var totalComisiones = api.column(8, opt).data().reduce((acc, val) => (acc +
                        convertStrToFloat(val)), 0);

                    // Agrega la suma total al footer de la columna "Total Ventas"
                    $(api.column(4).footer()).html('$' + totalVentas.toFixed(2));
                    $(api.column(5).footer()).html('$' + totalNuevos.toFixed(2));
                    $(api.column(6).footer()).html('$' + totalRenovaciones.toFixed(2));
                    $(api.column(7).footer()).html('$' + totalFirmas.toFixed(2));
                    $(api.column(8).footer()).html('$' + totalComisiones.toFixed(2));
                }
            });

            //Clic en boton buscar
            $('#kt_search').on('click', function(e) {
                e.preventDefault();
                table.draw();
            });

            $('#kt_reset').on('click', function(e) {
                $("#filtroDistribuidor").val('');
                $("#filtroVendedores").val('');
                $("#filtroEstado").val('no');
                $("#filtroFecha").val('');
                initDateMonth();
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

            // Buttons to export
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
            // End buttons to export

            function initDateMonth() {
                $("#filtroFecha").val(
                    `${moment().startOf('month').format('DD-MM-YYYY')} / ${ moment().endOf('month').format('DD-MM-YYYY')}`
                    );
            }

            function convertStrToFloat(val) {
                var numeroTexto = val;
                var numero = Number(numeroTexto.replace(",", ""));
                return parseFloat(numero) || 0;
            }

        });
    </script>
@endsection
