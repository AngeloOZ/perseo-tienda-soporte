<?php

namespace App\Http\Controllers;

// use App\Models\Categorias;
// use App\Models\Clientes;
// use App\Models\Implementaciones;

use App\Mail\EnviarClave;
use App\Models\Clientes;
use App\Models\Log;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables as DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
// use App\Mail\EnviarClave;
// use App\Models\Actividades;
// use App\Models\Colaboradores;
// use App\Models\Cotizaciones;
// use App\Models\Log;
// use App\Models\implementacionesDocumentos;
// use App\Models\Planificaciones;
// use App\Models\PlanificacionesDetalles;
// use App\Models\Plantillas;
// use App\Models\Productos;
// use App\Models\Sesiones;
// use App\Models\Tecnicos;
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
                // ->where('distribuidoresid', Auth::guard('tecnico')->user()->distribuidoresid)
                // ->where('subdistribuidoresid', Auth::guard('tecnico')->user()->subdistribuidoresid)
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
            // $eliminar = Implementaciones::where('clientesid', $clientes->clientesid)->get();
            // $eliminarPlantillas = Plantillas::where('clientesid', $clientes->clientesid)->get();
            // $eliminarColaboradores = Colaboradores::where('clientesid', $clientes->clientesid)->get();
            // $eliminarCotizaciones = Cotizaciones::where('clientesid', $clientes->clientesid)->get();
            // $eliminarActividades = Actividades::where('clientesid', $clientes->clientesid)->get();

            // if (count($eliminar) > 0 || count($eliminarPlantillas) > 0 || count($eliminarColaboradores) > 0 || count($eliminarActividades) > 0 || count($eliminarCotizaciones) > 0) {
            //     flash('Registro asociado, no se puede eliminar')->warning();
            // } else {

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
            // }
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
        return redirect()->route('clientesFront.index');
    }

    /* REVIEW: Hasta aqui funciona <- */

    public function indexFront(Request $request)
    {
        $current = Auth::guard('cliente')->user();
        dd($current);
        if ($request->ajax()) {

            $data = Implementaciones::select('implementaciones.implementacionesid', 'implementaciones.tecnicosid', 'implementaciones.clientesid', 'implementaciones.productosid', 'tecnicos.nombres as nombres', 'clientes.razonsocial as razonsocial', 'implementaciones.fechainicio', 'productos.descripcion as productos')
                ->join('tecnicos', 'tecnicos.tecnicosid', 'implementaciones.tecnicosid')
                ->join('clientes', 'clientes.clientesid', 'implementaciones.clientesid')
                ->join('productos', 'implementaciones.productosid', 'productos.productosid')
                ->where('implementaciones.clientesid', Auth::guard()->user()->clientesid);

            return DataTables::of($data)
                ->editColumn('detalle', function ($implementacion) {
                    $consulta = Implementaciones::select('fechainicio', 'fechafin')->where('implementacionesid', $implementacion->implementacionesid)->first();
                    if ($consulta->fechainicio == null) {
                        return '<a href="' . route('implementacionesClientes.ver',  array($implementacion->clientesid, $implementacion->productosid, $implementacion->implementacionesid)) . '" title="Ver"> <button class="btn btn-sm btn-primary">Iniciar Capacitación</button> </a>  <a href="' . route('implementaciones.procesos.cliente',  array($implementacion->productosid, $implementacion->clientesid)) . '"> <button class="btn btn-icon" title="Procesos"> <li class="fas fa-layer-group text-muted"> </li> </button> </a><a href="' . route('implementaciones.videos.cliente',  array($implementacion->productosid, $implementacion->clientesid)) . '"> <button class="btn btn-icon" title="Videos"> <li class="fa fa-eye text-muted"> </li> </button> </a> ';
                    } elseif ($consulta->fechainicio != null && $consulta->fechafin == null) {
                        return '<a href="' . route('implementacionesClientes.ver',  array($implementacion->clientesid, $implementacion->productosid, $implementacion->implementacionesid)) . '" title="Ver"> <button class="btn btn-sm btn-warning">Continuar Capacitación</button> </a> <a href="' . route('implementaciones.procesos.cliente',  array($implementacion->productosid, $implementacion->clientesid)) . '"> <button class="btn btn-icon" title="Procesos"> <li class="fas fa-layer-group text-muted"> </li> </button> </a> <a href="' . route('implementaciones.videos.cliente',  array($implementacion->productosid, $implementacion->clientesid)) . '"> <button class="btn btn-icon" title="Videos"> <li class="fa fa-eye text-muted"> </li> </button> </a>';
                    } elseif ($consulta->fechainicio != null && $consulta->fechafin != null) {
                        return '<a href="' . route('implementacionesClientes.ver',  array($implementacion->clientesid, $implementacion->productosid, $implementacion->implementacionesid)) . '" title="Ver"> <button class="btn btn-sm btn-success">Capacitación Finalizada</button> </a><a href="' . route('implementaciones.procesos.cliente',  array($implementacion->productosid, $implementacion->clientesid)) . '"> <button class="btn btn-icon" title="Procesos"> <li class="fas fa-layer-group text-muted"> </li> </button> </a><a href="' . route('implementaciones.videos.cliente',  array($implementacion->productosid, $implementacion->clientesid)) . '"> <button class="btn btn-icon" title="Videos"> <li class="fa fa-eye text-muted"> </li> </button> </a> ';
                    }
                })
                ->rawColumns(['action', 'detalle'])
                ->make(true);
        }
        return view('cliente.clientes.index');
    }

    public function logout()
    {
        if (Auth::guard()->check()) {
            Auth::guard()->logout();
            return  view('cliente.auth.login');
        }
    }

    public function implementacionesClientes($clientes, $productosid, $implementacion)
    {
        $idImplementacion = Implementaciones::where('implementacionesid', $implementacion)->first();
        if ($idImplementacion->validaciones) {
            $implementacionProducto = Productos::select('asignadosid', 'productosid', 'tipo')->where('productosid', $productosid)->first();
            $nombreCliente = Clientes::select('clientesid', 'razonsocial')
                ->where('clientesid', $clientes)
                ->first();


            $contadorTotal = count(json_decode($idImplementacion->validaciones));

            $filtrarTemas = array_filter(json_decode($idImplementacion->validaciones), function ($k) {
                return $k->finCliente == 1;
            });
            $contadorTemas =  count($filtrarTemas);
            $operacion = ($contadorTemas * 100) / $contadorTotal;


            return view('cliente.implementacionesDetalles.index', compact('nombreCliente', 'idImplementacion', 'operacion', 'implementacionProducto', 'productosid'));
        } else {
            flash('El técnico debe iniciar la capacitación')->warning();
            return back();
        }
    }

    public function cambiarMenuCliente(Request $request)
    {
        Session::put('menuCliente', $request->estado);
    }

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
            $cliente = Clientes::findOrFail(Auth::guard()->user()->clientesid);
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

            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Clientes";
            $historial->operacion = "Cambiar Clave";
            $historial->fecha = now();
            $historial->detalle = $cliente;
            $historial->save();

            DB::commit();
            flash('Contraseña Actualizada Correctamente')->success();
            return redirect()->route('clientesFront.index');
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
        }

        return back();
    }

    public function listadoDocumentos(Request $request)
    {
        if ($request->ajax()) {

            $data = implementacionesDocumentos::select('implementaciones_documentos.implementacionesid', 'implementaciones_documentos.implementaciones_documentosid', 'implementaciones_documentos.descripcion', 'implementaciones_documentos.extencion', 'implementaciones_documentos.documento')
                ->join('implementaciones', 'implementaciones_documentos.implementacionesid', 'implementaciones.implementacionesid')
                ->join('clientes', 'implementaciones.clientesid', 'clientes.clientesid')
                ->where('clientes.clientesid', Auth::guard()->user()->clientesid);

            return DataTables::of($data)
                ->editColumn('action', function ($clientes) {
                    return  '<a href=" ' . route('documentosClientes.descargar', $clientes->implementaciones_documentosid) . '" class="btn btn-sm btn-clean btn-icon" id="export_pdf" <span class="navi-icon"> <i class="la la-download"></i></span> </a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('cliente.clientes.listadodocumentos');
    }

    public function indexVista(Request $request)
    {
        return view('cliente.sesiones.index');
    }

    public function indexSesiones(Request $request)
    {
        if ($request->ajax()) {
            $fecha = $request->fecha;
            $tipo = $request->tipo;
            $consulta = Sesiones::select('sesiones.sesionesid', 'sesiones.suma', 'sesiones.enlace', 'sesiones.descripcion', 'sesiones.fechainicio', 'sesiones.fechafin', 'tecnicos.nombres as creador', 'clientes.razonsocial as clientesid', 'productos.descripcion as productosid', 'planificaciones.planificacionesid')->join('tecnicos', 'tecnicos.tecnicosid', 'sesiones.tecnicosid')->join('clientes', 'clientes.clientesid', 'sesiones.clientesid')->join('planificaciones', 'planificaciones.planificacionesid', 'sesiones.planificacionesid')->join('productos', 'productos.productosid', 'planificaciones.productosid');
            $data = $consulta->where('sesiones.clientesid', Auth::guard()->user()->clientesid)->get();


            if ($tipo != null) {
                $tipo_fecha = $tipo == 1 ? "sesiones.fechainicio" : "sesiones.fechafin";
                //Si existe fecha en el filtro agrega condicion
                if ($fecha) {
                    $desde = date('Y-m-d', strtotime(explode(" / ", $fecha)[0]));
                    $hasta = date('Y-m-d', strtotime(explode(" / ", $fecha)[1]));

                    $data = $consulta->where('sesiones.clientesid', Auth::guard()->user()->clientesid);
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
                $recuperar = Sesiones::select('planificacionesid')->where('sesionesid', $request->sesiones)->first();
                $detalles = PlanificacionesDetalles::select('productos.tipo', 'categorias.descripcion AS categorias', 'categorias.categoriasid', 'temas.descripcion AS temas', 'categorias.categoriasid AS identificador', 'temas.temasid', 'planificaciones_detalles.calificacioncliente', 'temas.enlace_tutorial', 'temas.enlace_tutorialWeb')->join('temas', 'temas.temasid', 'planificaciones_detalles.temasid')->join('subcategorias', 'subcategorias.subcategoriasid', 'temas.subcategoriasid')->join('categorias', 'categorias.categoriasid', 'subcategorias.categoriasid')->join('planificaciones', 'planificaciones.planificacionesid', 'planificaciones_detalles.planificacionesid')->join('productos', 'planificaciones.productosid', 'productos.productosid')->where('planificaciones_detalles.planificacionesid', $recuperar->planificacionesid)->orderBy('temas.orden')->get();

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
        $sesiones = Sesiones::select('planificacionesid')->where('sesionesid', $request->sesionesid)->first();
        $planificaciones = Planificaciones::where('planificacionesid', $sesiones->planificacionesid)->first();
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

        $productos = Productos::select('descripcion')
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
