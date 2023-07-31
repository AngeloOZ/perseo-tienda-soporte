@csrf

<div class="form-group row">
    <div class="col-lg-6">
        <label>Detalle:</label>
        <div id="spinner">
            <input type="text" class="form-control" placeholder="Ingrese el detalle" name="detalle" id="detalle"
                autocomplete="off" value="{{ old('detalle', $detalles->detalle) }}" />
        </div>
        @if ($errors->has('detalle'))
            <span class="text-danger">{{ $errors->first('detalle') }}</span>
        @endif
    </div>
    <div class="col-lg-6">
        <label>Precio</label>
        <input type="text" class="form-control validarDigitos" placeholder="Ingrese el precio" name="precio" id="precio"
            autocomplete="off" value="{{ old('precio', $detalles->precio) }}"  />
        @if ($errors->has('precio'))
            <span class="text-danger">{{ $errors->first('precio') }}</span>
        @endif
    </div>
</div>


<div class="form-group row">
    @if ($detalles->fechacreacion != null)
        <div class="col-lg-6">
            <label>Fecha Creacion:</label>
            <input type="text" class="form-control" placeholder="" name="fechacreacion" id="fechacreacion"
                value="{{ $detalles->fechacreacion }}" disabled />
        </div>
        <div class="col-lg-6">
            <label>Fecha Modificacion:</label>
            <input type="text" class="form-control" placeholder="" name="fechamodificacion" autocomplete="off"
                id="fechamodificacion" value="{{ $detalles->fechamodificacion }}" disabled />
        </div>
    @endif
</div>


@section('script')
    <script>
        $(document).ready(function() {

            $('#precio').TouchSpin({
                buttondown_class: 'btn btn-secondary',
                buttonup_class: 'btn btn-secondary',
                min: 0,
                max: 100000,
                step: 1,
                decimals: 2,
                boostat: 5,
                maxboostedstep: 10,
                forcestepdivisibility: 'none'
            });

        

        });

      
    </script>
@endsection
