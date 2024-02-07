<?php

namespace App\Http\Controllers;

use App\Models\Cupones;
use App\Models\Factura;
use App\Models\Log;
use App\Models\Producto;
use App\Models\ProductosLicenciador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LiberarLicenciasController extends Controller
{
    public function redirigir_vista(Factura $factura)
    {
        $productos = collect(json_decode($factura->productos));
        $esContafacil = $productos->where('categoria', 4)->count() > 0;

        if ($esContafacil) {
            return redirect()->route('liberar.vista.contafacil', $factura->facturaid);
        } else {
            return redirect()->route('facturas.ver.liberar', $factura->facturaid);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                      funciones para liberar productos                      */
    /* -------------------------------------------------------------------------- */

    public function vista_liberar_producto(Factura $factura, $ruc = null)
    {
        try {
            $licencias =  $this->validar_lincecias($ruc);
            $vendedorSIS = $this->obtenerVendedorSIS($factura);
            $promocion = $this->obtenerPromocion($factura);
            $productos_contadores = [62, 63, 64, 65];
            $contador = [
                "esContador" => false,
                "error" => false,
            ];

            $productos = json_decode($factura->productos);
            $productos_liberables = [];

            foreach ($productos as $producto) {
                $queryProducto = Producto::find($producto->productoid);
                $producto->descripcion = $queryProducto->descripcion;
                $producto->tipo = $queryProducto->tipo;
                $producto->licenciador = $queryProducto->licenciador;

                if (in_array($producto->productoid, $productos_contadores)) {
                    $contador["esContador"] = true;
                }

                if (!isset($producto->liberado)) {
                    $producto->liberado = 1;

                    if ($factura->liberado == 0) {
                        $producto->liberado = 0;
                        if ($producto->licenciador == 0) {
                            $producto->liberado = 2;
                        }
                    }

                    if ($producto->categoria == 2) {
                        $producto->liberado = 3;
                    }
                }

                if ($producto->licenciador == 1) {
                    $productos_liberables = [...$productos_liberables, [
                        "producto_id" => $queryProducto->productosid,
                        "tipo" => $queryProducto->tipo,
                    ]];
                }
            }

            if (count($productos_liberables) > 1) {
                $contador["error"] = true;
            }

            if ($licencias != null) {
                if ($licencias->liberar &&  $licencias->id_producto != 0) {
                    foreach ($productos_liberables as $producto) {
                        $productoAux = ProductosLicenciador::firstWhere('id_producto_local', $producto['producto_id']);
                        if ($productoAux->id_licenciador != $licencias->id_producto) {
                            $licencias->liberar = false;
                        }
                    }
                }
            }

            return view('auth.facturas.liberar.index', [
                "factura" => $factura,
                "productos" => $productos,
                "productos_liberables" => $productos_liberables,
                "vendedorSIS" => $vendedorSIS,
                "ruc_renovacion" => $ruc,
                "licencias" => $licencias,
                "promocion" => $promocion,
                "contador" => (object)$contador,
            ]);
        } catch (\Throwable $th) {
            flash("Error interno: " . $th->getMessage())->error();
            return back();
        }
    }

    public function liberar_producto(Factura $factura, Request $request)
    {
        $request->validate(
            [
                'rucContador' => 'required|numeric|min:10|max:13',
                'rucCliente' => 'required|numeric|min:10|max:13',
            ],
            [
                'rucContador.required' => 'Ingrese el Ruc del Contador',
                'rucContador.numeric' => 'El Ruc del Contador debe ser numerico',
                'rucContador.min' => 'El Ruc del Contador debe tener al menos 10 digitos',
                'rucContador.max' => 'El Ruc del Contador debe tener maximo 13 digitos',
                'rucCliente.required' => 'Ingrese el Ruc del Cliente',                
                'rucCliente.numeric' => 'El Ruc del Cliente debe ser numerico',                
                'rucCliente.min' => 'El Ruc del Cliente debe tener al menos 10 digitos',                
                'rucCliente.max' => 'El Ruc del Cliente debe tener maximo 13 digitos',
            ],
        );

        $responseData = (object)[
            "status" => 500,
            "message" => "Error interno",
        ];

        try {

            $url = "https://perseo.app/api/registrar_licencia";
            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'Perseo', "clave" => "Perseo1232*"])
                ->withOptions(["verify" => false])
                ->post($url, $request->licenciador)
                ->json();

            if (!isset($resultado["licencia"])) {
                $responseData->message = "No se pudo liberar la licencia";
                $responseData->status = 400;
                return response()->json($responseData, 400);
            }

            if ($resultado["licencia"][0] != "Creado correctamente") {
                $responseData->message = $resultado["licencia"][0];
                $responseData->status = 400;
                return response()->json($responseData, 400);
            }

            try {
                $productos = $this->marcarLiberadoProductos($request->productos);
                $factura->productos = json_encode($productos);
                $factura->liberado = 1;
                $factura->save();

                $this->registro_logs([
                    "pantalla" => "Facturas",
                    "operacion" => "Liberar Licencia",
                    "detalle" => $factura,
                ]);

                $responseData->message = "Licencias liberadas correctamente";
                $responseData->sms = $resultado["licencia"][0];
                $responseData->status = 200;
                return response()->json($responseData, 200);
            } catch (\Throwable $th) {
                $responseData->message = "Licencias liberadas con errores: " . $th->getMessage();
                $responseData->status = 201;
                return response()->json($responseData, 201);
            }
        } catch (\Throwable $th) {
            $responseData->message = $th->getMessage();
            return response()->json($responseData, 500);
        }
    }

    public function renovar_licencia(Factura $factura, Request $request)
    {
        $responseData = (object)[
            "status" => 400,
            "message" => "Error interno",
        ];

        try {
            $url = "https://perseo.app/api/renovar_web";
            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'Perseo', "clave" => "Perseo1232*"])
                ->withOptions(["verify" => false])
                ->post($url, $request->renovacion)
                ->json();

            if (!isset($resultado["renovar"]) && !$resultado["renovar"]) {
                $responseData->message = "Hubo un error al renovar la licencia, es posible que se haya perdido la conexión o el cliente al que tratas de renovar no te pertenezca";
                $responseData->status = 400;
                return response()->json($responseData, 400);
            }

            try {
                $productos = $this->marcarLiberadoProductos($request->productos);
                $factura->productos = json_encode($productos);
                $factura->liberado = 1;
                $factura->save();

                $this->registro_logs([
                    "pantalla" => "Facturas",
                    "operacion" => "Renovacion Licencia",
                    "detalle" => $factura,
                ]);

                $responseData->message = "Licencia renovada correctamente";
                $responseData->status = 200;
                return response()->json($responseData, 200);
            } catch (\Throwable $th) {
                $responseData->message = "Licencia renovada con errores: " . $th->getMessage();
                $responseData->status = 201;
                return response()->json($responseData, 201);
            }
        } catch (\Throwable $th) {
            $responseData->message = $th->getMessage();
            $responseData->status = 500;
            return response()->json($responseData, 500);
        }
    }

    public function reactivar_liberacion(Factura $factura)
    {
        try {
            $productos = json_decode($factura->productos);
            foreach ($productos as $producto) {
                if ($producto->licenciador == 1 && $producto->liberado == 1) {
                    $producto->liberado = 0;
                }
            }

            $factura->productos = json_encode($productos);
            $factura->liberado = 0;
            $factura->save();

            $this->registro_logs([
                "pantalla" => "Facturas",
                "operacion" => "Reactivar liberacion",
                "detalle" => $factura,
            ]);
            flash("Liberacion reactivada")->success();
            return back();
        } catch (\Throwable $th) {
            flash("No se pudo reactivar la liberacion: " . $th->getMessage())->error();
            return back();
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                        Funcciones liberar contafacil                       */
    /* -------------------------------------------------------------------------- */

    public function vista_liberar_contafacil(Factura $factura)
    {
        $productos = collect(json_decode($factura->productos));
        $numeroItems = $productos->where('categoria', 4)->count();
        $contafacilALiberar = null;
        $this->obtenerProductosLiberables($productos, $factura);

        if ($numeroItems > 1) {
            flash("Solo se puede liberar 1 licencia contafacil a la vez")->warning();
        } else {
            $contafacilALiberar = $productos->where('categoria', 4)->first();
        }

        return view('auth.facturas.liberar_contafacil.index', [
            "factura" => $factura,
            "productos" => $productos,
            "contafacilALiberar" => $contafacilALiberar,
            "idDas" => $this->obtener_das_admin_contafacil(),
        ]);
    }

    public function liberar_licencia_contafacil(Factura $factura, Request $request)
    {
        $responseData = (object)[
            "status" => 500,
            "message" => "Error interno",
        ];
        try {
            $url = "https://contafacil.online/licencias/api/registrar_licencia";

            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'Perseo', "clave" => "Perseo1232*"])
                ->withOptions(["verify" => false])
                ->post($url, $request->licenciador)
                ->json();


            if (!isset($resultado["licencia"])) {
                $responseData->message = "No se pudo liberar la licencia";
                $responseData->status = 400;
                return response()->json($responseData, 400);
            }

            if ($resultado["licencia"][0] != "Creado correctamente") {
                $responseData->message = $resultado["licencia"][0];
                $responseData->status = 400;
                return response()->json($responseData, 400);
            }

            try {
                $productos = $this->marcarLiberadoProductos($request->productos);
                $factura->productos = json_encode($productos);
                $factura->liberado = 1;
                $factura->save();

                $this->registro_logs([
                    "pantalla" => "Facturas",
                    "operacion" => "Liberar Licencia",
                    "detalle" => $factura,
                ]);

                $responseData->message = "Licencias liberadas correctamente";
                $responseData->sms = $resultado["licencia"][0];
                $responseData->status = 200;

                return response()->json($responseData, 200);
            } catch (\Throwable $th) {
                $responseData->message = "Licencias liberadas con errores: " . $th->getMessage();
                $responseData->status = 201;
                return response()->json($responseData, 201);
            }
        } catch (\Throwable $th) {
            $responseData->message = $th->getMessage();
            return response()->json($responseData, 500);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                  Funciones para refactorizacion liberacion                 */
    /* -------------------------------------------------------------------------- */

    private function obtenerPromocion($factura)
    {
        $promocion = 0;
        if ($factura->cuponid) {
            $cupon = Cupones::where('cuponid', $factura->cuponid)->first();
            if ($cupon && $cupon->tipo == 2) {
                $promocion = 1;
            }
        }
        return $promocion;
    }

    private function obtenerVendedorSIS($factura)
    {
        if ($factura->liberado == 0) {
            $url = "https://perseo.app/api/vendedores_consulta";

            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'Perseo', "clave" => "Perseo1232*"])
                ->withOptions(["verify" => false])
                ->post($url, ['identificacion' => substr(Auth::user()->identificacion, 0, 10)])
                ->json();

            $vendedorSIS = $resultado["vendedor"][0];

            if ($vendedorSIS == null) {
                flash("Usuario no registrado en el licenciador como vendedor")->warning();
                return back();
            }
            $vendedorSIS = json_decode(json_encode($vendedorSIS));
            return $vendedorSIS;
        }
        return null;
    }

    private function validar_lincecias($ruc)
    {
        try {
            if (!$ruc) {
                return json_decode(json_encode([]));
            }

            $url = "https://perseo.app/api/consultar_licencia_web";

            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'Perseo', "clave" => "Perseo1232*"])
                ->withOptions(["verify" => false])
                ->post($url, ['identificacion' => $ruc])
                ->json();

            return json_decode(json_encode($resultado));
        } catch (\Throwable $th) {
            return json_decode(json_encode([]));
        }
    }

    private function obtenerProductosLiberables(&$productos, $factura)
    {
        $productosLiberables = [];

        foreach ($productos as $producto) {
            $queryProducto = Producto::find($producto->productoid);
            $producto->descripcion = $queryProducto->descripcion;
            $producto->tipo = $queryProducto->tipo;
            $producto->licenciador = $queryProducto->licenciador;
            $producto->cantidad_empresas_ctf = $queryProducto->cantidad_empresas_ctf;

            //producto->liberado: 0 => por liberar 1 => liberado, 2 => liberado manual, 3 => no aplica

            // Aun no liberado
            if (!isset($producto->liberado)) {
                $producto->liberado = 1;

                // Factura no liberada
                if ($factura->liberado == 0) {
                    $producto->liberado = 0;
                    // si no aplica liberacion
                    if ($producto->licenciador == 0) {
                        $producto->liberado = 2;
                    }
                }

                // Producto es firma electronica
                if ($producto->categoria == 2) {
                    $producto->liberado = 3;
                }
            }

            if ($producto->licenciador == 1) {
                $productosLiberables = [...$productosLiberables, [
                    "producto_id" => $queryProducto->productosid,
                    "tipo" => $queryProducto->tipo,
                ]];
            }
        }

        return $productosLiberables;
    }

    private function marcarLiberadoProductos($productosArgs)
    {
        $productos = json_decode(json_encode($productosArgs));
        foreach ($productos as $item) {
            if ($item->licenciador == 1 && $item->liberado == 0) {
                $item->liberado = 1;
            }
        }
        return $productos;
    }

    private function obtener_das_admin_contafacil($das = null)
    {
        $das = $das ?? Auth::user()->distribuidoresid;
        switch ($das) {
            case 1:
                return 1;
            case 2:
                return 4;
            case 3:
                return 3;
            case 4:
                return 2;
        }
    }

    private function registro_logs($logs)
    {
        $logs = (object)$logs;
        try {
            $log = new Log();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = $logs->pantalla;
            $log->operacion = $logs->operacion;
            $log->fecha = now();
            $log->detalle =  $logs->detalle;
            $log->save();
        } catch (\Throwable $th) {
        }
    }
}
