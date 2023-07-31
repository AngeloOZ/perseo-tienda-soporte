@extends('soporte.layout.app')

@section('titulo', 'Soporte - Perseo')
@section('descripcion', 'Encuentra la solución a todos tus problemas')
{{-- @section('imagen', asset('assets/media/tienda.jpg')) --}}

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid">
        <div class="d-flex flex-column-fluid">
            <div class="container">
                <div class="card card-custom">
                    <div class="card-header d-flex justify-content-center align-items-center">
                        <h1 class="text-uppercase font-size-h3 m-0">Solicitud de Soporte técnico</h1>
                    </div>
                    <div class="card-body py-2">
                        <form action="{{ route('soporte.crear_ticket') }}" id="formTicket" method="POST">
                            @csrf
                            <div class="form-group row mb-2">
                                <div class="col-12 mb-2">
                                    <h3 class="font-size-h4">Datos de la empresa </h3>
                                </div>
                                <div class="col-12 mb-3 col-md-6 mb-md-0">
                                    <label>RUC <span class="text-danger">*</span></label>
                                    <div id="spinner">
                                        <input type="number"
                                            class="form-control {{ $errors->has('ruc') ? 'is-invalid' : '' }}"
                                            value="{{ old('ruc') }}" name="ruc" id="ruc"
                                            placeholder="17XXXXXXXX001"
                                            oninput="if(this.value.length > 13) this.value = this.value.slice(0, 13);" />
                                        @error('ruc')
                                            <span class="text-danger">{{ $errors->first('ruc') }}</span>
                                        @enderror
                                        <p class="text-danger d-none" id="ticketAbierto"></p>
                                    </div>
                                </div>

                                <div class="col-12 mb-3 col-md-6 mb-md-0">
                                    <label>Razón Social</label>
                                    <input type="text"
                                        class="form-control {{ $errors->has('razon_social') ? 'is-invalid' : '' }}"
                                        id="razon_social" name="razon_social" value="{{ old('razon_social') }}"
                                        placeholder="Nombre empresa" />
                                    @error('razon_social')
                                        <span class="text-danger">{{ $errors->first('razon_social') }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-2">
                                <div class="col-12 mb-2">
                                    <h3 class="font-size-h4">Datos del solicitante </h3>
                                </div>
                                <div class="col-12 mb-3 col-md-6 mb-md-0">
                                    <label>Nombres <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control {{ $errors->has('nombres') ? 'is-invalid' : '' }}"
                                        value="{{ old('nombres') }}" name="nombres" placeholder="Angello" />
                                    @error('nombres')
                                        <span class="text-danger">{{ $errors->first('nombres') }}</span>
                                    @enderror
                                </div>
                                <div class="col-12 mb-3 col-md-6 mb-md-0">
                                    <label>Apellidos <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control {{ $errors->has('apellidos') ? 'is-invalid' : '' }}"
                                        value="{{ old('apellidos') }}" name="apellidos" placeholder="Ordonez" />
                                    @error('apellidos')
                                        <span class="text-danger">{{ $errors->first('apellidos') }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-2">
                                <div class="col-12 mb-3 col-md-6 mb-md-0">
                                    <label>Correo <span class="text-danger">*</span></label>
                                    <input type="email"
                                        class="form-control {{ $errors->has('correo') ? 'is-invalid' : '' }}"
                                        value="{{ old('correo') }}" name="correo" placeholder="tucorreo@dominio.com" />
                                    @error('correo')
                                        <span class="text-danger">{{ $errors->first('correo') }}</span>
                                    @enderror
                                </div>
                                <div class="col-12 mb-3 col-md-6 mb-md-0">
                                    <label>Whatsapp <span class="text-danger">*</span></label>
                                    <input type="tel"
                                        class="form-control {{ $errors->has('whatsapp') ? 'is-invalid' : '' }}"
                                        value="{{ old('whatsapp') }}" name="whatsapp" placeholder="09XXXXXX00" />
                                    @error('whatsapp')
                                        <span class="text-danger">{{ $errors->first('whatsapp') }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-2">
                                <div class="col-12">
                                    <label>Motivo del soporte <span class="text-danger">*</span></label>
                                    <textarea name="motivo" placeholder="Describe la razón del soporte en un mínimo de 50 caracteres"
                                        class="form-control {{ $errors->has('motivo') ? 'is-invalid' : '' }}" id="" cols="30" rows="3">{{ old('motivo') }}</textarea>
                                    @error('motivo')
                                        <span class="text-danger">{{ $errors->first('motivo') }}</span>
                                    @enderror
                                </div>
                            </div>
                            <input type="hidden" name="producto" value="{{ $producto }}">
                            <input type="hidden" name="distribuidor" value="{{ $distribuidor }}">

                            <div class="container text-center mt-4">
                                <button class="btn btn-primary" id="btnCrearTicket">Crear ticket</button>
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
        // if (window.history.replaceState) {
        //     window.history.replaceState(null, null, window.location.href)
        // }
    </script>
    <script>
        $(document).ready(function() {
            const ruc = document.getElementById('ruc');
            ruc.addEventListener('blur', validarRuc);

            const formTicket = document.getElementById('formTicket');

            formTicket.addEventListener('submit', function(e) {
                e.preventDefault();
                const btnCrearTicket = document.getElementById('btnCrearTicket');
                btnCrearTicket.disabled = true;
                this.submit();
            });
        });

        function validarRuc() {
            const ruc = this.value;
            if (ruc == "") {
                $('#ticketAbierto').html("");
                $('#ticketAbierto').addClass("d-none");
            } else if (ruc.length == 13) {
                $('#ticketAbierto').html('');
                $('#ticketAbierto').addClass("d-none");
                validarProductoLicenciador();
            } else {
                $('#ticketAbierto').html('El RUC no tiene la longitud correcta');
                $('#ticketAbierto').removeClass("d-none");
            }
        }

        async function validarProductoLicenciador() {
            const inputRuc = document.getElementById('ruc');
            const spiner = document.getElementById('spinner');
            spiner.classList.add('spinner', 'spinner-success', 'spinner-right');
            $('#ticketAbierto').text("");

            var myHeaders = new Headers();
            myHeaders.append("usuario", "Perseo");
            myHeaders.append("clave", "Perseo1232*");
            myHeaders.append("Content-Type", "application/json");

            var raw = JSON.stringify({
                "identificacion": inputRuc.value
            });

            var requestOptions = {
                method: 'POST',
                headers: myHeaders,
                body: raw,
                redirect: 'follow'
            };
            try {
                const data = await fetch("https://perseo.app/api/consultar_licencia", requestOptions);
                const result = await data.json();
                if (result.licencia) {
                    $('#ticketAbierto').addClass("d-none");
                    res = await comprobarTicketsAbiertos();
                    if (res)
                        $("#razon_social").val(result.cliente);
                } else {
                    $('#ticketAbierto').html(
                        `El ruc: <strong>${inputRuc.value}</strong> no tiene ninguna licencia registrada en el sistema`
                        );
                    $('#ticketAbierto').removeClass("d-none");
                    inputRuc.value = '';
                }
            } catch (error) {
                alert("Error al obtener los datos, intentalo más tarde");
                inputRuc.value = "";
                console.log(error);
            } finally {
                spiner.classList.remove('spinner', 'spinner-success', 'spinner-right');
            }
        }

        async function comprobarTicketsAbiertos() {
            var cad = document.getElementById('ruc');

            let url = '{{ route('soporte.consultar_estado', 'cad') }}';
            url = url.replace('cad', ruc.value);

            const response = await fetch(url)
            const json = await response.json();

            if (json.status == 400) {
                $('#ticketAbierto').html(json.message);
                $('#ticketAbierto').removeClass("d-none");
                ruc.value = "";
                return false;
            } else {
                $('#ticketAbierto').addClass("d-none");
                return true;
            }
        }
    </script>
@endsection
