<h2 class="font-size-h3 font-weight-bold mb-4">Datos del vendedor</h2>
<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Ingresado por:</label>
        <input type="text" class="form-control" disabled autocomplete="off" value="{{ $vendedor->nombres }}" />
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Télefono</label>
        <input type="text" class="form-control" disabled autocomplete="off" value="{{ $vendedor->telefono }}" />
    </div>
    <div class="col-12 mt-2 col-lg-6">
        <label>Fecha modificado</label>
        <input type="text" class="form-control" disabled autocomplete="off" value="{{ $factura->fecha_actualizado }}" />
    </div>
</div>
<h2 class="font-size-h3 font-weight-bold mb-4">Datos de la factura</h2>
<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Identificación:</label>
        <div id="spinner">
            <input type="text" class="form-control" autocomplete="off" value="{{ $factura->identificacion }}"
                readonly />
        </div>
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Nombres:</label>
        <input type="text" class="form-control" readonly value="{{ $factura->nombre }}" />
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label for="">Calle principal, secundaria. N.
            casa/departamento</label>
        <input type="text" class="form-control" readonly value="{{ $factura->direccion }}">
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Correo</label>
        <input type="email" class="form-control" readonly value="{{ $factura->correo }}" />
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Teléfono:</label>
        <input type="text" class="form-control" readonly value="{{ $factura->telefono }}" />
    </div>
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label for="">Observación</label>
        <input type="text" class="form-control" value="{{ $factura->observacion }}" readonly>
    </div>
</div>
<div class="form-group row">
    <div class="col-12 mt-2 col-lg-6 mt-lg-0">
        <label>Concepto de la factura:</label>
        <input type="text" class="form-control" value="{{ $factura->concepto }}" readonly />
    </div>

    @if ($factura->secuencia_perseo)
        <div class="col-12 mt-2 col-lg-6 mt-lg-0">
            <label>Secuencia de factura</label>
            <input type="text" class="form-control" readonly value="{{ $factura->secuencia_perseo }}" />
        </div>
    @endif
    @if ($factura->secuencia_nota_credito)
        <div class="col-12 mt-2 col-lg-6 mt-lg-4">
            <label>Número de nota de credito</label>
            <input type="text" class="form-control" readonly value="{{ $factura->secuencia_nota_credito }}" />
        </div>
    @endif
</div>
