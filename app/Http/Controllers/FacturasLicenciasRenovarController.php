<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoHomologado;
use App\Models\ProductosLicenciadorRenovacion;
use App\Models\User;
use Error;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class FacturasLicenciasRenovarController extends Controller
{
    public static function generar_facturas_renovacion()
    {
        $instancia = new self();
        // $licencias = $this->obtener_licencias();
        $licencias = $instancia->obtener_licencias();

        return $licencias;
        if (count($licencias) == 0) return;

        $facturadas = 0;
        foreach ($licencias as $licencia) {
            try {
                // $productos = $this->buscar_producto($licencia);
                $productos = $instancia->buscar_producto($licencia);

                // $vendedor = $this->obtener_vendedor($licencia->vendedor, $licencia->sis_distribuidoresid);
                $vendedor = $instancia->obtener_vendedor($licencia->vendedor, $licencia->sis_distribuidoresid);


                // $cliente = $this->crear_cliente($vendedor, $licencia);
                $cliente = $instancia->crear_cliente($vendedor, $licencia);
                $cliente->concepto = $licencia->concepto;

                // $factura = $this->crear_factura($cliente, $vendedor, $productos);
                $factura = $instancia->crear_factura($cliente, $vendedor, $productos);

                $facturadas++;

                // $autorizada = $this->autorizar_factura($factura, $vendedor);
                $autorizada = $instancia->autorizar_factura($factura, $vendedor);

                // TODO: enviar mensaje de whatsapp

            } catch (\Throwable $th) {
                continue;
            }
        }
        return $facturadas;
    }

    private function obtener_licencias()
    {
        // TODO: BETA => solo distribuidor 1
        $url = "https://perseo.app/api/proximas_caducar/1";

        $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'Perseo', "clave" => "Perseo1232*"])
            ->withOptions(["verify" => false])
            ->post($url)
            ->json();

        $arrayDeObjetos = Collection::make($resultado)->map(function ($item) {
            return (object)$item;
        })->toArray();
        return $arrayDeObjetos;
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

        if ($productoHomologado == null) {
            throw new Error("No se encontro el producto");
        }

        $producto = ProductoHomologado::where([
            ['id_producto_local', $productoHomologado->id_producto_local],
            ['distribuidoresid', $this->homologar_distribuidor($licencia->sis_distribuidoresid)],
        ])->first();

        if (str_contains(strtolower($productoHomologado->descripcion), "facturito")) {
            $licencia->concepto = "FTR - " . $licencia->identificacion;
        } else if (str_contains(strtolower($productoHomologado->descripcion), "web")) {
            $licencia->concepto = "RNW - " . $licencia->identificacion;
        } else if (str_contains(strtolower($productoHomologado->descripcion), "pc")) {
            $licencia->concepto = "RRP - " . $licencia->identificacion;
        }

        return [
            (object)[
                "productoid" => $productoHomologado->id_producto_local,
                "productoid_homo" => $producto->productos_homologados_id,
                "cantidad" => 1
            ]
        ];
    }

    private function obtener_vendedor_default(int $distribuidor)
    {
        $dis = $this->homologar_distribuidor($distribuidor);

        return User::where([['distribuidoresid', $dis], ['rol', 1]])
            ->where('nombres', 'PREDETERMINADO')
            ->first();
    }

    private function obtener_vendedor(string $cedula, int $distribuidor)
    {
        // TODO: Delete next line
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

        $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
            ->withOptions([
                "verify" => false,
                'timeout' => 5,
            ])
            ->post($vendedor->api . "/clientes_crear", $cliente)
            ->json();

        $cliente = $resultado["clientes"][0] ?? null;

        if ($cliente == null) {
            throw new Error("No se pudo crear el cliente");
        }

        return (object)$cliente;
    }

    private function crear_factura($cliente, $vendedor, $productos)
    {
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

        $factura = [
            "api_key" => $vendedor->token,
            "registro" => [
                [
                    "facturas" => [
                        "secuenciasid" => $vendedor->secuenciasid,
                        "forma_pago_empresaid" => 2,
                        "forma_pago_sri_codigo" => "01",
                        "cajasid" => $vendedor->cajasid,
                        "centros_costosid" => $vendedor->centro_costosid,
                        "almacenesid" => $vendedor->almacenesid,
                        "facturadoresid" => $vendedor->vendedoresid,
                        "vendedoresid" => $vendedor->vendedoresid,
                        "clientesid" => $cliente->clientesid_nuevo,
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
                        "concepto" => $cliente->concepto,
                        "observacion" => "",
                        "detalles" => $detalleFactura,
                        "usuariocreacion" => $vendedor->identificacion,
                    ],
                ],
            ],
        ];

        $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
            ->withOptions([
                "verify" => false,
                'timeout' => 5,
            ])
            ->post($vendedor->api . "/facturas_crear", $factura)
            ->json();


        if (isset($resultado["fault"])) {
            throw new Error("No se genero la factura");
        }

        $response = (object)[
            "facturaid" => $resultado["facturas"][0]["facturasid_nuevo"],
            "secuencia" => $resultado["facturas"][0]["facturas_secuencia"],
        ];

        return $response;
    }

    private function autorizar_factura($factura, $vendedor)
    {
        $solicitud = [
            "api_key" => $vendedor->token,
            "facturasid" => $factura->facturaid,
            "enviomail" => true,
        ];

        $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
            ->withOptions([
                "verify" => false,
            ])
            ->post($vendedor->api . "/facturas_autorizar", $solicitud)
            ->json();

        if (isset($resultado["fault"])) {
            throw new Error("No se autorizo la factura: " . $resultado["fault"]["faultstring"]);
        }

        return $resultado;
    }
}
