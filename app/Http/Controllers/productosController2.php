<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use App\Models\Log;
use App\Models\Productos2;
use App\Models\Temas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables as DataTables;

class productosController2 extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Productos2::select('productos2.productosid', 'productos2.descripcion',  'productos2.descripcion');
            return DataTables::of($data)

                ->editColumn('action', function ($producto) {
                    return '<a class="btn btn-sm btn-clean btn-icon" href="' . route('asignacion', $producto->productosid) . '" title="Editar Procesos"> <i class="la la-edit"></i> </a> <a class="btn btn-sm btn-clean btn-icon" href="' . route('asignacionvideos', $producto->productosid) . '" title="Videos"> <i class="la la-eye"></i> </a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('soporte.admin.capacitaciones.asignacion.index');
    }


    public function asignacion($producto, Request $request)
    {
        if ($request->ajax()) {
            $data  = Categorias::select('categorias.categoriasid', 'subcategorias.subcategoriasid', 'categorias.descripcion as categorias', 'subcategorias.descripcion as subcategorias', 'temas.descripcion as temas', 'temas.temasid')->join('subcategorias', 'subcategorias.categoriasid', 'categorias.categoriasid')->join('temas', 'subcategorias.subcategoriasid', 'temas.subcategoriasid')
                ->orderBy('categorias.orden')->orderBy('subcategorias.orden')->orderBy('temas.orden')->get();

            return DataTables::of($data)
                ->editColumn('accion', function ($data) {
                    return '<label class="checkbox checkbox-outline checkbox-success">
                    <input type="checkbox" name=asignados[] id="' . $data->temasid . '"  />
                    <span></span>
                </label>';
                })
                ->rawColumns(['accion'])
                ->make(true);
        }

        $asignados = Productos2::select('asignadosid')
            ->where('productosid', $producto)
            ->firstOrFail();
        return view('soporte.admin.capacitaciones.asignacion.producto-categoria', ['asignados' => $asignados, 'producto' => $producto]);
    }


    public function asignacionvideos($producto, Request $request)
    {

        if ($request->ajax()) {
            $data  = Categorias::select('categorias.categoriasid', 'subcategorias.subcategoriasid', 'categorias.descripcion as categorias', 'subcategorias.descripcion as subcategorias', 'temas.descripcion as temas', 'temas.temasid')->join('subcategorias', 'subcategorias.categoriasid', 'categorias.categoriasid')->join('temas', 'subcategorias.subcategoriasid', 'temas.subcategoriasid')
                ->orderBy('categorias.orden')->orderBy('subcategorias.orden')->orderBy('temas.orden')->get();

            return DataTables::of($data)

                ->editColumn('accion', function ($data) {
                    return '<label class="checkbox checkbox-outline checkbox-success">
                    <input type="checkbox" name=asignados[] id="' . $data->temasid . '"  />
                    <span></span>
                </label>';
                })
                ->rawColumns(['accion'])
                ->make(true);
        }

        $asignados = Productos2::select('asignadosvideos')
            ->where('productosid', $producto)
            ->first();
        return view('soporte.admin.capacitaciones.asignacion.producto-videos', ['asignados' => $asignados, 'producto' => $producto]);
    }

    public function asignacionActualizar(Request $request)
    {

        $asignadosid = explode(";", $request->asignadosid);

        foreach ($asignadosid as $asignados) {
            if ($asignados != "") {

                $temas[] = Temas::where('temasid', $asignados)->first();
            }
        }

        $guardar = Productos2::where('productosid', $request->producto)->first();
        $guardar->asignadosid = $request->asignadosid;

        if ($guardar->save()) {

            flash('Guardado Correctamente')->success();
            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Asignacion";
            $historial->operacion = "Modificar Asignacion";
            $historial->fecha = now();
            $historial->detalle = $guardar;
            $historial->save();

            return back();
        } else {

            flash('Ocurrió un error vuelva a intentarlo')->warning();
            return back();
        }
    }

    public function asignacionActualizarvideos(Request $request)
    {

        $asignadosid = explode(";", $request->asignadosid);

        foreach ($asignadosid as $asignados) {
            if ($asignados != "") {

                $temas[] = Temas::where('temasid', $asignados)->first();
            }
        }

        $guardar = Productos2::where('productosid', $request->producto)->first();
        $guardar->asignadosvideos = $request->asignadosid;
        if ($guardar->save()) {

            flash('Guardado Correctamente')->success();
            $historial = new Log();
            $historial->usuario = Auth::guard('tecnico')->user()->nombres;
            $historial->pantalla = "Asignacion";
            $historial->operacion = "Modificar Asignacion";
            $historial->fecha = now();
            $historial->detalle = $guardar;
            $historial->save();

            return back();
        } else {
            flash('Ocurrió un error vuelva a intentarlo')->warning();
            return back();
        }
    }
}
