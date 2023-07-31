@extends('auth.layouts.app')

@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form action="{{ route('productos.actualizar', $producto->productos_homologados_id) }}" method="POST"
                            novalidate>
                            @method('PUT')
                            @csrf
                            <div class="card card-custom" id="kt_page_sticky_card">
                                <div class="card-header d-block">
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <div class="card-title">
                                            <h3 class="card-label"> Editar Producto</h3>
                                        </div>

                                        <div class="card-toolbar">
                                            <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                                <div class="btn-group" role="group" aria-label="First group">

                                                    <a href="{{ route('productos.listado') }}"
                                                        class="btn btn-secondary btn-icon" data-toggle="tooltip"
                                                        title="Volver"><i class="la la-long-arrow-left"></i>
                                                    </a>

                                                    <button class="btn btn-success btn-icon" id="buttonSave"
                                                        data-toggle="tooltip" title="Guardar"><i class="la la-save"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-info btn-icon" id="buttonReset"
                                                        data-toggle="tooltip" title="Resetear precio"><i
                                                            class="la la-undo-alt"></i>
                                                    </button>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group row">
                                        <div class="col-12 col-md-6">
                                            <label>Producto</label>
                                            <input type="text" class="form-control" readonly value="{{ $producto->nombre }}">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="">Distribuidor</label>
                                            <select class="form-control" id="distribuidores" name="distribuidor">
                                                <option value=""
                                                    {{ '' == $producto->distribuidoresid ? 'selected' : '' }}>Todos</option>
                                                <option value="1"
                                                    {{ '1' == $producto->distribuidoresid ? 'selected' : '' }}>Perseo Alfa
                                                </option>
                                                <option value="2"
                                                    {{ '2' == $producto->distribuidoresid ? 'selected' : '' }}>Perseo Matriz
                                                </option>
                                                <option value="3"
                                                    {{ '3' == $producto->distribuidoresid ? 'selected' : '' }}>Perseo Delta
                                                </option>
                                                <option value="4"
                                                    {{ '4' == $producto->distribuidoresid ? 'selected' : '' }}>Perseo Omega
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12 col-md-6">
                                            <label>Precio sin IVA</label>
                                            <input type="number" class="form-control" readonly name="precio"
                                                id="precio" value="{{ $producto->precio }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label>Precio con IVA</label>
                                            <input type="number" class="form-control" readonly name="precioiva"
                                                id="precioiva" readonly value="{{ $producto->precioiva }}">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12 col-md-6">
                                            <label>Categoria</label>
                                            <select class="form-control" name="categoria">
                                                <option value="1" {{ $producto->categoria == 1 ? 'selected' : '' }}>
                                                    FACTURITO
                                                </option>
                                                <option value="2" {{ $producto->categoria == 2 ? 'selected' : '' }}>
                                                    FIRMA
                                                    ELECTRONICA</option>
                                                <option value="3" {{ $producto->categoria == 3 ? 'selected' : '' }}>
                                                    PERSEO
                                                    PC
                                                </option>
                                                <option value="4" {{ $producto->categoria == 4 ? 'selected' : '' }}>
                                                    CONTAFACIL</option>
                                                <option value="5" {{ $producto->categoria == 5 ? 'selected' : '' }}>
                                                    PERSEO
                                                    WEB</option>
                                                <option value="6" {{ $producto->categoria == 6 ? 'selected' : '' }}>
                                                    WHAPI
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label>Descuento %</label>
                                            <input type="number" class="form-control" name="descuento" id="descuento"
                                                value="{{ $producto->descuento }}">
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
        const precio = document.getElementById('precio');
        const precioiva = document.getElementById('precioiva');
        const descuento = document.getElementById('descuento');

        const buttonReset = document.getElementById('buttonReset');

        buttonReset.addEventListener('click', function() {
            resetearValores();
        });

        precio.addEventListener('input', function() {
            const precioAux1 = parseFloat(precio.value) || 0;
            precioiva.value = calcularIVA(precioAux1);
        });

        descuento.addEventListener('input', function() {
            const descuentoAux = parseFloat(descuento.value) || 0;
            if (descuentoAux < 0 || descuentoAux > 100) {
                alert("El descuento no puede ser mayor a 100% ni menor a 0%");
                this.value = 0;
                resetearValores();
                return;
            }
            resetearPrecios();
            const precioAux1 = parseFloat(precio.value) || 0;
            const precioDescuento = calcularDescuento(precioAux1, descuentoAux);
            const precioivaAux2 = calcularIVA(precioDescuento);
            precio.value = (precioivaAux2/1.12).toFixed(6);
            precioiva.value = precioivaAux2;
        });

        descuento.addEventListener('blur', function() {
            if (this.value === "") {
                return resetearValores();
            }
        });


        function calcularIVA(precio) {
            const precioIVA = precio * 1.12;
            return precioIVA.toFixed(2);
        }

        function calcularDescuento(precio, descuento) {
            const precioDescuento = precio - (precio * descuento / 100);
            return precioDescuento.toFixed(2);
        }

        function resetearValores() {
            resetearPrecios();
            descuento.value = 0;
        }

        function resetearPrecios() {
            precio.value = "{{ $producto->preciobase }}";
            precioiva.value = "{{ $producto->precioivabase }}";
        }
    </script>
@endsection
