<?php

namespace App\Http\Controllers;

use App\Mail\EnviarClave;
use App\Models\Clientes;
use App\Models\Log;
use App\Models\Planificaciones;
use App\Models\PlanificacionesDetalles;
use App\Models\Productos2;
use App\Models\Sesiones;
use App\Models\Tecnicos;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables as DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Rules\ValidarCelular;
use App\Rules\ValidarCorreo;
use Carbon\Carbon;

class clientesController extends Controller
{
    /* -------------------------------------------------------------------------- */
    /*                       Funciones para el administrador                      */
    /* -------------------------------------------------------------------------- */

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Clientes::select('clientesid', 'identificacion', 'razonsocial', 'nombrecomercial', 'correo', 'celular', 'estado')
                ->when($request->estado, function ($query, $estado) {
                    if ($estado == 1) {
                        return $query->where('clientes.estado', 1);
                    } else {
                        return $query->where('clientes.estado', 0);
                    }
                })
                ->get();


            return DataTables::of($data)
                ->editColumn('estado', function ($cliente) {
                    if ($cliente->estado == 1) {
                        return 'Activo';
                    } else {
                        return 'Inactivo';
                    }
                })
                ->editColumn('action', function ($cliente) {
                    return '<a class="btn btn-sm btn-clean btn-icon" href="' . route('clientes.editar', $cliente->clientesid) . '" title="Editar"> <i class="la la-edit"></i> </a>' . '<a class="btn btn-sm btn-clean btn-icon confirm-delete" href="javascript:void(0)" data-href="' . route('clientes.eliminar', $cliente->clientesid) . '" title="Eliminar"> <i class="la la-trash"></i> </a>';
                })
                ->rawColumns(['action', 'estado'])
                ->make(true);
        }

        return view('soporte.admin.capacitaciones.clientes.index');
    }

    public function crear()
    {
        $clientes = new Clientes();
        return view('soporte.admin.capacitaciones.clientes.crear', compact('clientes'));
    }

    public function guardar(Request $request)
    {
        $request->validate(
            [
                'identificacion' => ['required', 'unique:clientes'],
                'razonsocial' => ['required'],
                'nombrecomercial' => ['required'],
                'correo' => ['required', 'email', new ValidarCorreo],
                'celular' => ['required', new  ValidarCelular],
                'estado' => ['required'],
                'clave' => ['required'],
                'distribuidoresid' => ['required'],
            ],
            [
                'identificacion.required' => 'Ingrese su cédula o RUC ',
                'identificacion.unique' => 'Su cédula o RUC ya se encuentra registrado',
                'razonsocial.required' => 'Ingrese la razon social',
                'nombrecomercial.required' => 'Ingrese el nombre comercial',
                'correo.required' => 'Ingrese un correo',
                'correo.email' => 'Ingrese un correo válido',
                'celular.required' => 'Ingrese un celular',
                'estado.required' => 'Ingrese el estado',
                'clave.required' => 'Ingrese una clave',
                'distribuidoresid.required' => 'Escoja un distribuidor',
            ],
        );

        DB::beginTransaction();
        try {
            $clientes = $request->all();
            $clientes['clave'] = encrypt_openssl($request->clave, 'Perseo1232*');
            $clientes['estado'] = $request->estado == null || $request->estado == 0 ? 0 : 1;
            $clientes['fechacreacion'] = now();
            $clientes['usuariocreacion'] = Auth::guard('tecnico')->user()->nombres;
            $clientes = Clientes::create($clientes);


            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Clientes";
            $historial->operacion = "Crear";
            $historial->fecha = now();
            $historial->detalle = $clientes;
            $historial->save();

            $array['view'] = 'cliente.emails.enviarClave';
            $array['from'] = env('MAIL_FROM_ADDRESS');
            $array['subject'] = 'Capacitación clave';
            $array['clave'] = $request->clave;
            $emailsEnviar = $request->correo;
            Mail::to($emailsEnviar)->queue(new EnviarClave($array));

            DB::commit();

            flash('Guardado Correctamente')->success();
            return redirect()->route('clientes.index');
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->error();
            return back();
        }
    }

    public function editar(Clientes $clientes)
    {
        return view('soporte.admin.capacitaciones.clientes.editar', compact('clientes'));
    }

    public function actualizar(Request $request, Clientes $clientes)
    {
        $request->validate(
            [

                'identificacion' => ['required', 'unique:clientes,identificacion,' . $clientes->clientesid . ',clientesid'],
                'razonsocial' => ['required'],
                'nombrecomercial' => ['required'],
                'correo' => ['required', 'email', new ValidarCorreo],
                'celular' => ['required', new  ValidarCelular],
                'estado' => ['required'],
                'distribuidoresid' => ['required'],
            ],
            [
                'identificacion.required' => 'Ingrese su cédula o RUC ',
                'identificacion.unique' => 'Su cédula o RUC ya se encuentra registrado',
                'razonsocial.required' => 'Ingrese la razon social',
                'nombrecomercial.required' => 'Ingrese el nombre comercial',
                'correo.required' => 'Ingrese un correo',
                'correo.email' => 'Ingrese un correo válido',
                'celular.required' => 'Ingrese un celular',
                'estado.required' => 'Ingrese el estado',
                'distribuidoresid.required' => 'Escoja un distribuidor',
            ],
        );
        DB::beginTransaction();
        try {
            if ($request->clave != null) {
                $request['clave'] = encrypt_openssl($request->clave, 'Perseo1232*');
            } else {
                $request['clave'] = $clientes->clave;
            }
            $request['estado'] = $request->estado == null || $request->estado == 0 ? 0 : 1;
            $request['fechamodificacion'] =  now();
            $request['usuariomodificacion'] = Auth::guard('tecnico')->user()->nombres;
            $clientes->update($request->all());

            //Asignacion masiva
            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Clientes";
            $historial->operacion = "Modificar";
            $historial->fecha = now();
            $historial->detalle = $clientes;
            $historial->save();
            DB::commit();
            flash('Actualizado Correctamente')->success();
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
        }
        return back();
    }

    public function eliminar(Clientes $clientes)
    {
        DB::beginTransaction();
        try {
            $eliminarCliente = Clientes::where('clientesid', $clientes->clientesid)->first();

            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Clientes";
            $historial->operacion = "Eliminar";
            $historial->fecha = now();
            $historial->detalle = $eliminarCliente;
            $historial->save();

            $eliminarCliente->delete();
            DB::commit();
            flash('Eliminado Correctamente')->success();
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
        }
        return back();
    }

    /* -------------------------------------------------------------------------- */
    /*                          Funciones para el cliente                         */
    /* -------------------------------------------------------------------------- */
    public function login()
    {
        return view('cliente.auth.login');
    }

    public function post_login(Request $request)
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

        $identificacion = $request->identificacion;

        $cliente = Clientes::where('identificacion', $identificacion)
            ->where('estado', 1)
            ->first();

        if (!$cliente) {
            flash('El Usuario o clave son incorrectos')->error();
            return back();
        }

        if ($cliente->clave != encrypt_openssl($request->clave, 'Perseo1232*')) {
            flash('El Usuario o clave son incorrectos')->error();
            return back();
        }

        Auth::guard('cliente')->login($cliente, false);
        $request->session()->regenerate();
        return redirect()->route('sesiones.indexVistaCliente');
    }

    public function logout()
    {
        if (Auth::guard('cliente')->check()) {
            Auth::guard('cliente')->logout();
            return  view('cliente.auth.login');
        }
    }

    /* actualizar clave */
    public function cambiarClaveCliente()
    {
        return view('cliente.auth.cambiarClaveCliente');
    }

    public function guardarClaveCliente(Request $request)
    {
        $request->validate(
            [
                'de_clave' => 'required|confirmed',
            ],
            [
                'de_clave.required' => 'Ingrese su contraseña',
                'de_clave.confirmed' => 'La contraseña no coincide',
            ],
        );

        DB::beginTransaction();
        try {
            $cliente = Clientes::findOrFail(Auth::guard('cliente')->user()->clientesid);
            if ($request->de_clave != null) {
                if ($request->de_clave == $request->de_clave_confirmation) {

                    $claveFinal = encrypt_openssl($request->de_clave, 'Perseo1232*');
                    $cliente->clave = $claveFinal;
                } else {
                    flash('Las contraseñas no coinciden')->error();
                    return back();
                }
            }
            $cliente->save();
            DB::commit();
            flash('Contraseña Actualizada Correctamente')->success();
            return redirect()->route('sesiones.indexVistaCliente');
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
        }
        return back();
    }

    /* sesiones cliente */
    public function indexVista(Request $request)
    {
        return view('cliente.sesiones.index');
    }

    public function indexSesiones(Request $request)
    {
        if ($request->ajax()) {
            $fecha = $request->fecha;
            $tipo = $request->tipo;

            $consulta = Sesiones::select('sesiones.sesionesid', 'sesiones.suma', 'sesiones.enlace', 'sesiones.descripcion', 'sesiones.fechainicio', 'sesiones.fechafin', 'tecnicos.nombres as creador', 'clientes.razonsocial as clientesid', 'productos2.descripcion as productosid', 'planificaciones.planificacionesid')
                ->join('tecnicos', 'tecnicos.tecnicosid', 'sesiones.tecnicosid')
                ->join('clientes', 'clientes.clientesid', 'sesiones.clientesid')
                ->join('planificaciones', 'planificaciones.planificacionesid', 'sesiones.planificacionesid')
                ->join('productos2', 'productos2.productosid', 'planificaciones.productosid');

            $data = $consulta->where('sesiones.clientesid',  Auth::guard('cliente')->user()->clientesid)->get();


            if ($tipo != null) {
                $tipo_fecha = $tipo == 1 ? "sesiones.fechainicio" : "sesiones.fechafin";
                //Si existe fecha en el filtro agrega condicion
                if ($fecha) {
                    $desde = date('Y-m-d', strtotime(explode(" / ", $fecha)[0]));
                    $hasta = date('Y-m-d', strtotime(explode(" / ", $fecha)[1]));

                    $data = $consulta->where('sesiones.clientesid',  Auth::guard('cliente')->user()->clientesid);
                    $data = $data->whereBetween(DB::raw("DATE($tipo_fecha)"), [$desde, $hasta])->get();
                }
            }
            return DataTables::of($data)

                ->editColumn('action', function ($sesiones) {
                    $planificaciones = Planificaciones::select('revisioncliente')->where('planificacionesid', $sesiones->planificacionesid)->first();

                    if ($planificaciones->revisioncliente == 1) {
                        return '<a class="btn btn-sm btn-clean btn-icon" href="' . route('sesiones.ver', $sesiones->sesionesid) . '" title="Planificación"> <i class="la la-eye"></i> </a>' . '<a class="btn btn-sm btn-clean btn-icon" href="' . $sesiones->enlace . '" title="Videoconferencia" target="_blank" > <i class="la la-link"></i> </a>';
                    } else {
                        return '<a class="btn btn-sm btn-clean btn-icon confirm-sesion" href="javascript:void(0)" data-href="' . $sesiones->sesionesid . '" title="Ver"> <i class="la la-eye"></i> </a>';
                    }
                })
                ->editColumn('fechainicio', function ($sesiones) {
                    if ($sesiones->fechainicio != null) {
                        return Carbon::parse($sesiones->fechainicio)->format('d-m-Y H:i:s');
                    } else {
                        return '';
                    }
                })
                ->editColumn('fechafin', function ($sesiones) {
                    if ($sesiones->fechafin != null) {
                        return Carbon::parse($sesiones->fechafin)->format('d-m-Y  H:i:s');
                    } else {
                        return '';
                    }
                })
                ->rawColumns(['action', 'fechainicio', 'fechafin'])
                ->make(true);
        }

        return view('cliente.sesiones.index');
    }

    public function verificar(Request $request)
    {
        if ($request->ajax()) {
            if ($request->sesiones > 0) {

                $recuperar = Sesiones::select('planificacionesid')
                    ->where('sesionesid', $request->sesiones)
                    ->first();

                $detalles = PlanificacionesDetalles::select('productos2.tipo', 'categorias.descripcion AS categorias', 'categorias.categoriasid', 'temas.descripcion AS temas', 'categorias.categoriasid AS identificador', 'temas.temasid', 'planificaciones_detalles.calificacioncliente', 'temas.enlace_tutorial', 'temas.enlace_tutorialWeb')
                    ->join('temas', 'temas.temasid', 'planificaciones_detalles.temasid')
                    ->join('subcategorias', 'subcategorias.subcategoriasid', 'temas.subcategoriasid')
                    ->join('categorias', 'categorias.categoriasid', 'subcategorias.categoriasid')
                    ->join('planificaciones', 'planificaciones.planificacionesid', 'planificaciones_detalles.planificacionesid')->join('productos2', 'planificaciones.productosid', 'productos2.productosid')
                    ->where('planificaciones_detalles.planificacionesid', $recuperar->planificacionesid)
                    ->orderBy('temas.orden')
                    ->get();

                return DataTables::of($detalles)
                    ->editColumn('calificacion', function ($sesiones) {
                        return '<div class="checkCat-' . $sesiones->categoriasid . '""> <div class="d-flex flex-column flex-sm-row"> 
                            <div class="form-check ">' .
                            '<input class="form-check-input radio-calificacion" type="radio"  style="width: 18px; height: 18px;" name="calificacion-' . $sesiones->temasid . '" id="calificacion0-' . $sesiones->temasid . '" value="0"    ' . ($sesiones->calificacioncliente == 0 ? 'checked' : '') . '>' .
                            '<label class="form-check-label ml-5" for="calificacion1">No calificado</label>' .
                            '</div>' .
                            '<div class="form-check mt-3 mt-sm-0 ml-md-5">' .
                            '<input class="form-check-input radio-calificacion" type="radio" style="width: 18px; height: 18px;"  name="calificacion-' . $sesiones->temasid . '" id="calificacion1-' . $sesiones->temasid . '" value="1"  ' . ($sesiones->calificacioncliente == 1 ? 'checked' : '') . '>' .
                            '<label class="form-check-label ml-5" for="calificacion2">Aprendí</label>' .
                            '</div>' .
                            '<div class="form-check  mt-3 mt-sm-0 ml-md-5">' .
                            '<input class="form-check-input radio-calificacion" type="radio" style="width: 18px; height: 18px;" name="calificacion-' . $sesiones->temasid . '" id="calificacion2-' . $sesiones->temasid . '" value="2" ' . ($sesiones->calificacioncliente == 2 ? 'checked' : '') . '>' .
                            '<label class="form-check-label ml-5" for="calificacion3">Ya sabía</label>' .
                            '</div> </div> </div>';
                    })
                    ->editColumn('youtube', function ($sesiones) {
                        if (($sesiones->tipo == 1 && $sesiones->enlace_tutorial != "") || ($sesiones->tipo == 2 && $sesiones->enlace_tutorialWeb != ""))
                            return '<div  class="text-center" > <a class="btn btn-sm btn-clean btn-icon" target="_blank" title="Youtube" href="' . ($sesiones->tipo == 1 ? $sesiones->enlace_tutorial : $sesiones->enlace_tutorialWeb) . '"> <i class="la la-youtube fa-2x"></i> </a>   </div> ';
                    })
                    ->rawColumns(['calificacion', 'youtube'])
                    ->make(true);
            }
        }
        return view('cliente.sesiones.index');
    }

    public function guardarRevision(Request $request)
    {
        $sesiones = Sesiones::select('planificacionesid')
            ->where('sesionesid', $request->sesionesid)
            ->first();

        $planificaciones = Planificaciones::where('planificacionesid', $sesiones->planificacionesid)
            ->first();

        $planificaciones->revisioncliente = 1;

        if ($planificaciones->save()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function sesionesVer($sesionesid)
    {
        $sesiones = Sesiones::where('sesionesid', $sesionesid)->first();

        $planificaciones = Planificaciones::select('planificacionesid', 'descripcion', 'productosid', 'tecnicosid', 'revisioncliente')
            ->where('planificacionesid', $sesiones->planificacionesid)
            ->first();

        $productos = Productos2::select('descripcion')
            ->where('productosid', $planificaciones->productosid)
            ->first();
        $tecnicos = Tecnicos::select('nombres')
            ->where('tecnicosid', $planificaciones->tecnicosid)
            ->first();

        return view('cliente.sesiones.ver', compact('sesiones', 'planificaciones', 'productos', 'tecnicos'));
    }

    public function guardarCalificacion(Request $request)
    {
        $detalles = PlanificacionesDetalles::where('planificacionesid', $request->planificaciones)->where('temasid', $request->temas)->first();
        $detalles->calificacioncliente = $request->calificacion;
        $detalles->save();
    }
}
