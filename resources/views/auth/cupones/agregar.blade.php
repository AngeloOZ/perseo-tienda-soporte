@extends('auth.layouts.app')

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form action="{{ route('cupones.guardar') }}" method="POST">
                            @csrf
                            <div class="card card-custom" id="kt_page_sticky_card">
                                <div class="card-header d-block">
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <div class="card-title">
                                            <h3 class="card-label"> Agregar cupón</h3>
                                        </div>

                                        <div class="card-toolbar">
                                            <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                                <div class="btn-group" role="group" aria-label="First group">

                                                    <a href="{{ route('cupones.listado') }}"
                                                        class="btn btn-secondary btn-icon" data-toggle="tooltip"
                                                        title="Volver"><i class="la la-long-arrow-left"></i>
                                                    </a>

                                                    <button class="btn btn-success btn-icon"
                                                        data-toggle="tooltip" title="Guardar"><i class="la la-save"></i>
                                                    </button>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">

                                    <div class="form-group row">
                                        <div class="col-12 mb-2 col-md-6 mb-0">
                                            <label>Tipo cupón</label>
                                            <select name="tipo" class="form-control {{ $errors->has('tipo') ? 'is-invalid' : '' }}" id="selectTipo">
                                                <option value="1" {{ old('tipo') == 1 ? 'selected' : '' }} >Descuento</option>
                                                <option value="2" {{ old('tipo') == 2 ? 'selected' : '' }} >Promoción +3 Meses</option>
                                            </select>
                                            @error('tipo')
                                                <span class="text-danger">{{ $errors->first('tipo') }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-12 mb-2 col-md-6 mb-0">
                                            <label>Nombre Cupón</label>
                                            <input type="text" name="cupon"
                                                class="form-control {{ $errors->has('cupon') ? 'is-invalid' : '' }}"
                                                value="{{ old('cupon') }}" id="nombreCupon">
                                            @error('cupon')
                                                <span class="text-danger">{{ $errors->first('cupon') }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12 mb-2 col-md-6 mb-0">
                                            <label>Fecha de vencimiento</label>
                                            <input type="date" class="form-control {{ $errors->has('tiempo_vigencia') ? 'is-invalid' : '' }}" name="tiempo_vigencia"
                                                min="{{ date('Y-m-d') }}" value="{{ old('tiempo_vigencia') }}">
                                            @error('tiempo_vigencia')
                                                <span class="text-danger">{{ $errors->first('tiempo_vigencia') }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-12 mb-2 col-md-6 mb-0">
                                            <label>Limite de uso</label>
                                            <input type="number" class="form-control {{ $errors->has('limite') ? 'is-invalid' : '' }}" name="limite" id="limite"
                                                value="{{  old('limite') ? old('limite') : '1' }}" min="1" max="10">
                                            @error('limite')
                                                <span class="text-danger">{{ $errors->first('limite') }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row" id="ctnDescuento">
                                        <div class="col-12 mb-2 col-md-6 mb-0">
                                            <label>Descuento %</label>
                                            <input type="number" class="form-control {{ $errors->has('descuento') ? 'is-invalid' : '' }}" name="descuento" id="descuento"
                                                value="{{ old('descuento') ? old('descuento') : '0' }}" min="0"
                                                max="50" step="any">
                                            @error('descuento')
                                                <span class="text-danger">{{ $errors->first('descuento') }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        const nombreCupon = document.getElementById('nombreCupon');
        const descuento = document.getElementById('descuento');


        nombreCupon.addEventListener('input', convertir_mayusculas);
        descuento.addEventListener('input', validarDescuento);

        function convertir_mayusculas() {
            this.value = this.value.toUpperCase();
        }

        $(document).ready(function(){
            if($('#selectTipo').val() == 2){
                $('#ctnDescuento').hide();
            }else{
                $('#ctnDescuento').show(); 
            }
        });
    
 
        $('#selectTipo').on('change', function(){
            if($(this).val() == 2){
                $('#ctnDescuento').hide();
            }else{
                $('#ctnDescuento').show(); 
            }
        });


        function validarDescuento() {
            if (descuento.value > 100 || descuento.value < 0) {
                descuento.value = 0;
                return alert("El descuento debe estar entre 0 y 100%");
            }
        }
    </script>
@endsection
