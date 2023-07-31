@extends('soporte.auth.layouts.app')
@section('title_page', 'Listado de técnicos')
@php
    $disabled = "disabled";
    if(Auth::user()->distribuidoresid == 2){
        $disabled = "";
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
                                    <h3 class="card-label"> Reporte de soportes </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="formFiltrado">
                                    <div class="row">
                                        <div class="col-12 mb-3 col-md-3 mb-md-0">
                                            <label><span class="font-size-h6 font-weight-bold">Distribuidor<span>
                                            </label>
                                            <select class="form-control" id="filtroDistribuidor" {{ $disabled }}>
                                                <option value="">Todos</option>
                                                <option value="1" {{ Auth::user()->distribuidoresid == 1 ? 'selected' : '' }} >Perseo Alfa</option>
                                                <option value="2" {{ Auth::user()->distribuidoresid == 2 ? 'selected' : '' }} >Perseo Matriz</option>
                                                <option value="3" {{ Auth::user()->distribuidoresid == 3 ? 'selected' : '' }} >Perseo Delta</option>
                                                <option value="4" {{ Auth::user()->distribuidoresid == 4 ? 'selected' : '' }} >Perseo Omega</option>
                                            </select>
                                        </div>

                                        <div class="col-12 mb-3 col-md-3 mb-md-0">
                                            <label><span class="font-size-h6 font-weight-bold">Técnico<span>
                                            </label>
                                            <select class="form-control select2" id="filtroTecnico">
                                                <option value="" selected>Todos</option>
                                                @foreach ($tecnicos as $tecnico)
                                                    <option value="{{ $tecnico->usuariosid }}">
                                                        {{ $tecnico->nombres }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12 mb-3 col-md-4 mb-md-0">
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
                                        <div class="col-12 mb-3 col-md-2 mb-md-0 mt-7">
                                            <button class="btn btn-primary">Buscar</button>
                                            <button type="button" id="btnReset" class="btn btn-warning">Limpiar</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="mt-10">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <h4 class="font-size-h5">Número total de soportes: <strong
                                                    id="totalSoportes">0</strong></h4>
                                            <div id="chart"></div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <h4 class="font-size-h5">Gráfica de soportes </h4>
                                            <div id="chartTime"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <h4 class="font-size-h5">Soportes por técnicos</h4>
                                        <div id="chartTecnicos"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <h4 class="font-size-h5">Soportes por estados</h4>
                                        <div class="d-flex justify-content-center mt-8">
                                            <div id="chartEstados"></div>
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
    <script>
        const primary = '#6993FF';
        const success = '#1BC5BD';
        const info = '#8950FC';
        const warning = '#FFA800';
        const danger = '#F64E60';
        const colors = [info, warning, primary, danger, success];

        var dataChart = {
            labels: [],
            values: [],
            tecnicos: {
                labels: [],
                values: []
            },
            estados: {
                labels: [],
                values: []
            },
            tiempo: {
                labels: [],
                values: []
            },
            total: 0
        };

        var options = {
            series: [{
                name: 'Soportes',
                data: dataChart.values
            }],
            chart: {
                height: 350,
                type: 'bar',
            },
            colors: colors,
            plotOptions: {
                bar: {
                    columnWidth: '45%',
                    distributed: true,
                }
            },
            legend: {
                show: false
            },
            xaxis: {
                categories: dataChart.labels,
                labels: {
                    style: {
                        colors: "#000",
                        fontSize: '14px',
                        fontWeight: "600"
                    }
                }
            }
        };
        const chart = new ApexCharts(document.querySelector("#chart"), options);

        var optionsT = {
            series: [{
                name: "Soportes",
                data: dataChart.tecnicos.values
            }],
            chart: {
                type: 'bar',
                height: 350
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                }
            },
            dataLabels: {
                enabled: true
            },
            xaxis: {
                categories: dataChart.tecnicos.labels
            }
        };
        const chartTecnicos = new ApexCharts(document.querySelector("#chartTecnicos"), optionsT);

        var optionsEstados = {
            series: dataChart.estados.values,
            chart: {
                width: 480,
                type: 'pie',
            },
            labels: dataChart.estados.labels,
            colors: [primary, info, success, danger],
            dataLabels: {
                enabled: true,
                formatter(val, opts) {
                    const tempValue = opts.w.globals.seriesTotals[opts.seriesIndex]
                    return [tempValue, val.toFixed(1) + '%']
                }
            },
            plotOptions: {
                pie: {
                    dataLabels: {
                        offset: -20
                    }
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 300
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };
        const chartEstados = new ApexCharts(document.querySelector("#chartEstados"), optionsEstados);

        var optionsTime = {
            series: [{
                name: 'Soportes',
                data: dataChart.tiempo.values
            }],
            chart: {
                height: 350,
                type: 'area'
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            xaxis: {
                type: 'datetime',
                categories: dataChart.tiempo.labels
            },
            tooltip: {
                x: {
                    format: 'dd/MM/yy HH:mm'
                },
            },
        };
        const chartTime = new ApexCharts(document.querySelector("#chartTime"), optionsTime);

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
            dateNow();
            
            initRequest();

            manejoSubmitFiltro();
            btnReset();

            chart.render();
            chartTecnicos.render();
            chartEstados.render();
            chartTime.render();
        });

        function dateNow() {
            $("#filtroFecha").val(`${moment().format('DD-MM-YYYY')} / ${moment().format('DD-MM-YYYY')}`);
        }

        function manejoSubmitFiltro() {
            const formFiltrado = document.getElementById('formFiltrado');

            formFiltrado.addEventListener('submit', async function(e) {
                e.preventDefault();
                const body = {
                    _token: '{{ csrf_token() }}',
                    tecnicoid: this.filtroTecnico.value,
                    fecha: this.filtroFecha.value,
                    distribuidor: this.filtroDistribuidor.value,
                }
                peticionReporte(body);
            })
        }

        function initRequest(){
            const body = {
                _token: '{{ csrf_token() }}',
                tecnicoid: $("#filtroTecnico").val(),
                fecha: $("#filtroFecha").val(),
                distribuidor: $("#filtroDistribuidor").val(),
            }
            peticionReporte(body);
        }

        async function peticionReporte(requestBody) {
            try {
                const request = await axios.post("{{ route('soporte.filtrado_reporte_soporte') }}", requestBody);
                const result = request.data;
                
                dataChart = result;

                const opt = {
                    series: [{
                        name: 'Soportes',
                        data: result.values
                    }],
                    xaxis: {
                        categories: result.labels
                    }
                }

                const opt2 = {
                    series: [{
                        name: 'Soportes',
                        data: result.tecnicos.values
                    }],
                    xaxis: {
                        categories: result.tecnicos.labels
                    }
                }

                const opt3 = {
                    series: result.estados.values,
                    labels: result.estados.labels,
                }

                const opt4 = {
                    series: [{
                        name: 'Soportes',
                        data: result.tiempo.values
                    }],
                    xaxis: {
                        type: 'datetime',
                        categories: result.tiempo.labels
                    },
                }

                if (result.estados.labels.length == 3) {
                    opt3.colors = [primary, info, danger]
                }


                $("#totalSoportes").text(result.total);
                chart.updateOptions(opt);
                chartTecnicos.updateOptions(opt2);
                chartEstados.updateOptions(opt3);
                chartTime.updateOptions(opt4);
            } catch (error) {
                console.log(error);
            }
        }

        function btnReset() {
            const btnReset = document.getElementById('btnReset');
            const formFiltrado = document.getElementById('formFiltrado');
            btnReset.addEventListener('click', e => {
                $("#filtroTecnico").val('');
                $("#filtroFecha").val('');
                $("#filtroDistribuidor").val('');
                formFiltrado.reset();
                dateNow();
                const body = {
                    _token: '{{ csrf_token() }}',
                    tecnicoid: this.filtroTecnico.value,
                    fecha: this.filtroFecha.value,
                    distribuidor: this.filtroDistribuidor.value,
                }
                peticionReporte(body);
            })
        }
    </script>
@endsection
