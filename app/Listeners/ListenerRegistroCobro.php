<?php

namespace App\Listeners;

use App\Events\RegistrarCobro;
use App\Models\Cobros;
use App\Models\Factura;
use App\Models\RenovacionLicencias;
use App\Services\RegistroCobrosSistema;
use Exception;
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
    private $servicioCobros;
    public function __construct()
    {
        $this->client = new Client();
        $this->servicioCobros = new RegistroCobrosSistema();
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

        $factura_perseo = $this->servicioCobros->obtener_factura_perseo($facturaid);
        $cobro_registrado = $this->servicioCobros->registro_del_cobro($factura_perseo, $datos_cobro);
        
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
}
