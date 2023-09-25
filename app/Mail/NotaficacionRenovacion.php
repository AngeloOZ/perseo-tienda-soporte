<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotaficacionRenovacion extends Mailable implements ShouldQueue
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
        return $this->view('emails.notificar_renovacion')
            ->from(env('MAIL_FROM_ADDRESS'), $this->array['from'])
            ->subject($this->array['subject'])
            ->attach($this->array['tempFilePath'], [
                'as' => "factura_" . $this->array['secuencia'] . '.pdf',
                'mime' => "application/pdf",
            ]);
    }
}
