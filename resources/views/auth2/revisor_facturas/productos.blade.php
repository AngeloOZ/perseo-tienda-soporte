@php
    $valorDescuento = $cupon->descuento ?? 0;

    $subTotal = 0;
    $iva = 0;
    $total = 0;
    $descuento = 0;

    foreach ($factura->productos2 as $item) {
        $precioBase = $item->precio;
    
        $descuentoFor = ($precioBase * $valorDescuento) / 100;
        $descuentoFor = floatval(number_format($descuentoFor, 2));
        $precioBaseConDescuento = $precioBase - $descuentoFor;

        $ivaFor = ($precioBaseConDescuento * $item->iva) / 100;
        $ivaFor = floatval(number_format($ivaFor, 3));
        $precioConIVA = $precioBaseConDescuento + $ivaFor;

        $calculoIVA = round(($item->precio * $item->iva) / 100, 2);
        $subTotal += $item->cantidad * $item->precio;
        $iva += $item->cantidad * (($item->precio * $item->iva) / 100);
        $total += $item->cantidad * ($calculoIVA + $item->precio);
    }
    
@endphp
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th class="text-left">Detalle</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Precio Unitario</th>
                <th class="text-right">Total</th>
                @if ($factura->facturado == 0 && $factura->pago_tarjeta == null)
                    <th class="text-right">Eliminar</th>
                @endif
            </tr>
        </thead>
        <tbody id="body_table">
            @foreach ($factura->productos2 as $item)
                <tr>
                    <td class="d-flex align-items-ritext-right ">
                        <p class="text-dark ">{{ $item->descripcion }}</p>
                    </td>
                    <td class="text-right align-middle font-weight-bolder font-size-h5">{{ $item->cantidad }}</td>
                    <td class="text-right align-middle font-weight-bolder font-size-h5">
                        ${{ number_format($item->precio, 2) }}</td>
                    <td class="text-right align-middle font-weight-bolder font-size-h5">
                        ${{ number_format($item->precio * $item->cantidad, 2) }}</td>
                    @if ($factura->facturado == 0)
                        <td class="text-right align-middle">
                            <button type="button" data-action="remover" data-id-producto="{{ $item->productosid }}"
                                class="btn btn-danger font-weight-bolder font-size-sm">X</button>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="{{ $factura->facturado == 0 ? '3' : '2' }}"></td>
                <td class="font-weight-bolder text-left">Subtotal</td>
                <td class="font-weight-bolder text-right" id="subTotal">${{ number_format($subTotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="{{ $factura->facturado == 0 && $factura->pago_tarjeta == null ? '3' : '2' }}"></td>
                <td class="font-weight-bolder text-left">Descuento</td>
                <td class="font-weight-bolder text-right" id="descuento">${{ number_format($descuento, 2) }}</td>
            </tr>
            <tr>
                <td colspan="{{ $factura->facturado == 0 ? '3' : '2' }}"></td>
                <td class="font-weight-bolder text-left">IVA</td>
                <td class="font-weight-bolder text-right" id="iva">${{ number_format($iva, 2) }}</td>
            </tr>
            <tr>
                <td colspan="{{ $factura->facturado == 0 ? '3' : '2' }}"></td>
                <td class="font-weight-bolder text-left">Total</td>
                <td class="font-weight-bolder text-right" id="total">${{ number_format($total, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
