<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Sesiones extends Mailable
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

          return $this->view('frontend.emails.temasSesiones')
               ->from($this->array['from'], env('MAIL_FROM_NAME'))
               ->subject($this->array['subject'])->attach(public_path() . '/generados/sesion-'.$this->array['tema'].'.pdf');
     }
}
