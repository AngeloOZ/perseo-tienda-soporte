<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class adminController extends Controller
{

    public function cambiarMenu(Request $request)
    {
        Session::put('menu', $request->estado);
    }


    public function verificarEmailCelular(Request $request)
    {
        $verificacionemail = 0;
        $verificacioncelular = 0;
        $verificacioncelular2 = 0;

        if ($request->correo != "") {
            $verificacionemail = $this->validarEmail($request->correo);
        }

        if ($request->celular != "") {
            $verificacioncelular = $this->validarCelular($request->celular);
        }

        if (isset($request->celular2) && $request->celular2 != "") {
            $verificacioncelular2 = $this->validarCelular($request->celular2);
        }

        return [$verificacionemail, $verificacioncelular, $verificacioncelular2];
    }

    private function validarEmail($email)
    {
        $url = 'https://emailvalidation.abstractapi.com/v1/?api_key=fae435e4569b4c93ac34e0701100778c&email=' . $email;

        $correo = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false])
            ->withOptions(["verify" => false])
            ->get($url)
            ->json();

        if ($correo['deliverability'] == "DELIVERABLE") return 1;

        if ($correo['is_valid_format']['value'] == false) return 0;

        //consultar api2 si es hotmail
        $url = 'https://api.debounce.io/v1/?email=' . rawurlencode($email) . '&api=6269b53f06aeb';
        $correo = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false])
            ->withOptions(["verify" => false])
            ->get($url)
            ->json();

        if (!isset($correo['debounce']['reason'])) return 0;

        $resultCorreo = $correo['debounce']['reason'];

        if ($resultCorreo == "Deliverable" || $resultCorreo == "Deliverable, Role") return 1;

        return 0;
    }

    private function validarCelular($phone)
    {
        $url = 'https://phonevalidation.abstractapi.com/v1/?api_key=7678748c57244785bc99109520e35d5f&phone=593' . $phone;
        $celular = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false,])
            ->withOptions(["verify" => false])
            ->get($url)
            ->json();

        if (isset($celular['error'])) return 0;

        if ($celular['valid'] == false) return 0;

        return 1;
    }
}
