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
                        <form class="form" action="{{ route('prospecto.actualizar', $prospecto->prospectoid) }}" method="POST" autocomplete="off">
                            @csrf
                            @method('PUT')
                            <div class="card card-custom" id="kt_page_sticky_card">
                                <div class="card-header flex-wrap py-5" style="position: sticky; background-color: white">
                                    <div class="card-title">
                                        <h3 class="card-label"> Editar prospecto </h3>
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
                                                <option value="C" {{ $prospecto->tipo == 'C' ? 'selected' : '' }}>
                                                    Cédula
                                                </option>
                                                <option value="R" {{ $prospecto->tipo == 'R' ? 'selected' : '' }}>RUC
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
                                                name="identificacion" value="{{ $prospecto->identificacion }}" />
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
                                                name="razon_social" value="{{ $prospecto->razon_social }}" />
                                            @error('razon_social')
                                                <span class="text-danger">{{ $errors->first('razon_social') }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label>Nombre Comercial <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control {{ $errors->has('nombre_comercial') ? 'is-invalid' : '' }}"
                                                name="nombre_comercial" value="{{ $prospecto->nombre_comercial }}" />
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
                                                name="direccion" value="{{ $prospecto->direccion }}" />
                                            @error('direccion')
                                                <span class="text-danger">{{ $errors->first('direccion') }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label>Email <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control {{ $errors->has('correo') ? 'is-invalid' : '' }}"
                                                name="correo" value="{{ $prospecto->correo }}" />
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
                                                name="whatsapp" value="{{ $prospecto->whatsapp }}" />
                                            @error('whatsapp')
                                                <span class="text-danger">{{ $errors->first('whatsapp') }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label>Convencional</label>
                                            <input type="text"
                                                class="form-control {{ $errors->has('convencional') ? 'is-invalid' : '' }}"
                                                name="convencional" value="{{ $prospecto->convencional }}" />
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
                                                        {{ $prospecto->ciudad == $ciudad->ciudadesid ? 'selected' : '' }}>
                                                        {{ $ciudad->ciudad }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('ciudad')
                                                <span class="text-danger">{{ $errors->first('ciudad') }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label>Fecha de creación</label>
                                            <input type="text" class="form-control" disabled
                                                value="{{ $prospecto->created_at }}" />
                                        </div>
                                        <div class="col-12 mb-4 col-md-6 mb-md-0">
                                            <label>Fecha de la ultima modificación</label>
                                            <input type="text" class="form-control" disabled
                                                value="{{ $prospecto->updated_at }}" />
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

@endsection
