<?php

namespace App\Listeners;

use App\Events\NuevoRegistroSopEsp;
use App\Mail\NotificacionCapacitacion;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotificarRgistroSopEspListner
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        // dd("Se creo NotificarRgistroSopEspListner");
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\NuevoRegistroSopEsp  $event
     * @return void
     */
    public function handle(NuevoRegistroSopEsp $event)
    {
        $subject = "Nuevo registro de soporte especial";

        if ($event->soporteAnterior) {
            $subject = "NOTIFICACION: Nuevo plan convertido";
        }
        $soporte = $event->soporteEspecial;
        $factura = $event->factura;

        $productos = json_decode($factura->productos);
        $listProduct = [];

        foreach ($productos as $producto) {
            $productoAux = Producto::find($producto->productoid, ["descripcion"]);
            array_push($listProduct, $productoAux->descripcion);
        }

        $vendedor = User::firstWhere('usuariosid', $factura->usuariosid);

        // $nombreRevisor = "Katherine Sarabia";
        // $mailRevisor = "katherine.sarabia@perseo.ec";

        $nombreRevisor = "Test Notification";
        $mailRevisor = "desarrollo@perseo.ec";

        $array = [
            'from' => "noresponder@perseo.ec",
            'subject' => $subject,
            'revisora' => $nombreRevisor,
            'asesor' => $vendedor->nombres,
            'ruc' => $soporte->ruc,
            'razon_social' => $soporte->razon_social,
            'correo' => $soporte->correo,
            'whatsapp' => $soporte->whatsapp,
            'planes' => $listProduct,
        ];

        Mail::to($mailRevisor)->queue(new NotificacionCapacitacion($array));
    }
}
