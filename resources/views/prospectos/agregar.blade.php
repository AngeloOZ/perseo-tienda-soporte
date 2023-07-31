@extends('auth.layouts.app')
@php
    $ciudades = App\Models\Ciudades::get();
    
@endphp
@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form class="form" action="{{ route('prospecto.guardar') }}" method="POST" autocomplete="off">
                            @csrf
                            <div class="card card-custom" id="kt_page_sticky_card">
                                <div class="card-header flex-wrap py-5" style="position: sticky; background-color: white">
                                    <div class="card-title">
                                        <h3 class="card-label"> Crear prospecto </h3>
                                    </div>
                                    @include('prospectos.inc.toolbar')
                                </div>
                                <div class="card-body">
                                    <div class="form-group row">
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label for="tipo">Tipo <span class="text-danger">*</span></label>
                                            <select class="form-control {{ $errors->has('tipo') ? 'is-invalid' : '' }}"
                                                id="tipo" name="tipo">
                                                <option value="" disabled selected>seleccione un tipo de documento
                                                </option>
                                                <option value="C" {{ old('tipo') == 'C' ? 'selected' : '' }}>Cédula
                                                </option>
                                                <option value="R" {{ old('tipo') == 'R' ? 'selected' : '' }}>RUC
                                                </option>
                                            </select>
                                            @error('tipo')
                                                <span class="text-danger">{{ $errors->first('tipo') }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label>Identificación <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control {{ $errors->has('identificacion') ? 'is-invalid' : '' }}"
                                                name="identificacion" disabled id="identificacion"
                                                value="{{ old('identificacion') }}" />
                                            <span class="text-danger d-none" id="mensajeCedula">Identificación no
                                                válida</span>
                                            @error('identificacion')
                                                <span class="text-danger">{{ $errors->first('identificacion') }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label>Razón Social <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control {{ $errors->has('razon_social') ? 'is-invalid' : '' }}"
                                                name="razon_social" value="{{ old('razon_social') }}" />
                                            @error('razon_social')
                                                <span class="text-danger">{{ $errors->first('razon_social') }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label>Nombre Comercial <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control {{ $errors->has('nombre_comercial') ? 'is-invalid' : '' }}"
                                                name="nombre_comercial" value="{{ old('nombre_comercial') }}" />
                                            @error('nombre_comercial')
                                                <span class="text-danger">{{ $errors->first('nombre_comercial') }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label>Dirección <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control {{ $errors->has('direccion') ? 'is-invalid' : '' }}"
                                                name="direccion" value="{{ old('direccion') }}" />
                                            @error('direccion')
                                                <span class="text-danger">{{ $errors->first('direccion') }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label>Email <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control {{ $errors->has('correo') ? 'is-invalid' : '' }}"
                                                name="correo" value="{{ old('correo') }}" />
                                            @error('correo')
                                                <span class="text-danger">{{ $errors->first('correo') }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label>WhatsApp <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control {{ $errors->has('whatsapp') ? 'is-invalid' : '' }}"
                                                name="whatsapp" value="{{ old('whatsapp') }}" />
                                            @error('whatsapp')
                                                <span class="text-danger">{{ $errors->first('whatsapp') }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label>Convencional</label>
                                            <input type="text"
                                                class="form-control {{ $errors->has('convencional') ? 'is-invalid' : '' }}"
                                                name="convencional" value="{{ old('convencional') }}" />
                                            @error('convencional')
                                                <span class="text-danger">{{ $errors->first('convencional') }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label>Ciudades</span>
                                            </label>
                                            <select class="form-control {{ $errors->first('ciudad') }} select2"
                                                id="kt_select2_1" name="ciudad">
                                                <option value="" disabled selected>Seleccionar una ciudad</option>
                                                @foreach ($ciudades as $ciudad)
                                                    <option value="{{ $ciudad->ciudadesid }}"
                                                        {{ old('ciudad') == $ciudad->ciudadesid ? 'selected' : '' }}>
                                                        {{ $ciudad->ciudad }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('ciudad')
                                                <span class="text-danger">{{ $errors->first('ciudad') }}</span>
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
        $(document).ready(function() {
            validarIdentificacionLongitud();

            document.getElementById('identificacion').addEventListener('blur', validarIdentificacion)
        });

        function validarIdentificacion() {
            var cad = document.getElementById('identificacion').value.trim();
            var total = 0;
            var longitud = cad.length;
            var longcheck = longitud - 1;
            var digitos = cad.split('').map(Number);
            var codigo_provincia = digitos[0] * 10 + digitos[1];
            if (cad !== "" && longitud === 10) {

                if (cad != '2222222222' && codigo_provincia >= 1 && (codigo_provincia <= 24 || codigo_provincia == 30)) {
                    for (i = 0; i < longcheck; i++) {
                        if (i % 2 === 0) {
                            var aux = cad.charAt(i) * 2;
                            if (aux > 9) aux -= 9;
                            total += aux;
                        } else {
                            total += parseInt(cad.charAt(i));
                        }
                    }
                    total = total % 10 ? 10 - total % 10 : 0;

                    if (cad.charAt(longitud - 1) == total) {
                        $('#mensajeCedula').addClass("d-none");
                        $('#identificacion').removeClass("is-invalid");


                    } else {
                        $('#identificacion').focus();
                        $('#mensajeCedula').removeClass("d-none");
                        $('#identificacion').addClass("is-invalid");
                    }
                } else {
                    $('#identificacion').focus();
                    $('#mensajeCedula').removeClass("d-none");
                    $('#identificacion').addClass("is-invalid");
                }
            } else
            if (longitud == 13 && cad !== "") {
                var extraer = cad.substr(10, 3);
                if (extraer == "001") {
                    $('#mensajeCedula').addClass("d-none");
                    $('#identificacion').removeClass("is-invalid");
                } else {
                    $('#identificacion').focus();
                    $('#mensajeCedula').removeClass("d-none");
                    $('#identificacion').addClass("is-invalid");
                }
            } else
            if (cad !== "") {
                $('#identificacion').focus();
                $('#mensajeCedula').removeClass("d-none");
                $('#identificacion').addClass("is-invalid");
            }
        }

        function validarIdentificacionLongitud() {
            const identificacion = document.getElementById('identificacion');
            const tipo = document.getElementById('tipo');

            if (tipo.value != "") {
                identificacion.removeAttribute('disabled');
                identificacion.focus();
            }

            tipo.addEventListener('change', e => {
                identificacion.removeAttribute('disabled');
                identificacion.focus();
            })

            identificacion.addEventListener('input', e => {
                if (tipo.value == 'C') {
                    e.target.value = e.target.value.slice(0, 10);
                } else if (tipo.value == 'R') {
                    e.target.value = e.target.value.slice(0, 13);
                }
            })
        }
    </script>
@endsection
