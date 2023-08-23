@php
$producto = Request::segment(2);
$cliente = Request::segment(3);
$asignados = App\Models\Productos::select('descripcion')
    ->where('productosid', $producto)
    ->first();
@endphp
@if (Auth::guard()->user()->clientesid == $cliente)
    @extends('frontend.layouts.app')
    @section('contenido')
        @php
            $horasEstimadas = 0;
            $total = 0;
            $asignadosid = explode(';', $implementacionProducto->asignadosvideos);
            foreach ($asignadosid as $tema) {
                if ($tema != '') {
                    if ($implementacionProducto->tipo == 1) {
                        $var = App\Models\Temas::select('categorias.categoriasid', 'subcategorias.subcategoriasid', 'categorias.descripcion as categorias', 'subcategorias.descripcion as subcategorias', 'temas.descripcion as temas', 'categorias.orden', 'temas.enlace_tutorial as enlace', 'temas.temasid')
                            ->join('subcategorias', 'subcategorias.subcategoriasid', 'temas.subcategoriasid')
                            ->join('categorias', 'categorias.categoriasid', 'subcategorias.categoriasid')
                            ->where('temasid', $tema)
                            ->where('temas.enlace_tutorial', '<>', null)
                            ->where('temas.enlace_tutorial', '<>', '')
                            ->first();
                    } else {
                        $var = App\Models\Temas::select('categorias.categoriasid', 'subcategorias.subcategoriasid', 'categorias.descripcion as categorias', 'subcategorias.descripcion as subcategorias', 'temas.descripcion as temas', 'categorias.orden', 'temas.enlace_tutorialWeb as enlace', 'temas.temasid')
                            ->join('subcategorias', 'subcategorias.subcategoriasid', 'temas.subcategoriasid')
                            ->join('categorias', 'categorias.categoriasid', 'subcategorias.categoriasid')
                            ->where('temasid', $tema)
                            ->where('temas.enlace_tutorialWeb', '<>', null)
                            ->where('temas.enlace_tutorialWeb', '<>', '')
                            ->first();
                    }
                    if (isset($var) ) {
                        $categorias[] = $var->categoriasid;
                        $subcategorias[] = $var->subcategoriasid;
                        $data[] = $var;
                    }
                }
            }
            $catRec = array_unique($categorias);
            $subcatRec = array_unique($subcategorias);
        @endphp

        <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
            <div class="d-flex flex-column-fluid" id="recargar">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-custom card-sticky" id="kt_page_sticky_card">
                                <div class="card-header flex-wrap py-5" style="position: sticky; background-color: white">
                                    <div class="card-title w-100">
                                        <div class="w-100">
                                            <h3 class="card-label">
                                                {{ $implementacionProducto->descripcion }}
                                            </h3>
                                        </div>
                                        <a href="{{ route('clientesFront.index') }}"
                                            class="btn btn-secondary btn-icon" data-toggle="tooltip" title="Volver"><i
                                                class="la la-long-arrow-left"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="mx-md-5">
                                    <div class="form-group" id="categoriasDiv">
                                        @foreach ($catRec as $cat => $categoria)
                                            @php
                                                
                                                $catDescripcion = App\Models\Categorias::select('descripcion')
                                                    ->where('categoriasid', $categoria)
                                                    ->first();
                                                
                                            @endphp
                                           
                                                <div class="py-0 my-0 pb-0">
                                                    <button class="btn my-4" data-toggle="collapse"
                                                        href="#categoria-{{ $categoria }}" role="button"
                                                        aria-expanded="false"
                                                        aria-controls="categoria-{{ $categoria }}"><i
                                                            class="la la-plus pr-3"></i>{{ $catDescripcion->descripcion }}</button>
                                                </div>
                                                <div class="collapse ml-5 pr-5 my-0" id="categoria-{{ $categoria }}">
                                                    <div class="ml-md-5 pr-md-5 ">
                                                        <div class="ml-2 pr-2">

                                                            @foreach ($subcatRec as $subcat => $subcategoria)
                                                                @php
                                                                    $subDescripcion = App\Models\Subcategorias::where('subcategoriasid', $subcategoria)->first();
                                                                @endphp
                                                                @if ($subDescripcion->categoriasid == $categoria)
                                                                    @if ($subDescripcion->visible == 1)
                                                                        <div class="my-0 font-weight-bold"
                                                                            style="font-size: 14px"
                                                                            id="{{ $subcategoria }}">
                                                                            {{ $subDescripcion->descripcion }}
                                                                        </div>
                                                                    @endif
                                                                    @foreach ($data as $key => $tema)
                                                                        @php
                                                                            $temaDescripcion = App\Models\Temas::where('temasid', $tema->temasid)->first();
                                                                            
                                                                        @endphp
                                                                        @if ($subcategoria == $tema->subcategoriasid )
                                                                            <div class="ml-md-5 pr-md-5 d-xl-flex pt-5"
                                                                                id="select-{{ $tema->temasid }}-{{ $tema->subcategoria }}">
                                                                                <div class="ml-md-5 pr-md-5  w-md-50">
                                                                                    <div
                                                                                        class=" text-xs-center text-md-left ">
                                                                                        {{ $temaDescripcion->descripcion }}
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex w-100 w-xl-50 ">
                                                                                    <div class="w-100 ml-5 ml-md-2 ">

                                                                                    </div>
                                                                                    <div class="d-md-flex w-100">
                                                                                        <div class="d-flex ">

                                                                                            <div
                                                                                                class="w-md-25 w-25 mr-5 mr-md-0">

                                                                                                @php
                                                                                                    
                                                                                                    if ($implementacionProducto->tipo == 1) {
                                                                                                        $youtube = $temaDescripcion->enlace_tutorial;
                                                                                                    } elseif ($implementacionProducto->tipo == 2) {
                                                                                                        $youtube = $temaDescripcion->enlace_tutorialWeb;
                                                                                                    } else {
                                                                                                        $youtube = '';
                                                                                                    }
                                                                                                    
                                                                                                @endphp
                                                                                                <a @if ($youtube != '' || $youtube != null) class="btn btn-sm btn-clean btn-icon" @else class="btn btn-sm btn-clean btn-icon invisible" @endif
                                                                                                    href="{{ $youtube }}"
                                                                                                    target="_blank"
                                                                                                    title="Enlace Youtube">
                                                                                                    <i
                                                                                                        class="la la-youtube"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
@else
    <script>
        location.href = "{{ route('clientes.404') }}"
    </script>

@endif
