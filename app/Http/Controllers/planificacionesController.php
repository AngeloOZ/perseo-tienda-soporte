<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use App\Models\Planificaciones;
use App\Models\PlanificacionesDetalles;
use Yajra\DataTables\DataTables as DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class planificacionesController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            if (Auth::guard('tecnico')->user()->rol == 7) {
                $data = Planificaciones::select('planificaciones.planificacionesid', 'planificaciones.descripcion AS plantilla', 'planificaciones.productosid', 'productos2.descripcion', 'planificaciones.clientesid', 'clientes.razonsocial', 'clientes.identificacion', 'planificaciones.tecnicosid')
                    ->join('clientes', 'clientes.clientesid', 'planificaciones.clientesid')
                    ->join('productos2', 'productos2.productosid', 'planificaciones.productosid')
                    ->join('tecnicos', 'tecnicos.tecnicosid', 'planificaciones.tecnicosid')
                    ->where('tecnicos.distribuidoresid', Auth::guard('tecnico')->user()->distribuidoresid);
            } else {
                $data =  Planificaciones::select('planificaciones.planificacionesid', 'planificaciones.descripcion AS plantilla', 'planificaciones.productosid', 'productos2.descripcion', 'planificaciones.clientesid', 'clientes.razonsocial', 'clientes.identificacion', 'planificaciones.tecnicosid')
                    ->join('clientes', 'clientes.clientesid', 'planificaciones.clientesid')
                    ->join('productos2', 'productos2.productosid', 'planificaciones.productosid')
                    ->join('tecnicos', 'tecnicos.tecnicosid', 'planificaciones.tecnicosid')
                    ->where('planificaciones.tecnicosid', Auth::guard('tecnico')->user()->tecnicosid);
            }

            return DataTables::of($data)
                ->editColumn('action', function ($planificaciones) {
                    return '<a class="btn btn-sm btn-clean btn-icon" href="' . route('planificaciones.editar', $planificaciones->planificacionesid) . '" title="Editar"> <i class="la la-edit"></i> </a>' . '<a class="btn btn-sm btn-clean btn-icon confirm-delete" href="javascript:void(0)" data-href="' . route('planificaciones.eliminar', $planificaciones->planificacionesid) . '" title="Eliminar"> <i class="la la-trash"></i> </a>';
                })
                ->rawColumns(['action'])
                ->make();
        }

        return view('soporte.admin.capacitaciones.planificaciones.index');
    }

    public function crear()
    {
        $planificaciones = new Planificaciones();
        return view('soporte.admin.capacitaciones.planificaciones.crear', compact('planificaciones'));
    }

    public function temas(Request $request)
    {
        if ($request->ajax()) {
            $productosid = $request->planificaciones;
            $data  =  DB::select("
                        SELECT
                            categorias.categoriasid AS identificador,
                            subcategorias.subcategoriasid,
                            categorias.descripcion AS categorias,
                            subcategorias.descripcion AS subcategorias,
                            temas.descripcion AS temas,
                            temas.temasid
                        FROM categorias
                        JOIN subcategorias ON subcategorias.categoriasid = categorias.categoriasid
                        JOIN temas ON subcategorias.subcategoriasid = temas.subcategoriasid
                        WHERE FIND_IN_SET(temas.temasid, (
                            SELECT REPLACE(productos2.asignadosid, ';', ',')
                            FROM productos2
                            WHERE productos2.productosid = :productosid
                        ))
                        ORDER BY categorias.orden;
                    ", ['productosid' => $productosid]);


            return DataTables::of($data)
                ->editColumn('accion', function ($data) {
                    return '<label class="checkbox checkbox-outline checkbox-success">
                    <input class="checkCat-' . $data->identificador . ' checkboxList checkboxTemas" type="checkbox" name=temasasignados[] id="' . $data->temasid . '"  />
                    <span></span>
                </label>';
                })
                ->rawColumns(['accion'])
                ->make(true);
        }
        return view('soporte.admin.capacitaciones.asignacion.producto-categoria');
    }

    public function guardar(Request $request)
    {
        $request->validate(
            [
                'descripcion'   => 'required',
                'productosid'   => 'required',
                'clientesid'    => 'required',
                'tecnicosid'    => 'required',
                'temasA'        => 'required',
            ],
            [
                'descripcion.required' => 'Ingrese una descripcion',
                'productosid.required' => 'Escoja un producto',
                'clientesid.required' => 'Escoja un cliente',
                'tecnicosid.required' => 'Escoja un técnico',
                'temasA.required'       => 'Selecciones temas',
            ],
        );

        $verificar = Planificaciones::where('clientesid', $request->clientesid)->where('productosid', $request->productosid)->first();

        if ($verificar) {
            flash('Ya existe una planificación para ese cliente con el mismo producto')->warning();
            return redirect()->route('planificaciones.crear');
        }

        $temasid = explode(";", $request->temasA);
        $planificaciones = new Planificaciones();
        $planificaciones->descripcion = $request->descripcion;
        $planificaciones->productosid = $request->productosid;
        $planificaciones->clientesid = $request->clientesid;
        $planificaciones->tecnicosid = $request->tecnicosid;
        $planificaciones->revisioncliente = 0;
        $planificaciones->fechacreacion = now();
        $planificaciones->usuariocreacion = Auth::guard('tecnico')->user()->nombres;
        $planificaciones->save();

        foreach ($temasid as $tema) {
            if ($tema != "") {
                $planificaciones_detalles = new PlanificacionesDetalles();
                $planificaciones_detalles->planificacionesid = $planificaciones->planificacionesid;
                $planificaciones_detalles->temasid = $tema;
                $planificaciones_detalles->calificacioncliente = 0;
                $planificaciones_detalles->save();
            }
        }
        $historial = new Log();
        $historial->usuario = Auth::guard('tecnico')->user()->nombres;
        $historial->pantalla = "Planificaciones";
        $historial->operacion = "Guardar";
        $historial->fecha = now();
        $historial->detalle = $planificaciones;
        $historial->save();

        flash('Guardado Correctamente')->success();
        return redirect()->route('planificaciones.editar', $planificaciones->planificacionesid);
    }

    public function editar(Planificaciones $planificaciones)
    {
        return view('soporte.admin.capacitaciones.planificaciones.editar', compact('planificaciones'));
    }

    public function actualizar(Request $request, $planificaciones)
    {
        $request->validate(
            [
                'descripcion'   => 'required',
                'productosid'   => 'required',
                'clientesid'    => 'required',
                'tecnicosid'    => 'required',
            ],
            [
                'descripcion.required' => 'Ingrese una descripcion',
                'productosid.required' => 'Escoja un producto',
                'clientesid.required' => 'Escoja un cliente',
                'tecnicosid.required' => 'Escoja un técnico',
                'temasA.required'       => 'Selecciones temas',
            ],
        );

        try {
            $actualizar = Planificaciones::where('planificacionesid', $planificaciones)->first();
            $actualizar->descripcion = $request->descripcion;
            $actualizar->productosid = $request->productosid;
            $actualizar->clientesid = $request->clientesid;
            $actualizar->tecnicosid = $request->tecnicosid;
            $actualizar->fechamodificacion = now();
            $actualizar->usuariomodificacion = Auth::guard('tecnico')->user()->nombres;

            $deleteTemas  = PlanificacionesDetalles::where('planificacionesid', $planificaciones)->where('calificacioncliente', 0)->delete();

            $detalleTema = PlanificacionesDetalles::select('temasid')->where('planificacionesid', $planificaciones)->where('calificacioncliente', '<>', 0)->get();
            $temasArray = $detalleTema->pluck('temasid')->toArray();
            $temasid = explode(";", $request->temasA);
            $combinadosArray = array_diff($temasid, $temasArray);
            foreach ($combinadosArray as $tema) {
                if ($tema != "") {
                    $planificaciones_detalles = new PlanificacionesDetalles();
                    $planificaciones_detalles->planificacionesid = $planificaciones;
                    $planificaciones_detalles->temasid = $tema;
                    $planificaciones_detalles->calificacioncliente = 0;
                    $planificaciones_detalles->save();
                }
            }

            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Planificaciones";
            $historial->operacion = "Actualizar";
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

    public function eliminar($planificaciones)
    {
        DB::beginTransaction();
        try {
            $eliminar = PlanificacionesDetalles::where('planificacionesid', $planificaciones)->where('calificacioncliente', '<>', 0)->first();

            if ($eliminar) {
                flash('El registro ya tiene calificación no se puede eliminar')->warning();
                return redirect()->route('planificaciones.index');
            }
            $eliminarPlanificacion = Planificaciones::where("planificacionesid", $planificaciones)->first();


            if ($eliminarPlanificacion) {
                $deleteTemas  = PlanificacionesDetalles::where('planificacionesid', $planificaciones)->delete();
                $historial = new Log();
                $historial->usuario = Auth::guard('tecnico')->user()->nombres;
                $historial->pantalla = "Planificaciones";
                $historial->operacion = "Eliminar";
                $historial->fecha = now();
                $historial->detalle = $eliminarPlanificacion;
                $historial->save();
                $eliminarPlanificacion->delete();
                DB::commit();
                flash('Eliminado Correctamente')->success();
            }
        } catch (\Exception $e) {

            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
        }
        return redirect()->route('planificaciones.index');
    }
}
