<?php

namespace App\Rules;

use DateTime;
use Illuminate\Contracts\Validation\Rule;
use PhpParser\Node\Stmt\TryCatch;

class validarFecha implements Rule
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
        try {
            $fecha_nacimiento = new DateTime($value);
            $currentDay = new DateTime("today");
            $dias = date_diff($fecha_nacimiento, $currentDay);
            $anios = intval($dias->format('%y'));
            if($anios < 18){
                return false;
            }
            return true;
        } catch (\Throwable $th) {
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
        return 'La fecha es de un menor de edad';
    }
}
