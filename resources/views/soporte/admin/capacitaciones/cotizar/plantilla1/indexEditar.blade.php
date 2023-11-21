    @php
        $detalle = App\Models\CotizacionesDetalle::select('detallesid', 'detalle')->get();
        $plantilla = App\Models\PlantillaDescarga::select('plantillaDescargaid', 'detalle')->get();
    @endphp
    @extends('auth.layouts.app')
    @section('contenido')
        <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
            <div class="d-flex flex-column-fluid">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <form class="form"
                                action="{{ route('actualizarCotizacion.index', $cotizaciones->cotizacionesid) }}"
                                method="POST">
                                @method('PUT')
                                <div class="card card-custom card-sticky" id="kt_page_sticky_card">
                                    <div class="card-header flex-wrap py-5">
                                        <div class="card-title">
                                            <h3 class="card-label">Cotizar </h3>
                                        </div>
                                        <div class="card-toolbar">
                                            <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                                <div class="btn-group" role="group" aria-label="">
                                                    <a href="{{ route('listadoCotizaciones.listado') }}"
                                                        class="btn btn-secondary btn-icon" data-toggle="tooltip"
                                                        title="Listado Cotizaciones"><i style="color:#000000"
                                                            class="la la-long-arrow-left "></i></a>
                                                    <a>
                                                        <button type="submit" class="btn btn-icon"
                                                            style="border-radius: 0px;  background-color:#d6d9dd"
                                                            data-toggle="tooltip" title="Word" value="descargar"
                                                            name="botonDescargaCrear"><i class="la la-file-word"
                                                                style="color:#000000"></i>
                                                        </button>
                                                    </a>
                                                    <a>
                                                        <button type="submit" class="btn btn-primary btn-icon"
                                                            style="border-radius: 0px; " data-toggle="tooltip"
                                                            title="Guardar" value="guardar" name="botonDescargaCrear"><i
                                                                style="color:#ffffff" class="la la-save"></i>
                                                        </button>
                                                    </a>
                                                    <a href="{{ route('cotizarPlantilla1.index', 0) }}"
                                                        class="btn btn-warning btn-icon" data-toggle="tooltip"
                                                        title="Nueva Cotizacion"><i style="color:#ffffff"
                                                            class="la la-file"></i></a>

                                                    <button type="button" class="btn btn-success btn-icon"
                                                        title="Agregar fila" onclick="agregarFila()"><i
                                                            style="color:#ffffff" class="la la-plus "></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @csrf
                                        <div class="form-group row">
                                            <div class="col-lg-6">
                                                <label>Fecha:</label>
                                                <input type="text" class="form-control" placeholder="Ingrese fecha"
                                                    name="fecha" id="fecha" value="{{ $cotizaciones->fecha }}"
                                                    autocomplete="off" />
                                                <span class="text-danger d-none" id="mensajeFecha">Ingrese una fecha</span>
                                                @if ($errors->has('fecha'))
                                                    <span class="text-danger">{{ $errors->first('fecha') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-lg-6">
                                                <label>Cédula/RUC <span class="text-danger">*</span></label>
                                                <div id="spinner">
                                                    <input type="text"
                                                        class="form-control {{ $errors->has('identificacion_cliente') ? 'is-invalid' : '' }}"
                                                        id="inputRuc" name="identificacion_cliente"
                                                        oninput="if(this.value.length > 13) this.value = this.value.slice(0, 13);"
                                                        autocomplete="off" placeholder="17XXXXXX00"
                                                        value="{{ old('identificacion_cliente', $cotizaciones->identificacion_cliente) }}">
                                                    <span class="form-text text-danger d-none" id="helperTextRuc"></span>
                                                </div>
                                                @if ($errors->has('identificacion_cliente'))
                                                    <span
                                                        class="text-danger">{{ $errors->first('identificacion_cliente') }}</span>
                                                @endif
                                            </div>

                                            <div class="col-lg-6">
                                                <label for="">Nombres<span class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control {{ $errors->has('nombre_cliente') ? 'is-invalid' : '' }}"
                                                    name="nombre_cliente" id="nombre_cliente" autocomplete="off"
                                                    value="{{ old('nombre_cliente', $cotizaciones->nombre_cliente) }}">
                                                @if ($errors->has('nombre_cliente'))
                                                    <span class="text-danger">{{ $errors->first('nombre_cliente') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-lg-6">
                                                <label>Plantilla</label>
                                                <select class="form-control select2" id="tipo_plantilla"
                                                    name="tipo_plantilla">
                                                    <option value="">
                                                        Escoja una plantilla
                                                    </option>
                                                    @foreach ($plantilla as $plantillaL)
                                                        <option value="{{ $plantillaL->plantillaDescargaid }}"
                                                            style="font-size: 2px;"
                                                            {{ $plantillaL->plantillaDescargaid == $cotizaciones->plantillasid ? 'selected' : '' }}>
                                                            {{ $plantillaL->detalle }}
                                                        </option>
                                                    @endforeach

                                                </select>
                                                <span class="text-danger d-none" id="mensajePlantilla">Escoja una
                                                    plantilla</span>


                                            </div>
                                            <div class="col-lg-6">
                                                <label>Detalle de Pago</label>
                                                <select class="form-control select2" id="forma_pagoid"
                                                    name="forma_pagoid">
                                                    <option value="">
                                                        Escoja número de pago
                                                    </option>
                                                    <option value="1"
                                                        {{ $cotizaciones->detalle_pago == 1 ? 'selected' : '' }}>
                                                        1 Pago
                                                    </option>
                                                    <option value="2"
                                                        {{ $cotizaciones->detalle_pago == 2 ? 'selected' : '' }}>
                                                        2 Pagos
                                                    </option>
                                                    <option value="3"
                                                        {{ $cotizaciones->detalle_pago == 3 ? 'selected' : '' }}>
                                                        3 Pagos
                                                    </option>
                                                    <option value="4"
                                                        {{ $cotizaciones->detalle_pago == 4 ? 'selected' : '' }}>
                                                        4 Pagos
                                                    </option>
                                                </select>
                                                <span class="text-danger d-none" id="mensajePago">Escoja una pago</span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-lg-6">
                                                <label>Subtotal:</label>
                                                <input type="text" class="form-control" placeholder=""
                                                    name="subtotal" id="subtotal" value="{{ $cotizaciones->subtotal }}"
                                                    readonly />
                                            </div>
                                            <div class="col-lg-6">
                                                <label>Iva:</label>
                                                <input type="text" class="form-control" placeholder="" name="iva"
                                                    autocomplete="off" id="iva" value="{{ $cotizaciones->iva }}"
                                                    readonly />
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-lg-6">
                                                <label>Total:</label>
                                                <input type="text" class="form-control" placeholder="" name="total"
                                                    id="total" value="{{ $cotizaciones->total }}" readonly />
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-lg-6">
                                                <label>Fecha Creacion:</label>
                                                <input type="text" class="form-control" placeholder=""
                                                    name="fechacreacion" id="fechacreacion"
                                                    value="{{ $cotizaciones->fechacreacion }}" disabled />
                                            </div>

                                            @if ($cotizaciones->fechacreacion != null)
                                                <div class="col-lg-6">
                                                    <label>Fecha Modificacion:</label>
                                                    <input type="text" class="form-control" placeholder=""
                                                        name="fechamodificacion" autocomplete="off"
                                                        id="fechamodificacion"
                                                        value="{{ $cotizaciones->fechamodificacion }}" disabled />
                                                </div>
                                            @endif
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-lg-12 mt-5" style="width:100%">
                                                <table
                                                    class="table table-sm table-bordered table-head-custom table-hover text-center display responsive no-wrap"
                                                    id="kt_datatable" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th data-priority="1" width="50">Detalle</th>
                                                            <th width="12%">Cantidad</th>
                                                            <th width="24%">Descuento</th>
                                                            <th width="12%">Eliminar</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $decodificar = json_decode($cotizaciones->detalle_cotizacion);
                                                            
                                                        @endphp
                                                        @foreach ($decodificar as $dec)
                                                            <tr>
                                                                <td>
                                                                    <select class="form-control select2 valoresSelect"
                                                                        name="detallesid">
                                                                        <option value="">
                                                                            Escoja un detalle
                                                                        </option>
                                                                        @foreach ($detalle as $detalleL)
                                                                            <option value="{{ $detalleL->detallesid }}"
                                                                                style="font-size: 2px;"
                                                                                {{ $detalleL->detallesid == $dec->detalle ? 'selected' : '' }}>
                                                                                {{ $detalleL->detalle }}
                                                                            </option>
                                                                        @endforeach

                                                                    </select>
                                                                    <span class="text-danger d-none"
                                                                        name="mensajeDetalle">Escoja
                                                                        una Detalle</span>
                                                                </td>
                                                                <td>
                                                                    <input type="text"
                                                                        class="form-control input-sm cantidad cantidadF"
                                                                        onkeypress="return validarNumero(event)"
                                                                        value="{{ $dec->cantidad }}">
                                                                    <span class="text-danger d-none"
                                                                        name="mensajeCantidad">
                                                                        Ingrese cantidad</span>
                                                                </td>
                                                                <td>
                                                                    <input type="text"
                                                                        class="form-control descuento input-sm validarDigitos descuentoF"
                                                                        value="{{ $dec->descuento }}">
                                                                    <span class="text-danger d-none"
                                                                        name="mensajeDescuento">Ingrese descuento</span>
                                                                </td>
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-danger"
                                                                        name="botonEliminar"
                                                                        onclick="eliminarFila(this)">-</button>

                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <select style="visibility:hidden" id="arrayDetalles" name="arrayDetalles[]"
                                    class=" recuperarArray" multiple="multiple">
                                </select>
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
                $('.cantidadF').TouchSpin({
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

                $('.descuentoF').TouchSpin({
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

                handleBlurOnRucInput();
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

            function limpiarCampos() {
                $("#fecha").val("");
                $("#prospectosid").val("").trigger('change');
                $(".valoresSelect, .recuperarArray").val('').trigger('change');
                $("#forma_pagoid").val("").trigger('change');
                $("#tipo_plantilla").val("").trigger('change');
            }

            function agregarFila() {
                table.row.add({
                    detalle: ` <td><select class="form-control select2 valoresSelect" name="detallesid"><option value="">Escoja un detalle </option>
                    @foreach ($detalle as $detalleL)
                        <option value="{{ $detalleL->detallesid }}"
                            {{ collect(old('detallesid'))->contains($detalleL->detallesid) ? 'selected' : '' }}>
                            {{ $detalleL->detalle }}
                        </option>
                    @endforeach </select> <span class="text-danger d-none" name="mensajeDetalle">Escoja un detalle</span> </td> `,
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

            $("form").submit(function(e) {
                let inputFecha = document.getElementById("fecha").value;
                let inputPlanilla = document.getElementById("tipo_plantilla").value;
                let inputPago = document.getElementById("forma_pagoid").value;

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
                    var subtotal = 0;
                    var descuentototal = 0;
                    var totalneto = 0;
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
                                var idDetalle = valoresSelect.val();
                                $.post('{{ route('recuperarPrecio') }}', {
                                    _token: '{{ csrf_token() }}',
                                    idDetalle

                                }, function(resultado) {

                                    var precioFinal = resultado.precio * valoresCantidad;
                                    var descuentoFinal = (precioFinal * valoresDescuento) / 100;
                                    var valorneto = precioFinal - descuentoFinal;


                                    totalneto = round(valorneto) + totalneto;

                                    var iva = (totalneto * 12) / 100;
                                    var totaliva = totalneto + iva;
                                    $("#subtotal").val(round(totalneto));
                                    $("#iva").val(round(iva));
                                    $("#total").val(round(totaliva));
                                })


                            } else {
                                break;

                            }

                        } else {
                            e.preventDefault();

                        }

                    }



                    if ((inputValue.length == array.length)) {
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

            function eliminarFila(boton) {
                var table = document.getElementById("kt_datatable");
                var t = $("#kt_datatable").DataTable();

                var rowCount = table.rows.length;

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

            /* -------------------------------------------------------------------------- */
            /*                        funciones para obtener datos                        */
            /* -------------------------------------------------------------------------- */

            function handleBlurOnRucInput() {
                const rucInput = document.getElementById('inputRuc');
                const errorMessage = rucInput.nextElementSibling;

                rucInput.addEventListener('blur', async function() {
                    const enteredValue = this.value.trim();

                    if (!enteredValue) {
                        return;
                    }

                    if (isValidRUC(enteredValue) || isValidCedula(enteredValue)) {
                        errorMessage.classList.add('d-none');
                        const cliente = await fetchCompanyInformation(enteredValue);
                        $("#nombre_cliente").val(cliente.razon_social)
                    } else {
                        rucInput.value = '';
                        errorMessage.textContent = "La cédula o RUC ingresado no es válido";
                        errorMessage.classList.remove("d-none");
                    }
                });
            }

            function isValidRUC(text) {
                return text.length === 13 && text.substr(10, 3) === "001";
            }

            function isValidCedula(idNumber) {
                let totalSum = 0;
                const numLength = idNumber.length;
                const checkLength = numLength - 1;
                const digits = idNumber.split('').map(Number);
                const provinceCode = digits[0] * 10 + digits[1];

                if (idNumber && numLength === 10 && idNumber !== '2222222222' &&
                    (provinceCode >= 1 && (provinceCode <= 24 || provinceCode === 30))) {

                    for (let i = 0; i < checkLength; i++) {
                        const digit = (i % 2 === 0) ? idNumber.charAt(i) * 2 : parseInt(idNumber.charAt(i));
                        totalSum += (digit > 9) ? digit - 9 : digit;
                    }

                    const verificationDigit = totalSum % 10 ? 10 - totalSum % 10 : 0;
                    return idNumber.charAt(numLength - 1) == verificationDigit;
                }

                return false;
            }

            function fetchCompanyInformation(rucValue) {
                const loadingSpinner = document.getElementById('spinner');

                loadingSpinner.classList.add('spinner', 'spinner-success', 'spinner-right');

                // Retornamos una nueva Promesa
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: "{{ route('firma.index') }}",
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            identificacion: rucValue
                        },
                        success: function(data) {
                            loadingSpinner.classList.remove('spinner', 'spinner-success', 'spinner-right');
                            console.log(data);
                            // Si los datos tienen la propiedad "identification", resolvemos la promesa con esos datos.
                            if (data.identificacion && data.identificacion != '') {
                                resolve(data);
                            } else {
                                reject(new Error('No identification found'));
                            }
                        },
                        error: function(error) {
                            loadingSpinner.classList.remove('spinner', 'spinner-success', 'spinner-right');
                            reject(error);
                        }
                    });
                });
            }
        </script>
    @endsection
