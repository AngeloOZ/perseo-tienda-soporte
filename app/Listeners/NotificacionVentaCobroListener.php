<?php

namespace App\Listeners;

use App\Events\NotificacionNuevoVentaCobro;
use App\Mail\NotificacionVentaCobro;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotificacionVentaCobroListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\NotificacionNuevoVentaCobro  $event
     * @return void
     */
    public function handle(NotificacionNuevoVentaCobro $event)
    {
        $factura = $event->factura;
        $revisor = User::where('distribuidoresid', $factura->distribuidoresid)->where('rol', 2)->first();

        if (!$revisor) return;


        $array = [
            'from' => "noresponder@perseo.ec",
            'subject' => "Nuevo venta y cobro registrado",
            'revisora' => $revisor->nombres,
            'ruc' => $factura->identificacion,
            'razon_social' => $factura->nombre,
            'correo' => $factura->correo,
        ];

        Mail::to($revisor->correo)->queue(new NotificacionVentaCobro($array));
    }
}
