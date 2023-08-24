<?php

namespace App\Http\Controllers;

use App\Constants\ConstantesTecnicos;
use App\Mail\Sesiones as MailSesiones;
use App\Models\Log;
use App\Models\Sesiones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables as DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Planificaciones;
use App\Models\PlanificacionesDetalles;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class sesionesController extends Controller
{
    public function indexVista(Request $request)
    {
        return view('soporte.admin.capacitaciones.sesiones.index');
    }

    public function filtradoIndex(Request $request)
    {
        if ($request->ajax()) {
            $fecha = $request->fecha;
            $tipo = $request->tipo;

            $consulta = Sesiones::select('sesiones.sesionesid', 'sesiones.suma', 'sesiones.enlace', 'sesiones.descripcion', 'sesiones.fechainicio', 'sesiones.fechafin', 'tecnicos.nombres as creador', 'clientes.razonsocial as clientesid', 'productos2.descripcion as productosid', 'planificaciones.planificacionesid')
                ->join('tecnicos', 'tecnicos.tecnicosid', 'sesiones.tecnicosid')
                ->join('clientes', 'clientes.clientesid', 'sesiones.clientesid')
                ->join('planificaciones', 'planificaciones.planificacionesid', 'sesiones.planificacionesid')
                ->join('productos2', 'productos2.productosid', 'planificaciones.productosid');

            if (Auth::guard('tecnico')->user()->rol == ConstantesTecnicos::ROL_ADMINISTRADOR) {
                $data = $consulta
                    ->where('tecnicos.distribuidoresid', Auth::guard('tecnico')->user()->distribuidoresid)
                    ->get();
            } else {
                $data = $consulta
                    ->where('sesiones.tecnicosid', Auth::guard('tecnico')->user()->tecnicosid)
                    ->get();
            }

            if ($tipo != null) {
                $tipo_fecha = $tipo == 1 ? "sesiones.fechainicio" : "sesiones.fechafin";
                //Si existe fecha en el filtro agrega condicion
                if ($fecha) {
                    $desde = date('Y-m-d', strtotime(explode(" / ", $fecha)[0]));
                    $hasta = date('Y-m-d', strtotime(explode(" / ", $fecha)[1]));
                    if (Auth::guard('tecnico')->user()->rol == ConstantesTecnicos::ROL_ADMINISTRADOR) {
                        $data = $consulta
                            ->where('tecnicos.distribuidoresid', Auth::guard('tecnico')->user()->distribuidoresid);
                    } else {
                        $data = $consulta->where('sesiones.tecnicosid', Auth::guard('tecnico')->user()->tecnicosid);
                    }
                    $data = $data->whereBetween(DB::raw("DATE($tipo_fecha)"), [$desde, $hasta])->get();
                }
            }

            return DataTables::of($data)
                ->editColumn('action', function ($sesiones) {
                    return '<a class="btn btn-sm btn-clean btn-icon" href="' . route('sesiones.editar', $sesiones->sesionesid) . '" title="Editar"> <i class="la la-edit"></i> </a>' . '<a class="btn btn-sm btn-clean btn-icon confirm-delete" href="javascript:void(0)" data-href="' . route('sesiones.eliminar', $sesiones->sesionesid) . '" title="Eliminar"> <i class="la la-trash"></i> </a>';
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

        return view('soporte.admin.capacitaciones.sesiones.index');
    }


    public function crear()
    {
        $sesiones = new Sesiones();
        return view('soporte.admin.capacitaciones.sesiones.crear', compact('sesiones'));
    }

    public function guardar(Request $request)
    {
        $request->validate(
            [
                'descripcion' => ['required'],
                'clientesid' => ['required'],
                'planificacionesid' => ['required'],
                'tecnicosid' => ['required'],
            ],
            [
                'descripcion.required' => 'Ingrese una descripción',
                'clientesid.required' => 'Escoja un cliente',
                'planificacionesid.required' => 'Escoja una planificación',
                'tecnicosid.required' => 'Escoja un técnico',
            ],
        );

        DB::beginTransaction();
        try {
            $sesion = new Sesiones();
            $sesion->clientesid = $request->clientesid;
            $sesion->descripcion = $request->descripcion;
            $sesion->enlace = $request->enlace;
            $sesion->planificacionesid = $request->planificacionesid;
            $sesion->tecnicosid = $request->tecnicosid;
            $sesion->fechahoracreacion = now();
            $sesion->usuariocreacion = Auth::guard('tecnico')->user()->nombres;
            $sesion->save();

            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Sesiones";
            $historial->operacion = "Crear";
            $historial->fecha = now();
            $historial->detalle = $sesion;
            $historial->save();

            flash('Guardado Correctamente')->success();
            DB::commit();

            return redirect()->route('sesiones.editar', $sesion->sesionesid);
        } catch (\Exception $e) {

            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
            return view('soporte.admin.capacitaciones.sesiones.crear');
        }
    }

    public function editar(Sesiones $sesiones)
    {
        return view('soporte.admin.capacitaciones.sesiones.editar', compact('sesiones'));
    }

    public function actualizar(Request $request, $sesiones)
    {
        $actualizar = Sesiones::where('sesionesid', $sesiones)->first();

        if ($actualizar->fechainicio == "" || $actualizar->fechainicio == null) {
            $request->validate(
                [
                    'clientesid' => ['required'],
                    'planificacionesid' => ['required'],
                    'tecnicosid' => ['required'],
                ],
                [
                    'clientesid.required' => 'Escoja un cliente',
                    'planificacionesid.required' => 'Escoja una planificación',
                    'tecnicosid.required' => 'Escoja un técnico',
                ],
            );
        }

        try {
            $actualizar->fechahoramodificacion = now();
            $actualizar->usuariomodificacion = Auth::guard('tecnico')->user()->nombres;

            $actualizar->descripcion = $request->descripcion;
            $actualizar->clientesid = $request->clientesid <> null ?  $request->clientesid : $actualizar->clientesid;
            $actualizar->tecnicosid = $request->tecnicosid <> null ?  $request->tecnicosid : $actualizar->tecnicosid;
            $actualizar->planificacionesid = $request->planificacionesid <> null ?  $request->planificacionesid : $actualizar->planificacionesid;
            $actualizar->enlace = $request->enlace;

            $deleteTemas  = PlanificacionesDetalles::where('planificacionesid',  $request->planificacionesid)->where('calificacioncliente', 0)->delete();

            $detalleTema = PlanificacionesDetalles::select('temasid')->where('planificacionesid', $request->planificacionesid)->where('calificacioncliente', '<>', 0)->get();
            $temasArray = $detalleTema->pluck('temasid')->toArray();
            $temasid = explode(";", $request->tsesion);
            $combinadosArray = array_diff($temasid, $temasArray);

            foreach ($combinadosArray as $tema) {
                if ($tema != "") {
                    $planificaciones_detalles = new PlanificacionesDetalles();
                    $planificaciones_detalles->planificacionesid = $request->planificacionesid;
                    $planificaciones_detalles->temasid = $tema;
                    $planificaciones_detalles->calificacioncliente = 0;
                    $planificaciones_detalles->save();
                }
            }

            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Sesiones";
            $historial->operacion = "Modificar";
            $historial->fecha = now();
            $historial->detalle = $actualizar;
            $historial->save();
            $actualizar->save();
            flash('Actualizado Correctamente')->success();
        } catch (\Exception $e) {

            flash('Ocurrió un error vuelva a intentarlo')->warning();
        }
        return back();
    }

    public function ingresarFechaInicio(Request $request)
    {

        DB::beginTransaction();
        try {
            $buscar = Sesiones::where('sesionesid', $request->idsesiones)->first();
            $buscar->fechainicio = now();

            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Sesiones";
            $historial->operacion = "Ingresar Fecha Inicio";
            $historial->fecha = now();
            $historial->detalle = $buscar;
            $historial->save();
            $buscar->save();
            DB::commit();
            return 1;
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
            return 2;
        }
    }

    public function ingresarFechaFin(Request $request)
    {
        DB::beginTransaction();
        try {

            $buscar = Sesiones::where('sesionesid', $request->idsesiones)->first();
            $buscar->fechafin = now();
            $buscar->save();

            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Sesiones";
            $historial->operacion = "Ingresar Fecha Fin";
            $historial->fecha = now();
            $historial->detalle = $buscar;
            $historial->save();

            $horas = Sesiones::select(DB::raw("SEC_TO_TIME(TIME_TO_SEC(TIMEDIFF(fechafin,fechainicio))) AS horas"), 'clientes.correo', 'clientes.razonsocial', 'planificaciones.descripcion AS descripcion', 'productos2.descripcion AS producto', 'tecnicos.nombres', 'planificaciones.planificacionesid', 'sesiones.sesionesid')->join('planificaciones', 'planificaciones.planificacionesid', 'sesiones.planificacionesid')->join('productos2', 'productos2.productosid', 'planificaciones.productosid')->join('tecnicos', 'tecnicos.tecnicosid', 'planificaciones.tecnicosid')->join('clientes', 'clientes.clientesid', 'planificaciones.clientesid')->where('sesionesid', $request->idsesiones)->first();
            $buscar->suma =  $horas->horas;
            $buscar->save();

            $temas = PlanificacionesDetalles::select('temas.descripcion', 'calificacioncliente')->join('temas', 'temas.temasid', 'planificaciones_detalles.temasid')->where('planificacionesid', $horas->planificacionesid)->where('calificacioncliente', '>', 0)->get();

            $fecha = now();
            $fechaP =   $fecha->locale('es')->translatedFormat(' d \d\e F \d\e\l Y');


            $array['from'] = env('MAIL_FROM_ADDRESS');
            $array['subject'] = 'Temas Sesiones';
            $array['cliente'] = $horas->razonsocial;
            $array['producto'] = $horas->producto;
            $array['planificacion'] = $horas->descripcion;
            $array['tecnico'] = $horas->nombres;
            $array['tema'] = $horas->sesionesid;
            $emails = [$horas->correo, Auth::guard('tecnico')->user()->correo];


            $htmlPath = public_path('word/correo.html');
            $html = file_get_contents($htmlPath);
            $html = str_replace('${fecha}', $fechaP, $html);
            $html = str_replace('${producto}', $horas->producto, $html);
            $html = str_replace('${nombre}', $horas->razonsocial, $html);
            $html = str_replace('${logo}', env('URL'), $html);

            $tablaHtml = '';
            foreach ($temas as $dato) {
                $tablaHtml .= "<tr><td>{$dato->descripcion}</td><td>{$dato->calificacioncliente}</td></tr>";
            }

            $html = str_replace('${tabla}', $tablaHtml, $html);
            // $pdf = PDF::loadHTML($html);

            // $pdf->save(public_path('generados/Sesion-' . $horas->sesionesid . '.pdf'));

            try {
                Mail::to($emails)->queue(new MailSesiones($array));
                unlink(public_path() . '/generados/Sesion-' . $array['tema'] . '.pdf');
            } catch (\Exception $e) {
                dd($e);
                flash('Error enviando email')->error();
            }

            DB::commit();

            return $horas->horas;
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
            return 'a';
        }
    }

    public function eliminar($sesiones)
    {
        DB::beginTransaction();
        try {
            $buscar = Sesiones::where('sesionesid', $sesiones)->first();
            if ($buscar->fechafin == null) {
                $historial = new Log();
                $historial->usuario = Auth::guard('tecnico')->user()->nombres;
                $historial->pantalla = "Sesiones";
                $historial->operacion = "Eliminar";
                $historial->fecha = now();
                $historial->detalle = $buscar;
                $historial->save();
                $buscar->delete();
                flash('Eliminado Correctamente')->success();
                DB::commit();
            } else {
                DB::rollBack();
                flash('No se puede eliminar, la sesión se encuentra culminada')->warning();
            }
        } catch (\Exception $e) {

            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
        }
        return back();
    }

    public function recuperar(Request $request)
    {

        $planificaciones = Planificaciones::select('planificaciones.descripcion', 'planificaciones.planificacionesid', 'productos2.descripcion AS producto', 'planificaciones.productosid', 'planificaciones.tecnicosid')
            ->join('productos2', 'productos2.productosid', 'planificaciones.productosid')
            ->where('clientesid', $request->clientesid)
            ->get();

        return $planificaciones;
    }

    public function recuperarDetalles(Request $request)
    {
        $detalles = PlanificacionesDetalles::select('temasid', 'calificacioncliente')->where('planificacionesid', $request->detalles)->get();
        return $detalles;
    }
}
