<?php

namespace App\Http\Controllers;

use App\Models\Cupones;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BitrixController extends Controller
{
    function index()
    {
        try {
            $facturas = collect(Factura::select('facturas.facturaid', 'facturas.productos', 'facturas.total_venta', 'cuponid')
                ->where('facturas.distribuidoresid', '=', 1)
                ->where('facturas.facturado', 1)
                ->where('facturas.estado_pago', '!=', 0)
                ->cursor());

            $facturas = $facturas->filter(function ($value, $key) {
                $prod = json_decode($value->productos);

                if (isset($prod[0]->precioiva)) return true;
            })->flatten(1);

            $numFillasAfectadas = 0;

            foreach ($facturas as $key => $factura) {

                $cupon = Cupones::where('cuponid', $factura->cuponid)->first();
                $valorDescuento = $cupon->descuento ?? 0;
                $productos = json_decode($factura->productos);
    
                $subTotal = 0;
                $iva = 0;
                $total = 0;
                $descuento = 0;


                
                foreach ($productos as $item) {
                    $precioBase = $item->precio;
                
                    $descuentoFor = ($precioBase * $valorDescuento) / 100;
                    $descuentoFor = floatval(number_format($descuentoFor, 2));
                    $precioBaseConDescuento = $precioBase - $descuentoFor;
                
                    $ivaFor = ($precioBaseConDescuento * 12) / 100;
                    $ivaFor = floatval(number_format($ivaFor, 3));
                    $precioConIVA = $precioBaseConDescuento + $ivaFor;
                
                    $subTotal += $item->cantidad * $precioBase;
                    $descuento += $item->cantidad * $descuentoFor;
                    $iva += $item->cantidad * $ivaFor;
                    $total += $item->cantidad * $precioConIVA;
                }

                $total = floatval(number_format($total, 2));

                // if(count($productos) > 0 && $valorDescuento > 0) {
                //     dd($total, $factura);
                // }

                Factura::where('facturaid', $factura->facturaid)
                    ->update(['total_venta' => $total]);

                $numFillasAfectadas++;
            }

            echo $numFillasAfectadas;

        } catch (\Throwable $th) {
            dd($th);
        }
    }
}
