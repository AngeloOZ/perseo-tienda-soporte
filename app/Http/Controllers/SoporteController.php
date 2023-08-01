<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSoporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SoporteController extends Controller
{
    public function render_login()
    {
        return view('soporte.auth.login');
    }

    public function login_soporte(Request $request)
    {
        $request->validate(
            [
                'identificacion' => 'required',
                'clave' => 'required',
            ],
            [
                'identificacion.required' => 'Ingrese su cédula o RUC ',
                'clave.required' => 'Ingrese su contraseña',
            ],
        );
        $identificacion = trim($request->identificacion) . '-SOP';
        $usuario = User::where('identificacion', $identificacion)->where('estado', 1)->where('rol', '>=', '5')->first();

        if (!$usuario) {
            flash('Usuario Incorrecto o Usuario Inactivo')->error();
            return back();
        }

        if ($usuario->clave !== $request->clave) {
            flash('Usuario o Contraseña Incorrectos')->error();
            return back();
        }

        Auth::guard()->login($usuario, false);
        $request->session()->regenerate();

        $estadoWhatsapp = new WhatsappController();
        if (!$estadoWhatsapp->obtener_estado()) {
            flash("El servicio de WhatsAapp está desconectado")->important();
        }

        if (Auth::user()->rol == 5) {
            $hora = strtotime(date('G:i'));
            $inicio = strtotime('08:00');
            $fin = strtotime('16:55');
            if ($hora >= $inicio && $hora <= $fin) {
                UserSoporte::where('usuariosid', Auth::user()->usuariosid)->update(['estado' => 1, 'fecha_de_ingreso' => now()]);
            }
        }

        return $this->redirect_by_rol();
    }

    public function redirect_by_rol()
    {
        switch (Auth::user()->rol) {
            case 5:
                return redirect()->route('soporte.listado.activos');
                break;
            case 6:
                return redirect()->route('soporte.listado.revidor.desarrollo');
                break;
            case 7:
                return redirect()->route('soporte.listado.revisor');
                break;
            case 8:
                return redirect()->route('soporte.filtrado_reporte_soporte');
                break;
            case 9:
                return redirect()->route('especiales.listado_supervisor');
                break;
        }
    }
}
