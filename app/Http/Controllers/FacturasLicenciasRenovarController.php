<?php

namespace App\Http\Controllers;

use App\Mail\NotaficacionRenovacion;
use App\Models\Producto;
use App\Models\ProductoHomologado;
use App\Models\ProductosLicenciadorRenovacion;
use App\Models\RenovacionLicencias;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class FacturasLicenciasRenovarController extends Controller
{

    public static function index(Request $request)
    {
        $instancia = new self();

        $licencias = $instancia->obtener_licencias([1, 2, 3, 6]);

        return $licencias;

        $vendedor = $instancia->obtener_vendedor_default(1);
        $factura = $instancia->autorizar_factura((object)["facturaid" => 14060], $vendedor);

        $instancia->notificar_renovacion_correo([
            "to" => "angello.ordonez@hotmail.com",
            "from" => "Sistema de renovación",
            "subject" => "Renovación del sistema contable Perseo",
            "pdfBase64" => $factura->pdf,
            "cliente" => "CELLERI PESANTEZ RAUL OSVALDO",
            "comprobante" => "e12q42e59th8",
            "secuencia" => "000013423",
        ]);

        $decodedPdf = base64_decode($factura->pdf);

        return response($decodedPdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="filename.pdf"',
        ]);
    }

    public static function generar_facturas_renovacion()
    {
        try {
            $instancia = new self();
            // 1 => Alfa
            // 2 => Delta
            // 3 => Omega
            // 6 => Matriz
            // vacio => Todos
            $licencias = $instancia->obtener_licencias([1, 2, 3, 6]);

            if (count($licencias) == 0) return 0;

            $facturadasAlfa = 0;
            $facturadasMatriz = 0;
            $facturadasDelta = 0;
            $facturadasOmega = 0;

            foreach ($licencias as $licencia) {
                try {
                    $productos = $instancia->buscar_producto($licencia);
                    $vendedor = $instancia->obtener_vendedor_default($licencia->sis_distribuidoresid);

                    $datos_cliente = $instancia->obtener_datos_facturacion($licencia);
                    $cliente = $instancia->crear_cliente($vendedor, $datos_cliente);
                    $factura = $instancia->crear_factura($cliente, $vendedor, $productos);
                    
                    if($vendedor->distribuidoresid == 1 || $vendedor->distribuidoresid == 2){
                        echo "\n";
                        echo "Se denego la autorizacion a las facturas de los distribuidores Matriz y Alfa\n";
                    }else{
                        $autorizada = $instancia->autorizar_factura($factura, $vendedor);  
                    }                        

                    switch ($vendedor->distribuidoresid) {
                        case '1':
                            $facturadasAlfa++;
                            break;
                        case '2':
                            $facturadasMatriz++;
                            break;
                        case '3':
                            $facturadasDelta++;
                            break;
                        case '4':
                            $facturadasOmega++;
                            break;
                    }

                    $renovacion = new RenovacionLicencias();
                    $renovacion->uuid = uniqid();
                    $renovacion->secuencia = $factura->secuencia;
                    $renovacion->datos = json_encode([
                        "datos_cliente" => $datos_cliente,
                        "licencia" => $licencia,
                        "factura" => $factura,
                    ]);
                    $renovacion->distribuidoresid = $vendedor->distribuidoresid;
                    $renovacion->origen = 1;
                    $renovacion->save();

                    $instancia->notificar_renovacion_correo([
                        "to" => $datos_cliente->correos,
                        "from" => "Sistema de renovación",
                        "subject" => "Renovación del sistema contable Perseo",
                        "pdfBase64" => $autorizada->pdf,
                        "cliente" => $datos_cliente->nombres,
                        "comprobante" => $renovacion->uuid,
                        "secuencia" => $factura->secuencia,
                    ]);

                    if($vendedor->distribuidoresid == 1 || $vendedor->distribuidoresid == 2){
                        echo "\n";
                        echo "Se denego el envio de sms y correo a Matriz, se envia facturas no autorizadas al correo y numero de Matriz\n";
    
                        if ($datos_cliente->telefono2 != "" || $datos_cliente->telefono2 != null) {
                            WhatsappRenovacionesController::enviar_archivo_mensaje([
                                "phone" => $datos_cliente->telefono2,
                                "caption" => "🎉 ¡Hola *{$datos_cliente->nombres}*! Esperamos que estés teniendo un excelente día. Queremos informarte con mucha alegría que hemos generado la factura de la renovación de tu plan, cuyo vencimiento está programado en 5 días. 🔄💼\n\n¡Agradecemos tu confianza en nosotros y estamos aquí para cualquier cosa que necesites! 🤝🌟💙\n\nPuedes cargar 📤 tu comprobante de pago en el siguiente enlace 💳💰:\n\n" . route('pagos.registrar', $renovacion->uuid),
                                "filename" => "factura_{$factura->secuencia}.pdf",
                                "filebase64" => "data:application/pdf;base64," . $autorizada->pdf,
                                "distribuidor" => $instancia->homologar_distribuidor($licencia->sis_distribuidoresid),
                            ]);
                        }
                    }else{
                        if ($datos_cliente->telefono2 != "" || $datos_cliente->telefono2 != null) {
                            WhatsappRenovacionesController::enviar_archivo_mensaje([
                                "phone" => $datos_cliente->telefono2,
                                "caption" => "🎉 ¡Hola *{$datos_cliente->nombres}*! Esperamos que estés teniendo un excelente día. Queremos informarte con mucha alegría que hemos generado la factura de la renovación de tu plan, cuyo vencimiento está programado en 5 días. 🔄💼\n\n¡Agradecemos tu confianza en nosotros y estamos aquí para cualquier cosa que necesites! 🤝🌟💙\n\nPuedes cargar 📤 tu comprobante de pago en el siguiente enlace 💳💰:\n\n" . route('pagos.registrar', $renovacion->uuid),
                                "filename" => "factura_{$factura->secuencia}.pdf",
                                "filebase64" => "data:application/pdf;base64," . $autorizada->pdf,
                                "distribuidor" => $instancia->homologar_distribuidor($licencia->sis_distribuidoresid),
                            ]);
                        }
                    }   
                    
                } catch (\Throwable $th) {
                    echo $th->getMessage() . "\n";
                    continue;
                }
            }

            echo "\n";
            echo "Total de facturas Alfa: $facturadasAlfa\n";
            echo "Total de facturas Matriz: $facturadasMatriz\n";
            echo "Total de facturas Delta: $facturadasDelta\n";
            echo "Total de facturas Omega: $facturadasOmega\n";
            echo "Total de facturas: " . ($facturadasAlfa + $facturadasMatriz + $facturadasDelta + $facturadasOmega) . "\n";
            return 1;
        } catch (\Throwable $th) {
            echo "\n";
            echo $th->getMessage() . "\n";
            echo "\n";
            return 0;
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                   Funciones para renovacion desde la API                   */
    /* -------------------------------------------------------------------------- */

    public static function generar_factura_licenciador(Request $request)
    {
        $usuario = $request->header('usuario');
        $clave = $request->header('clave');

        if ($usuario != "Perseo" || $clave != "Perseo1232*") {
            return response()->json(['error' => 'Acceso no autorizado'], 401);
        }

        $respuesta = (object)[
            'facturado' => false,
            'autorizado' => false,
            'cobros_generado' => false,
            'enlace_pago' => null,
            'error' => null,
        ];

        // TODO: temporal hasta arreglar
        return response()->json($respuesta, 401);

        try {
            $instancia = new self();

            $licencia = (object)[
                "identificacion" => $request->identificacion,
                "nombres" => $request->nombres,
                "telefono2" => $request->telefono2,
                "correos" => $request->correos,
                "direccion" => $request->direccion,
                "tipo_licencia" => $request->tipo_licencia,
                "periodo" => $request->periodo,
                "producto" => $request->producto,
                "sis_distribuidoresid" => $request->sis_distribuidoresid,
                "vendedor" => $request->vendedor,
                "contador_identificacion" => $request->contador_identificacion,
                "contador_nombres" => $request->contador_nombres,
                "contador_correo" => $request->contador_correo,
                "contador_celular" => $request->contador_celular,
                "contador_direccion" => $request->contador_direccion,
                "modulopractico" => $request->modulopractico,
                "modulocontrol" => $request->modulocontrol,
                "modulocontable" => $request->modulocontable,
            ];

            $productos = $instancia->buscar_producto($licencia);
            $vendedor = $instancia->obtener_vendedor_default($licencia->sis_distribuidoresid);
            $datos_cliente = $instancia->obtener_datos_facturacion($licencia);
            $cliente = $instancia->crear_cliente($vendedor, $datos_cliente);
            $factura = $instancia->crear_factura($cliente, $vendedor, $productos);
            $respuesta->facturado = true;
            $autorizada = $instancia->autorizar_factura($factura, $vendedor);
            $respuesta->autorizado = true;


            $renovacion = new RenovacionLicencias();
            $renovacion->uuid = uniqid();
            $renovacion->secuencia = $factura->secuencia;
            $renovacion->datos = json_encode([
                "datos_cliente" => $datos_cliente,
                "licencia" => $licencia,
                "factura" => $factura,
            ]);
            $renovacion->distribuidoresid = $vendedor->distribuidoresid;
            $renovacion->origen = 2;
            $renovacion->save();

            $respuesta->cobros_generado = true;
            $respuesta->enlace_pago = route('pagos.registrar', $renovacion->uuid);

            $instancia->notificar_renovacion_correo([
                "to" => $datos_cliente->correos,
                "from" => "Sistema de renovación",
                "subject" => "Renovación del sistema contable Perseo",
                "pdfBase64" => $autorizada->pdf,
                "cliente" => $datos_cliente->nombres,
                "comprobante" => $renovacion->uuid,
                "secuencia" => $factura->secuencia,
            ]);

            if ($datos_cliente->telefono2 != "" || $datos_cliente->telefono2 != null) {
                WhatsappRenovacionesController::enviar_archivo_mensaje([
                    "phone" => $datos_cliente->telefono2,
                    "caption" => "🎉 ¡Hola *{$datos_cliente->nombres}*! Esperamos que estés teniendo un excelente día. Queremos informarte con mucha alegría que se ha generado la factura de la renovación de tu plan.\n\n¡Agradecemos tu confianza en nosotros y estamos aquí para cualquier cosa que necesites! 🤝🌟💙\n\nPuedes cargar 📤 tu comprobante de pago en el siguiente enlace 💳💰:\n\n" . route('pagos.registrar', $renovacion->uuid),
                    "filename" => "factura_{$factura->secuencia}.pdf",
                    "filebase64" => "data:application/pdf;base64," . $autorizada->pdf,
                    "distribuidor" => $instancia->homologar_distribuidor($licencia->sis_distribuidoresid),
                ], 10, false);
            }
            return response()->json($respuesta, 200);
        } catch (\Throwable $th) {
            $respuesta->error = $th->getMessage();
            return response()->json($respuesta, 500);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*           Funciones para renovacion para renovacions de licencias          */
    /* -------------------------------------------------------------------------- */

    private function obtener_licencias(array $das = null)
    {
        $url = "https://perseo.app/api/proximas_caducar/";

        $resultado = Http::withHeaders([
            'Content-Type' => 'application/json; charset=UTF-8',
            'verify' => false,
            'usuario' => 'Perseo', "clave" => "Perseo1232*"
        ])
            ->withOptions(["verify" => false])
            ->post($url)
            ->json();

        $arrayDeObjetos = Collection::make($resultado)
            ->map(function ($item) {
                return (object)$item;
            })
            ->filter(function ($item) {
                return $item->producto != 9 && $item->producto != 10;
            })
            ->filter(function ($item) use ($das) {
                if ($das == null) return true;

                if (in_array($item->sis_distribuidoresid, $das)) return true;
            })
            ->flatten()
            ->toArray();
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
            throw new Error("No se encontro el producto homologado tipo_licencia: {$licencia->tipo_licencia} producto: {$tipo_producto} periodo: {$licencia->periodo}");
        }

        $producto = ProductoHomologado::where([
            ['id_producto_local', $productoHomologado->id_producto_local],
            ['distribuidoresid', $this->homologar_distribuidor($licencia->sis_distribuidoresid)],
        ])->first();

        $descripcion = strtolower($productoHomologado->descripcion);
        if (str_contains($descripcion, "facturito")) {
            $licencia->concepto = "FTR - {$licencia->nombres}";
        } else if (str_contains($descripcion, "web")) {
            $licencia->concepto = "RNW - {$licencia->nombres}";
        } else if (str_contains($descripcion, "pc")) {
            $licencia->concepto = "RRP - {$licencia->nombres}";
        }

        // COMMENT 5 y 8 son los id de los productos SoyContador en el admin
        if (in_array($licencia->producto, [5, 8])) {
            $licencia->esContador = true;
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
            ->where('identificacion', 'PREDETERMINADO')
            ->first();
    }

    private function obtener_datos_facturacion($licencia)
    {
        $datos = (object)[
            "nombres" => $licencia->nombres,
            "identificacion" => $licencia->identificacion,
            "direccion" => $licencia->direccion,
            "telefono2" => $licencia->telefono2,
            "correos" => $licencia->correos,
            "concepto" => $licencia->concepto,
        ];

        if (isset($licencia->esContador) && $licencia->esContador) {
            $datos->nombres = $licencia->contador_nombres;
            $datos->identificacion = $licencia->contador_identificacion;
            $datos->direccion = $licencia->contador_direccion;
            $datos->telefono2 = $licencia->contador_celular ?? null;
            $datos->correos = $licencia->contador_correo;
        }

        return $datos;
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
            throw new Error("No se pudo crear el cliente {$datosCliente->identificacion}");
        }

        $cliente["concepto"] = $datosCliente->concepto;

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
                        "observacion" => $cliente->concepto,
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
            throw new Error("No se genero la factura {$cliente->concepto}");
        }

        $response = (object)[
            "facturaid" => $resultado["facturas"][0]["facturasid_nuevo"],
            "secuencia" => $resultado["facturas"][0]["facturas_secuencia"],
            "total_facturado" => round($valoresFactura["totalneto"], 2),
            "distribuidor" => $vendedor->distribuidoresid,
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
            throw new Error("No se autorizo la factura: {$factura->facturaid} distribuidor: {$factura->distribuidor} =>" . $resultado["fault"]["faultstring"]);
        }

        return (object)$resultado;
    }

    private function obtener_vendedor(string $cedula, int $distribuidor)
    {
        $das = $this->homologar_distribuidor($distribuidor);
        if (in_array($das, [2, 3, 4])) {
            return $vendedor = $this->obtener_vendedor_default($distribuidor);
        }

        $vendedor = User::where('identificacion', $cedula)->first();

        if ($vendedor == null) {
            return $this->obtener_vendedor_default($distribuidor);
        }

        if ($vendedor->token == null) {
            return $this->obtener_vendedor_default($distribuidor);
        }

        return $vendedor;
    }

    private function notificar_renovacion_correo($correo)
    {
        $temporaryFilePath = sys_get_temp_dir() . '/' . $correo['secuencia'];
        try {
            $fileContent = base64_decode($correo['pdfBase64']);
            file_put_contents($temporaryFilePath, $fileContent);

            $correo["tempFilePath"] = $temporaryFilePath;

            Mail::to($correo["to"])->queue(new NotaficacionRenovacion($correo));
        } catch (\Throwable $th) {
            echo "Error enviar corre: " . $th->getMessage() . "\n";
        } finally {
            unlink($temporaryFilePath);
        }
    }
}
