<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use App\Models\Log;
use App\Models\Subcategorias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables as DataTables;

class categoriasController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Categorias::select('categorias.categoriasid', 'categorias.orden',  'categorias.descripcion AS categoria');
            return DataTables::of($data)

                ->editColumn('action', function ($categorias) {
                    return '<a class="btn btn-sm btn-clean btn-icon" href="' . route('categorias.editar', $categorias->categoriasid) . '" title="Editar"> <i class="la la-edit"></i> </a>' . '<a class="btn btn-sm btn-clean btn-icon confirm-delete" href="javascript:void(0)" data-href="' . route('categorias.eliminar', $categorias->categoriasid) . '" title="Eliminar"> <i class="la la-trash"></i> </a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('soporte.admin.capacitaciones.categorias.index');
    }
    public function crear()
    {
        $categorias = new Categorias();
        return view('soporte.admin.capacitaciones.categorias.crear', compact('categorias'));
    }
    public function guardar(Request $request)
    {
        $request->validate(
            [
                'descripcion' => ['required'],
                'orden' => ['required']
            ],
            [
                'descripcion.required' => 'Ingrese una descripción',
                'orden.required' => 'Ingrese el orden a mostrarse'
            ],
        );
        DB::beginTransaction();
        try {
            $categorias = new Categorias();
            $categorias->descripcion = $request->descripcion;

            $categorias->orden = $request->orden;

            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Categorias";
            $historial->operacion = "Crear";
            $historial->fecha = now();
            $historial->detalle = $categorias;
            $historial->save();

            $categorias->save();
            flash('Guardado Correctamente')->success();
            DB::commit();
            return redirect()->route('categorias.editar', $categorias->categoriasid);
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
            return view('soporte.admin.capacitaciones.categorias.crear');
        }
    }
    public function editar(Categorias $categorias)
    {
        return view('soporte.admin.capacitaciones.categorias.editar', compact('categorias'));
    }
    public function actualizar(Request $request, $categorias)
    {

        $request->validate(

            [
                'descripcion' => ['required'],

                'orden' => ['required']
            ],
            [
                'descripcion.required' => 'Ingrese una descripción',

                'orden.required' => 'Ingrese el orden a mostrarse'

            ],

        );
        DB::beginTransaction();
        try {
            $actualizar = Categorias::where('categoriasid', $categorias)->first();
            $actualizar->descripcion = $request->descripcion;

            $actualizar->orden = $request->orden;

            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Categorias";
            $historial->operacion = "Actualizar";
            $historial->fecha = now();
            $historial->detalle = $actualizar;
            $historial->save();

            $actualizar->save();
            DB::commit();
            flash('Actualizado Correctamente')->success();
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
           
        }
        return back();
    }
    public function eliminar($categorias)
    {
        DB::beginTransaction();
        try {
            $eliminar = Categorias::where('categoriasid', $categorias)->first();


            $buscarDetalles = Subcategorias::where('categoriasid', $categorias)->get();
            if (count($buscarDetalles) > 0) {
                flash('Registro asociado, no se puede eliminar')->warning();
            } else {
                $historial = new Log();
                $historial->usuario = Auth::guard('tecnico')->user()->nombres;
                $historial->pantalla = "Categorias";
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
        return redirect()->route('categorias.index');
    }
}
