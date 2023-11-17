<?php

namespace App\Listeners;

use App\Events\RegistrarCobro;
use App\Models\Cobros;
use App\Models\Factura;
use App\Models\RenovacionLicencias;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class ListenerRegistroCobro
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    private $client;
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\RegistrarCobro  $event
     * @return void
     */
    public function handle(RegistrarCobro $event)
    {
        $request = $event->request;
        $esFactura = $event->esFactura;

        if ($esFactura) {
            $factura = Factura::findOrFail($request->facturaid, ['facturaid', 'facturaid_perseo', 'secuencia_perseo', 'total_venta', 'detalle_pagos', 'usuariosid', 'estado_pago']);
            
            $datos_cobro = json_decode($factura->detalle_pagos);
            $datos_cobro->forma_pago = $request->forma_pago;
            $datos_cobro->monto = $request->monto;
            $datos_cobro->fecha = date("Ymd", strtotime($request->fecha));

            $facturaid = $factura->facturaid_perseo;
        } else {
            $cobro = Cobros::findOrFail($request->cobrosid, ['banco_destino', 'numero_comprobante', 'renovacionid', 'estado', 'cobros_id_perseo']);
            
            $renovaciones = RenovacionLicencias::findOrFail($cobro->renovacionid, ['renovacionid', 'datos']);

            $datos = json_decode($renovaciones->datos);

            $facturaid = $datos->factura->facturaid;
            $datos_cobro = (object)[
                'numero_comprobante' => $cobro->numero_comprobante,
                'banco_destino' => $cobro->banco_destino,
                'banco_origen' => $cobro->banco_origen,
                'forma_pago' => $request->forma_pago,
                'monto' => $request->monto,
                'fecha' => date("Ymd", strtotime($request->fecha)),
            ];
        }

        $factura_perseo = $this->obtener_factura_perseo($facturaid);
        $cobro_registrado = $this->registro_del_cobro($factura_perseo, $datos_cobro);
        $datos_cobro->cobros_id_perseo = $cobro_registrado->cobrosid_nuevo;
        $datos_cobro->cobros_cod_perseo = $cobro_registrado->codigo_nuevo;

        if ($esFactura) {
            $factura->update(['detalle_pagos' => json_encode($datos_cobro), 'estado_pago' => 2]);
        } else {
            $data = [
                'estado' => 2,
                'cobros_id_perseo' => json_encode([
                    'cobros_id_perseo' => $datos_cobro->cobros_id_perseo,
                    'cobros_cod_perseo' => $datos_cobro->cobros_cod_perseo,
                    'forma_pago' => $datos_cobro->forma_pago,
                    'monto' => $datos_cobro->monto,
                ]),
            ];
            Cobros::where('cobrosid', $request->cobrosid)->update($data);
        }
    }

    private function obtener_factura_perseo($facturaid)
    {
        try {
            $url = Auth::user()->api;

            $body = [
                "api_key" => Auth::user()->token,
                "facturaid" => $facturaid,
            ];

            $factura = $this->client->post($url . "/facturas_consulta", ["json" => $body]);
            $factura = json_decode($factura->getBody()->getContents());

            return $factura->facturas[0];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function registro_del_cobro($factura, $datos_cobro)
    {
        $fechaActual = date("Ymd");
        try {
            $cobro = [
                'api_key' => Auth::user()->token,
                'registros' => [
                    0 => [
                        'cobros' => [
                            'clientesid' => $factura->clientesid,
                            'cobroscodigo' => '1', // Default
                            'cobradoresid' => Auth::user()->vendedoresid,
                            'tipo' => 'AB', // Default
                            'movimientos_conceptosid' => 3, //Default 
                            'forma_pago_empresaid' => $datos_cobro->forma_pago,
                            'concepto' => $factura->concepto,
                            'fechaemision' => $datos_cobro->fecha,
                            'fecharecepcion' => $datos_cobro->fecha,
                            'fechavencimiento' => $datos_cobro->fecha,
                            'importe' => floatval($datos_cobro->monto),
                            'cajasid' => Auth::user()->cajasid,
                            'bancosid' => $datos_cobro->banco_destino,
                            'usuariocreacion' => Auth::user()->identificacion,
                            'usuarioid' => Auth::user()->vendedoresid,
                            'detalles' => [
                                0 => [
                                    'bancoid' => 0, // Solo si es cheque o TC
                                    'cajasid' => $datos_cobro->banco_destino,
                                    'comprobante' => $datos_cobro->numero_comprobante,
                                    'importe' => floatval($datos_cobro->monto),
                                    'documentosid' => $factura->facturasid,
                                    'formapago' => $datos_cobro->forma_pago,
                                    'saldo' => 0, // Default
                                    'fechaemision' => $datos_cobro->fecha,
                                    'fecharecepcion' => $datos_cobro->fecha,
                                    'fechavence' => $datos_cobro->fecha,
                                    'fechavenceCH' => $datos_cobro->fecha,
                                    'secuencia' => $factura->secuencial,
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $request = $this->client->post(Auth::user()->api . "/cobros_crear", ["json" => $cobro]);
            $response = json_decode($request->getBody()->getContents());
            $response_cobro = $response->cobros[0];
            return $response_cobro;
        } catch (\Throwable $th) {
            throw new \Exception("el servicio API fallo");
        }
    }
}
