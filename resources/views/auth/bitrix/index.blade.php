@php
    $disabled = Auth::user()->rol == 1 ? 'disabled' : '';
    $vendedorActual = Auth::user()->rol == 1 ? Auth::user()->usuariosid : '';
@endphp
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
                                <div class="container-fluid">
                                    {{-- Filtros --}}
                                    <div class="form-group row">
                                        <div class="col-12 mb-2 col-md-3 mb-md-0">
                                            <label for="">Vendedores:</label>
                                            <select name="" id="filtroVendedores" class="form-control select2"
                                                {{ $disabled }}>
                                                <option value="" selected>Todos</option>
                                                @foreach ($vendedores as $vendedor)
                                                    <option value="{{ $vendedor->usuariosid }}"
                                                        {{ $vendedor->usuariosid == $vendedorActual ? 'selected' : '' }}>
                                                        {{ $vendedor->nombres }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12 mb-2 col-md-3 mb-md-0">
                                            <label for="">Fecha</label>
                                            <input type="text" id="filtroFecha" class="form-control">
                                        </div>
                                        <div class="col-12 mb-2 col-md-2 mb-md-0 d-none">
                                            <label>Tipo de busqueda</label>
                                            <div class="radio-list">
                                                <label class="radio">
                                                    <input type="radio" checked="checked" name="tipoFecha"
                                                        value="created" />
                                                    <span></span>
                                                    Fecha de creación
                                                </label>
                                                <label class="radio">
                                                    <input type="radio" name="tipoFecha" value="closed" />
                                                    <span></span>
                                                    Fecha de cierre
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12 mb-2 col-md-3 mb-md-0">
                                            <button class="btn btn-primary mt-8" id="buscar">Buscar</button>
                                        </div>
                                    </div>
                                    {{-- End Filtros --}}
                                    <div class="row mb-10">
                                        <div class="col-12 col-md-6 min-h-250px">
                                            <div class="d-flex h-100 justify-content-center align-items-center"
                                                id="loaderUtilidadProsecto">
                                                @include('auth.bitrix.inc.spiner')
                                            </div>
                                            <div id="utilidadProspectos"></div>
                                        </div>
                                        <div class="col-12 col-md-6 min-h-250px">
                                            <div class="d-flex h-100 justify-content-center align-items-center"
                                                id="loaderTasaConversion">
                                                @include('auth.bitrix.inc.spiner')
                                            </div>
                                            <div id="tasaDeCierreVentas"></div>
                                        </div>
                                    </div>
                                    <div class="row mb-10">
                                        <div class="col-12 col-md-6 min-h-250px">
                                            <div id="promedioDeVentas"></div>
                                        </div>
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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const HEIGHT_CHART = 250;
        let chartPromedioVentas = null;
        let chartUtilidadProspectos = null;
        let chartTiempoDeCierreDeConversion = null;
        let chartTasaDeCierreVentas = null;

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
            obtenerTasaUtilidadProspectos();
            obtenerTasaConvertido();
            obtenerPromedioVentas();
            obtenerTiempoDeCierreDeConversion();


            btnBuscar.addEventListener('click', function() {
                btnBuscar.setAttribute('disabled', 'enable');
                Promise.all([
                    obtenerTasaUtilidadProspectos(),
                    obtenerTasaConvertido(),
                    obtenerTiempoDeCierreDeConversion(),
                    obtenerPromedioVentas(),
                ]).then().catch().finally(() => {
                    btnBuscar.removeAttribute('disabled');
                });
            });
        });


        /* -------------------------------------------------------------------------- */
        /*                         funciones para los reportes                        */
        /* -------------------------------------------------------------------------- */

        async function obtenerPromedioVentas() {
            try {
                const body = {
                    _token: '{{ csrf_token() }}',
                    vendedor: $('#filtroVendedores').val()
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
            } catch (error) {
                console.log(error);
            }
        }

        async function obtenerTasaUtilidadProspectos() {
            try {
                const body = {
                    _token: '{{ csrf_token() }}',
                    vendedor: $('#filtroVendedores').val(),
                    tipoBusqueda: obtenerTipodeBusqueda(),
                }

                const fechas = validarFiltroFecha();
                if (fechas) {
                    body.fecha_inicio = fechas.fecha_inicio;
                    body.fecha_fin = fechas.fecha_fin;
                }

                const {
                    data
                } = await axios.post("{{ route('bitrix.tasa_utilidad_prospectos') }}", body);

                const totalNoUtiles = data.series[0].data?.reduce((a, b) => a + b, 0);
                const totalUtiles = data.series[1].data?.reduce((a, b) => a + b, 0);
                const totalProspectos = totalNoUtiles + totalUtiles;

                const options = {
                    series: data.series,
                    chart: {
                        type: 'bar',
                        height: HEIGHT_CHART + 50,
                        stacked: true,
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            dataLabels: {
                                enable: true,
                                total: {
                                    enabled: true,
                                    offsetX: 10,
                                    style: {
                                        fontSize: '13px',
                                        fontWeight: 900,
                                        colors: ["#304758"]
                                    }
                                }
                            }
                        },
                    },
                    colors: ['#dc3545', '#0d6efd'],
                    stroke: {
                        width: 1,
                        colors: ['#fff']
                    },
                    title: {
                        text: 'Tasa de utilidad de prospectos',
                    },
                    xaxis: {
                        categories: data.categories,
                        labels: {
                            formatter: function(val) {
                                return val + ""
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + ""
                            }
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        offsetX: 0,
                        fontSize: '14px',
                        formatter: function(seriesName, opts) {
                            if (seriesName == "Utiles") {
                                const porcentaje = ((totalUtiles * 100) / totalProspectos).toFixed(2);
                                return [seriesName, ' ', porcentaje + "%"]
                            } else {
                                const porcentaje = ((totalNoUtiles * 100) / totalProspectos).toFixed(2);
                                return [seriesName, ' ', porcentaje + "%"]
                            }
                        },
                    }
                };

                document.getElementById('loaderUtilidadProsecto')?.remove();

                if (!chartUtilidadProspectos) {
                    chartUtilidadProspectos = new ApexCharts(document.querySelector("#utilidadProspectos"), options);
                    chartUtilidadProspectos.render();
                } else {
                    chartUtilidadProspectos.updateOptions(options);
                }
            } catch (error) {
                console.log(error);
            }
        }

        async function obtenerTiempoDeCierreDeConversion() {
            try {
                const body = {
                    _token: '{{ csrf_token() }}',
                    vendedor: $('#filtroVendedores').val(),
                    tipoBusqueda: obtenerTipodeBusqueda(),
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
                        height: HEIGHT_CHART,
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
                    chartTiempoDeCierreDeConversion = new ApexCharts(document.querySelector(
                            "#tiempoDeCierreDeConversion"),
                        options);
                    chartTiempoDeCierreDeConversion.render();
                } else {
                    chartTiempoDeCierreDeConversion.updateOptions(options);
                }
            } catch (error) {
                console.log(error);
            }
        }

        async function obtenerTasaConvertido() {
            try {
                const body = {
                    _token: '{{ csrf_token() }}',
                    vendedor: $('#filtroVendedores').val(),
                    tipoBusqueda: obtenerTipodeBusqueda(),
                }

                const fechas = validarFiltroFecha();
                if (fechas) {
                    body.fecha_inicio = fechas.fecha_inicio;
                    body.fecha_fin = fechas.fecha_fin;
                }

                const {
                    data
                } = await axios.post("{{ route('bitrix.tasa_conversion_prospectos') }}", body);

                var options = {
                    series: data.series,
                    chart: {
                        type: 'bar',
                        height: HEIGHT_CHART + 50,
                        stacked: true,
                    },
                    stroke: {
                        width: 1,
                        colors: ['#fff']
                    },
                    dataLabels: {
                        formatter: (val) => {
                            return val
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true
                        }
                    },
                    xaxis: {
                        categories: data.categories,
                        labels: {
                            formatter: (val) => {
                                return val
                            }
                        }
                    },
                    fill: {
                        opacity: 1,
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'left'
                    },
                    title: {
                        text: 'Ratio de conversion de clientes',
                        align: 'left'
                    },
                };

                document.getElementById('loaderTasaConversion')?.remove();

                if (!chartTasaDeCierreVentas) {
                    chartTasaDeCierreVentas = new ApexCharts(document.querySelector("#tasaDeCierreVentas"),
                        options);
                    chartTasaDeCierreVentas.render();
                } else {
                    chartTasaDeCierreVentas.updateOptions(options);
                }
            } catch (error) {
                console.log(error);
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

        function obtenerTipodeBusqueda() {
            const tipos = [...document.querySelectorAll('input[name="tipoFecha"]')];

            const tipo = tipos.find(tipo => tipo.checked);

            return tipo.value;
        }
    </script>
@endsection
