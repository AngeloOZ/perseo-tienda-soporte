@extends('auth.layouts.app')
@section('titulo', 'Mis estadisticas')
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
                                    <h3 class="card-label"> Mis estadisticas <small>Vendedor</small></h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="container">
                                    <div class="form-group row">
                                        @if (1 != 1)
                                            <div class="col-3">
                                                <label for="">Vendedores:</label>
                                                <select name="" id="filtroVendedores" class="form-control select2">
                                                    <option value="">Todos</option>
                                                    @foreach ($vendedores as $vendedor)
                                                        <option value="{{ $vendedor->usuariosid }}">{{ $vendedor->nombres }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                        <div class="col-6">
                                            <label for="">Fecha</label>
                                            <input type="text" id="filtroFecha" class="form-control">
                                        </div>
                                        <div class="col-2">
                                            <button class="btn btn-primary mt-8" id="buscar">Buscar</button>
                                        </div>
                                    </div>
                                    <div class="row mb-10">
                                        <div class="col-12 col-md-6 min-h-250px">
                                            <div id="promedioDeVentas"></div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div id="utilidadProspectos"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-6 min-h-250px">
                                            <div id="tiempoDeCierreDeConversion"></div>
                                        </div>
                                    </div>
                                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script> --}}
    <script>
        const HEIGHT_CHART = 250;
        let chartPromedioVentas = null;
        let chartUtilidadProspectos = null;
        let chartTiempoDeCierreDeConversion = null;

        $(document).ready(function() {
            const btnBuscar = document.querySelector('#buscar');
            $('#filtroFecha').daterangepicker({
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
                $('#filtroFecha').val(start.format('DD-MM-YYYY') + ' / ' + end.format(
                    'DD-MM-YYYY'));
            });

            inicializarFecha();
            obtenerPromedioVentas();
            obtenerTasaUtilidadProspectos();
            obtenerTiempoDeCierreDeConversion();


            btnBuscar.addEventListener('click', function() {
                btnBuscar.setAttribute('disabled', 'enable');
                Promise.all([
                    obtenerPromedioVentas(),
                    obtenerTasaUtilidadProspectos(),
                    obtenerTiempoDeCierreDeConversion()
                ]).then().catch().finally(() => {
                    btnBuscar.removeAttribute('disabled');
                });
            });
        });


        /* -------------------------------------------------------------------------- */
        /*                         funciones para los reportes                        */
        /* -------------------------------------------------------------------------- */

        async function obtenerPromedioVentas() {
            const body = {
                _token: '{{ csrf_token() }}',
            }

            const fechas = validarFiltroFecha('YYYY-MM-DD 00:00:00', 'YYYY-MM-DD 23:59:59');
            if (fechas) {
                body.fecha_inicio = fechas.fecha_inicio;
                body.fecha_fin = fechas.fecha_fin;
            }

            const {
                data
            } = await axios.post("{{ route('bitrix.promedio_ventas') }}", body)


            const options = {
                series: [{
                    name: 'Promedio de ventas',
                    data: data.data
                }],
                title: {
                    text: 'Promedio de ventas',
                    align: 'left'
                },
                chart: {
                    type: 'bar',
                    height: HEIGHT_CHART
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: data.categories,
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return "$" + val;
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    textAnchor: 'start',
                    style: {
                        colors: ['#fff']
                    },
                    formatter: function(val, opt) {
                        return `$${val}`;
                    },
                    offsetX: 0,
                },
            };

            if (!chartPromedioVentas) {
                chartPromedioVentas = new ApexCharts(document.querySelector('#promedioDeVentas'), options);
                chartPromedioVentas.render();
            } else {
                chartPromedioVentas.updateOptions(options);
            }

        }

        async function obtenerTasaUtilidadProspectos(destroy = false) {
            const body = {
                _token: '{{ csrf_token() }}',
            }

            const fechas = validarFiltroFecha();
            if (fechas) {
                body.fecha_inicio = fechas.fecha_inicio;
                body.fecha_fin = fechas.fecha_fin;
            }

            const {
                data
            } = await axios.post("{{ route('bitrix.tasa_utilidad_prospectos') }}", body);

            const options = {
                series: [{
                    name: 'Prospectos',
                    data: data.data
                }],
                chart: {
                    height: HEIGHT_CHART,
                    type: 'bar',
                },
                colors: ['#dc3545', '#0090FF', '#198754'],
                plotOptions: {
                    bar: {
                        columnWidth: '45%',
                        distributed: true,
                        dataLabels: {
                            position: 'top',
                        },
                    },
                },
                legend: {
                    show: true
                },
                xaxis: {
                    categories: data.categories,
                },
                axisTicks: {
                    show: false
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val + "%";
                    },
                    offsetY: -25,
                    style: {
                        fontSize: '14px',
                        colors: ["#304758"]
                    }
                },
            };

            if (!chartUtilidadProspectos) {
                chartUtilidadProspectos = new ApexCharts(document.querySelector("#utilidadProspectos"), options);
                chartUtilidadProspectos.render();
            } else {
                chartUtilidadProspectos.updateOptions(options);
            }
        }

        async function obtenerTiempoDeCierreDeConversion() {

            const body = {
                _token: '{{ csrf_token() }}',
            }

            const fechas = validarFiltroFecha();
            if (fechas) {
                body.fecha_inicio = fechas.fecha_inicio;
                body.fecha_fin = fechas.fecha_fin;
            }

            const {
                data
            } = await axios.post("{{ route('bitrix.tiempo_de_conversion') }}", body);

            const options = {
                series: [{
                    name: '',
                    data: data.data
                }],
                dataLabels: {
                    enabled: true,
                    textAnchor: 'start',
                    offsetY: 0,
                    style: {
                        fontSize: '14px',
                        colors: ["#304758"]
                    },
                    formatter: function(val, opt) {
                        const dias = Math.floor(val / 24);
                        const horas = val % 24;
                        return `${dias} dias y ${horas.toFixed(2)} horas`;
                    },
                },
                xaxis: {
                    categories: data.categories,
                },
                yaxis: {
                    labels: {
                        show: true
                    }
                },
                title: {
                    text: 'Promedio de tiempo de conversión',
                    align: 'left'
                },
                chart: {
                    type: 'bar',
                    height: HEIGHT_CHART
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                    }
                },
                colors: ['#00E396'],
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " horas"
                        }
                    }
                }
            };

            if (!chartTiempoDeCierreDeConversion) {
                chartTiempoDeCierreDeConversion = new ApexCharts(document.querySelector("#tiempoDeCierreDeConversion"),
                    options);
                chartTiempoDeCierreDeConversion.render();
            } else {
                chartTiempoDeCierreDeConversion.updateOptions(options);
            }

        }

        /* -------------------------------------------------------------------------- */
        /*                             Funciones genericas                            */
        /* -------------------------------------------------------------------------- */

        function inicializarFecha() {
            $("#filtroFecha").val(
                `${moment().startOf('month').format('DD-MM-YYYY')} / ${moment().endOf('month').format('DD-MM-YYYY')}`);
        }

        function validarFiltroFecha(format1 = 'YYYY-MM-DD', format2 = 'YYYY-MM-DD') {
            const date = $('#filtroFecha').val();

            if (date.length < 8) return null;

            const fecha = date.split(' / ');

            return {
                fecha_inicio: moment(fecha[0], 'DD-MM-YYYY').format(format1),
                fecha_fin: moment(fecha[1], 'DD-MM-YYYY').format(format2),
            }
        }
    </script>
@endsection
