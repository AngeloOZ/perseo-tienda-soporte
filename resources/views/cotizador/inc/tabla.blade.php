<div class="form-group row">
    <div class="col-lg-12 mt-5" style="width:100%">
        <table class="table table-sm table-bordered table-head-custom table-hover text-center" id="kt_datatable"
            style="width:100%">
            <thead>
                <tr>
                    <th data-priority="1" width="50">Detalle</th>
                    <th width="12%">Cantidad</th>
                    <th width="24%">Descuento</th>
                    <th width="12%">Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select class="form-control select2 valoresSelect" name="detallesid" id="detallesid">
                            <option value="">
                                Escoja un detalle
                            </option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->productosid }}" style="font-size: 2px;"
                                    {{ collect(old('detallesid'))->contains($producto->productosid) ? 'selected' : '' }}>
                                    {{ $producto->descripcion }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-danger d-none" name="mensajeDetalle">Escoja
                            una Detalle</span>
                    </td>
                    <td>
                        <input type="text" class="form-control input-sm cantidad"
                            onkeypress="return validarNumero(event)" id="cantidadF" value="1">
                        <span class="text-danger d-none" name="mensajeCantidad">Ingrese cantidad</span>
                    </td>
                    <td>
                        <input type="text" class="form-control descuento input-sm validarDigitos" id="descuentoF"
                            value="0.00">
                        <span class="text-danger d-none" name="mensajeDescuento">Ingrese descuento</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" name="botonEliminar"
                            onclick="eliminarFila(this)">-</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
