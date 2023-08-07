<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacionEstadoCapacitacion extends Mailable implements ShouldQueue
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
        return $this->view('emails.notificacion_estado_spe')
            ->from($this->array['from'], "Perseo Capacitaciones")
            ->subject($this->array['subject']);
    }
}
