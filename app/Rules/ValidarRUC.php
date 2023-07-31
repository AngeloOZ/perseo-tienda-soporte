<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidarRUC implements Rule
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
     * @param  string  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $ruc = trim($value);
        if (strlen($ruc) == 13 && $ruc != "") {
            $subcad = substr($ruc, 10, 13);
            if($subcad == "001"){
                return true;
            }
            return false;
        } else {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Debe ingresar un RUC válido';
    }
}
