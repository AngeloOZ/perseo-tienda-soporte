@extends('firma.layouts.app')
@php
    $provincias = App\Models\Provincias::get();
    $verificacion = isset($verificacion) ? $verificacion : '';
    $id_firma = isset($id_firma) ? $id_firma : '';
    
    $fechaactual = date('Y-m-d');
    $nuevafecha = strtotime('-18 year', strtotime($fechaactual)); //Se resta un año menos
    $nuevafecha = date('Y-m-d', $nuevafecha);
@endphp

@section('titulo', 'Firmas')
@section('descripcion', 'Datos para la Firma Electrónica')
@section('imagen', asset('assets/media/firmas.jpg'))

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid w-75 mx-auto" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="wizard wizard-4" id="kt_wizard" data-wizard-state="step-first" data-wizard-clickable="true">
                    <!--begin::Wizard Nav-->
                    <div class="wizard-nav">
                        <div class="wizard-steps">
                            <div class="wizard-step" data-wizard-type="step" data-wizard-state="current">
                                <div class="wizard-wrapper">
                                    <div class="wizard-number">1</div>
                                    <div class="wizard-label">
                                        <div class="wizard-title">Datos</div>
                                        <div class="wizard-desc">Ingrese sus datos</div>
                                    </div>
                                </div>
                            </div>
                            <div class="wizard-step" data-wizard-type="step">
                                <div class="wizard-wrapper">
                                    <div class="wizard-number">2</div>
                                    <div class="wizard-label">
                                        <div class="wizard-title">Imágenes</div>
                                        <div class="wizard-desc">Adjunte las imágenes</div>
                                    </div>
                                </div>
                            </div>
                            <div class="wizard-step" data-wizard-type="step">
                                <div class="wizard-wrapper">
                                    <div class="wizard-number">3</div>
                                    <div class="wizard-label">
                                        <div class="wizard-title">Documentos</div>
                                        <div class="wizard-desc">Adjunte Documentos</div>
                                    </div>
                                </div>
                            </div>
                            <div class="wizard-step" data-wizard-type="step">
                                <div class="wizard-wrapper">
                                    <div class="wizard-number">4</div>
                                    <div class="wizard-label">
                                        <div class="wizard-title">Finalización</div>
                                        <div class="wizard-desc">Estado del Proceso</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-custom card-shadowless rounded-top-0">
                        <div class="card-body p-0">
                            <div class="row justify-content-center py-8 px-8 py-lg-15 px-lg-10">
                                <div class="col-md-12 col-xxl-10">
                                    <form class="form" id="informacion"
                                        @if ($verificacion == '') action="{{ route('firma.guardar') }}" @endif
                                        method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row justify-content-center">


                                            <div class="col-xl-12">
                                                <!--begin::Wizard Step 1-->
                                                <div class="my-5 step" data-wizard-type="step-content"
                                                    data-wizard-state="current">

                                                    <div class="form-group row ">
                                                        @include('firma.datos')
                                                    </div>
                                                </div>

                                                <div class="my-5 step " data-wizard-type="step-content">

                                                    <div class="form-group row ">
                                                        @include('firma.imagenes')
                                                    </div>

                                                </div>

                                                <div class="my-5 step" data-wizard-type="step-content">

                                                    <div class="form-group row">
                                                        @include('firma.archivos')
                                                    </div>

                                                </div>
                                                <div class="my-5 step" data-wizard-type="step-content">

                                                    <div class="form-group row">
                                                        <div class="col-xl-12">
                                                            <div class="card  gutter-b" style="height: 240px;">
                                                                <!--begin::Body-->
                                                                <div class="card-body d-flex flex-column">
                                                                    <div
                                                                        class="d-flex align-items-center justify-content-between flex-grow-1">
                                                                        <div class=" mx-auto">

                                                                            @if ($verificacion == 1)
                                                                                <h5 class="font-weight-bolder correcto">La
                                                                                    información
                                                                                    se ha compleado satisfactoriamente</h5>
                                                                            @elseif ($verificacion == 2)
                                                                                <h5 class="font-weight-bolder incorrecto">La
                                                                                    información
                                                                                    no se ha compleado satisfactoriamente
                                                                                </h5>
                                                                                <small>Por favor vuelva a intentarlo</small>
                                                                            @endif
                                                                            <div class="text-muted font-size-lg mt-2">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class=" pt-0 pt-md-5">

                                                                        <div class="text-center ">
                                                                            @if ($verificacion == 1)
                                                                                <li
                                                                                    class="far fa-check-circle text-success icon-7x ">

                                                                                </li>
                                                                                <div class="container mt-8">
                                                                                    @if ($id_firma != '')
                                                                                        <a href="{{ route('firma.estadosolicitud', $id_firma) }}"
                                                                                            class="btn btn-primary">Ver
                                                                                            estado de la solicitud</a>
                                                                                    @endif
                                                                                </div>
                                                                            @elseif ($verificacion == 2)
                                                                                <li
                                                                                    class="far fa-times-circle text-danger icon-7x ">

                                                                                </li>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!--end::Body-->
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>


                                                <div class="d-flex justify-content-between border-top pt-10 mt-15">
                                                    <div class="mr-2" id="anterior">
                                                        <button type="button" id="prev-step"
                                                            class="btn btn-light-primary font-weight-bolder px-9 py-4"
                                                            data-wizard-type="action-prev">Anterior</button>
                                                    </div>
                                                    <div>

                                                        <button type="button" id="next-step"
                                                            class="btn btn-primary font-weight-bolder px-9 py-4"
                                                            data-wizard-type="action-next">Siguiente</button>
                                                    </div>
                                                </div>
                                                <!--end::Wizard Actions-->
                                            </div>

                                        </div>
                                    </form>
                                    <!--end::Wizard Form-->
                                </div>
                                <div class="container mt-5 font-size-h6-md text-justify pb-10 pb-md-0">
                                    <strong class="font-size-h6 font-size-h4-md font-weight-boldest">NOTA:</strong> PARA LA
                                    GENERACION DE LA CONTRASEÑA DE SU CERTIFICADO, USAR NUMEROS Y LETRAS EXCEPTO LA Ñ, SIN
                                    CARACTERES ESPECIALES. DE IGUAL MANERA LA FIRMA DEBE SER DESCARGADA UNICAMENTE DESDE
                                    COMPUTADORA. <strong>PERSEOSOFT NO SE RESPONSABILIZA</strong> SI TIENE PROBLEMAS AL
                                    AUTORIZAR DOCUMENTOS, POR TEMA DE CLAVES NO VALIDAS O MAL DESCARGADAS.
                                </div>
                            </div>
                        </div>
                        <!--end::Body-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modal')
    <div class="modal" id="carga">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size"
            role="document">
            <div class="modal-content position-relative">
                <div class="c-preloader text-center p-3">
                    <i class="las la-spinner la-spin la-3x"></i>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function validarExtensionArchivo(file, types) {
            const extensionesValidas = types;

            const extension = file.name.toLowerCase().split('.').pop();
            if (!extensionesValidas.includes(extension)) {
                Swal.fire({
                    title: "Tipo de archivo no válido",
                    html: `Solo se permite imagenes de tipo <strong>${extensionesValidas.join(', ')}</strong>`,
                    icon: "warning",
                    confirmButtonText: "OK",
                })
                return false;
            }
            return true;
        }

        function verificarPesoMenor2MB(inputFile, maxSize = 2097152, types = ['jpg', 'jpeg', 'png']) {
            const file = inputFile?.files[0];
            if (file) {
                if (file.size <= maxSize) {
                    return validarExtensionArchivo(file, types);
                }
                return false;
            }
        }

        function validarPesoArchivos() {
            const inputFoto = document.getElementById('foto');
            const inputCedulaFront = document.getElementById('cedula');
            const inputCedulaReverso = document.getElementById('reverso');
            const documento = document.getElementById('documento');
            const constitucion = document.getElementById('constitucion');
            const nombramiento = document.getElementById('nombramiento');
            const aceptacion = document.getElementById('aceptacion');


            inputFoto.addEventListener('change', e => {
                const result = verificarPesoMenor2MB(e.target);
                const mensaje = document.getElementById('mensajeFoto2');
                if (result) {
                    const mensaje1 = document.getElementById('mensajeFoto');
                    mensaje1.classList.add('d-none');
                    mensaje.classList.add('d-none');
                } else {
                    mensaje.classList.remove('d-none');
                    e.target.value = "";
                }
            });

            inputCedulaFront.addEventListener('change', e => {
                const result = verificarPesoMenor2MB(e.target);
                const mensaje = document.getElementById('mensajeAnverso2');
                if (result) {
                    const mensaje1 = document.getElementById('mensajeAnverso');
                    mensaje.classList.add('d-none');
                    mensaje1.classList.add('d-none');
                } else {
                    mensaje.classList.remove('d-none');
                    e.target.value = "";
                }
            });

            inputCedulaReverso.addEventListener('change', e => {
                const result = verificarPesoMenor2MB(e.target);
                const mensaje = document.getElementById('mensajeReverso2');
                if (result) {
                    const mensaje1 = document.getElementById('mensajeReverso');
                    mensaje.classList.add('d-none');
                    mensaje1.classList.add('d-none');
                } else {
                    mensaje.classList.remove('d-none');
                    e.target.value = "";
                }
            });

            documento.addEventListener('change', e => {
                const result = verificarPesoMenor2MB(e.target, 2097152, ['pdf']);
                const mensaje = document.getElementById('mensajeArchivoRuc2');
                if (result) {
                    const mensaje1 = document.getElementById('mensajeArchivoRuc');
                    mensaje.classList.add('d-none');
                    mensaje1.classList.add('d-none');
                } else if (result == false) {
                    mensaje.classList.remove('d-none');
                    e.target.value = "";
                }
            });

            constitucion.addEventListener('change', e => {
                const result = verificarPesoMenor2MB(e.target, 4194304, ['pdf']);
                const mensaje = document.getElementById('mensajeArchivoConstitucion2');
                if (result) {
                    const mensaje1 = document.getElementById('mensajeArchivoConstitucion');
                    mensaje.classList.add('d-none');
                    mensaje1.classList.add('d-none');
                } else {
                    mensaje.classList.remove('d-none');
                    e.target.value = "";
                }
            });

            nombramiento.addEventListener('change', e => {
                const result = verificarPesoMenor2MB(e.target, 2097152, ['pdf']);
                const mensaje = document.getElementById('mensajeArchivoNombramiento2');
                if (result) {
                    const mensaje1 = document.getElementById('mensajeArchivoNombramiento');
                    mensaje.classList.add('d-none');
                    mensaje1.classList.add('d-none');
                } else {
                    mensaje.classList.remove('d-none');
                    e.target.value = "";
                }
            });

            aceptacion.addEventListener('change', e => {
                const result = verificarPesoMenor2MB(e.target, 2097152, ['pdf']);
                const mensaje = document.getElementById('mensajeArchivoAceptacion');
                if (result) {
                    mensaje.classList.add('d-none');
                } else if (result == false) {
                    mensaje.classList.remove('d-none');
                    e.target.value = "";
                }
            });
        }

        function validarHuellaDactilar() {
            const inputHuella = document.getElementById('codigo_cedula');
            const helperTExt = document.getElementById('mensajeCodigo2');
            inputHuella.addEventListener('blur', (e) => {

                const expresion = /^[A-Za-z]\d{4}[A-Za-z]{2}\d{4}$/;
                if (expresion.test(inputHuella.value)) {
                    helperTExt.classList.add('d-none');
                } else {
                    inputHuella.value = "";
                    helperTExt.classList.remove('d-none');
                }
            })
        }

        function convertCedulaToRuc(cedula) {
            if (cedula.length == 13) {
                return cedula;
            } else if (cedula.length == 10) {
                return cedula + "001";
            } else {
                return "Identificación con longitud no válida";
            }
        }

        function validarSiTieneRuc() {
            const inputFileRuc = document.getElementById('documento');
            inputFileRuc.addEventListener('change', (e) => {
                var conRuc = document.querySelector('input[name="conruc"]:checked').value;
                if (conRuc == 1 && inputFileRuc.value != "") {
                    const ruc = convertCedulaToRuc($("#identificacion").val());
                    console.log(ruc);
                    $("#ruc_hidden").val(ruc);
                }
            })
        }

        $('.persona').hide();
        $('.legal').hide();
        $('#tipo_persona').val("");
        $('#tipo_persona').change();
        var foto = new KTImageInput('kt_image_1');
        var cedula1 = new KTImageInput('kt_image_2');
        var cedula2 = new KTImageInput('kt_image_3');


        $('#ruc1').change(function() {
            if ($(this).is(':checked')) {
                $('.verificarRuc').removeClass('invisible');
                $("#textDireccion").text("Dirección Domicilio");
            }
        });
        $('#ruc2').change(function() {
            if ($(this).is(':checked')) {
                $('.verificarRuc').addClass('invisible');
            }
        });


        $(document).ready(function() {
            validarSiTieneRuc();
            validarPesoArchivos();
            $('#tipo_persona').val("");
            $('#tipo_persona').change();
            $('.persona').hide();
            $('.legal').hide();
            $('.fecha').datepicker({
                language: "es",
                todayHighlight: true,
                orientation: "bottom left",
                templates: {
                    leftArrow: '<i class="la la-angle-left"></i>',
                    rightArrow: '<i class="la la-angle-right"></i>'
                },
            }).datepicker("setDate", new Date());

            kizzard.init();

            $('#tipo_persona').change(function() {
                if ($('#tipo_persona').val() == "1") {
                    $("#textDireccion").text("Dirección Domicilio");
                    $("#textDatosPer").text("Datos personales");
                    $('#vigenciaFirma').append('<option value="7">30 Días</option>');
                } else {
                    $("#textDireccion").html(
                        "Dirección de la Empresa <small>(especificada en el ruc)</small>");
                    $("#textDatosPer").text("Datos del representante legal");
                    $('#vigenciaFirma option[value="7"]').remove();
                }
            });




            $('#hombre').attr("checked", "checked");

            $('.hombre').css('background-color', '#babcc3');
            $('.mujer').css('background-color', '#f8f8ff');
            $('#hombre').change(function() {
                if ($(this).is(':checked')) {
                    $('.hombre').css('background-color', '#babcc3');
                    $('.mujer').css('background-color', '#f8f8ff');
                }
            });
            $('#mujer').change(function() {
                if ($(this).is(':checked')) {
                    $('.hombre').css('background-color', '#f8f8ff');
                    $('.mujer').css('background-color', '#babcc3');
                }
            });

        });

        function cambiarCiudad(id) {

            $.ajax({
                url: '{{ route('firma.recuperarciudades') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id.value
                },
                success: function(data) {
                    $('#ciudadesid').empty();


                    data.map(ciudades =>
                        $('#ciudadesid').append('<option value="' + ciudades.ciudadesid + '">' +
                            ciudades
                            .ciudad + '</option>')
                    );
                }
            })
        }

        function tipopersona(id) {

            if (id.value == 1) {
                $('.persona').show();
                $('.legal').hide();
                $('.natural').show();
            } else if (id.value == 2) {
                $('.persona').show();
                $('.legal').show();
                $('.natural').hide();
            } else {
                $('.persona').hide();
                $('.legal').hide();
            }
        }

        async function validarPantalla1() {
            var tipopersona = $("#tipo_persona").val();
            var cedula = $("#identificacion").val();
            var nombres = $("#nombres").val();
            var apellidoPaterno = $("#apellidoPaterno").val();
            var codigo_cedula = $("#codigo_cedula").val();
            var correo = $("#correo").val();
            var celular = $("#celular").val();
            var celular2 = $("#telefono_contacto").val();
            var fechanacimiento = $("#fechanacimiento").val();
            var provincias = $("#provincias").val();
            var ciudadesid = $("#ciudadesid").val();
            var direccion = $("#direccion").val();
            var ruc = $("#ruc").val();
            var cargo = $("#cargo").val();
            var razon = $("#razonsocial").val();
            var verificarcorreo = 0;
            var verificarcelular = 0;
            var formatofecha = fechanacimiento.split("-").reverse().join("-");
            var obteneranio = new Date(formatofecha).getFullYear();
            var anioActual = new Date().getFullYear();

            var vigenciaFirma = document.getElementById('vigenciaFirma').value;

            const datasend = {
                _token: '{{ csrf_token() }}',
                correo: correo,
                celular: celular,
                celular2: celular2
            };

            const rest = await fetch("{{ route('admin.verificaremailcelular') }}", {
                method: 'POST',
                body: JSON.stringify(datasend),
                headers: {
                    'Content-Type': 'application/json'
                }
            })

            const [valor1, valor2, valor3] = await rest.json();

            if (vigenciaFirma.trim().length < 1) {
                $('#vigenciaFirma').focus();
                $('#vigenciaFirmaText').removeClass("d-none");

            } else {
                $('#vigenciaFirmaText').addClass("d-none");
            }

            if (cedula.trim().length < 1) {
                $('#mensajeCedula').removeClass("d-none");
                $('#identificacion').focus();

            } else {
                $('#mensajeCedula').addClass("d-none");
            }

            if (nombres.trim().length < 1) {
                $('#nombres').focus();
                $('#mensajeNombres').removeClass("d-none");

            } else {
                $('#mensajeNombres').addClass("d-none");
            }

            if (apellidoPaterno.trim().length < 1) {
                $('#nombres').focus();
                $('#mensajeApellidoPaterno').removeClass("d-none");

            } else {
                $('#mensajeApellidoPaterno').addClass("d-none");
            }

            if (codigo_cedula.trim().length < 1) {
                $('#codigo_cedula').focus();
                $('#mensajeCodigo').removeClass("d-none");

            } else {
                $('#mensajeCodigo').addClass("d-none");
            }

            if (correo.trim().length < 1 || valor1 == 0) {
                $('#mensajeCorreo').removeClass("d-none");
                $('#correo').focus();

            } else {
                $('#mensajeCorreo').addClass("d-none");

            }

            if (celular.trim().length < 1 || valor2 == 0) {
                $('#mensajeCelular').removeClass("d-none");
                $('#celular').focus();

            } else {
                $('#mensajeCelular').addClass("d-none");
            }

            if (celular2.trim().length < 1 || valor3 == 0) {
                $('#mensajeTelefonoContacto').removeClass("d-none");
                $('#celular2').focus();

            } else {
                $('#mensajeTelefonoContacto').addClass("d-none");
            }

            if (fechanacimiento.trim().length < 1 || ((anioActual - obteneranio) < 18)) {
                $('#mensajeFecha').removeClass("d-none");
                $('#fechanacimiento').focus();

            } else {
                $('#mensajeFecha').addClass("d-none");
            }

            if (tipopersona == 2) {
                if (cargo.trim().length < 1) {
                    $('#mensajeCargo').removeClass("d-none");
                    $('#cargo').focus();
                } else {
                    $('#mensajeCargo').addClass("d-none");
                }

                if (ruc.trim().length < 1 || ruc.trim().length < 13) {
                    $('#mensajeRuc').removeClass("d-none");
                    $('#ruc').focus();

                } else {
                    $('#mensajeRuc').addClass("d-none");
                }


                if (razon.trim().length < 1) {
                    $('#mensajeRazonSocial').removeClass("d-none");
                    $('#razonsocial').focus();


                } else {
                    $('#mensajeRazonSocial').addClass("d-none");
                }
            }


            if (provincias.trim().length < 1) {
                $('#mensajeProvincias').removeClass("d-none");

            } else {
                $('#mensajeProvincias').addClass("d-none");
            }

            if (ciudadesid.trim().length < 1) {
                $('#mensajeCiudades').removeClass("d-none");

            } else {
                $('#mensajeCiudades').addClass("d-none");
            }

            if (direccion.trim().length < 1) {
                $('#mensajeDireccion').removeClass("d-none");
                $('#direccion').focus();

            } else {
                $('#mensajeDireccion').addClass("d-none");
            }




            if (vigenciaFirma.trim().length < 1 || cedula.trim().length < 1 || nombres.trim().length < 1 ||
                apellidoPaterno.trim().length < 1 ||
                codigo_cedula.trim().length < 1 || correo.trim().length < 1 || celular.trim().length < 1 || celular2
                .trim().length < 1 || fechanacimiento.trim().length < 1 || provincias.trim().length < 1 || ciudadesid
                .trim().length < 1 || direccion.trim().length < 1 || valor1 == 0 ||
                valor2 == 0 || valor3 == 0 || (anioActual - obteneranio) < 19) {

                return 0;
            } else {

                if (tipopersona == 2) {
                    if (cargo.trim().length < 1 || ruc.trim().length < 1 || razon.trim().length < 1) {

                        return 0;
                    }
                }
                return 1;
            }
        }

        function validarPantalla2() {
            var cedula = $("#foto").val();
            var cedula_anverso = $("#cedula").val();
            var cedula_reverso = $("#reverso").val();

            if (cedula == "") {
                $('#mensajeFoto').removeClass("d-none");
            } else {
                $('#mensajeFoto').addClass("d-none");
            }

            if (cedula_anverso == "") {
                $('#mensajeAnverso').removeClass("d-none");
            } else {
                $('#mensajeAnverso').addClass("d-none");
            }


            if (cedula_reverso == "") {
                $('#mensajeReverso').removeClass("d-none");
            } else {
                $('#mensajeReverso').addClass("d-none");
            }

            if (cedula == "" || cedula_anverso == "" || cedula_reverso == "") {
                return 0;
            } else {
                return 1;
            }
        }

        function validarPantalla3() {
            var tipopersona = $("#tipo_persona").val();
            var conRuc = document.querySelector('input[name="conruc"]:checked').value;
            var documento = $("#documento").val();

            if (tipopersona == 1) {
                if (documento == "" && conRuc == 1) {
                    $('#mensajeArchivoRuc').removeClass("d-none");
                    return 0;
                } else {
                    $('#mensajeArchivoRuc').addClass("d-none");
                    return 1;
                }
            }


            if (tipopersona == 2) {
                var documento = $("#constitucion").val();
                var constitucion = $("#constitucion").val();
                var nombramiento = $("#nombramiento").val();



                if (documento == "") {
                    $('#mensajeArchivoRuc').removeClass("d-none");
                } else {
                    $('#mensajeArchivoRuc').addClass("d-none");
                }

                if (constitucion == "") {
                    $('#mensajeArchivoConstitucion').removeClass("d-none");
                } else {
                    $('#mensajeArchivoConstitucion').addClass("d-none");
                }

                if (nombramiento == "") {
                    $('#mensajeArchivoNombramiento').removeClass("d-none");
                } else {
                    $('#mensajeArchivoNombramiento').addClass("d-none");
                }


            }


            if (documento == "" || constitucion == "" || nombramiento == "") {
                return 0;
            } else {
                return 1;
            }
        }

        var kizzard = function() {

            var _wizardEl;
            var _wizardObj;
            var paso;
            var _initWizard = function() {
                var recuperarVerificacion = '{{ $verificacion }}';

                if (recuperarVerificacion != "") {
                    paso = 4;
                    $("#anterior").hide();
                    if (window.history.replaceState) {
                        window.history.replaceState(null, null, window.location.href);
                    }

                } else {
                    paso = 1;
                }
                _wizardObj = new KTWizard(_wizardEl, {
                    startStep: paso,
                    clickableSteps: false,
                    navigation: false
                });;
                $("#prev-step").click(function() {
                    _wizardObj.goPrev();
                    document.getElementById("next-step").innerHTML = "Siguiente"

                });


                $("#next-step").click(async function() {
                    var actual = _wizardObj.getStep();
                    document.getElementById("next-step").setAttribute("disabled", 'true');
                    if (actual == 1) {

                        var validacion1 = await validarPantalla1();

                        if (validacion1 != 0) {
                            _wizardObj.goNext();
                        }
                        document.getElementById("next-step").removeAttribute("disabled");
                        return
                    }
                    if (actual == 2) {
                        var validacion2 = await validarPantalla2();
                        if (validacion2 != 0) {
                            _wizardObj.goNext();
                            document.getElementById("next-step").innerHTML = "Finalizar";
                        }
                        document.getElementById("next-step").removeAttribute("disabled");
                        return
                    }

                    if (actual == 3) {
                        console.log("Entree...");
                        var validacion3 = await validarPantalla3();
                        if (validacion3 != 0) {
                            _wizardObj.goNext();
                            $("#anterior").addClass('invisible');
                            $('#carga').modal({
                                backdrop: 'static',
                                keyboard: false
                            });
                            $("#informacion").submit();
                        }
                        document.getElementById("next-step").removeAttribute("disabled");
                        return
                    }


                });



            }
            return {
                init: function() {
                    _wizardEl = KTUtil.getById('kt_wizard');
                    _initWizard();
                }
            };
        }();

        async function recuperarInformacion() {
            var cad = document.getElementById('identificacion').value;

            let url = '{{ route('validarestado', 'cad') }}';
            url = url.replace('cad', cad);

            const response = await fetch(url)
            const json = await response.json();

            if (json.status == 400) {
                document.getElementById('mensajeCedulaDigitos').textContent = json.message;
                $('#mensajeCedulaDigitos').removeClass("d-none");
                const input = document.getElementById('identificacion');
                input.value = "";
                return;
            } else {
                $('#mensajeCedulaDigitos').addClass("d-none");
            }

            return
            // $("#spinner").addClass("spinner spinner-success spinner-right");
            // $.ajax({
            //     url: "{{ route('firma.index') }}",
            //     headers: {
            //         'usuario': 'perseo',
            //         'clave': 'Perseo1232*'
            //     },
            //     method: 'POST',
            //     data: {
            //         _token: '{{ csrf_token() }}',
            //         identificacion: cad
            //     },
            //     success: function(data) {
            //         $("#spinner").removeClass("spinner spinner-success spinner-right");
            //         if (data.identificacion) {

            //             $("#nombres").val(data.razon_social);
            //             $("#direccion").val(data.direccion);
            //             $("#correo").val(data.correo);
            //             $("#convencional").val(data.telefono1);

            //             $("#celular").val(data.telefono2);
            //         }
            //     }
            // });
        }
    </script>
@endsection
