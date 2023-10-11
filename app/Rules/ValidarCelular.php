<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class ValidarCelular implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        if (strlen($value) < 10) return false;

        //consultar api1
        $url = 'https://phonevalidation.abstractapi.com/v1/?api_key=7678748c57244785bc99109520e35d5f&phone=+593' . $value;
        $celular = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false,])
            ->withOptions(["verify" => false])
            ->get($url)
            ->json();

        if (isset($celular['error'])) return false;

        if ($celular['valid'] == false) return false;

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Ingrese un Celular Válido.';
    }
}
