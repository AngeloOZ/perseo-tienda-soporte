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

        $factura = Factura::select('identificacion', 'nombre', 'productos', 'concepto', 'observacion', 'fecha_actualizado', 'distribuidoresid')
            ->when($distribuidor, function ($query, $dis) {
                return $query->where('distribuidoresid', $dis);
            })
            ->where('secuencia_perseo', 'like', '%' . $secuencia . '%')
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

            $base = Producto::find($item->productoid);
            $otro = ProductoHomologado::find($item->productoid_homo);

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


            $item->nombre = $base->descripcion;

            $nuevo = (object)[];

            $nuevo->plan = $base->descripcion;
            $nuevo->identificacion = $factura->identificacion;
            $nuevo->cliente = $factura->nombre;
            $nuevo->concepto = $factura->concepto;
            $nuevo->observacion = $factura->observacion;
            $nuevo->tipo = $tipo;
            $nuevo->periodo = $periodo;
            $nuevo->pvp = $otro->precio * $item->cantidad;
            $nuevo->iva = ($otro->precio * $otro->iva) / 100;
            $nuevo->iva = round($nuevo->iva, 2);
            $nuevo->total = round($nuevo->pvp + $nuevo->iva, 2);
            $nuevo->promo = "S/n";
            $nuevo->origen = "TIENDA";
            $nuevo->fecha = date('d/m/Y', strtotime($factura->fecha_actualizado));

            return $nuevo;
        });

        return $productos;
    }
}
