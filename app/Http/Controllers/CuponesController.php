<?php

namespace App\Http\Controllers;

use App\Models\Cupones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CuponesController extends Controller
{
    public function listado()
    {
        self::validarCupones();
        return view('auth.cupones.index');
    }

    public function listado_ajax(Request $request)
    {
        $estado = $request->estado;
        $cupones = Cupones::where('vendedor', Auth::user()->usuariosid);

        if ($estado != "") {
            $cupones = $cupones->where('estado', $estado);
        }

        if ($request->ajax()) {
            return DataTables::of($cupones)
                ->editColumn('estado', function ($cupon) {
                    if ($cupon->estado == 1) {
                        return '<span class="label label-lg font-weight-bold label-success label-inline">Activo</span>';
                    } else {
                        return '<span class="label label-lg font-weight-bold label-danger label-inline">Inactivo</span>';
                    }
                })
                ->editColumn('veces_usado', function ($cupon) {
                    return $cupon->veces_usado . " de " . $cupon->limite;
                })
                ->editColumn('tipo', function ($cupon) {
                    if ($cupon->tipo == 1) {
                        return "Descuento";
                    }
                    return "Promoción";
                })
                ->editColumn('tiempo_vigencia', function ($cupon) {
                    return date('d-m-Y', strtotime($cupon->tiempo_vigencia));
                })
                ->editColumn('descuento', function ($cupon) {

                    if ($cupon->tipo == 1) {
                        return $cupon->descuento . '% DESC';
                    }
                    return '+3 MESES';
                })
                ->editColumn('action', function ($cupon) {
                    if($cupon->estado == 0) return ;
                    
                    $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('cupones.editar', $cupon->cuponid) . '" title="Editar producto"> <i class="la la-edit"></i></a>';


                    $botones .= '<button class="btn btn-icon btn-light btn-hover-primary btn-sm copyCupon" data-url-cupon="' . route('tienda', Auth::user()->usuariosid) . '?cupon=' . $cupon->codigo . '" title="Copiar enlace"> <i class="la la-external-link-alt"></i></button>';
                    
                    return $botones;
                })
                ->rawColumns(['action', 'estado'])
                ->make(true);
        }
    }

    public function crear()
    {
        return view('auth.cupones.agregar');
    }

    public function guardar(Request $request)
    {
        $request->validate(
            [
                'tipo' => 'required',
                'cupon' => 'required',
                'descuento' => 'min:0|max:50',
                'tiempo_vigencia' => 'required',
                'limite' => 'required|min:1|max:10',
            ],
            [
                'tipo.required' => 'El campo tipo es obligatorio',
                'cupon.required' => 'El campo cupon es obligatorio',
                'tiempo_vigencia.required' => 'El campo fecha de vencimiento es obligatorio',
                'limite.required' => 'El campo limite de uso es obligatorio',
            ]
        );

        try {
            $uniqueId = Str::uuid();

            $cupon = new Cupones();
            $cupon->tipo = $request->tipo;
            $cupon->cupon = $request->cupon;
            $cupon->motivo = $request->cupon;
            $cupon->codigo = $uniqueId->toString();


            $cupon->descuento = $request->tipo == 1 ? $request->descuento : 0;
            $cupon->tiempo_vigencia = $request->tiempo_vigencia;
            $cupon->limite = $request->limite;


            $cupon->veces_usado = 0;
            $cupon->estado = 1;
            $cupon->fecha_creacion = now();
            $cupon->vendedor = Auth::user()->usuariosid;
            $cupon->distribuidor = Auth::user()->distribuidoresid;
            $cupon->save();

            flash('Cupon creado correctamente')->success();
            return redirect()->route('cupones.listado');
        } catch (\Throwable $th) {
            flash('Error al crear el cupon')->error();
            return redirect()->route('cupones.listado');
        }
    }

    public function editar(Cupones $cupon)
    {
        return view('auth.cupones.editar', compact('cupon'));
    }

    public function actualizar(Cupones $cupon, Request $request){
        $request->validate(
            [
                'tipo' => 'required',
                'cupon' => 'required',
                'descuento' => 'min:0|max:50',
                'tiempo_vigencia' => 'required',
                'limite' => 'required|min:1|max:10',
            ],
            [
                'tipo.required' => 'El campo tipo es obligatorio',
                'cupon.required' => 'El campo cupon es obligatorio',
                'tiempo_vigencia.required' => 'El campo fecha de vencimiento es obligatorio',
                'limite.required' => 'El campo limite de uso es obligatorio',
            ]
        );

        try {
            $cupon->tipo = $request->tipo;
            $cupon->cupon = $request->cupon;
            $cupon->motivo = $request->cupon;
            $cupon->descuento = $request->tipo == 1 ? $request->descuento : 0;
            $cupon->tiempo_vigencia = $request->tiempo_vigencia;
            $cupon->limite = $request->limite;
            $cupon->save();

            flash('Cupon actualizado correctamente')->success();
            return redirect()->route('cupones.listado');
        } catch (\Throwable $th) {
            flash('Error al actualizar el cupon')->error();
            return back();
        }
    }

    public static function validarCupones()
    {
        DB::beginTransaction();
        try {
            $fecha_actual = date('Y-m-d');
            $cuponesActivos = Cupones::where('estado', 1)->get();

            foreach ($cuponesActivos as $cupon) {
                if ($fecha_actual > $cupon->tiempo_vigencia || $cupon->veces_usado >= $cupon->limite) {
                    $cupon->estado = 0;
                    $cupon->save();
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }
}
