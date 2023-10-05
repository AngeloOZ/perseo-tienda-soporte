<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacionVendedores extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $array;

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
        if ($this->array['tipo'] == 'incorrecto') {
            return $this->view('emails.vendedor_incorrecto')
                ->from($this->array['from'], $this->array['fromName'])
                ->subject($this->array['subject']);
        } else {
            return $this->view('emails.vendedor_correcto')
                ->from($this->array['from'], $this->array['fromName'])
                ->subject($this->array['subject']);
        }
    }
}
