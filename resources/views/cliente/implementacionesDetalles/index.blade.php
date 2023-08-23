@if (Auth::guard()->user()->clientesid == $nombreCliente->clientesid)
    @extends('frontend.layouts.app')
    @section('contenido')
        @php
            $decodificadoValidaciones = json_decode($idImplementacion->validaciones);
            $horasEstimadas = 0;
            $total = 0;
            
            foreach ($decodificadoValidaciones as $value) {
                if ($value->estado == 1) {
                    $categorias[] = $value->categoria;
                    $subcategorias[] = $value->subcategoria;
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
                                            <h3 class="card-label">Implementaciones -
                                                {{ $nombreCliente->razonsocial }}
                                            </h3>
                                        </div>

                                        <a href="{{ route('clientesFront.index') }}" class="btn btn-secondary btn-icon"
                                            data-toggle="tooltip" title="Volver"><i class="la la-long-arrow-left"></i>
                                        </a>

                                    </div>

                                    <div class="card-title w-100 p-0 bg-white">
                                        <div class="w-100">
                                            <div class="progress mx-5" style="height: 13px;" id="progressCliente">
                                                <div class="progress-bar" role="progressbar"
                                                    style="width: {{ $operacion }}%" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    {{ round($operacion, 2) }}%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mx-md-5">

                                    <div class="form-group" id="categoriasDiv">

                                        @foreach ($catRec as $cat => $categoria)
                                            @php
                                                $id = $categoria;
                                                if ($decodificadoValidaciones != null) {
                                                    $filtrar = array_filter($decodificadoValidaciones, function ($k) use ($id) {
                                                        return $k->categoria == $id && $k->estado == 1;
                                                    });
                                                
                                                    $filtrarUltimo = end($filtrar);
                                                    $validarCheck = $filtrarUltimo->finCliente;
                                                } else {
                                                    $validarCheck = 2;
                                                }
                                                $catDescripcion = App\Models\Categorias::select('descripcion')
                                                    ->where('categoriasid', $categoria)
                                                    ->first();
                                                
                                            @endphp
                                            <div class="py-0 my-0 pb-0">
                                                <button class="btn my-4" data-toggle="collapse"
                                                    href="#categoria-{{ $categoria }}" role="button"
                                                    aria-expanded="false" aria-controls="categoria-{{ $categoria }}"><i
                                                        class="la la-plus pr-3"></i>{{ $catDescripcion->descripcion }}</button>
                                                <span class="ml-3" id="categoriaCheck-{{ $categoria }}">
                                                    @if ($validarCheck == 1)
                                                        <li class="fa fa-check text-success">
                                                        </li>
                                                    @endif

                                                </span>
                                            </div>

                                            <div class="collapse ml-5 pr-5" id="categoria-{{ $categoria }}">
                                                <div class="ml-md-5 pr-md-5 ">
                                                    <div class="ml-2 pr-2 my-5 ">

                                                        @foreach ($subcatRec as $subcat => $subcategoria)
                                                            @php
                                                                $subDescripcion = App\Models\Subcategorias::where('subcategoriasid', $subcategoria)->first();
                                                            @endphp
                                                            @if ($subDescripcion->categoriasid == $categoria)
                                                                @if ($subDescripcion->visible == 1)
                                                                    <div class="my-0 font-weight-bold"
                                                                        style="font-size: 14px" id="{{ $subcategoria }}">
                                                                        {{ $subDescripcion->descripcion }}
                                                                    </div>
                                                                @endif

                                                                @foreach ($decodificadoValidaciones as $key => $tema)
                                                                    @if ($subcategoria == $tema->subcategoria)
                                                                        @php
                                                                            $temaDescripcion = App\Models\Temas::where('temasid', $tema->tema)->first();
                                                                            
                                                                            if ($decodificadoValidaciones != null && $decodificadoValidaciones != '') {
                                                                                $busqueda = array_search($tema->tema, array_column($decodificadoValidaciones, 'tema'));
                                                                                $validar = $decodificadoValidaciones[$busqueda];
                                                                            
                                                                                if ($key > 0) {
                                                                                    $validarAnterior = $decodificadoValidaciones[$busqueda - 1];
                                                                                }
                                                                            } else {
                                                                                $validar = json_decode(json_encode(['colaborador' => 2, 'inicio' => 2, 'finCliente' => 2]));
                                                                            }
                                                                            if ($implementacionProducto->tipo == 1) {
                                                                                $parts = explode(':', $temaDescripcion->tiempo);
                                                                            } elseif ($implementacionProducto->tipo == 2) {
                                                                                $parts = explode(':', $temaDescripcion->tiempoWeb);
                                                                            }
                                                                            $total += $parts[2] + $parts[1] * 60 + $parts[0] * 3600;
                                                                            $horasEstimadas = gmdate('H:i:s', $total);
                                                                            $horasCategoria = App\Models\ImplentacionesDetalles::select(DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(cliente_fechahorafin,cliente_fechahorainicio)))) AS horas'))
                                                                                ->where('implementacionesid', $idImplementacion->implementacionesid)
                                                                                ->where('temasid', $tema->tema)
                                                                                ->first();
                                                                        @endphp

                                                                        <div class="ml-md-5 pr-md-5 d-xl-flex pt-5"
                                                                            id="select-{{ $tema->tema }}-{{ $tema->subcategoria }}">
                                                                            <div class="ml-md-5 pr-md-5  w-md-50">
                                                                                <div
                                                                                    class="my-2 text-xs-center text-md-left ">
                                                                                    {{ $temaDescripcion->descripcion }}
                                                                                </div>
                                                                            </div>
                                                                            <div class="d-flex w-100 w-xl-50 ">
                                                                                <div class="w-100 ml-5 ml-md-2 d-flex">
                                                                                    <div class="mt-xl-3 text-center ">
                                                                                        <button
                                                                                            @if ($validar->inicio == 1 && $validar->inicioCliente == 0) enabled  @else disabled @endif
                                                                                            class="btn btn-primary inicio"
                                                                                            id="inicio-{{ $tema->tema }}-{{ $tema->subcategoria }}-{{ $idImplementacion->implementacionesid }}-{{ $tema->categoria }}">
                                                                                            Iniciar
                                                                                        </button>

                                                                                        <button class="btn btn-primary fin"
                                                                                            @if ($validar->finCliente == 0 && $validar->inicioCliente == 1) enabled  @else disabled @endif
                                                                                            id="fin-{{ $tema->tema }}-{{ $tema->subcategoria }}-{{ $idImplementacion->implementacionesid }}-{{ $tema->categoria }}">
                                                                                            Fin
                                                                                        </button>

                                                                                    </div>
                                                                                    <div class="mt-3">
                                                                                        <span class="ml-3 ml-md-2 "
                                                                                            id="checkCliente-{{ $tema->tema }}-{{ $tema->subcategoria }}">
                                                                                            <li
                                                                                                @if ($validar->finCliente == 1) class="fa fa-check text-success"
                                                                                                @else
                                                                                                    class="fa fa-check text-success invisible" @endif>
                                                                                            </li>
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-md-flex w-100">
                                                                                    <div class="d-flex ">
                                                                                        <div class="zoomModal mx-auto">
                                                                                            @php
                                                                                                $enlace = '';
                                                                                                $Implementaciones = App\Models\ImplentacionesDetalles::select('enlace_capacitacion')
                                                                                                    ->where('implementacionesid', $idImplementacion->implementacionesid)
                                                                                                    ->where('temasid', $tema->tema)
                                                                                                    ->first();
                                                                                                if (isset($Implementaciones->enlace_capacitacion)) {
                                                                                                    if ($Implementaciones->enlace_capacitacion != '' || $Implementaciones->enlace_capacitacion != null) {
                                                                                                        $enlace = $Implementaciones->enlace_capacitacion;
                                                                                                    }
                                                                                                }
                                                                                            @endphp

                                                                                            <a @if ($enlace != '') class="btn btn-sm btn-clean btn-icon"   @else  class="btn btn-sm btn-clean btn-icon invisible" @endif
                                                                                                href="{{ $enlace }}"
                                                                                                target="_blank"
                                                                                                title="Enlace Tutorial">
                                                                                                <i
                                                                                                    class="la la-link "></i>
                                                                                            </a>


                                                                                        </div>
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

                                                                                    <div
                                                                                        class="w-100 d-none d-md-flex ml-md-5">

                                                                                        <div
                                                                                            class="w-md-25 my-2 text-md-left">
                                                                                            @if ($key == 0)
                                                                                                <div style="position:relative"
                                                                                                    class="ml-md-4">
                                                                                                    <label for=""
                                                                                                        class="ml-md-1"
                                                                                                        style="font-weight: bold; font-size: 11px; position:absolute; bottom:0px;">Tiempo
                                                                                                        Estimado</label>
                                                                                                </div>
                                                                                            @endif
                                                                                            <div class="ml-md-5">
                                                                                                @if ($implementacionProducto->tipo == 1)
                                                                                                    {{ $temaDescripcion->tiempo }}
                                                                                                @else
                                                                                                    {{ $temaDescripcion->tiempoWeb }}
                                                                                                @endif
                                                                                            </div>
                                                                                        </div>

                                                                                        <div
                                                                                            class="w-md-25 my-2 text-md-left ml-md-5">
                                                                                            @if ($key == 0)
                                                                                                <div style="position:relative"
                                                                                                    class="ml-md-4">
                                                                                                    <label
                                                                                                        class="ml-md-2"
                                                                                                        style="font-weight: bold; font-size: 11px; position:absolute; bottom:0px;">Tiempo
                                                                                                        Utilizado</label>
                                                                                                </div>
                                                                                            @endif
                                                                                            <div class="ml-md-5"
                                                                                                id="valordivHora-{{ $idImplementacion->implementacionesid }}-{{ $tema->tema }}">

                                                                                                @if ($horasCategoria->horas)
                                                                                                    {{ $horasCategoria->horas }}
                                                                                                @else
                                                                                                    <span
                                                                                                        class="invisible">00:00:00</span>
                                                                                                @endif

                                                                                            </div>
                                                                                        </div>
                                                                                    </div>


                                                                                </div>
                                                                            </div>
                                                                            <div class="w-100 d-md-none d-flex">
                                                                                <div class="my-5 d-flex mx-2">
                                                                                    <label for=""
                                                                                        style="font-weight: bold; font-size: 11px">T.
                                                                                        Estimado: </label>
                                                                                    <div class="ml-2">
                                                                                        @if ($implementacionProducto->tipo == 1)
                                                                                            {{ $temaDescripcion->tiempo }}
                                                                                        @else
                                                                                            {{ $temaDescripcion->tiempoWeb }}
                                                                                        @endif
                                                                                    </div>
                                                                                </div>

                                                                                <div class="my-5 d-flex mx-5">
                                                                                    <label for=""
                                                                                        style="font-weight: bold; font-size: 11px">T.
                                                                                        Utilizado: </label>

                                                                                    <div class="ml-2"
                                                                                        id="valordivResponsiveHora-{{ $idImplementacion->implementacionesid }}-{{ $tema->tema }}">
                                                                                        {{ $horasCategoria->horas }}

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
                                    @php
                                        $horast = App\Models\ImplentacionesDetalles::select(DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(cliente_fechahorafin,cliente_fechahorainicio)))) AS horas'))
                                            ->where('implementacionesid', $idImplementacion->implementacionesid)
                                            ->get();
                                        
                                    @endphp
                                    <div class="d-flex float-md-right justify-content-center">

                                        <div class="float-right  mt-2">
                                            <label for="horas" style="font-weight: bold; font-size: 13px">Horas
                                                Estimadas:</label>
                                            <label for="" style="font-size: 14px">{{ $horasEstimadas }}</label>
                                        </div>
                                        <div class="float-right mt-2 ml-5" id="horasDiv">
                                            <label for="horas" style="font-weight: bold; font-size: 13px">Horas
                                                Utilizadas:</label>
                                            <label for="" style="font-size: 14px">{{ $horast[0]->horas }}</label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @section('scriptJS')
        <script src="{{ asset('js/app.js') }}"></script>
        <script>
            Echo.channel('fecha')
                .listen('fechaInicio', (e) => {
                    if (e.fecha_inicio.cliente_fechahorainicio == null && e.fecha_inicio.cliente_fechahorafin == null && e
                        .fecha_inicio.tecnico_fechahorainicio != null) {
                        $('#inicio-' + e.fecha_inicio.temasid + '-' + e.fecha_inicio.subcategoriasid + '-' + e.fecha_inicio
                            .implementacionesid + '-' + e.var).prop('disabled',
                            false);
                        $('#fin-' + e.fecha_inicio.temasid + '-' + e.fecha_inicio.subcategoriasid + '-' + e.fecha_inicio
                            .implementacionesid + '-' + e.var).prop('disabled',
                            true);
                    } else if (e.fecha_inicio.cliente_fechahorainicio != null && e.fecha_inicio.cliente_fechahorafin ==
                        null && e.fecha_inicio.tecnico_fechahorainicio != null) {
                        $('#inicio-' + e.fecha_inicio.temasid + '-' + e.fecha_inicio.subcategoriasid + '-' + e.fecha_inicio
                            .implementacionesid + '-' + e.var).prop('disabled',
                            true);
                        $('#fin-' + e.fecha_inicio.temasid + '-' + e.fecha_inicio.subcategoriasid + '-' + e.fecha_inicio
                            .implementacionesid + '-' + e.var).prop('disabled',
                            false);

                    } else if (e.fecha_inicio.cliente_fechahorainicio != null && e.fecha_inicio.cliente_fechahorafin !=
                        null) {
                        $('#inicio-' + e.fecha_inicio.temasid + '-' + e.fecha_inicio.subcategoriasid + '-' + e.fecha_inicio
                            .implementacionesid + '-' + e.var).prop('disabled',
                            true);
                        $('#fin-' + e.fecha_inicio.temasid + '-' + e.fecha_inicio.subcategoriasid + '-' + e.fecha_inicio
                            .implementacionesid + '-' + e.var).prop('disabled',
                            true);

                    } else if (e.fecha_inicio.cliente_fechahorainicio == null && e.fecha_inicio.tecnico_fechahorainicio ==
                        null) {
                        $('#inicio-' + e.fecha_inicio.temasid + '-' + e.fecha_inicio.subcategoriasid + '-' + e.fecha_inicio
                            .implementacionesid + '-' + e.var).prop('disabled',
                            true);
                        $('#fin-' + e.fecha_inicio.temasid + '-' + e.fecha_inicio.subcategoriasid + '-' + e.fecha_inicio
                            .implementacionesid + '-' + e.var).prop('disabled',
                            true);
                    }


                });

            $(".inicio").click(function(event) {
                let variableTema = event.target.id;
                var arrayvariableTema = variableTema.split('-');
                let idImplementacion = "{{ $idImplementacion->implementacionesid }}";
                $('#inicio-' + arrayvariableTema[1] + '-' + arrayvariableTema[2] + '-' + idImplementacion + '-' +
                    arrayvariableTema[4]).prop(
                    'disabled',
                    true);
                $.post('{{ route('implementacionesDetalles.ingresarClienteFechaInicio') }}', {
                    _token: '{{ csrf_token() }}',
                    idTema: arrayvariableTema[1],
                    idSubcategorias: arrayvariableTema[2],
                    idCategorias: arrayvariableTema[4],
                    idImplementacion
                }, function(data) {
                    if (data == 1) {
                        $('#fin-' + arrayvariableTema[1] + '-' + arrayvariableTema[2] + '-' +
                            idImplementacion + '-' + arrayvariableTema[4]).prop('disabled',
                            false);
                    } else {
                        $('#inicio-' + arrayvariableTema[1] + '-' + arrayvariableTema[2] + '-' +
                            idImplementacion + '-' + arrayvariableTema[4]).prop('disabled',
                            false);
                    }

                });
            });

            $(".fin").click(function(event) {

                let variableTemaFin = event.target.id;
                var arrayvariableTemaFin = variableTemaFin.split('-');
                let idImplementacionFin = "{{ $idImplementacion->implementacionesid }}";


                let idProducto = "{{ $implementacionProducto->productosid }}";
                let idCliente = "{{ $nombreCliente->clientes }}";
                $('#fin-' + arrayvariableTemaFin[1] + '-' + arrayvariableTemaFin[2] + '-' + idImplementacionFin + '-' +
                        arrayvariableTemaFin[4])
                    .prop(
                        'disabled',
                        true);
                var contenidoDivCheck = document.getElementById('checkCliente-' + arrayvariableTemaFin[1] +
                    '-' +
                    arrayvariableTemaFin[2]);


                var templateCheck = `<li class = "fa fa-check text-success"></li>`;
                contenidoDivCheck.innerHTML = templateCheck;
                $.post('{{ route('implementacionesDetalles.ingresarClienteFechaFin') }}', {
                    _token: '{{ csrf_token() }}',
                    idTemaFin: arrayvariableTemaFin[1],
                    idSubcategoriasFin: arrayvariableTemaFin[2],
                    idCategoriasFin: arrayvariableTemaFin[4],
                    idImplementacionFin,
                    idProducto,
                    idCliente
                }, function(datos) {
                    if (datos != 'a') {
                        let contenidoDivProgess = document.getElementById('progressCliente');
                        let contenidoDivLabel = document.getElementById('horasDiv');
                        var redondeo = Number.parseFloat(datos[0]).toFixed(2);
                        let contenidoDivHora = document.getElementById('valordivHora-' +
                            idImplementacionFin +
                            '-' + arrayvariableTemaFin[1]);
                        let contenidoDivResponsiveHora = document.getElementById(
                            'valordivResponsiveHora-' +
                            idImplementacionFin + '-' + arrayvariableTemaFin[1]);

                        var TemplateDivHora = `<span> ${datos[2]}</span>`;
                        contenidoDivHora.innerHTML = TemplateDivHora;
                        contenidoDivResponsiveHora.innerHTML = TemplateDivHora;
                        var TemplateDivProgress =
                            `<div class='progress-bar' role='progressbar' style="width: ${ datos[0] }%" aria-valuemin="0" aria-valuemax="100">${redondeo}%</div>`
                        var TemplateDivHoras =
                            `<label> Horas: </label> <label> ${ datos[1][0].horas}</label>`
                        contenidoDivProgess.innerHTML = TemplateDivProgress;
                        contenidoDivLabel.innerHTML = TemplateDivHoras;
                        if (datos[3] == 1) {
                            let checkCategoria = document.getElementById('categoriaCheck-' +
                                arrayvariableTemaFin[4]);
                            var TemplateCheckCat =
                                `<li class="fa fa-check text-success"></li>`;
                            checkCategoria.innerHTML = TemplateCheckCat;
                        }
                    }
                });
            });
        </script>
    @endsection
@else
    <script>
        location.href = "{{ route('clientes.404') }}"
    </script>

@endif
