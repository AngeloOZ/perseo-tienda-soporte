<?php

namespace App\Http\Controllers;

use App\Models\Cobros;
use App\Models\Factura;
use App\Services\RegistroCobrosSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificarCobrosLotesController extends Controller
{
    private $cobrosClientesController;
    private $serviceRegistroCobros;

    public function  __construct()
    {
        $this->cobrosClientesController = new CobrosClientesController();
        $this->serviceRegistroCobros = new RegistroCobrosSistema();
    }

    public function listar_cobros_lotes()
    {
        $cobrosCollect = $this->obtener_cobros_restantes();
        $bancos = $this->cobrosClientesController->obtener_bancos(Auth::user());
        return view('auth2.revisor_facturas.cobros_lotes.index', [
            'cobros' => $cobrosCollect,
            'bancos' => $bancos,
            'procesado' => false
        ]);
    }

    public function procesar_cobro_lotes(Request $request)
    {
        try {
            $cobrosCollect = $this->obtener_cobros_restantes();
            $bancos = $this->cobrosClientesController->obtener_bancos(Auth::user());
            $listadoTransacciones = $this->procesar_csv($request->file('csv')->getRealPath());


            $cobrosCollect = $cobrosCollect->map(function ($cobro) use ($listadoTransacciones) {
                $transaccion = $listadoTransacciones->firstWhere('documento', $cobro->numero_comprobante);
                if ($transaccion) {
                    $esTransferencia = str_contains(strtolower($transaccion->concepto), 'transferencia');
                    $cobro->monto = $transaccion->monto;
                    $cobro->fecha = $transaccion->fecha;
                    $cobro->tipo = $esTransferencia ? 'transferencia' : 'deposito';
                    return $cobro;
                }
            })->filter()->flatten();

            return view('auth2.revisor_facturas.cobros_lotes.procesado',  [
                'cobros' => $cobrosCollect,
                'bancos' => $bancos,
                'procesado' => true
            ]);
        } catch (\Throwable $th) {
            flash('Error al procesar el archivo CSV')->error();
            return redirect()->route('pagos.lotes.list');
        }
    }

    public function registrar_cobro_sistema(Request $request)
    {
        $message = (object)["status" => 200, "message" => "Cobro registrado correctamente"];
        try {
            $datos_cobro = (object)[
                'numero_comprobante' => $request->numero_comprobante,
                'banco_destino' => $request->banco_destino,
                'banco_origen' => $request->banco_origen,
                'forma_pago' => $request->forma_pago == 'transferencia' ? 6 : 5,
                'monto' => $request->monto,
                'fecha' => date("Ymd", strtotime($request->fecha)),
            ];

            $factura = $this->serviceRegistroCobros->obtener_factura_perseo($request->facturaid);
            $cobro_registrado = $this->serviceRegistroCobros->registro_del_cobro($factura, $datos_cobro);

            if ($request->origen == "renovacion") {
                $data = [
                    'estado' => 2,
                    'cobros_id_perseo' => json_encode([
                        'cobros_id_perseo' => $datos_cobro->cobros_id_perseo,
                        'cobros_cod_perseo' => $datos_cobro->cobros_cod_perseo,
                        'forma_pago' => $datos_cobro->forma_pago,
                        'monto' => $datos_cobro->monto,
                    ]),
                ];
                Cobros::where('cobrosid', $request->id_origen)->update($data);
            } else {
                $datos_cobro->cobros_id_perseo = $cobro_registrado->cobrosid_nuevo;
                $datos_cobro->cobros_cod_perseo = $cobro_registrado->codigo_nuevo;
                $data = ['detalle_pagos' => json_encode($datos_cobro), 'estado_pago' => 2];
                Factura::where('facturaid', $request->id_origen)->update($data);
            }

            $message->data = $cobro_registrado;
            return response()->json($message, 201);
        } catch (\Throwable $th) {
            $message->message = $th->getMessage();
            $message->status = $th->getCode() ?? 500;
            return response()->json($message, $message->status);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*           Funciones para el funcionamiento de la carga por lotes           */
    /* -------------------------------------------------------------------------- */

    private function DTO_nuevo_cobro($cobro)
    {
        $cobro = (object) $cobro;
        return (object)[
            'facturaid' => $cobro->facturaid,
            'secuencia' => $cobro->secuencia,
            'fecha' => date("d/m/Y", strtotime($cobro->fecha)),
            'numero_comprobante' => $cobro->numero_comprobante,
            'banco_destino' => $cobro->banco_destino,
            'banco_origen' => $cobro->banco_origen,
            'monto' => $cobro->monto,
            'origen' => $cobro->origen,
            'id_origen' => $cobro->id_origen,
        ];
    }

    private function obtener_cobros_restantes()
    {
        $cobrosCollect = collect([]);

        $facturas = Factura::select('facturaid', 'identificacion', 'concepto', 'facturado', 'facturaid_perseo', 'secuencia_perseo', 'estado_pago', 'detalle_pagos', 'total_venta', 'distribuidoresid', 'usuariosid', 'fecha_actualizado')
            ->where('facturado', 1)
            ->where('estado_pago', 1)
            ->whereNotNull('detalle_pagos')
            ->where('distribuidoresid', 1)
            ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(detalle_pagos, "$.cobros_id_perseo")) IS NULL')
            ->get();

        $cobrosCollect = $facturas->map(function ($factura) {
            $detallePagos = json_decode($factura->detalle_pagos);
            return $this->DTO_nuevo_cobro([
                'facturaid' => $factura->facturaid_perseo,
                'secuencia' => $factura->secuencia_perseo,
                'numero_comprobante' => $detallePagos->numero_comprobante,
                'banco_destino' => $detallePagos->banco_destino,
                'banco_origen' => $detallePagos->banco_origen,
                'monto' => $factura->total_venta,
                'origen' => 'factura',
                'id_origen' => $factura->facturaid,
                'fecha' => $factura->fecha_actualizado,
            ]);
        });

        $cobros = Cobros::select('cobros.cobrosid', 'cobros.banco_origen', 'cobros.banco_destino', 'cobros.numero_comprobante', 'cobros.estado', 'cobros.fecha_actualizacion', 'renovacion_licencias.renovacionid', 'renovacion_licencias.datos')
            ->where('cobros.distribuidoresid', 1)
            ->where('cobros.estado', 1)
            ->whereNotNull('cobros.renovacionid')
            ->join('renovacion_licencias', 'cobros.renovacionid', '=', 'renovacion_licencias.renovacionid')
            ->get();

        $cobros->each(function ($cobro) use ($cobrosCollect) {
            $datos = json_decode($cobro->datos);
            $nuevoCobro = $this->DTO_nuevo_cobro([
                'facturaid' => $datos->factura->facturaid,
                'secuencia' => $datos->factura->secuencia,
                'numero_comprobante' => $cobro->numero_comprobante,
                'banco_destino' => $cobro->banco_destino,
                'banco_origen' => $cobro->banco_origen,
                'monto' => $datos->factura->total_facturado,
                'origen' => 'renovacion',
                'id_origen' => $cobro->cobrosid,
                'fecha' => $cobro->fecha_actualizacion,
            ]);
            $cobrosCollect->push($nuevoCobro);
        });

        return $cobrosCollect->sortBy('facturaid');
    }

    private function procesar_csv($pathCSV)
    {
        $pagos = collect([]);

        if (!file_exists($pathCSV)) return $pagos;

        $file = fopen($pathCSV, 'r');

        $headers = array_map(function ($item) {
            return trim(strtolower($item));
        }, fgetcsv($file));

        while ($row = fgetcsv($file)) {
            $data = (object) array_combine($headers, $row);
            $documento = preg_replace('/^0+/', '', $data->documento);
            $monto = str_replace(',', '', $data->monto);
            $saldo = str_replace(',', '', $data->saldo);

            $data->documento = $documento;
            $data->monto = floatval($monto);
            $data->saldo = floatval($saldo);
            $pagos->push($data);
        }
        fclose($file);

        return $pagos;
    }
}
