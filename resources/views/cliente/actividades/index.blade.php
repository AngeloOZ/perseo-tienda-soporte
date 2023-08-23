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
                                    <h3 class="card-label">Actividades</h3>
                                </div>
                                <div class="card-toolbar">
                                    <a href="#" class="btn btn-primary font-weight-bolder" id="filtrar">
                                        <span class="svg-icon svg-icon-md">
                                            <i class="la la-filter"></i>
                                        </span>Filtrar
                                    </a>
                                    <div class="dropdown dropdown-inline mr-2 ml-2">
                                        <button type="button"
                                            class="btn btn-md btn-light-primary font-weight-bolder dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="svg-icon svg-icon-md">
                                                <!--begin::Svg Icon | path:assets/media/svg/icons/Design/PenAndRuller.svg-->
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                                    viewBox="0 0 24 24" version="1.1">
                                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                        <rect x="0" y="0" width="24" height="24" />
                                                        <path
                                                            d="M3,16 L5,16 C5.55228475,16 6,15.5522847 6,15 C6,14.4477153 5.55228475,14 5,14 L3,14 L3,12 L5,12 C5.55228475,12 6,11.5522847 6,11 C6,10.4477153 5.55228475,10 5,10 L3,10 L3,8 L5,8 C5.55228475,8 6,7.55228475 6,7 C6,6.44771525 5.55228475,6 5,6 L3,6 L3,4 C3,3.44771525 3.44771525,3 4,3 L10,3 C10.5522847,3 11,3.44771525 11,4 L11,19 C11,19.5522847 10.5522847,20 10,20 L4,20 C3.44771525,20 3,19.5522847 3,19 L3,16 Z"
                                                            fill="#000000" opacity="0.3" />
                                                        <path
                                                            d="M16,3 L19,3 C20.1045695,3 21,3.8954305 21,5 L21,15.2485298 C21,15.7329761 20.8241635,16.200956 20.5051534,16.565539 L17.8762883,19.5699562 C17.6944473,19.7777745 17.378566,19.7988332 17.1707477,19.6169922 C17.1540423,19.602375 17.1383289,19.5866616 17.1237117,19.5699562 L14.4948466,16.565539 C14.1758365,16.200956 14,15.7329761 14,15.2485298 L14,5 C14,3.8954305 14.8954305,3 16,3 Z"
                                                            fill="#000000" />
                                                    </g>
                                                </svg>
                                                <!--end::Svg Icon-->
                                            </span>Exportar</button>
                                        <!--begin::Dropdown Menu-->
                                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                            <!--begin::Navigation-->
                                            <ul class="navi flex-column navi-hover py-2">
                                                <li
                                                    class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">
                                                    Elija una opcion:</li>
                                                <li class="navi-item">
                                                    <a href="#" class="navi-link" id="export_excel">
                                                        <span class="navi-icon">
                                                            <i class="la la-file-excel-o"></i>
                                                        </span>
                                                        <span class="navi-text">Excel</span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <!--end::Navigation-->
                                        </div>
                                        <!--end::Dropdown Menu-->
                                    </div>
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
                                            <th data-priority="2">Producto</th>
                                            <th data-priority="3">Descripción</th>
                                            <th>Fecha Hora Inicio</th>
                                            <th>Fecha Hora Fin</th>
                                            <th>Tipo</th>
                                            <th>Facturado</th>
                                            <th>Valor Facturado</th>
                                            <th>Enlace</th>
                                            <th class="no-exportar">Acciones</th>
                                            <th>Tiempo Ocupado</th>
                                            <th class="no-exportar">Suma</th>

                                        </tr>
                                    </thead>
                                    <tfoot style="">
                                        <tr>
                                            <th colspan="11"></th>
                                            <th></th>
                                            <th></th>

                                        </tr>
                                    </tfoot>

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
    @include('modals.delete_modal')
@endsection
@section('script')
    <script>
        $(document).ready(function() {

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
                    url: "{{ route('soportetecnico.listado') }}",
                    type: 'POST',
                    data: function(d) {

                        d.fecha = $('#fecha').val();
                        d.tipo = $('#tipo').val();

                    }
                },

                footerCallback: function(tfoot, data, start, end, display) {
                    api = this.api()
                        .column(12).data().reduce(function(a, b) {
                            suma = (parseInt(a) + parseInt(b));

                            return (suma);
                        }, 0)
                    $(this.api().column(11).footer()).html(


                        valor = this.api()
                        .column(12).data().reduce(function() {

                            dateObj = new Date(api * 1000);
                            hours = dateObj.getUTCHours();
                            minutes = dateObj.getUTCMinutes();
                            seconds = dateObj.getSeconds();

                            timeString = hours.toString().padStart(2, '0') + ':' +
                                minutes.toString().padStart(2, '0') + ':' +
                                seconds.toString().padStart(2, '0');
                            return ('Total Ocupado: ' + timeString);
                        }, 0)
                    );

                },
                columns: [{
                        data: 'actividadesid',
                        name: 'actividadesid',
                        searchable: false,
                        visible: false
                    },
                    {
                        data: 'creador',
                        name: 'tecnicos.nombres',

                    },
                    {
                        data: 'productosid',
                        name: 'productosid',

                    },
                    {
                        data: 'descripcion',
                        name: 'descripcion',
                        visible: false,
                        orderable: false,
                        searchable: false

                    },
                    {
                        data: 'fechahorainicio',
                        name: 'fechahorainicio',
                    },

                    {
                        data: 'fechahorafin',
                        name: 'fechahorafin',

                    },
                    {
                        data: 'tipo',
                        name: 'tipo',
                        visible: false,
                        orderable: false,
                        searchable: false

                    },
                    {
                        data: 'facturado',
                        name: 'facturado',

                    },
                    {
                        data: 'valorfacturado',
                        name: 'valorfacturado',

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
                        data: 'tiempo',
                        name: 'tiempo',
                        searchable: false,
                        orderable: false,
                    },
                    {
                        data: 'suma',
                        name: 'suma',

                        searchable: false,
                        orderable: false,

                        visible: false

                    },

                ],
                buttons: [

                    {
                        extend: 'excelHtml5',
                        title: 'Actividades',
                        exportOptions: {
                            columns: ':not(.no-exportar)'
                        },
                        customize: function(xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            var celdas = $('row c[r^="K"]', sheet);
                            var medida = celdas.length;
                            celdas.each(function(index) {
                                if (index === (medida - 1)) {
                                    $(this).attr('s', '17');
                                }


                            });
                        },

                        footer: true
                    }

                ],

            });
            table.search('').draw();
            //Clic en boton buscar
            $('#kt_search').on('click', function(e) {
                e.preventDefault();
                table.draw();
            });
            //Clic en boton resetear

            $('#kt_reset').on('click', function(e) {
                $("#fecha").val('');
                $("#tipo").val('');
                $('#tipo').change();
                table.draw();
            });

            //Mostrar div de busqueda
            $('#filtrar').on('click', function(e) {
                $("#filtro").toggle(500);
            });

            $('#export_excel').on('click', function(e) {
                e.preventDefault();
                table.button(0).trigger();
            });
        });
    </script>
@endsection
