<?php

namespace App\Http\Controllers;

use App\Models\Tecnicos;
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

        $identificacion = trim($request->identificacion);
        $tecnico = Tecnicos::where('identificacion', $identificacion)->where('estado', 1)->first();

        if (!$tecnico) {
            flash('Usuario Incorrecto o Usuario Inactivo')->error();
            return back();
        }

        if ($tecnico->clave !== $request->clave) {
            flash('Usuario o Contraseña Incorrectos')->error();
            return back();
        }

        Auth::guard('tecnico')->login($tecnico, false);
        $request->session()->regenerate();

        $estadoWhatsapp = new WhatsappController();
        if (!$estadoWhatsapp->obtener_estado()) {
            flash("El servicio de WhatsAapp está desconectado")->important();
        }

        if (Auth::guard('tecnico')->user()->rol == 5) {
            $hora = strtotime(date('G:i'));
            $inicio = strtotime('08:00');
            $fin = strtotime('16:55');
            if ($hora >= $inicio && $hora <= $fin) {
                $tecnico->fecha_de_ingreso = now();
                $tecnico->activo = 1;
                $tecnico->save();
            }
        }

        return $this->redirect_by_rol();
    }

    public function logout_tecnico(Request $request){
        Tecnicos::where('tecnicosid', Auth::guard('tecnico')->user()->tecnicosid)->update(['activo' => 0, 'fecha_de_salida' => now()]);
        Auth::guard('tecnico')->logout();
        return redirect()->route('soporte.auth.login');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function redirect_by_rol()
    {
        switch (Auth::guard('tecnico')->user()->rol) {
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

    public function clave(){
        return view('soporte.auth.cambiarclave');
    }

    public function actualizar_clave(Request $request)
    {
        $request->validate(
            [
                'clave_confirmacion' => 'required',
                'clave' => 'required',
            ],
            [
                'clave.required' => 'Ingrese su contraseña',
                'clave_confirmacion.required' => 'Ingrese la verificación de la contraseña ',
            ],
        );


        if ($request->clave == $request->clave_confirmacion) {

            $usuarios = Tecnicos::where('tecnicosid', Auth::guard('tecnico')->user()->tecnicosid)->first();

            $usuarios->clave = $request->clave;
            if ($usuarios->save()) {
                flash('La contraseña se ha guardado correctamente')->success();
            } else {
                flash('Ocurrió un error, vuelva a intentarlo')->error();
            }
            return back();
        } else {
            flash('La verificación de la contraseña no coincide')->error();
            return back();
        }
    }
}
