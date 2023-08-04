<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Log;
use App\Models\Producto;
use App\Models\ProductoHomologado;
use App\Models\ProductosLicenciadorRenovacion;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class FacturasLicenciasRenovarController extends Controller
{
    private function obtener_licencias()
    {
        // TODO: BETA => solo distribuidor 1
        $url = "https://perseo.app/api/proximas_caducar/";

        $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'Perseo', "clave" => "Perseo1232*"])
            ->withOptions(["verify" => false])
            ->post($url)
            ->json();

        $arrayDeObjetos = Collection::make($resultado)->map(function ($item) {
            return (object)$item;
        })->filter(function ($item) {
            if ($item->tipo_licencia == 2) return $item;
        })->toArray();
        // productos_homologados_licenciador_renovacion
        return $arrayDeObjetos;
    }

    public function generar_facturas_renovacion()
    {
        $licencias = $this->obtener_licencias();

        if (count($licencias) == 0) dd("No hay licencias para renovar");

        return $licencias;

        foreach ($licencias as $key => $licencia) {
            // return $licencia;
            $productos = $this->buscar_producto($licencia);
            return $productos;
            $vendedor = $this->obtener_vendedor($licencia->vendedor, $licencia->sis_distribuidoresid);
            $cliente = $this->crear_cliente($vendedor, $licencia);
            // return $cliente;
        }
    }

    private function generar_factura(Factura $factura)
    {
        try {
            $vendedor = User::find($factura->usuariosid);

            if ($vendedor->token == null) {
                flash('Ud no esta autorizado para facturar')->warning();
                return back();
            }

            if ($factura->concepto == null) {
                flash('El concepto de la factura es requerido')->warning();
                return back();
            }

            $cliente = $this->crear_cliente($vendedor, $factura);
            $resp = $this->crear_factura($factura, $cliente["cliente"], $cliente["vendedor"]);

            if ($resp["estado"] == "ok") {
                try {
                    Factura::where('facturaid', $factura->facturaid)->update(["facturado" => 1, "facturaid_perseo" => $resp["facturaid"], "secuencia_perseo" => $resp["secuencia"], "fecha_actualizado" => now()]);
                    flash('Factura generada')->success();
                    $facturas = Factura::where('facturaid', $factura->facturaid)->first();
                    $log = new Log();
                    $log->usuario = Auth::user()->nombres;
                    $log->pantalla = "Facturas";
                    $log->operacion = "Generar";
                    $log->fecha = now();
                    $log->detalle =  $facturas;
                    $log->save();
                } catch (\Throwable $th) {
                    flash("Factura generada con secuencia {$resp["secuencia"]} y id: {$resp["facturaid"]}, pero existe un error: " . $th->getMessage())->warning();
                    $log = new Log();
                    $log->usuario = Auth::user()->nombres;
                    $log->pantalla = "Facturas";
                    $log->operacion = "Generar-Observacion";
                    $log->fecha = now();
                    $log->detalle =  $facturas;
                    $log->save();
                }

                $facturaAux =  Factura::where('facturaid', $factura->facturaid)->first();
                ComisionesController::registrar_comision($facturaAux);
            } else {
                flash('Hubo un error al generar la factura')->error();
            }
            return back();
        } catch (\Throwable $th) {
            // dd($th);
            flash('Hubo un error al generar la factura: ' . $th->getMessage())->error();
            return back();
        }
    }

    private function obtener_vendedor_default(int $distribuidor)
    {
        switch ($distribuidor) {
            case 1:

                break;
        }
        return;
    }

    private function obtener_vendedor(string $cedula, int $distribuidor)
    {
        $vendedor = User::where('identificacion', $cedula)->first();

        if ($vendedor == null) {
            return $this->obtener_vendedor_default($distribuidor);
        }

        if ($vendedor->token == null) {
            return $this->obtener_vendedor_default($distribuidor);
        }

        return $vendedor;
    }

    private function crear_cliente($vendedor, $datosCliente)
    {
        try {
            $fecha = date("YmdHis");
            $cliente = [
                "api_key" => $vendedor->token,
                "registros" => [
                    [
                        "clientes" => [
                            "clientes_gruposid" => 1,
                            "razonsocial" => $datosCliente->nombres,
                            "clientes_zonasid" => 1,
                            "clientes_rutasid" => 1,
                            "direccion" => $datosCliente->direccion,
                            "tipoidentificacion" => strlen($datosCliente->identificacion) == 13 ? "R" : "C",
                            "identificacion" => $datosCliente->identificacion,
                            "email" => $datosCliente->correos,
                            "telefono3" => $datosCliente->telefono2,
                            "vendedoresid" => $vendedor->vendedoresid,
                            "cobradoresid" => $vendedor->vendedoresid,
                            "estado" => true,
                            "tarifasid" => 1,
                            "forma_pago_empresaid" => 1,
                            "usuariocreacion" => $vendedor->identificacion,
                            "fechacreacion" => $fecha,
                        ],
                    ],
                ],
            ];

            // TODO: Borrar la linea de abajo
            return $cliente;

            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
                ->withOptions([
                    "verify" => false,
                    'timeout' => 5,
                ])
                ->post($vendedor->api . "/clientes_crear", $cliente)
                ->json();

            $cliente = $resultado["clientes"][0];

            if (isset($cliente["vendedoresid"])) {
                if ($cliente["vendedoresid"] != $vendedor->vendedoresid) {
                    $vendedorAux = User::where('vendedoresid', $cliente["vendedoresid"])->where('distribuidoresid', $vendedor->distribuidoresid)->first();
                    if ($vendedorAux != null) {
                        $vendedor = $vendedorAux;
                    }
                }
            }

            return ["cliente" => $cliente, "vendedor" => $vendedor];
        } catch (\Throwable $th) {
            throw new Error("No se pudo crear el cliente, revise la api del sistema");
        }
    }

    private function crear_factura($factura, $cliente, $vendedor)
    {
        $productos = json_decode($factura->productos);
        $fecha = date("Ymd");

        $valoresFactura = [
            "subtotal" => 0,
            "total_descuento" => 0,
            "subtotalconiva" => 0,
            "subtotalsiniva" => 0,
            "subtotalneto" => 0,
            "total_ice" => 0,
            "total_iva" => 0,
            "propina" => 0,
            "total" => 0,
            "totalneto" => 0,
        ];

        $detalleFactura = [];
        $valorDescuento = 0;

        foreach ($productos as $item) {
            $producto = Producto::find($item->productoid);
            $producto2 = ProductoHomologado::where([
                ['id_producto_local', $producto->productosid],
                ['distribuidoresid', $vendedor->distribuidoresid],
            ])->first();

            $item->precio = $producto2->precio;
            $item->precioiva = $producto2->precioiva;

            // CALCULOS
            $precioBase = $producto2->precio;

            $descuentoFor = ($precioBase * $valorDescuento) / 100;
            $descuentoFor = floatval(number_format($descuentoFor, 2));
            $precioBaseConDescuento = $precioBase - $descuentoFor;

            $ivaFor = ($precioBaseConDescuento * $producto2->iva) / 100;
            $ivaFor = floatval(number_format($ivaFor, 3));
            $precioConIVA = $precioBaseConDescuento + $ivaFor;
            // FIN CALCULOS

            $detalle = [
                "centros_costosid" => $vendedor->centro_costosid,
                "almacenesid" => $vendedor->almacenesid,
                "productosid" => $producto2->id_producto_perseo,
                "medidasid" => 1,
                "cantidaddigitada" => $item->cantidad,
                "cantidad" => $item->cantidad,
                "precio" => $producto2->precio,
                "precioiva" => $producto2->precioiva,
                "descuento" => $valorDescuento,
                "costo" => $producto2->costo,
                "iva" => $producto2->iva,
                "descuentovalor" => $descuentoFor,
                "servicio" => true,
            ];

            array_push($detalleFactura, $detalle);

            $valoresFactura["subtotal"] = $valoresFactura["subtotal"] + $item->cantidad * $precioBase;
            $valoresFactura["total_descuento"] = $valoresFactura["total_descuento"] + $item->cantidad * $descuentoFor;
            $valoresFactura["subtotalconiva"] = $valoresFactura["subtotalconiva"] + $item->cantidad * $precioBaseConDescuento;
            $valoresFactura["subtotalneto"] = $valoresFactura["subtotalneto"] + $item->cantidad * $precioBaseConDescuento;
            $valoresFactura["total_iva"] = $valoresFactura["total_iva"] + $item->cantidad * $ivaFor;
            $valoresFactura["total"] = $valoresFactura["total"] + $item->cantidad * ($precioBaseConDescuento + $ivaFor);
            $valoresFactura["totalneto"] = $valoresFactura["totalneto"] + $item->cantidad * ($precioBaseConDescuento + $ivaFor);
        }

        $factura2 = [
            "api_key" => $vendedor->token,
            "registro" => [
                [
                    "facturas" => [
                        "secuenciasid" => $vendedor->secuenciasid,
                        "forma_pago_empresaid" => ($factura->pago_tarjeta) ? 4 : 2,
                        "forma_pago_sri_codigo" => "01",
                        "cajasid" => $vendedor->cajasid,
                        "centros_costosid" => $vendedor->centro_costosid,
                        "almacenesid" => $vendedor->almacenesid,
                        "facturadoresid" => $vendedor->vendedoresid,
                        "vendedoresid" => $vendedor->vendedoresid,
                        "clientesid" => $cliente["clientesid_nuevo"],
                        "tarifasid" => $vendedor->tarifasid,
                        "emision" => $fecha,
                        "vence" => $fecha,
                        "subtotal" => round($valoresFactura["subtotal"], 2),
                        "total_descuento" => round($valoresFactura["total_descuento"], 2),
                        "subtotalconiva" => round($valoresFactura["subtotalconiva"], 2),
                        "subtotalsiniva" => round($valoresFactura["subtotalsiniva"], 2),
                        "subtotalneto" => round($valoresFactura["subtotalneto"], 2),
                        "total_ice" => round($valoresFactura["total_ice"], 2),
                        "total_iva" => round($valoresFactura["total_iva"], 2),
                        "propina" => round($valoresFactura["propina"], 2),
                        "total" => round($valoresFactura["total"], 2),
                        "totalneto" => round($valoresFactura["totalneto"], 2),
                        "concepto" => $factura->concepto,
                        "observacion" => $factura->observacion,
                        "detalles" => $detalleFactura,
                        "usuariocreacion" => $vendedor->identificacion,
                    ],
                ],
            ],
        ];

        return $factura2;

        $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
            ->withOptions([
                "verify" => false,
                'timeout' => 5,
            ])
            ->post($vendedor->api . "/facturas_crear", $factura2)
            ->json();

        $response = [];

        if (isset($resultado["fault"])) {
            $response["estado"] = "error";
            $response["mensaje"] = $resultado["fault"]["detail"];
        } else {
            $response["estado"] = "ok";
            $response["mensaje"] = $resultado["facturas"];
            $response["facturaid"] = $resultado["facturas"][0]["facturasid_nuevo"];
            $response["secuencia"] = $resultado["facturas"][0]["facturas_secuencia"];

            try {
                $factura->productos = json_encode($productos);
                $factura->save();
            } catch (\Throwable $th) {
            }
        }
        return $response;
    }

    private function autorizar_factura(Factura $factura)
    {
        try {

            $vendedor = User::find($factura->usuariosid);

            if ($vendedor->token == null) {
                flash("Ud no esta autorizado para realizar esta accion")->warning();
                return back();
            }

            $solicitud = [
                "api_key" => $vendedor->token,
                "facturasid" => $factura->facturaid_perseo,
                "enviomail" => true,
            ];

            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
                ->withOptions([
                    "verify" => false,
                ])
                ->post($vendedor->api . "/facturas_autorizar", $solicitud)
                ->json();



            if (isset($resultado["fault"])) {
                $response = $resultado["fault"]["detail"];
                flash($response)->warning();
            } else {
                Factura::where('facturaid', $factura->facturaid)->update(["autorizado" => 1]);
                $facturas = Factura::where('facturaid', $factura->facturaid)->first();
                flash('Factura autorizada')->success();
                $log = new Log();
                $log->usuario = Auth::user()->nombres;
                $log->pantalla = "Facturas";
                $log->operacion = "Autorizar";
                $log->fecha = now();
                $log->detalle =  $facturas;
                $log->save();
            }
            return back();
        } catch (\Throwable $th) {
            flash($th->getMessage())->error();
            return back();
        }
    }

    private function buscar_producto($licencia)
    {
        $tipo_producto = $licencia->producto;

        if ($licencia->tipo_licencia == 2) {
            if ($licencia->modulopractico == 1) {
                $tipo_producto = "modulopractico=1";
            } else if ($licencia->modulocontrol == 1) {
                $tipo_producto = "modulocontrol=1";
            } else if ($licencia->modulocontable == 1) {
                $tipo_producto = "modulocontable=1";
            }
        }

        $productoHomologado = ProductosLicenciadorRenovacion::where([
            ['tipo_licencia', $licencia->tipo_licencia],
            ['producto', $tipo_producto],
            ['periodo', $licencia->periodo],
        ])->first();

        $producto = ProductoHomologado::where([
            ['id_producto_local', $productoHomologado->id_producto_local],
            ['distribuidoresid', $this->homologar_distribuidor($licencia->sis_distribuidoresid)],
        ])->first();


        return [
            (object)[
                "productoid" => $productoHomologado->id_producto_local,
                "productoid_homo" => $producto->productos_homologados_id,
                "cantidad" => 1
            ]
        ];
    }

    private function homologar_distribuidor($distribuidor)
    {
        switch ($distribuidor) {
            case 1:
                // * Alfa
                return 1;
            case 2:
                // * Delta
                return 3;
            case 3:
                // * Omega
                return 4;
            case 6:
                // * Matriz
                return 2;
            default:
                return 2;
        }
    }
}
