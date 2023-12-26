<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoHomologado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductosController extends Controller
{
    public function listado(Request $request)
    {
        return view('auth.productos.index');
    }

    public function listado_ajax(Request $request)
    {
        $distribuidor = $request->distribuidor;
        $productos = Producto::all();
        $productoHomologados = ProductoHomologado::all();

        if ($distribuidor != "") {
            $productoHomologados = $productoHomologados->where('distribuidoresid', $distribuidor);
        }

        $data = $productoHomologados->map(function ($productoHomologado) use ($productos) {
            $producto = $productos->where('productosid', $productoHomologado->id_producto_local)->first();
            $productoHomologado->nombre = $producto->descripcion ?? "Producto no encontrado";
            $productoHomologado->precio = number_format($productoHomologado->precio, 4, '.', '.');
            $productoHomologado->precioiva = number_format($productoHomologado->precioiva, 2, '.', '.');

            return $productoHomologado;
        });


        if ($request->ajax()) {
            return DataTables::of($data)
                ->editColumn('distribuidor', function ($prod) {
                    switch ($prod->distribuidoresid) {
                        case 1:
                            return "Perseo Alfa";
                        case 2:
                            return "Perseo Matriz";
                        case 3:
                            return "Perseo Delta";
                        case 4:
                            return "Perseo Omega";
                    }
                })
                ->editColumn('action', function ($prod) {
                    $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('productos.editar', $prod->productos_homologados_id) . '" title="Editar producto"> <i class="la la-edit"></i> </a>';
                    return $botones;
                })
                ->editColumn('descuento', function ($prod) {
                    return $prod->descuento . '%';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function editar(ProductoHomologado $producto, Request $request)
    {
        $prod = Producto::where('productosid', $producto->id_producto_local)->first();
        $producto->nombre = $prod->descripcion;
        return view('auth.productos.editar', compact('producto'));
    }

    private function redondearValor($precio, $decimales = 2)
    {
        $auxPrecio = number_format($precio, $decimales);
        return floatval($auxPrecio);
    }

    public function actualizar(ProductoHomologado $producto, Request $request)
    {
        $productos = ProductoHomologado::where('id_producto_local', $producto->id_producto_local)->get();

        if ($request->distribuidor != "") {
            $productos = $productos->where('distribuidoresid', $request->distribuidor);
        }

        DB::beginTransaction();
        try {
            foreach ($productos as $key => $prod) {
                $descuento = floatval($request->descuento);
                $prod->descuento = $descuento;

                $prod->precio = $prod->preciobase - (($prod->preciobase * $descuento) / 100);
                $prod->precio = $this->redondearValor($prod->precio);

                $prod->precioiva = $prod->precio + (($prod->precio * 12) / 100);
                $prod->precioiva = $this->redondearValor($prod->precioiva);
                $prod->precio = $this->redondearValor(($prod->precioiva / 1.12), 6);

                $prod->save();
            }
            DB::commit();
            flash('Descuento aplicado correctamente correctamente')->success();
            return redirect()->route('productos.listado');
        } catch (\Throwable $th) {
            DB::rollBack();
            flash('Error al actualizar el producto')->error();
            return back();
        }
    }

    public function actualizar_masivo(Request $request)
    {
        DB::beginTransaction();
        try {
            $productos = ProductoHomologado::where('categoria', $request->categoria)->get();

            if ($request->distribuidor != "") {
                $productos = $productos->where('distribuidoresid', $request->distribuidor);
            }

            foreach ($productos as $key => $prod) {
                $descuento = floatval($request->descuento);
                $prod->descuento = $descuento;

                $prod->precio = $prod->preciobase - (($prod->preciobase * $descuento) / 100);
                $prod->precio = $this->redondearValor($prod->precio);

                $prod->precioiva = $prod->precio + (($prod->precio * 12) / 100);
                $prod->precioiva = $this->redondearValor($prod->precioiva);
                $prod->precio = $this->redondearValor(($prod->precioiva / 1.12), 6);
                $prod->save();
            }

            DB::commit();
            flash('Descuento aplicado correctamente correctamente')->success();
            return redirect()->route('productos.listado');
        } catch (\Throwable $th) {
            DB::rollBack();
            flash('Error al actualizar el descuento a los productos')->error();
            return back();
        }
    }

    public function resetear_precios(Request $request)
    {
        DB::beginTransaction();
        try {
            DB::statement("UPDATE productos_homologados_distribuidor2 SET precio = preciobase, precioiva = precioivabase, descuento = 0 WHERE descuento > 0");
            DB::commit();
            flash('Descuento aplicado correctamente correctamente')->success();
            return redirect()->route('productos.listado');
        } catch (\Throwable $th) {
            DB::rollBack();
            flash('Error al restaurar los precios a defecto')->error();
            return back();
        }
    }
}
