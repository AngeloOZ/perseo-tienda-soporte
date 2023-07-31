@extends('soporte.auth.layouts.app')
@section('title_page', 'Listado de técnicos')
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
                    <div class="col-12">
                        <div class="card card-custom card-sticky">
                            <div class="card-header ">
                                <div class="card-title">
                                    <h3 class="card-label"> Mis calificaciones <small>Técnico</small> </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="formFiltrado" class="d-flex">
                                    <div class="input-group" id='kt_fecha'>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="la la-calendar-check-o"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control" autocomplete="off"
                                            placeholder="Rango de Fechas" id="filtroFecha">
                                    </div>
                                    <button class="btn btn-primary">Buscar</button>
                                    <button type="button" id="btnReset" class="btn btn-warning">Limpiar</button>
                                </form>
                                <div class="mt-7">
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="font-size-h5 m-0">Número total de calificaciones: <strong
                                                    id="totalCalificaciones">0</strong></p>
                                            <p class="font-size-h6 m-0">
                                                Puntaje promedio de la pregunta 1 <i class="far fa-question-circle"
                                                    data-toggle="tooltip" data-theme="dark"
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
                                    </div>
                                    <div class="text-center mt-2">
                                        <button class="btn btn-primary" data-toggle="modal"
                                            data-target="#staticBackdrop">Ver
                                            calificaciones</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modal')
    <div class="modal fade" id="staticBackdrop" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Listo de calificaciones</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p style="font-size: 14px;"><strong>Pregunta 1:</strong> ¿Cómo calificaría la amabilidad y cordialidad
                        del servicio de atención al cliente que recibió en su última interacción con nuestro equipo de
                        soporte?</p>
                    <p style="font-size: 14px;"><strong>Pregunta 2:</strong> ¿Qué tan satisfecho está con la solución que
                        recibió para su problema en su última interacción con nuestro equipo de soporte?</p>
                    <div class="table-responsive">
                        <table class="table table-responsive">
                            <thead>
                                <tr>
                                    <th>RUC</th>
                                    <th>Razón social</th>
                                    <th>WhatsApp</th>
                                    <th style="width: 100px;">Correo</th>
                                    <th class="text-center">Pregunta 1</th>
                                    <th class="text-center">Pregunta 2</th>
                                    <th>Comentario</th>
                                    <th>Fecha de cierre</th>
                                </tr>
                            </thead>
                            <tbody id="tablaModalList"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary font-weight-bold"
                        data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
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
            }
        }

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

        const chartP1 = new ApexCharts(document.querySelector("#chart1"), options1);
        const chartP2 = new ApexCharts(document.querySelector("#chart2"), options2);


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
            const formFiltrado = document.getElementById('formFiltrado');
            const btnReset = document.getElementById('btnReset');


            formFiltrado.addEventListener('submit', async function(e) {
                e.preventDefault();
                const body = {
                    _token: '{{ csrf_token() }}',
                    fecha: this.filtroFecha.value,
                }
                peticionReporte(body);
            });

            btnReset.addEventListener('click', e => {
                dateNow();
                const body = {
                    _token: '{{ csrf_token() }}',
                    fecha: formFiltrado.filtroFecha.value,
                }
                peticionReporte(body);
            });

            chartP1.render();
            chartP2.render();

            btnReset.click();
        });

        async function peticionReporte(requestBody) {
            try {
                const request = await axios.post("{{ route('soporte.mis_calificaciones_filtrado') }}", requestBody);
                const result = request.data;

                const opt = {
                    series: result.pregunta_1.values,
                    labels: result.pregunta_1.labels,
                }

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

                $("#tablaModalList").html(result.tabla);

            } catch (error) {
                console.log(error);
            }
        }

        function dateNow() {
            $("#filtroFecha").val(`${moment().format('DD-MM-YYYY')} / ${moment().format('DD-MM-YYYY')}`);
        }
    </script>
@endsection
