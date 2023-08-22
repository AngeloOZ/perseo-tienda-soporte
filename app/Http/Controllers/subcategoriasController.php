<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables as DataTables;
use App\Models\Subcategorias;
use App\Models\Temas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class subcategoriasController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Subcategorias::select('subcategorias.subcategoriasid', 'subcategorias.orden', 'subcategorias.categoriasid', 'subcategorias.visible', 'subcategorias.descripcion AS subcategoria', 'categorias.descripcion AS categoria')->join('categorias', 'categorias.categoriasid', 'subcategorias.categoriasid');
            return DataTables::of($data)
                ->editColumn('visible', function ($subcategorias) {
                    if ($subcategorias->visible == 1) {
                        return 'Si';
                    } else {
                        return 'No';
                    }
                })
                ->editColumn('action', function ($subcategorias) {
                    return '<a class="btn btn-sm btn-clean btn-icon" href="' . route('subcategorias.editar', $subcategorias->subcategoriasid) . '" title="Editar"> <i class="la la-edit"></i> </a>' . '<a class="btn btn-sm btn-clean btn-icon confirm-delete" href="javascript:void(0)" data-href="' . route('subcategorias.eliminar', $subcategorias->subcategoriasid) . '" title="Eliminar"> <i class="la la-trash"></i> </a>';
                })
                ->rawColumns(['action', 'visible'])
                ->make(true);
        }
        return view('backend.subcategorias.index');
    }
    public function crear()
    {
        $subcategorias = new Subcategorias();
        return view('backend.subcategorias.crear', compact('subcategorias'));
    }
    public function guardar(Request $request)
    {
        $request->validate(
            [
                'descripcion' => ['required'],
                'categoriasid' => ['required'],
                'orden' => ['required']
            ],
            [
                'descripcion.required' => 'Ingrese una descripción',
                'categoriasid.required' => 'Escoja una categoría',
                'orden.required' => 'Ingrese el orden a mostrarse'

            ],
        );
        DB::beginTransaction();
        try {
            $subcategorias = new Subcategorias();
            $subcategorias->descripcion = $request->descripcion;
            $subcategorias->categoriasid = $request->categoriasid;
            $subcategorias->orden = $request->orden;
            $subcategorias->visible = $request->visible == 'on' || $request->visible == 1 ? 1 : 0;
            $subcategorias->save();
            
            $historial = new Log();
            $historial->usuario = Auth::guard('admin')->user()->nombres;
            $historial->pantalla = "Subcategorias";
            $historial->operacion = "Crear";
            $historial->fecha = now();
            $historial->detalle = $subcategorias;
            $historial->save();
            DB::commit();
            flash('Guardado Correctamente')->success();
            return redirect()->route('subcategorias.editar', $subcategorias->subcategoriasid);
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
            return view('subcategorias.crear');
        }
    }
    public function editar(Subcategorias $subcategorias)
    {
        return view('backend.subcategorias.editar', compact('subcategorias'));
    }
    public function actualizar(Request $request, $subcategorias)
    {

        $request->validate(

            [
                'descripcion' => ['required'],
                'categoriasid' => ['required'],
                'orden' => ['required']
            ],
            [
                'descripcion.required' => 'Ingrese una descripción',
                'categoriasid.required' => 'Escoja una categoría',
                'orden.required' => 'Ingrese el orden a mostrarse'

            ],

        );
        DB::beginTransaction();
        try {
            $actualizar = Subcategorias::where('subcategoriasid', $subcategorias)->first();
            $actualizar->descripcion = $request->descripcion;
            $actualizar->categoriasid = $request->categoriasid;
            $actualizar->orden = $request->orden;
            $actualizar->visible = $request->visible == 'on' || $request->visible == 1 ? 1 : 0;
            $actualizar->save();
            $historial = new Log();
            $historial->usuario = Auth::guard('admin')->user()->nombres;
            $historial->pantalla = "Subcategorias";
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
    public function eliminar($subcategorias)
    {
        DB::beginTransaction();
        try {
            $eliminar = Subcategorias::where('subcategoriasid', $subcategorias)->first();


            $buscarDetalles = Temas::where('subcategoriasid', $subcategorias)->get();
            if (count($buscarDetalles) > 0) {
                flash('Registro asociado, no se puede eliminar')->warning();
            } else {
                $historial = new Log();
                $historial->usuario = Auth::guard('admin')->user()->nombres;
                $historial->pantalla = "Subcategorias";
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

        return redirect()->route('subcategorias.index');
    }
}
