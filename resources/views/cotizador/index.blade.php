    @extends('cotizador.layouts.app')
    @php
        $cliente = 1;
    $productos = App\Models\Producto::all();
    @endphp
    @section('contenido')
        <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
            <div class="d-flex flex-column-fluid">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <!--begin::Card-->
                            <form class="form" action="{{ route('cotizacion.guardar') }}" method="POST">
                                <div class="card card-custom card-sticky" id="kt_page_sticky_card">
                                    <div class="card-header flex-wrap py-5">
                                        <div class="card-title">
                                            <h3 class="card-label">Cotizar </h3>
                                        </div>
                                        @include('cotizador.inc.toolsbar')
                                    </div>
                                    <div class="card-body">
                                        @csrf
                                        @include('cotizador.inc.datos')
                                        @include('cotizador.inc.tabla')
                                    </div>
                                </div>
                                <select style="visibility:hidden" id="arrayDetalles" name="arrayDetalles[]"
                                    class="recuperarArray" multiple="multiple"></select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @section('scriptFecha')
        <script>
            $(document).ready(function() {
                $('#cantidadF').TouchSpin({
                    verticalbuttons: true,
                    verticalupclass: 'btn btn-sm btn-secondary',
                    verticaldownclass: 'btn  btn-sm btn-secondary',
                    min: 1,
                    max: 100,
                    step: 1,
                    boostat: 5,
                    maxboostedstep: 10,
                    forcestepdivisibility: 'none'
                });

                $('#descuentoF').TouchSpin({
                    verticalbuttons: true,
                    verticalupclass: 'btn btn-sm btn-secondary',
                    verticaldownclass: 'btn  btn-sm btn-secondary',
                    min: 0,
                    max: 100,
                    step: 1,
                    decimals: 2,
                    boostat: 5,
                    maxboostedstep: 10,
                    forcestepdivisibility: 'none',
                    prefix: "%",
                });


                $.fn.datepicker.dates['es'] = {
                    days: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"],
                    daysShort: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"],
                    daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
                    months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre",
                        "Octubre",
                        "Noviembre", "Diciembre"
                    ],
                    monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov",
                        "Dic"
                    ],
                    today: "Hoy",
                    monthsTitle: "Meses",
                    clear: "Borrar",
                    weekStart: 1,
                    format: "dd-mm-yyyy"
                };
                $('#fecha').datepicker({
                    language: "es",
                    todayHighlight: true,
                    orientation: "bottom left",
                    templates: {
                        leftArrow: '<i class="la la-angle-left"></i>',
                        rightArrow: '<i class="la la-angle-right"></i>'
                    }
                });
                inicializarValidacion();
            });

            function inicializarValidacion() {
                $('.validarDigitos').on('input', function() {
                    this.value = this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');
                });
            }

            var table = $('#kt_datatable').DataTable({

                paging: false,
                searching: false,
                bInfo: false,
                responsive: true,
                processing: true,
                columns: [{
                        data: 'detalle',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'cantidad',
                        orderable: false,
                        searchable: false
                    },

                    {
                        data: 'descuento',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'eliminar',
                        orderable: false,
                        searchable: false
                    }
                ]

            });

            $("form").submit(function(e) {
                let inputFecha = document.getElementById("fecha").value;
                let inputPlanilla = document.getElementById("tipo_plantilla").value;
                let inputPago = document.getElementById("forma_pagoid").value;

                if ('{{ $cliente }}' == 1) {
                    let inputCliente = document.getElementById("clientesid").value;
                    if (inputCliente != "") {
                        $('#mensajeCliente').addClass("d-none");
                    } else {
                        $('#mensajeCliente').removeClass("d-none");
                        e.preventDefault();
                    }
                } else {
                    let inputProspecto = document.getElementById("prospectosid").value;
                    if (inputProspecto != "") {
                        $('#mensajeProspecto').addClass("d-none");

                    } else {
                        $('#mensajeProspecto').removeClass("d-none");
                        e.preventDefault();
                    }
                }


                if (inputFecha != "") {
                    $('#mensajeFecha').addClass("d-none");
                } else {
                    $('#mensajeFecha').removeClass("d-none");
                }


                if (inputPlanilla != "") {
                    $('#mensajePlantilla').addClass("d-none");
                } else {
                    $('#mensajePlantilla').removeClass("d-none");
                }

                if (inputPago != "") {
                    $('#mensajePago').addClass("d-none");
                } else {
                    $('#mensajePago').removeClass("d-none");
                }


                if (inputFecha != "" && inputPlanilla != "" && inputPago != "") {

                    let inputValue = document.getElementsByClassName("cantidad");

                    let array = []
                    for (var i = 0; i < inputValue.length; i++) {
                        let valoresCantidad = document.getElementsByClassName("cantidad")[i].value;
                        let valoresDescuento = document.getElementsByClassName("descuento")[i].value;
                        let valoresSelect = $(`.valoresSelect:eq(${i})`);

                        if (valoresSelect.val() != "") {
                            var valueSelect = $("[name='mensajeDetalle']").eq(i);
                            valueSelect.addClass("d-none");
                        } else {
                            var valueSelect = $("[name='mensajeDetalle']").eq(i);
                            valueSelect.removeClass("d-none");
                        }

                        if (valoresCantidad != "") {
                            var valueCant = $("[name='mensajeCantidad']").eq(i);
                            valueCant.addClass("d-none");
                        } else {
                            var valueCant = $("[name='mensajeCantidad']").eq(i);
                            valueCant.removeClass("d-none");
                        }

                        if (valoresDescuento != "") {
                            var valueDesc = $("[name='mensajeDescuento']").eq(i);
                            valueDesc.addClass("d-none");

                        } else {
                            var valueDesc = $("[name='mensajeDescuento']").eq(i);
                            valueDesc.removeClass("d-none");
                        }

                        if (valoresCantidad != '' && valoresDescuento != '' && valoresSelect.val() != '') {
                            if (valoresCantidad > 0) {
                                array[i] = [valoresSelect.val(), valoresCantidad, valoresDescuento];
                            } else {
                                break;
                            }
                        } else {
                            e.preventDefault();
                        }
                    }

                    if ((inputValue.length == array.length)) {
                        console.log(array);
                        return;
                        $('#arrayDetalles').empty();
                        for (item of array) {
                            $('#arrayDetalles').append(`<option value='${item}' selected='selected' >${item}</option>`);
                        }
                    } else {
                        e.preventDefault();
                    }
                } else {
                    e.preventDefault();
                }
            });

            function limpiarCampos() {
                $("#fecha").val("");
                $("#prospectosid").val("").trigger('change');
                $("#clientesid").val("").trigger('change');
                $(".valoresSelect, .recuperarArray").val('').trigger('change');
                $("#forma_pagoid").val("").trigger('change');
                $("#tipo_plantilla").val("").trigger('change');
            }

            function agregarFila() {

                table.row.add({
                    detalle: ` <td><select class="form-control select2 valoresSelect" name="detallesid"><option value="">Escoja un detalle </option>
                        @foreach ($productos as $producto)
                        <option value="{{ $producto->productosid }}"
                            {{ collect(old('productosid'))->contains($producto->productosid) ? 'selected' : '' }}>
                            {{ $producto->descripcion }}
                        </option>
                        @endforeach
                    </select> <span class="text-danger d-none" name="mensajeDetalle">Escoja un detalle</span> </td> `,
                    cantidad: `<td> <input value="1" type="text" class="form-control input-sm cantidad cantidadT" onkeypress="return validarNumero(event)"> <span class="text-danger d-none" name="mensajeCantidad">Ingrese cantidad</span></td>`,
                    descuento: `<td> <input value="0" type="text" class="form-control descuento descuentoT input-sm validarDigitos"> <span class="text-danger d-none" name="mensajeDescuento">Ingrese descuento</span></td>`,
                    eliminar: `<td> <button type="button" class="btn btn-sm btn-danger botonEliminar" name="botonEliminar"  onclick="eliminarFila(this)">-</button></td>`
                }).draw(false);

                $('.select2').select2({
                    width: '100%',
                    language: {
                        noResults: function() {
                            return "No hay resultado";
                        },
                        searching: function() {
                            return "Buscando..";
                        }
                    }
                });
                $('.cantidadT').TouchSpin({
                    verticalbuttons: true,
                    verticalupclass: 'btn btn-sm btn-secondary',
                    verticaldownclass: 'btn  btn-sm btn-secondary',
                    min: 1,
                    max: 100,
                    step: 1,
                    boostat: 5,
                    maxboostedstep: 10,
                    forcestepdivisibility: 'none'
                });
                $('.descuentoT').TouchSpin({
                    verticalbuttons: true,
                    verticalupclass: 'btn btn-sm btn-secondary',
                    verticaldownclass: 'btn  btn-sm btn-secondary',
                    min: 0,
                    max: 100,
                    step: 1,
                    decimals: 2,
                    boostat: 5,
                    maxboostedstep: 10,
                    forcestepdivisibility: 'none',
                    prefix: "%",
                });
                inicializarValidacion();

            }

            function eliminarFila(boton) {
                var table = document.getElementById("kt_datatable");
                var t = $("#kt_datatable").DataTable();
                var rowCount = table.rows.length;

                console.log(rowCount);

                if (rowCount <= 2) {
                    $.notify({
                        // options
                        message: 'No se puede eliminar la primera fila',
                    }, {
                        // settings
                        showProgressbar: true,
                        delay: 2500,
                        mouse_over: "pause",
                        placement: {
                            from: "top",
                            align: "right",
                        },
                        animate: {
                            enter: "animated fadeInUp",
                            exit: "animated fadeOutDown",
                        },
                        type: 'warning',
                    });
                } else {
                    var row = $(boton).parents('tr');
                    if ($(row).hasClass('child')) {
                        t.row($(row).prev('tr')).remove().draw();
                    } else {
                        t
                            .row($(boton).parents('tr'))
                            .remove()
                            .draw();
                    }
                }
            }
        </script>
    @endsection
