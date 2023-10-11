<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class ValidarCorreo implements Rule
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
        if (strlen($value) < 4) return false;

        $url = 'https://emailvalidation.abstractapi.com/v1/?api_key=fae435e4569b4c93ac34e0701100778c&email=' . $value;
        $correo = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false,])
            ->withOptions(["verify" => false])
            ->get($url)
            ->json();

        if ($correo['deliverability'] == "DELIVERABLE") return true;

        if ($correo['is_valid_format']['value'] == false) return false;

        //consultar api2 si es hotmail
        $url = 'https://api.debounce.io/v1/?email=' . rawurlencode($value) . '&api=6269b53f06aeb';
        $correo = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false,])
            ->withOptions(["verify" => false])
            ->get($url)
            ->json();

        if (!isset($correo['debounce']['reason'])) return false;

        $resultCorreo = $correo['debounce']['reason'];
        if ($resultCorreo == "Deliverable" || $resultCorreo == "Deliverable, Role") return true;

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Ingrese un Correo Válido';
    }
}
