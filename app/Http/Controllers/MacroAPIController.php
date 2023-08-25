<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Producto;
use App\Models\ProductoHomologado;
use Illuminate\Http\Request;

class MacroAPIController extends Controller
{
    private $token = "05775d0ff4abc9f21c144907110a758cf654ecb26692c328e5dd9d9221de4c61";
    
    public function obtener_macro_ventas(Request $request, $secuencia, $distribuidor = null)
    {
        $token = $request->header('Authorization');

        if ($token != $this->token) {
            return response()->json([
                'error' => 'No autorizado'
            ], 401);
        }

        $factura = Factura::select('facturaid', 'identificacion', 'nombre', 'productos', 'concepto', 'observacion', 'fecha_actualizado', 'distribuidoresid', 'cupones.tipo as tipo_cupon', 'cupones.descuento')
            ->leftJoin('cupones','cupones.cuponid','=','facturas.cuponid')
            ->when($distribuidor, function ($query, $dis) {
                return $query->where('distribuidoresid', $dis);
            })
            ->where('secuencia_perseo', 'like', '%' . $secuencia)
            ->first();

        if ($factura == null) {
            return response()->json([
                'error' => 'No se encontró la factura'
            ], 404);
        }

        $productos = collect(json_decode($factura->productos));

        $productos = $productos->transform(function ($item) use ($factura) {
            $tipo = "FIRMA";
            $periodo = "ANUAL";
            $descuento  = $factura->descuento ? $factura->descuento : 0;
            $promocion = "";
            
            switch ($factura->tipo_cupon) {
                case '1':
                    $promocion = "Descuento $descuento%";
                    break;
                case '2':
                    $promocion = "+3 Meses";
                    break;
            }

            $base = Producto::find($item->productoid);
            
            $otro = ProductoHomologado::where([
                ['id_producto_local', $base->productosid],
                ['distribuidoresid', $factura->distribuidoresid],
            ])->first();

            if (str_contains(strtolower($base->descripcion), 'web')) {
                $tipo = 'WEB';
            } else if (str_contains(strtolower($base->descripcion), 'pc')) {
                $tipo = 'PC';
            } else if (str_contains(strtolower($base->descripcion), 'facturito')) {
                $tipo = 'FACTURITO';
            } else if (str_contains(strtolower($base->descripcion), 'contafácil')) {
                $tipo = 'CONFACIL';
            } else if (str_contains(strtolower($base->descripcion), 'whapi')) {
                $tipo = 'WHAPI';
            }

            if (str_contains(strtolower($base->descripcion), 'compra')) {
                $periodo = "COMPRA";
            } else if (str_contains(strtolower($base->descripcion), 'mensual')) {
                $periodo = "MENSUAL";
            }
            
            
            $descuento2 = ($otro->precio * $descuento) / 100;
            $descuento2 = round(($descuento2 * $item->cantidad), 2);
            
            $precio = $otro->precio * $item->cantidad;
            $precioDesc = $precio - $descuento2;
            
            $iva = ($precioDesc * $otro->iva) / 100;

            $item->nombre = $base->descripcion;

            $nuevo = (object)[];

            $nuevo->plan = $base->descripcion;
            $nuevo->identificacion = $factura->identificacion;
            $nuevo->cliente = $factura->nombre;
            $nuevo->concepto = $factura->concepto;
            $nuevo->observacion = $factura->observacion;
            $nuevo->tipo = $tipo;
            $nuevo->periodo = $periodo;
            $nuevo->pvp = round($precioDesc, 2);
            $nuevo->descuento = $descuento2;
            $nuevo->iva = round($iva, 2);
            $nuevo->total = round(($nuevo->pvp + $nuevo->iva), 2);
            $nuevo->promo = $promocion;
            $nuevo->origen = "";
            $nuevo->fecha = date('d/m/Y', strtotime($factura->fecha_actualizado));
            $nuevo->id = $factura->facturaid;

            return $nuevo;
        });

        return $productos;
    }
    
    
}
