<h2 class="font-size-h3 font-weight-bold mb-4">Datos del vendedor</h2>
<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Ingresado por:</label>
        <input type="text" class="form-control" disabled autocomplete="off" value="{{ $vendedor->nombres }}"/>
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Télefono</label>
        <input type="text" class="form-control" disabled autocomplete="off" value="{{ $vendedor->telefono }}"/>
    </div>
</div>
<h2 class="font-size-h3 font-weight-bold mb-4">Datos de la firma</h2>
<div class="form-group row">
    <div class="col-lg-4 d-none">
        <label>Tipo de Persona:</label>
        <select class="form-control" name="tipo_persona" id="tipo_persona" onchange="tipopersona(this);">
            <option value="1" {{ $firma->tipo_persona == '1' ? 'Selected' : '' }}>
                Persona Natural</option>
            <option value="2" {{ $firma->tipo_persona == '2' ? 'Selected' : '' }}>
                Persona Jurídica</option>
        </select>

    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Identificación:</label>
        <div id="spinner">
            <input type="text" class="form-control {{ $errors->has('identificacion') ? 'is-invalid' : '' }}"
                placeholder="Ingrese su identificacion" name="identificacion" id="identificacion" autocomplete="off"
                value="{{ $firma->identificacion }}" onkeypress="return validarNumero(event)"
                onblur="validarIdentificacion()" />
        </div>
        <span class="text-danger d-none" id="mensajeBandera">La cédula o Ruc no es
            válido</span>
        @if ($errors->has('identificacion'))
            <span class="text-danger">{{ $errors->first('identificacion') }}</span>
        @endif
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Código Huella Dactilar:</label>
        <input type="text" class="form-control {{ $errors->has('codigo_cedula') ? 'is-invalid' : '' }}"
            placeholder="Código de la Huella Dactilar" name="codigo_cedula" id="codigo_cedula" autocomplete="off"
            value="{{ $firma->codigo_cedula }}" />
        @if ($errors->has('codigo_cedula'))
            <span class="text-danger">{{ $errors->first('codigo_cedula') }}</span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Nombres:</label>
        <input type="text" class="form-control {{ $errors->has('nombres') ? 'is-invalid' : '' }}"
            placeholder="Ingrese los nombres" name="nombres" id="nombres" autocomplete="off"
            value="{{ $firma->nombres }}" />
        @if ($errors->has('nombres'))
            <span class="text-danger">{{ $errors->first('nombres') }}</span>
        @endif
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Apellido paterno</label>
        <input type="text" class="form-control {{ $errors->has('apellido_paterno') ? 'is-invalid' : '' }}"
            placeholder="Ingrese un apellido paterno" name="apellido_paterno" id="apellidoPaterno" autocomplete="off"
            value="{{ $firma->apellido_paterno }}" />
        @if ($errors->has('apellido_paterno'))
            <span class="text-danger">{{ $errors->first('apellido_paterno') }}</span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Apellido materno</label>
        <input type="text" class="form-control {{ $errors->has('apellido_materno') ? 'is-invalid' : '' }}"
            placeholder="Ingrese un apellido materno" name="apellido_materno" id="apellidoMaterno" autocomplete="off"
            value="{{ $firma->apellido_materno }}" />
        @if ($errors->has('apellido_materno'))
            <span class="text-danger">{{ $errors->first('apellido_materno') }}</span>
        @endif
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Correo</label>
        <input type="email" class="form-control {{ $errors->has('correo') ? 'is-invalid' : '' }}"
            placeholder="Ingrese un correo" name="correo" id="correo" autocomplete="off"
            value="{{ $firma->correo }}" />
        @if ($errors->has('correo'))
            <span class="text-danger">{{ $errors->first('correo') }}</span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Celular:</label>
        <input type="text" class="form-control {{ $errors->has('celular') ? 'is-invalid' : '' }}"
            placeholder="Ingrese un celular" name="celular" autocomplete="off" id="celular"
            value="{{ $firma->celular }}" onkeypress="return validarNumero(event)" />

        @if ($errors->has('celular'))
            <span class="text-danger">{{ $errors->first('celular') }}</span>
        @endif
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label for="">Convencional</label>
        <input type="text" class="form-control" name="convencional" id="convencional"
            value=" {{ $firma->convencional }}" autocomplete="off"
            oninput="if(this.value.length > 9) this.value = this.value.slice(0, 9);"
            onkeypress="return validarNumero(event)">
        <span class="text-danger d-none" id="mensajeConvencional"> Ingrese un
            Número
            Convencional</span>
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Provincia:</label>
        <select class="form-control select2" name="provinciasid" id="provincias" onchange="cambiarCiudad(this);">

            @foreach ($provincias as $provincia)
                <option value="{{ $provincia->provinciasid }}"
                    {{ $firma->provinciasid == $provincia->provinciasid ? 'Selected' : '' }}>
                    {{ $provincia->provincia }}</option>
            @endforeach


        </select>
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label for="">Seleccione una ciudad</label>
        <select class="form-control select2" name="ciudadesid" id="ciudadesid">
            <option value="">Seleccione una Ciudad</option>
            @foreach ($ciudades as $ciudad)
                <option value="{{ $ciudad->ciudadesid }}"
                    {{ $firma->ciudadesid == $ciudad->ciudadesid ? 'Selected' : '' }}>
                    {{ $ciudad->ciudad }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label for="">Calle principal, secundaria. N.
            casa/departamento</label>
        <input type="text" class="form-control" name="direccion" id="direccion" value="{{ $firma->direccion }}"
            autocomplete="off">
        <span class="text-danger d-none" id="mensajeDireccion"> Ingrese una
            Dirección</span>
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label for="">Fecha de Nacimiento</label>
        <input type="text" class="form-control fecha" value="{{ $firma->fechanacimiento }}"
            name="fechanacimiento" id="fechanacimiento" autocomplete="off">
        @if ($errors->has('fechanacimiento'))
            <span class="text-danger">{{ $errors->first('fechanacimiento') }}</span>
        @endif

    </div>
</div>

<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Teléfono de contacto:</label>
        <input type="text" class="form-control {{ $errors->has('telefono_contacto') ? 'is-invalid' : '' }}"
            placeholder="Ingrese un celular" name="telefono_contacto" autocomplete="off" id="telefono_contacto"
            value="{{ $firma->telefono_contacto }}" onkeypress="return validarNumero(event)" />

        @if ($errors->has('telefono_contacto'))
            <span class="text-danger">{{ $errors->first('telefono_contacto') }}</span>
        @endif
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Secuencia de factura</label>
        <input type="text" class="form-control {{ $errors->has('numero_secuencia') ? 'is-invalid' : '' }}"
            placeholder="0000XXXXX" name="numero_secuencia" autocomplete="off" id="numero_secuencia"
            value="{{ $firma->numero_secuencia }}" />

        @if ($errors->has('numero_secuencia'))
            <span class="text-danger">{{ $errors->first('numero_secuencia') }}</span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Vigencia:</label>
        <select class="form-control select2" id="vigencia" name="vigencia">
            <option value="6" {{ $firma->vigencia == '6' ? 'Selected' : '' }}>7 Días</option>
            <option value="1" {{ $firma->vigencia == '1' ? 'Selected' : '' }}>
                1 Año</option>
            <option value="2" {{ $firma->vigencia == '2' ? 'Selected' : '' }}>
                2 Años</option>
            <option value="3" {{ $firma->vigencia == '3' ? 'Selected' : '' }}>
                3 Años</option>
            <option value="4" {{ $firma->vigencia == '4' ? 'Selected' : '' }}>
                4 Años</option>
            <option value="5" {{ $firma->vigencia == '5' ? 'Selected' : '' }}>
                5 Años</option>
        </select>
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label for="">Formato</label>
        <select class="form-control" name="formato">
            <option value="1" {{ $firma->formato == '1' ? 'Selected' : '' }}>
                Archivo .P12</option>
        </select>

    </div>
</div>

<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Estado:</label>
        <select class="form-control select2" id="estado" name="estado">
            <option value="1" {{ $firma->estado == '1' ? 'Selected' : '' }}>
                Recibido</option>
            <option value="2" {{ $firma->estado == '2' ? 'Selected' : '' }}>
                Revisado</option>
            <option value="3" {{ $firma->estado == '3' ? 'Selected' : '' }}>
                En proceso</option>
            <option value="4" {{ $firma->estado == '4' ? 'Selected' : '' }}>
                Finalizado</option>
            <option value="5" {{ $firma->estado == '5' ? 'Selected' : '' }}>
                Entregado al correo</option>
            <option value="6" {{ $firma->estado == '6' ? 'Selected' : '' }}>
                Anulado</option>
        </select>
    </div>
    <div class="col-12 mt-2 col-lg-3 mt-lg-0">
        <label for="">Sexo</label>
        <div>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn hombre"
                    @if ($firma->sexo == 'h') style="border: 1px solid #f8f8ff; background-color: #babcc3" @else style="border: 1px solid #c8c8c8; background-color: #f8f8ff" @endif>
                    <input type="radio" class="form-control" name="sexo" id="hombre" value="h"
                        autocomplete="off">
                    Hombre
                </label>
                <label class="btn  mujer"
                    @if ($firma->sexo == 'm') style="border: 1px solid #f8f8ff; ; background-color:#babcc3" @else style="border: 1px solid #c8c8c8; background-color: #f8f8ff" @endif>
                    <input type="radio" class="form-control" name="sexo" id="mujer" value="m"
                        autocomplete="off">
                    Mujer
                </label>
            </div>
        </div>
    </div>
    {{-- <div class="col-12 mt-2 col-lg-3 mt-lg-0">
        <label for="">Estado del pago</label>
        <div>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn por_pagar"
                    @if ($firma->estado_pago == 0) style="border: 1px solid #f8f8ff; background-color: #babcc3" @else style="border: 1px solid #c8c8c8; background-color: #f8f8ff" @endif>
                    <input type="radio" class="form-control" name="estado_pago" id="por_pagar" value="0"
                        autocomplete="off">
                    Por pagar
                </label>
                <label class="btn pagado"
                    @if ($firma->estado_pago == 1) style="border: 1px solid #f8f8ff; ; background-color:#babcc3" @else style="border: 1px solid #c8c8c8; background-color: #f8f8ff" @endif>
                    <input type="radio" class="form-control" name="estado_pago" id="pagado" value="1"
                        autocomplete="off">
                    Pagado
                </label>
            </div>
        </div>
    </div> --}}
</div>

<div class="form-group row">
    @if ($firma->cargo != '')
        <div class="col-12 mt-2 col-lg-6 mt-lg-0">
            <label for="">Cargo en la Empresa</label>
            <input type="text" class="form-control" name="cargo" id="cargo" value="{{ $firma->cargo }}"
                autocomplete="off">
            <span class="text-danger d-none" id="mensajeCargo"> Ingrese un
                cargo</span>

        </div>
    @endif
    @if ($firma->ruc != '')
        <div class="col-12 mt-2 col-lg-6 mt-lg-0">
            <label>Ruc:</label>
            <input type="text" class="form-control" name="ruc" readonly id="ruc_hidden" autocomplete="off"
                value="{{ $firma->ruc }}" />
        </div>
    @endif
</div>
@if ($firma->cargo != '')
    <div class="form-group row">

        <div class="col-12 mt-2 col-lg-6 mt-lg-0">
            <label for="">RUC de la Empresa</label>
            <input type="text" class="form-control" name="ruc_empresa" id="ruc_empresa"
                value="{{ $firma->ruc_empresa }}" autocomplete="off">
            <span class="text-danger d-none" id="mensajeRuc"> Ingrese un
                RUC</span>

        </div>
        <div class="col-12 mt-2 col-lg-6 mt-lg-0">
            <label for="">Razón Social</label>
            <input type="text" class="form-control" name="razonsocial" id="razonsocial"
                value="{{ $firma->razonsocial }}" autocomplete="off">
            <span class="text-danger d-none" id="mensajeRazon"> Ingrese un
                RUC</span>

        </div>
    </div>
@endif

<div class="form-group row">
    @if ($firma->fecha_creacion != null)
        <div class="col-12 mt-2 col-lg-6 mt-lg-0">
            <label>Fecha Creacion:</label>
            <input type="text" class="form-control" placeholder="" name="fecha_creacion" id="fecha_creacion"
                value="{{ $firma->fecha_creacion }}" disabled />
        </div>
        <div class="col-12 mt-2 col-lg-6 mt-lg-0">
            <label>Fecha Modificacion:</label>
            <input type="text" class="form-control" placeholder="" name="fecha_modificacion" autocomplete="off"
                id="fecha_modificacion" value="{{ $firma->fecha_modificacion }}" disabled />
        </div>
    @endif
</div>

