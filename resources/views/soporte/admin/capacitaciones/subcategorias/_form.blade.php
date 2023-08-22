@php
$listadoCategorias = App\Models\Categorias::select('categoriasid', 'descripcion')->get();
@endphp

@csrf
<div class="form-group row">
    <div class="col-lg-6">
        <label>Descripcion:</label>
        <input type="text" class="form-control {{ $errors->has('descripcion') ? 'is-invalid' : '' }}"
            placeholder="Ingrese la descripcion" name="descripcion" id="descripcion" autocomplete="off"
            value="{{ old('descripcion', $subcategorias->descripcion) }}" />

        @if ($errors->has('descripcion'))
            <span class="text-danger">{{ $errors->first('descripcion') }}</span>
        @endif
    </div>
    <div class="col-lg-6">
        <label>Categoría:</label>
        <select class="form-control select2" id="categoriasid" name="categoriasid">
            @if (count($listadoCategorias) > 0)
                <option value="">
                    Escoja una Categoría
                </option>
                @foreach ($listadoCategorias as $categoriasL)
  
                    <option value="{{ $categoriasL->categoriasid }}"
                        {{ ($categoriasL->categoriasid == old('categoriasid',$subcategorias->categoriasid)) ? 'selected':'' }}>
                        {{ $categoriasL->descripcion }} 
                    </option>
                    
                @endforeach
            @endif
        </select>
        @if ($errors->has('categoriasid'))
            <span class="text-danger">{{ $errors->first('categoriasid') }}</span>
        @endif
    </div>
</div>

<div class="form-group row">
    
    <div class="col-lg-6">
        <label>Orden:</label>
        <input type="text" class="form-control {{ $errors->has('orden') ? 'is-invalid' : '' }}"
            placeholder="Ingrese el orden" name="orden" id="orden" autocomplete="off"
            value="{{ old('orden', $subcategorias->orden) }}" />

        @if ($errors->has('orden'))
            <span class="text-danger">{{ $errors->first('orden') }}</span>
        @endif
    </div>
    <div class="col-lg-6">
        <label>Visible:</label>
        <span class="switch switch-outline switch-icon switch-primary">
            <label>
                <input type="checkbox" name="visible" id="visible" @if ($subcategorias->visible == 1) checked @endif />
                <span></span>
            </label>
        </span>
    </div>
</div>