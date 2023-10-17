
<h3 class="font-size-h6 font-weight-bold">Datos del ticket</h3>
<div class="form-group row ">
    <div class="col-12 mb-3 col-md-6 mb-md-0">
        <label>RUC</label>
        <input type="number" class="form-control" readonly value="{{ $ticket->ruc }}" />
    </div>
    <div class="col-12 mb-3 col-md-6 mb-md-0">
        <label>Razón Social</label>
        <input type="text" class="form-control" readonly value="{{ $ticket->razon_social }}" />
    </div>
</div>

<div class="form-group row ">
    <div class="col-12 mb-3 col-md-6 mb-md-0">
        <label>Nombres</label>
        <input type="text" class="form-control" readonly value="{{ $ticket->nombres }}" />
    </div>
    <div class="col-12 mb-3 col-md-6 mb-md-0">
        <label>Apellidos</label>
        <input type="text" class="form-control" readonly value="{{ $ticket->apellidos }}" />
    </div>
</div>

<div class="form-group row ">
    <div class="col-12 mb-3 col-md-6 mb-md-0">
        <label>Correo</label>
        <input type="email" class="form-control" readonly value="{{ $ticket->correo }}" />
    </div>
    <div class="col-12 mb-3 col-md-6 mb-md-0">
        <label>Whatsapp</label>
        <input type="tel" class="form-control" readonly value="{{ $ticket->whatsapp }}" />
    </div>
</div>

<div class="form-group row ">
    <div class="col-12">
        <label>Motivo del soporte</label>
        <textarea placeholder="Describe la razón del soporte en un mínimo de 50 caracteres" class="form-control" readonly
            rows="5" style="resize: none">{{ $ticket->motivo }}</textarea>
    </div>
</div>

<div class="form-group row">
    <div class="col-12 mb-3 col-md-6 mb-md-0">
        <label>Tipo de producto</label>
        <select class="form-control" name="producto">
            <option value="" selected disabled>OTRO</option>
            <option value="facturito" {{ $ticket->producto == 'facturito' ? 'selected' : '' }}>FACTURITO</option>
            <option value="web" {{ $ticket->producto == 'web' ? 'selected' : '' }}>WEB</option>
            <option value="pc" {{ $ticket->producto == 'pc' ? 'selected' : '' }}>PC</option>
            <option value="contafacil" {{ $ticket->producto == 'contafacil' ? 'selected' : '' }}>CONTAFACIL</option>
        </select>
    </div>
    <div class="col-12 mb-3 col-md-6 mb-md-0">
        <label>Distribuidor</label>
        <select class="form-control" name="distribuidor">
            <option value="1" {{ $ticket->distribuidor == 1 ? 'selected' : '' }}>Perseo Alfa
            </option>
            <option value="2" {{ $ticket->distribuidor == 2 ? 'selected' : '' }}>Perseo
                Matriz
            </option>
            <option value="3" {{ $ticket->distribuidor == 3 ? 'selected' : '' }}>Perseo Delta
            </option>
            <option value="4" {{ $ticket->distribuidor == 4 ? 'selected' : '' }}>Perseo Omega
            </option>
            <option value="5" {{ $ticket->distribuidor == 5 ? 'selected' : '' }}>OTROS</option>
        </select>
    </div>
</div>

<div class="form-group row ">
    <div class="col-12 mb-3 col-md-6 mb-md-0">
        <label>Fecha creación</label>
        <input type="text" class="form-control" disabled value="{{ $ticket->fecha_creado }}" />
    </div>
    <div class="col-12 mb-3 col-md-6 mb-md-0">
        <label>Fecha modificado</label>
        <input type="text" class="form-control" disabled value="{{ $ticket->fecha_modificado }}" />
    </div>
</div>
