<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacionVentaCobro extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $array;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public function __construct($array)
    {
        $this->array = $array;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.notificacion_revisor_factura')
            ->from($this->array['from'], "Perseo notificaciones ventas")
            ->subject($this->array['subject']);
    }
}
