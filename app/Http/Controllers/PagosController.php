<?php

namespace App\Http\Controllers;

use App\Models\Cobros;
use App\Models\RenovacionLicencias;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PagosController extends Controller
{
    public function registrar_pago_cliente($factura)
    {
        $renovacion = RenovacionLicencias::where('uuid', $factura)->firstOrfail();

        if ($renovacion->registrado === 1) {
            $isRenewed = null;

            if (session()->has('isRenewedLicence')) {
                $isRenewed = session('isRenewedLicence') ? 'renovado' : 'error';
                session()->forget('isRenewedLicence');
            }
            return view('pagos.resultado', ["renovacion" => $renovacion, 'isRenewed' => $isRenewed]);
        }


        return view('pagos.cargar_pago', ['renovacion' => $renovacion]);
    }

    public function guardar_pago(Request $request)
    {
        $renovacion = RenovacionLicencias::where('uuid', $request->uuid)->first();
        $datos = json_decode($renovacion->datos);
        $licencia = $datos->licencia;
        $factura = $datos->factura;
        $distribuidor = $this->homologar_distribuidor($licencia->sis_distribuidoresid);
        $default = $this->obtener_vendedor_default($distribuidor);

        try {
            $cobro = new Cobros();
            $cobro->secuencias = json_encode([["value" => $factura->secuencia]]);
            $cobro->estado = 1;
            $cobro->obs_vendedor = "Renovacion automatica: {$licencia->concepto}";

            $temp = [];
            if (isset($request->comprobantes)) {
                foreach ($request->comprobantes as $file) {
                    $id = uniqid("comprobante-");
                    $temp[$id] = base64_encode(file_get_contents($file->getRealPath()));
                }
            }

            $cobro->comprobante = json_encode($temp);
            $cobro->usuariosid = $default->usuariosid;
            $cobro->distribuidoresid = $distribuidor;
            $cobro->fecha_registro = now();
            $cobro->fecha_actualizacion = now();
            $cobro->renovacionid = $renovacion->renovacionid;

            $cobro->save();
            $renovacion->registrado = 1;
            $renovacion->cobrosid = $cobro->cobrosid;
            $renovacion->save();

            $isRenewed = $this->renovar_licencia($licencia);
            session()->put('isRenewedLicence', $isRenewed);

            return response()->json([
                "status" => 201,
                "message" => "Registro insertado correctamente",
                "isRenewed" => $isRenewed
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(["status" => 500, "message" => $th->getMessage()], 500);
        }
    }

    public function actualizar_pago(Request $request)
    {
        try {
            $renovacion = RenovacionLicencias::where('renovacionid', $request->renovacionid)->first();
            $cobro = Cobros::where('cobrosid', $renovacion->cobrosid)->first();

            $temp = [];
            if (isset($request->comprobantes)) {
                foreach ($request->comprobantes as $file) {
                    $id = uniqid("comprobante-");
                    $temp[$id] = base64_encode(file_get_contents($file->getRealPath()));
                }
                $cobro->comprobante = json_encode($temp);
            }
            $cobro->estado = 1;
            $cobro->fecha_actualizacion = now();
            $cobro->save();

            $renovacion->registrado = 1;
            $renovacion->save();

            return response()->json(["status" => 201, "message" => "Registro insertado correctamente"], 201);
        } catch (\Throwable $th) {
            return response()->json(["status" => 500, "message" => $th->getMessage()], 500);
        }
    }

    public function reactivar_pago(RenovacionLicencias $cobro)
    {
        try {
            $cobro->registrado = 0;
            $cobro->save();
            flash("Enlace reactivado")->success();
            return back();
        } catch (\Throwable $th) {
            flash("No se pudo reactivar el enlace: " . $th->getMessage())->error();
            return back();
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                             Funciones genericas                            */
    /* -------------------------------------------------------------------------- */

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

    private function obtener_vendedor_default(int $distribuidor)
    {
        return User::where([['distribuidoresid', $distribuidor], ['rol', 1]])
            ->where('nombres', 'PREDETERMINADO')
            ->where('identificacion', 'PREDETERMINADO')
            ->first();
    }

    private function renovar_licencia($licencia)
    {
        try {
            $url = "https://perseo.app/api";
            $consulta = (object)Http::withHeaders([
                'Content-Type' => 'application/json; charset=UTF-8',
                'verify' => false,
                'usuario' => 'Perseo', "clave" => "Perseo1232*"
            ])
                ->withOptions(["verify" => false])
                ->post($url . "/consultar_licencia_web", ['identificacion' => $licencia->identificacion])
                ->json();

            if ($consulta->liberar != true && $consulta->accion != "renovar") {
                return false;
            }

            if ($consulta->id_producto != $licencia->producto) {
                return false;
            }

            $datosRenovacion = [
                "id_licencia" => $consulta->id_licencia,
                "id_producto" => $consulta->id_producto,
                "id_servidor" => $consulta->id_servidor,
                "renovar" => $licencia->periodo,
                "sis_vendedoresid" => 0,
            ];

            $renovacion = (object)Http::withHeaders([
                'Content-Type' => 'application/json; charset=UTF-8',
                'verify' => false,
                'usuario' => 'Perseo', "clave" => "Perseo1232*"
            ])
                ->withOptions(["verify" => false])
                ->post($url . "/renovar_web", $datosRenovacion)
                ->json();

            if (!isset($renovacion->renovar) && !$renovacion->renovar) {
                return false;
            }
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
