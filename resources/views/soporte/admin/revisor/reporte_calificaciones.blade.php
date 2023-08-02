@extends('soporte.auth.layouts.app')
@section('title_page', 'Reporte de calificaciones')
@php
    $currentUser = Auth::guard('tecnico')->user();
    $disabled = 'disabled';
    if ($currentUser->distribuidoresid == 2) {
        $disabled = '';
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
                                    <h3 class="card-label"> Reporte de calificaciones </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="formFiltrado">
                                    <div class="row">
                                        <div class="col-12 mb-3 col-md-3 mb-md-0">
                                            <label><span class="font-size-h6 font-weight-bold">Distribuidor<span>
                                            </label>
                                            <select class="form-control" id="filtroDistribuidor">
                                                <option value="" selected>Todos</option>
                                                <option value="1"
                                                    {{ $currentUser->distribuidoresid == 1 ? 'selected' : '' }}>Perseo Alfa
                                                </option>
                                                <option value="2"
                                                    {{ $currentUser->distribuidoresid == 2 ? 'selected' : '' }}>Perseo
                                                    Matriz</option>
                                                <option value="3"
                                                    {{ $currentUser->distribuidoresid == 3 ? 'selected' : '' }}>Perseo Delta
                                                </option>
                                                <option value="4"
                                                    {{ $currentUser->distribuidoresid == 4 ? 'selected' : '' }}>Perseo Omega
                                                </option>
                                            </select>
                                        </div>

                                        <div class="col-12 mb-3 col-md-3 mb-md-0">
                                            <label><span class="font-size-h6 font-weight-bold">Técnico<span>
                                            </label>
                                            <select class="form-control select2" id="filtroTecnico">
                                                <option value="" selected>Todos</option>
                                                @foreach ($tecnicos as $tecnico)
                                                    <option value="{{ $tecnico->tecnicosid }}">
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
                                <div class="mt-5">
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="font-size-h5 m-0">Número total de calificaciones: <strong
                                                    id="totalCalificaciones">0</strong></p>
                                            <p class="font-size-h6 m-0">Puntaje promedio de la pregunta 1 <i
                                                    class="far fa-question-circle" data-toggle="tooltip" data-theme="dark"
                                                    title="¿Cómo calificaría la amabilidad y cordialidad del servicio de atención al cliente que recibió en su última interacción con nuestro equipo de soporte?"></i>:
                                                <strong id="promP1" class="ml-1">0</strong>
                                            </p>
                                            <p class="font-size-h6 m-0">Puntaje promedio de la pregunta 2 <i
                                                    class="far fa-question-circle" data-toggle="tooltip" data-theme="dark"
                                                    title="¿Qué tan satisfecho está con la solución que recibió para su problema en su última interacción con nuestro equipo de soporte?"></i>:
                                                <strong id="promP2" class="ml-1">0</strong>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row mt-5">
                                        <div class="col-12 col-md-6 text-center d-flex justify-content-center">
                                            <div id="chart1"></div>
                                        </div>
                                        <div class="col-12 col-md-6 text-center d-flex justify-content-center">
                                            <div id="chart2"></div>
                                        </div>
                                        <div class="col-12">
                                            <div id="chart3"></div>
                                        </div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <button class="btn btn-primary" data-toggle="modal"
                                            data-target="#staticBackdrop">Ver calificaciones</button>
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
@section('modal')
    @include('soporte.admin.revisor.modal_calificaciones')
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const colors = ["#FA5563", "#FB834F", "#FCCF3E", "#A0D77A", "#6BCA6C"];
        const dataChart = {
            pregunta_1: {
                values: [],
                labels: []
            },
            pregunta_2: {
                values: [],
                labels: []
            },
            calificaciones_por_tecnico: {
                values: [],
                labels: []
            }
        };

        var options1 = {
            series: dataChart.pregunta_1.values,
            chart: {
                width: 380,
                type: 'pie',
            },
            colors: colors,
            labels: dataChart.pregunta_1.labels,
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
            }],
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
            title: {
                text: 'Pregunta 1',
                offsetY: 0,
                offsetX: 0,
                style: {
                    color: '#000'
                }
            }
        };

        var options2 = {
            series: dataChart.pregunta_2.values,
            chart: {
                width: 400,
                type: 'pie',
            },
            colors: colors,
            labels: dataChart.pregunta_2.labels,
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
            }],
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
            title: {
                text: 'Pregunta 2',
                offsetY: 0,
                offsetX: 0,
                style: {
                    color: '#000'
                }
            }
        };

        var options3 = {
            series: [],
            chart: {
                type: 'bar',
                height: 350,
                stacked: true,
                stackType: '100%'
            },
            colors: colors,
            plotOptions: {
                bar: {
                    horizontal: true,
                },
            },
            stroke: {
                width: 1,
                colors: ['#fff']
            },
            title: {
                text: 'Calificaciones por técnico',
            },
            xaxis: {
                categories: [],
            },
            legend: {
                position: 'top',
                horizontalAlign: 'left',
                offsetX: 40
            }
        };

        const chartP1 = new ApexCharts(document.querySelector("#chart1"), options1);
        const chartP2 = new ApexCharts(document.querySelector("#chart2"), options2);
        const chartP3 = new ApexCharts(document.querySelector("#chart3"), options3);


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

            initData();
            chartP1.render();
            chartP2.render();
            chartP3.render();
            manejoSubmitFiltro();
            btnReset();
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

        async function peticionReporte(requestBody) {
            try {
                const request = await axios.post("{{ route('soporte.filtrado_reporte_calificaicones') }}", requestBody);
                const result = request.data;

                $("#totalCalificaciones").text(result.total);
                $("#promP1").text(result.pregunta_1.promedio);
                $("#promP2").text(result.pregunta_2.promedio);

                chartP1.updateOptions({
                    series: result.pregunta_1.values,
                    labels: result.pregunta_1.labels,
                });
                chartP2.updateOptions({
                    series: result.pregunta_2.values,
                    labels: result.pregunta_2.labels,
                });

                chartP3.updateOptions({
                    series: result.calificaciones_por_tecnico.values,
                    labels: result.calificaciones_por_tecnico.labels,
                });

                renderizarTablaCalificaciones(result.tabla);

            } catch (error) {
                console.log(error);
            }
        }

        function renderizarTablaCalificaciones(encuestas) {
            const tabla = document.getElementById('tablaModalList');
            const tbody = tabla.querySelector('tbody');
            const fragment = document.createDocumentFragment();

            encuestas.forEach(encuesta => {
                const tr = document.createElement('tr');
                tr.style.fontSize = '14px';
                tr.innerHTML = `
                    <td>${encuesta.ruc}</td>
                    <td>${encuesta.razon_social}</td>
                    <td>${encuesta.whatsapp}</td>
                    <td style='width: 100px;'>${encuesta.correo}</td>
                    <td>${encuesta.pregunta_1}/5</td>
                    <td>${encuesta.pregunta_2}/5</td>
                    <td>${encuesta.comentario ?? ''}</td>
                    <td>${encuesta.tecnico}</td>
                `;
                fragment.appendChild(tr);
            });
            tbody.appendChild(fragment);
        }

        function btnReset() {
            const btnReset = document.getElementById('btnReset');
            const formFiltrado = document.getElementById('formFiltrado');
            btnReset.addEventListener('click', e => {
                $("#filtroTecnico").val('');
                $("#filtroFecha").val('');
                $("#filtroDistribuidor").val('');
                dateNow();
                formFiltrado.reset();
                const body = {
                    _token: '{{ csrf_token() }}',
                    tecnicoid: this.filtroTecnico.value,
                    fecha: this.filtroFecha.value,
                }
                peticionReporte(body);
            })
        }

        function initData() {
            const body = {
                _token: '{{ csrf_token() }}',
                tecnicoid: $("#filtroTecnico").val(),
                fecha: $("#filtroFecha").val(),
                distribuidor: $("#filtroDistribuidor").val(),
            }
            peticionReporte(body);
        }
    </script>
@endsection
