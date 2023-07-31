<div class="w-100">
    <div class="col-md-12 mx-auto ">
        <div class="form-group row ">
            <div class="col-lg-6 m-0">
                <label>Tipo de Persona:</label>
                <select class="form-control" name="tipo_persona" id="tipo_persona" onchange="tipopersona(this);">
                    <option value=""> Seleccione tipo de persona</option>
                    <option value="1"> Persona Natural</option>
                    <option value="2"> Persona Jurídica</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mx-auto">
                <label for="" class="mx-auto">
                    <input type="hidden" name="usuarioid" id="usuarioid" value="{{ $base = Request::segment(2) }}"
                        autocomplete="off">
                </label>
            </div>

        </div>
        <div class="row persona">
            <div class="col-12 p-0">
                <p class="text-dark mb-3 font-size-h4 font-weight-bold" id="textDatosPer"></p>
            </div>
            <div class="col-lg-6 mt-0 mt-md-0 d-none">
                <label for="">Tipo de Identificación</label>
                <select class="form-control" name="tipo_identificacion" id="tipo_identificacion">
                    <option value="C">Cédula</option>
                    <option value="R">RUC</option>
                </select>
            </div>


            <div class="col-lg-6 mt-0 mt-md-0 persona ">
                <label for="">Identificación</label>
                <div id="spinner">
                    <input type="text" class="form-control" name="identificacion" id="identificacion"
                        autocomplete="off" onkeypress="return validarNumero(event)" onblur="validarIdentificacion('')"
                        placeholder="17XXXXXXX8">
                    <input type="hidden" name="ruc_hidden" id="ruc_hidden" value="">
                    <span class="text-danger d-none" id="mensajeCedula"> Ingrese una identificación válida</span>
                    <span class="text-danger d-none" id="mensajeCedulaDigitos"></span>

                </div>
            </div>

            <div class="col-lg-6 mt-0 mt-md-0 ">
                <label for="">Nombres</label>
                <input type="text" class="form-control" name="nombres" id="nombres" autocomplete="off"
                    placeholder="Angello Rafael">
                <span class="text-danger d-none" id="mensajeNombres"> Ingrese los Nombres</span>
            </div>

        </div>

        <div class="row mt-5 persona">
            <div class="col-12 col-lg-6 mt-0 mt-md-0 ">
                <label for="">Apellido paterno</label>
                <input type="text" class="form-control" name="apellido_paterno" id="apellidoPaterno"
                    autocomplete="off" placeholder="Ordonez">
                <span class="text-danger d-none" id="mensajeApellidoPaterno">Primer Apellido</span>
            </div>
            <div class="col-12 col-lg-6 mt-0 mt-md-0 ">
                <label for="">Apellido materno</label>
                <input type="text" class="form-control" name="apellido_materno" id="apellidoMaterno"
                    autocomplete="off" placeholder="Zapata">
                <span class="text-danger d-none" id="mensajeApellidoMaterno">Segundo Apellido</span>
            </div>
        </div>

        <div class="row mt-5 persona">

            <div class="col-lg-6 mt-0 mt-md-0 ">
                <label for="">Código de huella dactilar de la cédula
                    <i class="far fa-question-circle" data-toggle="popover"
                        title="Puedes encontrar el código dactilar al reverso de tu cédula" data-html="true"
                        data-content="<div style='width:100px'><img src='{{ asset('assets/media/huella-dactilar.jpg') }}' class='img-thumbnail' alt='Ejemplo de codigo dactilar' style='width: 100%'></div>"></i>
                </label>
                <input type="text" class="form-control" id="codigo_cedula" name="codigo_cedula" autocomplete="off"
                    oninput="if(this.value.length > 10) this.value = this.value.slice(0, 10);" placeholder="EXXXXIXXXX">
                <span class="text-danger d-none" id="mensajeCodigo"> Ingrese el Código de Huella Dactilar</span>
                <span class="text-danger d-none" id="mensajeCodigo2"> La huella ingresada no cumple con el formato
                    EXXXXIXXXX</span>
            </div>
            <div class="col-lg-6 mt-0 mt-md-0 ">
                <label for="">Email</label>
                <input type="email" class="form-control" name="correo" id="correo" autocomplete="off"
                    placeholder="correo@dominio.com">
                <span class="text-danger d-none" id="mensajeCorreo"> Ingrese un Correo Electrónico válido</span>

            </div>
        </div>

        <div class="row mt-5 persona">

            <div class="col-lg-6 mt-0 mt-md-0 ">
                <label for="">Convencional</label>
                <input type="text" class="form-control" name="convencional" id="convencional" autocomplete="off"
                    oninput="if(this.value.length > 7) this.value = this.value.slice(0, 7);"
                    onkeypress="return validarNumero(event)" placeholder="3770549">
                <span class="text-danger d-none" id="mensajeConvencional"> Ingrese un Número Convencional</span>

            </div>
            <div class="col-lg-6 mt-0 mt-md-0 ">
                <label for="">Celular</label>
                <input type="text" class="form-control" name="celular" id="celular" autocomplete="off"
                    oninput="if(this.value.length > 10) this.value = this.value.slice(0, 10);"
                    onkeypress="return validarNumero(event)" placeholder="0987654321">
                <span class="text-danger d-none" id="mensajeCelular"> Ingrese un Número de Celular Válido</span>
            </div>
        </div>

        <div class="row mt-5 ">
            <div class="col-lg-6 mt-0 mt-md-0 persona">
                <label for="">Fecha de Nacimiento</label>
                <input type="text" class="form-control fecha" name="fechanacimiento" id="fechanacimiento"
                    autocomplete="off" placeholder="dd/mm/aaaa">
                <span class="text-danger d-none" id="mensajeFecha"> Ingrese una Fecha de Nacimiento Válida </span>

            </div>

            <div class="col-lg-6 mt-2 mt-md-0 persona">
                <label for="">Sexo</label>
                <div>
                    <div class="btn-group btn-group-toggle" style="z-index: 0;" data-toggle="buttons">
                        <label class="btn hombre" style="border: 1px solid #c8c8c8">
                            <input type="radio" class="form-control" name="sexo" id="hombre" value="h"
                                autocomplete="off">
                            Hombre
                        </label>
                        <label class="btn  mujer" style="border: 1px solid #c8c8c8">
                            <input type="radio" class="form-control" name="sexo" id="mujer" value="m"
                                autocomplete="off">
                            Mujer
                        </label>
                    </div>
                </div>

            </div>
        </div>

        {{-- <hr class="persona"> --}}
        <div class="row mt-5 legal">
            <div class="col-12 p-0">
                <p class="text-dark mb-3 font-size-h4 font-weight-bold">
                    Datos de la Empresa
                </p>
            </div>
            <div class="col-lg-6 mt-0 mt-md-0 ">
                <label for="">Ruc de la empresa</label>
                <input class="form-control" name="ruc_empresa" id="ruc" type="text" autocomplete="off"
                    oninput="if(this.value.length > 13) this.value = this.value.slice(0, 13);"
                    onkeypress="return validarNumero(event)" onblur="validarRuc()" placeholder="1711024488001">
                <span class="text-danger d-none" id="mensajeRuc"> Ingrese un RUC válido de la empresa</span>
            </div>
            <div class="col-lg-6 mt-0 mt-md-0 ">
                <label for="">Razón Social</label>
                <input type="text" class="form-control" name="razonsocial" id="razonsocial" autocomplete="off"
                    placeholder="EL MEJOR SISTEMA CONTABLE">
                <span class="text-danger d-none" id="mensajeRazonSocial"> Ingrese la Razón Social</span>
            </div>
            <div class="col-lg-6 mt-0 mt-md-3 legal">
                <label for="">Cargo en la empresa</label>
                <input type="text" class="form-control" name="cargo" id="cargo" autocomplete="off"
                    placeholder="Director ejecutivo">
                <span class="text-danger d-none" id="mensajeCargo"> Ingrese el Cargo</span>
            </div>
        </div>

        <div class="row mt-5 persona">
            <div class="col-12 p-0">
                <p class="text-dark mb-3 font-size-h4 font-weight-bold" id="textDireccion"></p>
            </div>
            <div class="col-lg-6 mt-0 mt-md-0">
                <label for="">Seleccione una provincia</label>
                <select class="form-control select2" name="provinciasid" id="provincias"
                    onchange="cambiarCiudad(this);">
                    <option value="">Seleccione una Provincia</option>
                    @foreach ($provincias as $provincia)
                        <option value="{{ $provincia->provinciasid }}">
                            {{ $provincia->provincia }}</option>
                    @endforeach
                </select>
                <span class="text-danger d-none" id="mensajeProvincias"> Escoja una Provincia</span>

            </div>
            <div class="col-lg-6 mt-0 mt-md-0 ">
                <label for="">Seleccione una ciudad</label>
                <select class="form-control select2" name="ciudadesid" id="ciudadesid">
                    <option value="">Seleccione una Ciudad</option>

                </select>
                <span class="text-danger d-none" id="mensajeCiudades"> Escoja una Ciudad</span>
            </div>
        </div>
        <div class="row mt-5 persona">
            <div class="col-lg-12 mt-2">
                <label for="">Calle principal, secundaria. N. casa/departamento</label>
                <input type="text" class="form-control" name="direccion" id="direccion" autocomplete="off"
                    placeholder="Av Tsafiqui y Tulcán">
                <span class="text-danger d-none" id="mensajeDireccion"> Ingrese una Dirección</span>
            </div>
        </div>

        <div class="row mt-5 persona">
            <div class="col-12 p-0">
                <p class="text-dark mb-3 font-size-h4 font-weight-bold">
                    Formato y tiempo de vigencia
                </p>
            </div>
            <div class="col-lg-6 mt-0 mt-md-0 ">
                <label for="">Formato</label>
                <select class="form-control" name="formato">
                    <option value="1"> Archivo .P12</option>

                </select>

            </div>
            <div class="col-lg-6 mt-0 mt-md-0 ">
                <label for="">Tiempo de Vigencia</label>
                <select class="form-control" name="vigencia" id="vigenciaFirma">
                    <option value="" selected disabled>Seleccione la duración de la firma</option>
                    <option value="1"> 1 Año</option>
                    <option value="2"> 2 Años</option>
                    <option value="3"> 3 Años</option>
                    <option value="4"> 4 Años</option>
                    <option value="5"> 5 Años</option>
                    <option value="6"> 7 días</option>
                </select>
                <span class="text-danger d-none" id="vigenciaFirmaText">Seleccione la vigencia de la firma</span>
            </div>
        </div>
        <div class="row persona ">
            <div class="col-lg-6 mt-4 mt-md-3 m-0">
                <label for="" class="font-size-h4 font-weight-bold">Télefono de contacto</label>
                <input type="text" class="form-control" name="telefono_contacto" id="telefono_contacto" autocomplete="off"
                    oninput="if(this.value.length > 10) this.value = this.value.slice(0, 10);"
                    onkeypress="return validarNumero(event)" placeholder="0987654321">
                <span class="">A este número te contará un asesor de manera inmediata</span>
                <span class="text-danger d-none" id="mensajeTelefonoContacto"> Ingrese un número de teléfono válido</span>
            </div>
        </div>
    </div>
</div>
