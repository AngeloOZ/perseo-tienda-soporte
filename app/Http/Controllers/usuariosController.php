<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class usuariosController extends Controller
{

    public function validarVendedor($id_vendedor)
    {
        $user = User::findOrFail($id_vendedor);
        return view('firma.firma', ["user" => $user]);
    }

    public function datosFacturacion($id_vendedor)
    {
        $user = User::findOrFail($id_vendedor);
        return view('firma.facturacion', ["user" => $user]);
    }

    public function redirect_login()
    {
        return redirect()->route('auth.login');
    }

    public function vista_login()
    {
        return view('auth.login');
    }

    public function login(Request $request)
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

        $identificacionIngresada = $request->identificacion;
        $usuarios = User::where('identificacion', $identificacionIngresada)->where('estado', 1)->first();

        if ($usuarios) {
            if ($usuarios->clave === $request->clave) {
                Auth::guard()->login($usuarios, false);
                $request->session()->regenerate();
                switch (Auth::user()->rol) {
                    case 1:
                        return redirect()->route('firma.listado');
                        break;
                    case 2:
                        $estadoWhatsapp = new WhatsappRenovacionesController();
                        if (!$estadoWhatsapp->obtener_estado()) {
                            flash("El servicio de WhatsAapp está desconectado")->important();
                        }
                        return redirect()->route('facturas.revisor');
                        break;
                    case 3:
                        return redirect()->route('productos.listado');
                        break;
                    case 4:
                        return redirect()->route('firma.revisor');
                        break;
                }
            } else {
                flash('Usuario o Contraseña Incorrectos')->error();
                return back();
            }
        } else {
            flash('Usuario Incorrecto o Usuario Inactivo')->error();
            return back();
        }
    }

    public function cambiar_clave()
    {
        return view('auth.cambiarclave');
    }

    public function clave(Request $request)
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

            $usuarios = User::where('usuariosid', Auth::user()->usuariosid)->first();

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

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('auth.login');
    }
}
