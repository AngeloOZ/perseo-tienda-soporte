@extends('auth.layouts.app')
@section('titulo', 'Registrar nuevo')

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form action="{{ route('demos.guardar') }}" method="POST">
                            <div class="card card-custom" id="kt_page_sticky_card">
                                {{-- Inicio de tabs buttons --}}
                                <div class="card-header d-block">
                                    <div class="d-flex justify-content-between flex-wrap my-3" style="">
                                        <div class="card-title">
                                            <h3 class="card-label"> Registrar nuevo </h3>
                                        </div>
                                        <div class="card-toolbar">
                                            <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                                <div class="btn-group" role="group" aria-label="First group">

                                                    <a href="{{ route('demos.listado') }}"
                                                        class="btn btn-secondary btn-icon" data-toggle="tooltip"
                                                        title="Volver"><i class="la la-long-arrow-left"></i></a>

                                                    <button type="submit" class="btn btn-success btn-icon"
                                                        data-toggle="tooltip" title="Guardar"><i class="la la-save"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @csrf
                                    @include('auth.demos.inc._form')
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
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Separar la validación de RUC y Cédula
        function esRUCValido(text) {
            return text.length === 13 && text.substr(10, 3) === "001";
        }

        function esCedulaValida(cad) {
            let total = 0;
            const longitud = cad.length;
            const longcheck = longitud - 1;
            const digitos = cad.split('').map(Number);
            const codigo_provincia = digitos[0] * 10 + digitos[1];

            if (cad !== "" && longitud === 10 && cad !== '2222222222' &&
                (codigo_provincia >= 1 && codigo_provincia <= 24 || codigo_provincia == 30)) {
                for (let i = 0; i < longcheck; i++) {
                    if (i % 2 === 0) {
                        let aux = cad.charAt(i) * 2;
                        if (aux > 9) aux -= 9;
                        total += aux;
                    } else {
                        total += parseInt(cad.charAt(i));
                    }
                }
                total = total % 10 ? 10 - total % 10 : 0;

                return cad.charAt(longitud - 1) == total;
            }
            return false;
        }

        // Actualizar el mensaje de ayuda
        function actualizarHelperText(message) {
            const helperText = document.getElementById('helperTextRuc');
            helperText.textContent = message;
            helperText.classList.remove("d-none");
        }

        // Función principal
        async function recuperarInformacion() {
            const inputEmpresa = document.getElementById('ruc');
            const spiner = document.getElementById('spinner');
            const helperText = document.getElementById('helperTextRuc');

            let campos = {
                ruc: false
            };

            inputEmpresa.addEventListener('blur', async function() {
                const text = this.value;

                if (!text) return;

                if (!esRUCValido(text)) {
                    this.value = "";
                    actualizarHelperText("La cédula o RUC ingresado no es válido");
                    return;
                }

                helperText.classList.add("d-none");
                spiner.classList.add('spinner', 'spinner-success', 'spinner-right');
                try {
                    const {
                        data
                    } = await axios.post("{{ route('firma.index') }}", {
                        identificacion: text
                    }, {
                        headers: {
                            'usuario': 'perseo',
                            'clave': 'Perseo1232*',
                            '_token': '{{ csrf_token() }}'
                        }
                    });

                    if (data.identificacion) {
                        document.getElementById("razon_social").value = data.razon_social;
                        document.getElementById("correo").value = data.correo.split('\r\n')[0];
                        document.getElementById("whatsapp").value = data.telefono2;
                    }
                } catch (error) {
                    console.error("Error al obtener la información:", error);
                } finally {
                    spiner.classList.remove('spinner', 'spinner-success', 'spinner-right');
                }

            });
        }

        recuperarInformacion();
    </script>
@endsection
