@php
    $listadoProductos = App\Models\Productos2::select('productosid', 'descripcion')->get();
@endphp

@csrf
<div class="form-group row">
    <div class="col-lg-6">
        <label>Descripcion:</label>
        <input type="text" class="form-control {{ $errors->has('descripcion') ? 'is-invalid' : '' }}"
            placeholder="Ingrese la descripcion" name="descripcion" id="descripcion" autocomplete="off"
            value="{{ old('descripcion', $categorias->descripcion) }}" />

        @if ($errors->has('descripcion'))
            <span class="text-danger">{{ $errors->first('descripcion') }}</span>
        @endif
    </div>
    <div class="col-lg-6">
        <label>Orden:</label>
        <input type="text" class="form-control {{ $errors->has('orden') ? 'is-invalid' : '' }}"
            placeholder="Ingrese el orden" name="orden" id="orden" autocomplete="off"
            value="{{ old('orden', $categorias->orden) }}" />

        @if ($errors->has('orden'))
            <span class="text-danger">{{ $errors->first('orden') }}</span>
        @endif
    </div>
</div>
