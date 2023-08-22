<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Temas;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables as DataTables;
use App\Models\ImplentacionesDetalles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class temasController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Temas::select('temas.temasid', 'temas.tiempo', 'temas.orden', 'temas.subcategoriasid', 'temas.descripcion AS tema', 'subcategorias.descripcion AS subcategoria')->join('subcategorias', 'subcategorias.subcategoriasid', 'temas.subcategoriasid')->join('categorias', 'categorias.categoriasid', 'subcategorias.categoriasid');
            return DataTables::of($data)
                ->editColumn('action', function ($tema) {
                    return '<a class="btn btn-sm btn-clean btn-icon" href="' . route('temas.editar', $tema->temasid) . '" title="Editar"> <i class="la la-edit"></i> </a>' . '<a class="btn btn-sm btn-clean btn-icon confirm-delete" href="javascript:void(0)" data-href="' . route('temas.eliminar', $tema->temasid) . '" title="Eliminar"> <i class="la la-trash"></i> </a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('soporte.admin.capacitaciones.temas.index');
    }

    public function crear()
    {
        $temas = new Temas();
        return view('soporte.admin.capacitaciones.temas.crear', compact('temas'));
    }

    public function guardar(Request $request)
    {
        $request->validate(
            [
                'descripcion' => ['required'],
                'subcategoriasid' => ['required'],
                'orden' => ['required']
            ],
            [
                'descripcion.required' => 'Ingrese una descripción',
                'subcategoriasid.required' => 'Escoja una Subcategoría',
                'orden.required' => 'Ingrese el orden a mostrarse'


            ],
        );
        DB::beginTransaction();
        try {
            $temas = new Temas();
            $temas->descripcion = $request->descripcion;
            $temas->subcategoriasid = $request->subcategoriasid;
            $temas->tiempo = $request->tiempo;
            $temas->tiempoWeb = $request->tiempoWeb;
            $temas->orden = $request->orden;
            $temas->enlace_tutorial = $request->enlace_tutorial;
            $temas->enlace_tutorialWeb = $request->enlace_tutorialWeb;
            $temas->save();

            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Temas";
            $historial->operacion = "Crear";
            $historial->fecha = now();
            $historial->detalle = $temas;
            $historial->save();
            flash('Guardado Correctamente')->success();
            DB::commit();
            return redirect()->route('temas.editar', $temas->temasid);
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
            return back();
        }
    }

    public function editar(Temas $temas)
    {
        return view('soporte.admin.capacitaciones.temas.editar', compact('temas'));
    }

    public function actualizar(Request $request, $temas)
    {
        $request->validate(
            [
                'descripcion' => ['required'],
                'subcategoriasid' => ['required'],
                'orden' => ['required']
            ],
            [
                'descripcion.required' => 'Ingrese una descripción',
                'subcategoriasid.required' => 'Escoja una Subcategoría',
                'orden.required' => 'Ingrese el orden a mostrarse'
            ],
        );

        DB::beginTransaction();
        try {
            $actualizar = Temas::where('temasid', $temas)->first();
            $actualizar->descripcion = $request->descripcion;
            $actualizar->subcategoriasid = $request->subcategoriasid;
            $actualizar->tiempo = $request->tiempo;
            $actualizar->tiempoWeb = $request->tiempoWeb;
            $actualizar->orden = $request->orden;
            $actualizar->enlace_tutorial = $request->enlace_tutorial;
            $actualizar->enlace_tutorialWeb = $request->enlace_tutorialWeb;
            $actualizar->save();

            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Temas";
            $historial->operacion = "Modificar";
            $historial->fecha = now();
            $historial->detalle = $actualizar;
            $historial->save();
            DB::commit();
            flash('Actualizado Correctamente')->success();
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
        }
        return back();
    }

    public function eliminar($temas)
    {
        DB::beginTransaction();
        try {
            $eliminar = Temas::where('temasid', $temas)->first();
            // TODO: revisar
            // $buscarDetalles = ImplentacionesDetalles::where('temasid', $temas)->get();
            $buscarDetalles = [];
            if (count($buscarDetalles) > 0) {
                flash('Registro asociado, no se puede eliminar')->warning();
            } else {
                $historial = new Log();
                $historial->usuario = Auth::guard('tecnico')->user()->nombres;
                $historial->pantalla = "Temas";
                $historial->operacion = "Eliminar";
                $historial->fecha = now();
                $historial->detalle = $eliminar;
                $historial->save();

                $eliminar->delete();
                DB::commit();
                flash('Eliminado Correctamente')->success();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
        }
        return redirect()->route('temas.index');
    }
}
