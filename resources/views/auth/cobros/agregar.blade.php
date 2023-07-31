@extends('auth.layouts.app')

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form action="{{ route('cobros.guardar') }}" method="POST" enctype="multipart/form-data"
                            id="formCobros">
                            @csrf
                            <div class="card card-custom" id="kt_page_sticky_card">
                                {{-- Toolbar --}}
                                <div class="card-header d-block">
                                    <div class="d-flex justify-content-between flex-wrap pt-2">
                                        <div class="card-title">
                                            <h3 class="card-label"> Registrar cobro</h3>
                                        </div>

                                        <div class="card-toolbar">
                                            <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                                <div class="btn-group" role="group" aria-label="First group">

                                                    <a href="{{ route('cobros.listado.vendedor') }}"
                                                        class="btn btn-secondary btn-icon" data-toggle="tooltip"
                                                        title="Volver"><i class="la la-long-arrow-left"></i>
                                                    </a>

                                                    <button class="btn btn-success btn-icon" data-toggle="tooltip"
                                                        title="Guardar"><i class="la la-save"></i>
                                                    </button>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="form-group">
                                        <label style="flex-basis: 100%;">Números de facturas</label>
                                        <div class="d-flex">
                                            <input id="secuencias"
                                                class="form-control {{ $errors->has('secuencias') ? 'is-invalid' : '' }}"
                                                name="secuencias" value="{{ old('secuencias') }}" />
                                        </div>
                                        @error('secuencias')
                                            <span class="text-danger">{{ $errors->first('secuencias') }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Estado del pago</label>
                                        <select name="estado"
                                            class="form-control {{ $errors->has('estado') ? 'is-invalid' : '' }}">
                                            <option value="1" {{ old('estado') == 1 ? 'selected' : '' }}>Registrado
                                            </option>
                                            <option value="2" {{ old('estado') == 2 ? 'selected' : '' }} disabled>Verificado
                                            </option>
                                            <option value="3" {{ old('estado') == 3 ? 'selected' : '' }} disabled>Rechazado
                                            </option>
                                        </select>
                                        @error('estado')
                                            <span class="text-danger">{{ $errors->first('estado') }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="textObsPago2">Obseración del pago vendedor</label>
                                        <textarea class="form-control {{ $errors->has('obs_vendedor') ? 'is-invalid' : '' }}" id="textObsPago2"
                                            name="obs_vendedor" style="resize: none" rows="3">{{ old('obs_vendedor') }}</textarea>
                                        @error('obs_vendedor')
                                            <span class="text-danger">{{ $errors->first('obs_vendedor') }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="comprobante" class="customFile">Comprobantes:</label>
                                        <input type="file" multiple
                                            class="form-control {{ $errors->has('comprobante') ? 'is-invalid' : '' }}"
                                            name="comprobante[]" id="comprobante" accept=".jpg, .jpeg, .png">
                                        <span class="text-muted" id="">El número máximo de archivos es de 5 y el
                                            tamaño máximo de cada archivo es de 2 MB</span>
                                        <p class="text-danger d-none" id="mensajeArchios">Debe selecionar al menos un
                                            archivo
                                        </p>
                                        @error('comprobante')
                                            <p class="text-danger">{{ $errors->first('comprobante') }}</p>
                                        @enderror
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
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    <script>
        $(document).ready(function() {
            initTagify();
            init_subir_archivos();
        });

        function initTagify() {
            var input = document.getElementById('secuencias');
            var tagify = new Tagify(input);
        }

        /* -------------------------------------------------------------------------- */
        /*                            funciones para pagos                            */
        /* -------------------------------------------------------------------------- */

        function init_subir_archivos() {
            const mensajeArchios = document.getElementById('mensajeArchios');
            const mesajeEstado = document.getElementById('mesajeEstado');
            const comprobante = document.getElementById('comprobante');
            const formCobros = document.getElementById('formCobros');

            formCobros.addEventListener('submit', function(e) {
                if (comprobante.files.length == 0) {
                    e.preventDefault();
                    Swal.fire({
                        title: "No hay archivos seleccionados",
                        text: "Debe selecionar al menos un archivo",
                        icon: "warning",
                        confirmButtonText: "OK",
                    });
                    return;
                }
                this.submit();
            })


            comprobante.addEventListener('change', function() {
                if (this.files.length > 5) {
                    this.value = "";
                    return Swal.fire({
                        title: "Demasiados archivos",
                        text: "El número máximo de archivos es de 5 y el tamaño máximo de cada archivo es de 2 MB",
                        icon: "warning",
                        confirmButtonText: "OK",
                    })
                }
                this.files.forEach(file => {
                    if (!validar_peso(file)) {
                        this.value = ""
                        return
                    }
                })
            })
        }

        function validar_peso(file, pesoMax = 2097152) {
            if (file.size > pesoMax) {
                Swal.fire({
                    title: "Archivo muy pesado",
                    html: `El archivo: <strong>${file.name}</strong> excede el peso limite de 2MB`,
                    icon: "warning",
                    confirmButtonText: "OK",
                })
                return false;
            }
            return validarExtensionArchivo(file);
        }

        function validarExtensionArchivo(file) {
            const extensionesValidas = ['jpg', 'jpeg', 'png'];

            const extension = file.name.split('.').pop();
            if (!extensionesValidas.includes(extension)) {
                Swal.fire({
                    title: "Tipo de archivo no válido",
                    html: `Solo se permite imagenes de tipo <strong>${extensionesValidas.join(', ')}</strong>`,
                    icon: "warning",
                    confirmButtonText: "OK",
                })
                return false;
            }
            return true;
        }
    </script>
@endsection
