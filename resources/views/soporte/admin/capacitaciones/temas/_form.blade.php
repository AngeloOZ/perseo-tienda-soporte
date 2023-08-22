@php
$listadoSubcategorias = App\Models\Subcategorias::select('subcategoriasid', 'descripcion','categoriasid')->get();
@endphp

@csrf
<div class="form-group row">
    <div class="col-lg-6">
        <label>Descripcion:</label>
        <input type="text" class="form-control {{ $errors->has('descripcion') ? 'is-invalid' : '' }}"
            placeholder="Ingrese la descripcion" name="descripcion" id="descripcion" autocomplete="off"
            value="{{ old('descripcion', $temas->descripcion) }}" />

        @if ($errors->has('descripcion'))
            <span class="text-danger">{{ $errors->first('descripcion') }}</span>
        @endif
    </div>
    <div class="col-lg-6">
        <label>Subcategoría:</label>
        <select class="form-control select2" id="subcategoriasid" name="subcategoriasid">
            @if (count($listadoSubcategorias) > 0)
                <option value="">
                    Escoja una Subcategoría
                </option>
                @foreach ($listadoSubcategorias as $subcategoriasL)
            
                    <option value="{{ $subcategoriasL->subcategoriasid }}"
                        {{ ($subcategoriasL->subcategoriasid == old('subcategoriasid',$temas->subcategoriasid)) ?'selected':'' }}>
                        {{ $subcategoriasL->descripcion }}
                    </option>
                    
                @endforeach
            @endif
        </select>
        @if ($errors->has('subcategoriasid'))
            <span class="text-danger">{{ $errors->first('subcategoriasid') }}</span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-lg-6">
        <label>Tiempo:</label>
        <input type="text" class="form-control {{ $errors->has('tiempo') ? 'is-invalid' : '' }}"
            placeholder="Ingrese el tiempo aproximado" name="tiempo" id="tiempo" autocomplete="off"
            value="{{ old('tiempo', $temas->tiempo) }}" />

        @if ($errors->has('tiempo'))
            <span class="text-danger">{{ $errors->first('tiempo') }}</span>
        @endif
    </div>
    <div class="col-lg-6">
        <label>Tiempo Web:</label>
        <input type="text" class="form-control {{ $errors->has('tiempoWeb') ? 'is-invalid' : '' }}"
            placeholder="Ingrese el tiempo aproximado" name="tiempoWeb" id="tiempoWeb" autocomplete="off"
            value="{{ old('tiempoWeb', $temas->tiempoWeb) }}" />

        @if ($errors->has('tiempoWeb'))
            <span class="text-danger">{{ $errors->first('tiempoWeb') }}</span>
        @endif
    </div>
  
</div>

<div class="form-group row">
    <div class="col-lg-6">
        <label>Enlace Youtube:</label>
        <input type="text" class="form-control {{ $errors->has('enlace_tutorial') ? 'is-invalid' : '' }}"
            placeholder="Ingrese el enlace" name="enlace_tutorial" id="enlace_tutorial" autocomplete="off"
            value="{{ old('enlace_tutorial', $temas->enlace_tutorial) }}" />

        @if ($errors->has('enlace_tutorial'))
            <span class="text-danger">{{ $errors->first('enlace_tutorial') }}</span>
        @endif
    </div>
    <div class="col-lg-6">
        <label>Enlace Web Youtube:</label>
        <input type="text" class="form-control {{ $errors->has('enlace_tutorialWeb') ? 'is-invalid' : '' }}"
            placeholder="Ingrese el enlace" name="enlace_tutorialWeb" id="enlace_tutorialWeb" autocomplete="off"
            value="{{ old('enlace_tutorialWeb', $temas->enlace_tutorialWeb) }}" />

        @if ($errors->has('enlace_tutorialWeb'))
            <span class="text-danger">{{ $errors->first('enlace_tutorialWeb') }}</span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-lg-6">
        <label>Orden:</label>
        <input type="text" class="form-control {{ $errors->has('orden') ? 'is-invalid' : '' }}"
            placeholder="Ingrese el orden" name="orden" id="orden" autocomplete="off"
            value="{{ old('orden', $temas->orden) }}" />
    
        @if ($errors->has('orden'))
            <span class="text-danger">{{ $errors->first('orden') }}</span>
        @endif
    </div>
</div>
