@php
$tecnicos = App\Models\Tecnicos::select('tecnicosid', 'nombres')
    ->where('tecnicosid', $actividades->tecnicosid)
    ->get();

@endphp

@if (Auth::guard()->user()->clientesid == $actividades->clientesid)
    @extends('frontend.layouts.app')
    @section('contenido')
        <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
            <div class="d-flex flex-column-fluid">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <!--begin::Card-->
                            <form class="form">
                                @method('PUT')
                                <div class="card card-custom card-sticky" id="kt_page_sticky_card">
                                    <div class="card-header flex-wrap py-5">
                                        <div class="card-title">
                                            <h3 class="card-label"> Actividades </h3>
                                        </div>
                                        <div class="card-toolbar">
                                            <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                                <div class="btn-group" role="group" aria-label="First group">

                                                    <a href="{{ route('soportetecnico.index') }}"
                                                        class="btn btn-secondary btn-icon" data-toggle="tooltip"
                                                        title="Volver"><i class="la la-long-arrow-left"></i></a>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            
                                            $productos = App\Models\Productos::select('productosid', 'descripcion')->get();
                                        @endphp
                                        @csrf
                                        <div class="container-fluid">

                                            <div class="form-group row">
                                                <div class="col-lg-6">
                                                    <label for="">Técnico</label>
                                                    <select class="form-control select2" id="clientesid" name="clientesid"
                                                        disabled="disabled">

                                                        @if (count($tecnicos) > 0)
                                                            @foreach ($tecnicos as $tecnicosL)
                                                                @if ($tecnicosL->tecnicosid != 0)
                                                                    <option value="{{ $tecnicosL->tecnicosid }}"
                                                                        {{ $tecnicosL->tecnicosid == $actividades->tecnicosid ? 'selected' : '' }}>
                                                                        {{ $tecnicosL->nombres }}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </select>

                                                </div>

                                                <div class="col-lg-6">
                                                    <label for="">Producto</label>
                                                    <select class="form-control select2" id="productosid" name="productosid"
                                                        disabled="disabled">

                                                        @if (count($productos) > 0)
                                                            @foreach ($productos as $productosL)
                                                                @if ($productosL->productosid != 0)
                                                                    <option value="{{ $productosL->productosid }}"
                                                                        {{ $productosL->productosid == $actividades->productosid ? 'selected' : '' }}>
                                                                        {{ $productosL->descripcion }}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </select>

                                                </div>

                                            </div>

                                            <div class="form-group row">

                                                <div class="col-lg-6">
                                                    <label for="">Enlace</label>
                                                    <a @if($actividades->enlace != null) target="blank" href="{{ $actividades->enlace }}"@endif >
                                                        <input type="text" class="form-control" name="enlace" id="enlace"
                                                            autocomplete="off" readonly
                                                            value="{{ $actividades->enlace }}">
                                                    </a>


                                                </div>
                                                <div class="col-lg-6">
                                                    <label for="">Tipo</label>
                                                    <select class="form-control " id="tipo" name="tipo"
                                                        disabled="disabled">
                                                        <option value="">
                                                            Escoja el Tipo
                                                        </option>
                                                        <option value="1"
                                                            {{ $actividades->tipo == 'Presencial' ? 'Selected' : '' }}>
                                                            Presencial
                                                        </option>
                                                        <option value="2"
                                                            {{ $actividades->tipo == 'Virtual' ? 'Selected' : '' }}>
                                                            Virtual
                                                        </option>

                                                    </select>

                                                </div>

                                            </div>
                                            <div class="form-group row">

                                                <div class="col-lg-6">
                                                    <label for="">Facturado</label>
                                                    <span class="switch switch-outline switch-icon switch-primary">
                                                        <label>
                                                            <input type="checkbox" name="facturado" id="facturado" disabled
                                                                @if ($actividades->facturado == 'Si') checked @endif />
                                                            <span></span>
                                                        </label>
                                                    </span>

                                                </div>
                                                <div class="col-lg-6" id="divValorFacturado">
                                                    <label for="">Valor Facturado</label>
                                                    <input type="text" class="form-control" id="valorfacturado" readonly
                                                        name="valorfacturado" autocomplete="off"
                                                        value="{{ $actividades->valorfacturado }}">
                                                    <div>

                                                    </div>
                                                </div>
                                            </div>


                                            <div class="row">

                                                <div class="col-12 col-md-6 col-lg-5 col-xl-6">
                                                    <label for="">Actividad realizada:</label>
                                                    <div class="d-flex">
                                                        <textarea class="form-control" name="descripcion" id="descripcion" cols="40%" readonly
                                                            rows="5"> {{ $actividades->descripcion }}</textarea>

                                                    </div>



                                                </div>


                                                @php
                                                    if ($actividades->fechahorafin != null) {
                                                        $horas = App\Models\Actividades::select(DB::raw('SEC_TO_TIME(TIME_TO_SEC(TIMEDIFF(fechahorafin,fechahorainicio))) AS horas'))
                                                            ->where('actividadesid', $actividades->actividadesid)
                                                            ->first();
                                                        $horasOcupado = $horas->horas;
                                                    } else {
                                                        $horasOcupado = '00:00:00';
                                                    }
                                                    
                                                @endphp
                                                <div
                                                    class="d-flex col-6 col-md-3 col-lg-4 col-xl-4 mt-3 md-mt-0 align-items-center justify-content-center ">

                                                    <label style="font-weight: bold; ">Tiempo Ocupado: </label>
                                                    <span class="ml-2 mb-md-2 "
                                                        id="tiempoOcupado-{{ $actividades->actividadesid }}">{{ $horasOcupado }}</span>

                                                </div>

                                            </div>

                                        </div>

                                    @section('script')
                                        <script>
                                            $(document).ready(function() {

                                                var facturadoSwitch = document.getElementById('facturado').checked;
                                                var facturado = '{{ $actividades->facturado }}';

                                                if (facturado == 'No' || !facturado || facturadoSwitch == false) {
                                                    $('#divValorFacturado').hide();
                                                }
                                                if (facturadoSwitch == true) {
                                                    $('#divValorFacturado').show();

                                                }


                                            });
                                        </script>
                                    @endsection



                                </div>
                            </div>
                            <!--end::Card-->
                        </form>
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
