<?php

namespace App\Http\Controllers;

use App\Models\Comisiones;
use App\Models\Cupones;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\ProductoHomologado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ComisionesController extends Controller
{

    private function raw()
    {


        //         UPDATE productos_homologados_distribuidor2
        // SET 
        // precio_matriz_nv = (SELECT precio_matriz_nv FROM productos WHERE productos_homologados_distribuidor2.id_producto_local = productos.productosid),
        // precio_matriz_rnv = (SELECT precio_matriz_rnv FROM productos WHERE productos_homologados_distribuidor2.id_producto_local = productos.productosid)
        // WHERE distribuidoresid = 1;

        DB::table('productos_homologados_distribuidor2')
            ->where('distribuidoresid', 1)
            ->update([
                'precio_matriz_nv' => DB::raw('(SELECT precio_matriz_nv FROM productos WHERE productos_homologados_distribuidor2.id_producto_local = productos.productosid)'),
                'precio_matriz_rnv' => DB::raw('(SELECT precio_matriz_rnv FROM productos WHERE productos_homologados_distribuidor2.id_producto_local = productos.productosid)')
            ]);
    }
    /* -------------------------------------------------------------------------- */
    /*                 Listado para ver vendedores sus comnisiones                */
    /* -------------------------------------------------------------------------- */

    public function mis_comisiones_vendedor()
    {
        return view('auth.comisiones.vendedor');
    }

    public function filtrado_mis_comisiones_vendedor(Request $request)
    {
        $comisiones = Comisiones::select('comisiones.*', 'facturas.secuencia_perseo', 'facturas.identificacion', 'usuarios.nombres as vendedor', 'usuarios.meta_venta')
            ->join('facturas', 'facturas.facturaid', '=', 'comisiones.id_factura')
            ->join('usuarios', 'usuarios.usuariosid', '=', 'comisiones.id_vendedor')
            ->where('comisiones.id_vendedor', Auth::user()->usuariosid)
            ->when($request->estado, function ($query, $estado) {
                return $query->where('comisiones.pagado', $estado);
            })
            ->when($request->fecha, function ($query, $fecha) {
                if ($fecha != "") {
                    $dates = explode(" / ", $fecha);

                    $date1 = strtotime($dates[0]);
                    $desde = date('Y-m-d H:i:s', $date1);

                    $date2 = strtotime($dates[1] . ' +1 day -1 second');
                    $hasta = date('Y-m-d H:i:s', $date2);
                    return $query->whereBetween("fecha_registro", [$desde, $hasta]);
                }
            })
            ->get();

        foreach ($comisiones as $comision) {
            $productos = json_decode($comision->productos);
            $user = (object)["meta_venta" => $comision->meta_venta, 'usuariosid' => $comision->id_vendedor];
            $totalPr = (object)[
                'nuevos' => 0,
                'comisionNuevos' => 0,
                'renovaciones' => 0,
                'comisionRenovaciones' => 0,
                'firmas' => 0,
                'comisionFirmas' => 0,
                'total' => 0,
                'comisionTotal' => 0,
            ];
            $listadoProductos = "";

            foreach ($productos as $prod) {
                $listadoProductos .= "{$prod->nombre}\n";
                $calculo = round($prod->precioDistribuidor * $prod->cantidad, 2);
                if ($prod->esFirma) {
                    $totalPr->firmas += $calculo;
                } else if ($prod->esRenovacion) {
                    $totalPr->renovaciones += $calculo;
                } else {
                    $totalPr->nuevos += $calculo;
                }
            }
            $listadoProductos = substr($listadoProductos, 0, -1);

            $totalPr->comisionNuevos = $this->porcentajes_comisiones([10, 10, 10], $totalPr->nuevos, $user);
            $totalPr->comisionRenovaciones = $this->porcentajes_comisiones([0, 5, 5], $totalPr->renovaciones, $user);
            $totalPr->comisionFirmas = $this->porcentajes_comisiones([3, 5, 10], $totalPr->firmas, $user);

            $totalPr->total = $totalPr->nuevos + $totalPr->renovaciones + $totalPr->firmas;
            $totalPr->comisionTotal = $totalPr->comisionNuevos + $totalPr->comisionRenovaciones + $totalPr->comisionFirmas;

            $comision->total = $totalPr;
            $comision->productos = $listadoProductos;
            // unset($comision->productos);
        }

        return DataTables::of($comisiones)
            ->editColumn('nuevos', function ($comision) {
                return number_format($comision->total->comisionNuevos, 2);
            })
            ->editColumn('renovaciones', function ($comision) {
                return number_format($comision->total->comisionRenovaciones, 2);
            })
            ->editColumn('firmas', function ($comision) {
                return number_format($comision->total->comisionFirmas, 2);
            })
            ->editColumn('total', function ($comision) {
                return number_format($comision->total->comisionTotal, 2);
            })
            ->editColumn('total_venta', function ($comision) {
                return number_format($comision->total_venta, 2);
            })
            ->editColumn('fecha_registro', function ($comision) {
                return date('d/m/Y', strtotime($comision->fecha_registro));
            })
            ->rawColumns(['action',])
            ->make(true);
    }

    /* -------------------------------------------------------------------------- */
    /*                       Lisado de comisiones vendedores                      */
    /* -------------------------------------------------------------------------- */

    public function index()
    {
        return view('auth.comisiones.index');
    }

    public function filtrado_listado_comisiones(Request $request)
    {
        $comisiones = Comisiones::select('comisiones.*', 'facturas.secuencia_perseo', 'usuarios.nombres as vendedor', 'usuarios.meta_venta')
            ->join('facturas', 'facturas.facturaid', '=', 'comisiones.id_factura')
            ->join('usuarios', 'usuarios.usuariosid', '=', 'comisiones.id_vendedor')
            ->when($request->distribuidor, function($query, $distribuidor){
                return $query->where('comisiones.distribuidoresid', $distribuidor);
            })
            ->when($request->vendedor, function ($query, $vendedor) {
                return $query->where('comisiones.id_vendedor', $vendedor);
            })
            ->when($request->estado, function ($query, $estado) {
                return $query->where('comisiones.pagado', $estado);
            })
            ->when($request->fecha, function ($query, $fecha) {
                if ($fecha != "") {
                    $dates = explode(" / ", $fecha);

                    $date1 = strtotime($dates[0]);
                    $desde = date('Y-m-d H:i:s', $date1);

                    $date2 = strtotime($dates[1] . ' +1 day -1 second');
                    $hasta = date('Y-m-d H:i:s', $date2);
                    return $query->whereBetween("fecha_registro", [$desde, $hasta]);
                }
            })
            ->get();

        foreach ($comisiones as $comision) {
            $productos = json_decode($comision->productos);
            $user = (object)["meta_venta" => $comision->meta_venta, 'usuariosid' => $comision->id_vendedor];
            $totalPr = (object)[
                'nuevos' => 0,
                'comisionNuevos' => 0,
                'renovaciones' => 0,
                'comisionRenovaciones' => 0,
                'firmas' => 0,
                'comisionFirmas' => 0,
                'total' => 0,
                'comisionTotal' => 0,
            ];

            foreach ($productos as $prod) {
                $calculo = round($prod->precioDistribuidor * $prod->cantidad, 2);
                if ($prod->esFirma) {
                    $totalPr->firmas += $calculo;
                } else if ($prod->esRenovacion) {
                    $totalPr->renovaciones += $calculo;
                } else {
                    $totalPr->nuevos += $calculo;
                }
            }

            $totalPr->comisionNuevos = $this->porcentajes_comisiones([10, 10, 10], $totalPr->nuevos, $user);
            $totalPr->comisionRenovaciones = $this->porcentajes_comisiones([0, 5, 5], $totalPr->renovaciones, $user);
            $totalPr->comisionFirmas = $this->porcentajes_comisiones([3, 5, 10], $totalPr->firmas, $user);

            $totalPr->total = $totalPr->nuevos + $totalPr->renovaciones + $totalPr->firmas;
            $totalPr->comisionTotal = $totalPr->comisionNuevos + $totalPr->comisionRenovaciones + $totalPr->comisionFirmas;

            $comision->total = $totalPr;
            unset($comision->productos);
        }

        return DataTables::of($comisiones)
            ->editColumn('nuevos', function ($comision) {
                return number_format($comision->total->comisionNuevos, 2);
            })
            ->editColumn('renovaciones', function ($comision) {
                return number_format($comision->total->comisionRenovaciones, 2);
            })
            ->editColumn('firmas', function ($comision) {
                return number_format($comision->total->comisionFirmas, 2);
            })
            ->editColumn('total', function ($comision) {
                return number_format($comision->total->comisionTotal, 2);
            })
            ->editColumn('total_venta', function ($comision) {
                return number_format($comision->total_venta, 2);
            })
            ->editColumn('fecha_registro', function ($comision) {
                return date('d/m/Y', strtotime($comision->fecha_registro));
            })
            ->rawColumns(['action',])
            ->make(true);
    }

    /* -------------------------------------------------------------------------- */
    /*                       Listado de comisiones tecnicos                       */
    /* -------------------------------------------------------------------------- */

    public function listado_tecnicos(Request $request)
    {
        // return $this->filtrado_listado_comisiones_tecnicos($request);
        return view('auth.comisiones.tecnicos');
    }

    public function filtrado_listado_comisiones_tecnicos(Request $request)
    {
        try {
            $comisiones = Comisiones::select('comisiones.id_comision', 'comisiones.secuencia_factura', 'comisiones.productos', 'comisiones.fecha_registro', 'comisiones.id_factura', 'comisiones.id_capacitacion', 'usuarios_facturas.nombres as vendedor', 'usuarios_soportes.nombres as tecnico', 'facturas.concepto', 'soportes_especiales.calificacion', 'soportes_especiales.soporteid')
                ->join('facturas', 'facturas.facturaid', '=', 'comisiones.id_factura')
                ->join('usuarios as usuarios_facturas', 'usuarios_facturas.usuariosid', '=', 'facturas.usuariosid')
                ->leftJoin('soportes_especiales', 'soportes_especiales.soporteid', '=', 'comisiones.id_capacitacion')
                ->leftJoin('usuarios as usuarios_soportes', 'usuarios_soportes.usuariosid', '=', 'soportes_especiales.tecnicoid')
                ->whereRaw("JSON_EXTRACT(comisiones.productos, '$[*].esFirma') LIKE ?", ['%false%'])
                // ->where('comisiones.distribuidoresid', Auth::user()->distribuidoresid)
                // ->where('pagado', 'no')
                ->when($request->vendedor, function ($query, $vendedor) {
                    return $query->where('comisiones.id_vendedor', $vendedor);
                })
                ->when($request->tecnico, function ($query, $tecnico) {
                    return $query->where('usuarios_soportes.usuariosid', $tecnico);
                })
                ->when($request->estado, function ($query, $estado) {
                    // TODO: pagado2
                    return $query->where('comisiones.pagado', $estado);
                })
                ->when($request->fecha, function ($query, $fecha) {
                    if ($fecha != "") {
                        $dates = explode(" / ", $fecha);

                        $date1 = strtotime($dates[0]);
                        $desde = date('Y-m-d H:i:s', $date1);

                        $date2 = strtotime($dates[1] . ' +1 day -1 second');
                        $hasta = date('Y-m-d H:i:s', $date2);
                        return $query->whereBetween("fecha_registro", [$desde, $hasta]);
                    }
                })
                ->get();

            $comisiones->transform(function ($comision) {
                $productos = collect(json_decode($comision->productos, true));

                $productos = $productos->filter(function ($producto) {
                    return $producto['esFirma'] === false;
                });

                $productos = $productos->map(function ($producto) use ($comision) {
                    $producto = (object)$producto;
                    unset($producto->esFirma);
                    $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

                    $calculoGanancia = ($producto->precioDistribuidor - $producto->precioMatriz) * $producto->cantidad;
                    $calculoGanacia = round($calculoGanancia, 2);
                    $calculoComision = $producto->esRenovacion  ?
                        $this->calcular_comision($calculoGanacia, 5) : $this->calcular_comision($calculoGanacia, 10);
                    $calculoComisionTecnico = $this->calcular_comision($calculoGanacia, 5);

                    $producto->concepto = $comision->concepto;
                    $producto->id_comision = $comision->id_comision;
                    $producto->id_factura = $comision->id_factura;
                    $producto->id_capacitacion = $comision->id_capacitacion;
                    $producto->secuencia_factura = $comision->secuencia_factura;
                    $producto->vendedor = $comision->vendedor;
                    $producto->tecnico = $comision->tecnico;
                    $producto->ganancia =  number_format($calculoGanacia, 2);
                    $producto->precioMatriz = number_format($producto->precioMatriz, 2);
                    $producto->precioDistribuidor = number_format($producto->precioDistribuidor, 2);
                    $producto->mes = $meses[date('n', strtotime($comision->fecha_registro)) - 1];
                    $producto->fecha = date('d-m-Y', strtotime($comision->fecha_registro));



                    $producto->comision_vendedor = number_format($calculoComision, 2);
                    $producto->comision_permanencia = $comision->tecnico ? $calculoComisionTecnico : 0;
                    $producto->comision_capacitacion = 0;

                    if ($comision->tecnico != null && !$producto->esRenovacion) {
                        $producto->comision_capacitacion = $comision->calificacion === 5 ? $calculoComisionTecnico : 0;
                    }

                    $producto->comision_permanencia = number_format($producto->comision_permanencia, 2);
                    $producto->comision_capacitacion = number_format($producto->comision_capacitacion, 2);

                    return $producto;
                })->values();

                return $productos;
            });

            $comisiones = $comisiones->flatten(1);

            return DataTables::of($comisiones)
                ->addColumn('ganancia_final', function ($comision) {
                    $calculo = floatval($comision->ganancia) - (floatval($comision->comision_vendedor) + floatval($comision->comision_permanencia) + floatval($comision->comision_capacitacion));
                    return number_format($calculo, 2);
                })
                ->make(true);
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                     Funciones para modificar comisiones                    */
    /* -------------------------------------------------------------------------- */

    public static function registrar_comision(Factura $factura)
    {
        try {
            DB::beginTransaction();
            $capcitacion = null;

            $productos = self::parsear_productos_factura($factura);
            $totalVenta = self::obtener_total_venta($factura);
            $nuevaVenta = !self::verificar_si_es_renovacion($factura->concepto);

            if (!$nuevaVenta) {
                $capcitacion = self::obtener_capacitacion_id($factura);
            }

            $comision = new Comisiones();
            $comision->id_factura = $factura->facturaid;
            $comision->secuencia_factura = $factura->secuencia_perseo;

            $comision->productos = json_encode($productos);
            $comision->id_vendedor = $factura->usuariosid;
            $comision->id_capacitacion = $capcitacion;
            $comision->total_venta = $totalVenta;

            $comision->activo = 1;
            $comision->distribuidoresid = $factura->distribuidoresid;
            $comision->fecha_registro = now();
            $comision->pagado = 'no';
            $comision->nueva_venta = $nuevaVenta;

            $comision->save();

            $factura->id_comision = $comision->id_comision;
            $factura->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
            if ($th->getCode() == 23000)
                flash('Error al registrar la comision, ya existe una comision para esta factura')->error();
            else
                flash('Error al registrar la comision, informe a desarrollo: ' . $th->getMessage())->error();
        }
    }

    public static function actualizar_comision(Factura $factura, $idCapacitacion)
    {
        try {
            $comision = Comisiones::where('id_factura', $factura->facturaid)->where('secuencia_factura', $factura->secuencia_perseo)->first();

            if ($comision->nueva_venta == 1) {
                $comision->id_capacitacion = $idCapacitacion;
                $comision->save();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public static function eliminar_comision(Factura $factura)
    {
        try {
            $comision = Comisiones::where('id_factura', $factura->facturaid)->where('secuencia_factura', $factura->secuencia_perseo)->first();
            $comision->delete();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function marcar_pagado_vendedores(Request $request)
    {
        DB::beginTransaction();
        try {

            $comisiones = Comisiones::select('comisiones.*', 'facturas.secuencia_perseo', 'usuarios.nombres as vendedor', 'usuarios.meta_venta')
                ->join('facturas', 'facturas.facturaid', '=', 'comisiones.id_factura')
                ->join('usuarios', 'usuarios.usuariosid', '=', 'comisiones.id_vendedor')
                // ->where('comisiones.distribuidoresid', Auth::user()->distribuidoresid)
                ->when($request->vendedor, function ($query, $vendedor) {
                    return $query->where('comisiones.id_vendedor', $vendedor);
                })
                ->when($request->estado, function ($query, $estado) {
                    return $query->where('comisiones.pagado', $estado);
                })
                ->when($request->fecha, function ($query, $fecha) {
                    if ($fecha != "") {
                        $dates = explode(" / ", $fecha);

                        $date1 = strtotime($dates[0]);
                        $desde = date('Y-m-d H:i:s', $date1);

                        $date2 = strtotime($dates[1] . ' +1 day -1 second');
                        $hasta = date('Y-m-d H:i:s', $date2);
                        return $query->whereBetween("fecha_registro", [$desde, $hasta]);
                    }
                })
                ->update(['pagado' => 'si']);

            DB::commit();
            return $comisiones;
        } catch (\Throwable $th) {
            response()->json(['error' => $th->getMessage()], 500);
            DB::rollBack();
        }
    }

    public function marcar_pagado_soportes(Request $request)
    {
        DB::beginTransaction();
        try {

            $comisiones = Comisiones::select('comisiones.id_comision', 'comisiones.secuencia_factura', 'comisiones.productos', 'comisiones.fecha_registro', 'comisiones.id_factura', 'comisiones.id_capacitacion', 'usuarios_facturas.nombres as vendedor', 'usuarios_soportes.nombres as tecnico', 'facturas.concepto', 'soportes_especiales.calificacion', 'soportes_especiales.soporteid')
                ->join('facturas', 'facturas.facturaid', '=', 'comisiones.id_factura')
                ->join('usuarios as usuarios_facturas', 'usuarios_facturas.usuariosid', '=', 'facturas.usuariosid')
                ->leftJoin('soportes_especiales', 'soportes_especiales.soporteid', '=', 'comisiones.id_capacitacion')
                ->leftJoin('usuarios as usuarios_soportes', 'usuarios_soportes.usuariosid', '=', 'soportes_especiales.tecnicoid')
                ->whereRaw("JSON_EXTRACT(comisiones.productos, '$[*].esFirma') LIKE ?", ['%false%'])
                ->where('pagado', 'no')
                // ->where('comisiones.distribuidoresid', Auth::user()->distribuidoresid)
                ->when($request->vendedor, function ($query, $vendedor) {
                    return $query->where('comisiones.id_vendedor', $vendedor);
                })
                ->when($request->tecnico, function ($query, $tecnico) {
                    return $query->where('usuarios_soportes.usuariosid', $tecnico);
                })
                ->when($request->estado, function ($query, $estado) {
                    return $query->where('comisiones.pagado', $estado);
                })
                ->when($request->fecha, function ($query, $fecha) {
                    if ($fecha != "") {
                        $dates = explode(" / ", $fecha);

                        $date1 = strtotime($dates[0]);
                        $desde = date('Y-m-d H:i:s', $date1);

                        $date2 = strtotime($dates[1] . ' +1 day -1 second');
                        $hasta = date('Y-m-d H:i:s', $date2);
                        return $query->whereBetween("fecha_registro", [$desde, $hasta]);
                    }
                })
                ->update(['pagado' => 'si']);

            DB::commit();
            return $comisiones;
        } catch (\Throwable $th) {
            response()->json(['error' => $th->getMessage()], 500);
            DB::rollBack();
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                           Funciones para reportes                          */
    /* -------------------------------------------------------------------------- */

    private function porcentajes_comisiones(array $porcentajes, float $valor, $user)
    {
        if ($user->meta_venta == null) {
            $user->meta_venta = '{"min":2000,"max":3500}';
        }

        $meta = json_decode($user->meta_venta);
        $totalVenta = Comisiones::where('id_vendedor', $user->usuariosid)->sum('total_venta');
        $valorComision = 0;

        if ($totalVenta < $meta->min) {
            $porcentaje = $porcentajes[0];
            $valorComision = ($valor * $porcentaje) / 100;
        } else if ($totalVenta >= $meta->min && $totalVenta <= $meta->max) {
            $porcentaje = $porcentajes[1];
            $valorComision = ($valor * $porcentaje) / 100;
        } else if ($totalVenta > $meta->max) {
            $porcentaje = $porcentajes[2];
            $valorComision = ($valor * $porcentaje) / 100;
        }

        return round($valorComision, 2);
    }

    private function calcular_comision(float $valor, float $porcentaje)
    {
        $cal = ($valor * $porcentaje) / 100;
        return round($cal, 2);
    }

    /* -------------------------------------------------------------------------- */
    /*                             Funciones genericas                            */
    /* -------------------------------------------------------------------------- */

    private static function parsear_productos_factura($factura)
    {
        $cupon = Cupones::find($factura->cuponid);
        $valorDescuento = $cupon != null ? $cupon->descuento : 0;

        $productos = json_decode($factura->productos);
        $nuevo_array = [];
        $esRenovacion = self::verificar_si_es_renovacion($factura->concepto);

        foreach ($productos as $producto) {
            $productoBase = Producto::select('productosid', 'descripcion', 'precio_matriz_nv', 'precio_matriz_rnv')->where('productosid', $producto->productoid)->first();

            $produtoHomologado = ProductoHomologado::where([
                ['id_producto_local', $productoBase->productosid],
                ['distribuidoresid', $factura->distribuidoresid],
            ])->first();

            $esFirma = self::verificar_si_es_firma($productoBase->descripcion);
            $esSuscripcionAnual = self::verificar_suscripcion_anual($productoBase->descripcion);

            $precioDistribuidor = $produtoHomologado->precio;
            $precioMatriz = ($esRenovacion) ? $produtoHomologado->precio_matriz_rnv : $produtoHomologado->precio_matriz_nv;

            if ($valorDescuento != 0) {
                $mitadDescuento = round(($valorDescuento / 2), 3);

                $descuentoMatriz = ($precioMatriz * $mitadDescuento) / 100;
                $descuentoDist = (($precioDistribuidor * $valorDescuento) / 100) - $descuentoMatriz;

                $precioDistribuidor = $precioDistribuidor - $descuentoDist;
                $precioMatriz = $precioMatriz - $descuentoMatriz;
            }

            $item = array(
                'productoid' => $producto->productoid,
                'productoid_homo' => $producto->productoid_homo,
                'nombre' => $productoBase->descripcion,
                'esFirma' => $esFirma,
                'esRenovacion' => $esRenovacion,
                'cantidad' => $producto->cantidad,
                'esAnual' => $esSuscripcionAnual,
                'precioDistribuidor' => round($precioDistribuidor, 2),
                'precioMatriz' => round($precioMatriz, 2),

            );
            $nuevo_array = [...$nuevo_array, (object)$item];
        }

        $nuevo_array = $nuevo_array;
        return $nuevo_array;
    }

    private static function obtener_capacitacion_id(Factura $factura)
    {
        $productos = json_decode($factura->productos);
        $capacitacion = null;

        foreach ($productos as $item) {
            $result = Comisiones::select('comisiones.*', 'facturas.identificacion')
                ->join('facturas', 'facturas.facturaid', '=', 'comisiones.id_factura')
                ->where('facturas.identificacion', $factura->identificacion)
                ->whereRaw("JSON_EXTRACT(comisiones.productos, '$[*].productoid') LIKE ?", ['%' . $item->productoid . '%'])
                ->where('nueva_venta', 1)
                ->first();

            $totalComprasMensual = Comisiones::select('comisiones.*', 'facturas.identificacion')
                ->join('facturas', 'facturas.facturaid', '=', 'comisiones.id_factura')
                ->where('facturas.identificacion', $factura->identificacion)
                ->whereRaw("JSON_EXTRACT(comisiones.productos, '$[*].productoid') LIKE ?", ['%' . $item->productoid . '%'])
                ->whereRaw("JSON_EXTRACT(comisiones.productos, '$[*].esAnual') LIKE ?", ['%false%'])
                ->count();

            $totalComprasAnual = Comisiones::select('comisiones.*', 'facturas.identificacion')
                ->join('facturas', 'facturas.facturaid', '=', 'comisiones.id_factura')
                ->where('facturas.identificacion', $factura->identificacion)
                ->whereRaw("JSON_EXTRACT(comisiones.productos, '$[*].productoid') LIKE ?", ['%' . $item->productoid . '%'])
                ->whereRaw("JSON_EXTRACT(comisiones.productos, '$[*].esAnual') LIKE ?", ['%true%'])
                ->count();

            if ($result != null && $totalComprasMensual > 1 && $totalComprasMensual <= 12) {
                $capacitacion = $result->id_capacitacion;
                break;
            } else if ($result != null && $totalComprasAnual < 1) {
                $capacitacion = $result->id_capacitacion;
                break;
            }
        }

        return $capacitacion;
    }

    private static function verificar_si_es_renovacion(string $concepto)
    {
        $nomenclaturaRenovaciones = ['CTR', 'FTR', 'RNW', 'RRP'];
        $concepto = explode(' ', $concepto)[0];
        $concepto = strtoupper($concepto);
        return in_array($concepto, $nomenclaturaRenovaciones);
    }

    private static function verificar_si_es_firma(string $descripcion)
    {
        $descripcion = strtolower($descripcion);
        return str_contains($descripcion, 'firma');
    }

    private static function verificar_suscripcion_anual(string $descripcion)
    {
        $descripcion = strtolower($descripcion);
        return str_contains($descripcion, 'anual');
    }

    /* -------------------------------------------------------------------------- */
    /*                           Funciones para calculos                          */
    /* -------------------------------------------------------------------------- */

    private static function obtener_total_venta(Factura $factura)
    {
        $total = 0;
        $productos = json_decode($factura->productos);

        $cupon = Cupones::find($factura->cuponid);
        $valorDescuento = $cupon != null ? $cupon->descuento : 0;

        foreach ($productos as $item) {
            $producto = self::calcular_valores_producto($item, $valorDescuento, $factura->distribuidoresid);
            $total += $producto->totalConDescuento;
        }
        return round($total, 2);
    }

    private static function calcular_valores_producto($item, $valorDescuento, $distribuidor)
    {
        $productoBase = Producto::find($item->productoid);
        $produtoHomologado = ProductoHomologado::where([
            ['id_producto_local', $productoBase->productosid],
            ['distribuidoresid', $distribuidor],
        ])->first();

        $cantidad = intval($item->cantidad);
        $precioBase = $produtoHomologado->precio;

        $descuento = ($precioBase * $valorDescuento) / 100;
        $descuento = floatval(number_format($descuento, 2));
        $precioBaseConDescuento = $precioBase - $descuento;

        $iva = ($precioBaseConDescuento * $produtoHomologado->iva) / 100;
        $iva = floatval(number_format($iva, 3));
        $precioConIVA = $precioBaseConDescuento + $iva;


        $valores = [
            'precioBase' => $precioBase,
            'cantidad' => $cantidad,
            'subTotal' => $cantidad * $precioBase,
            'descuento' => $descuento * $cantidad,
            'totalConDescuento' => $precioBaseConDescuento * $cantidad,
            'iva' => $iva * $cantidad,
            'totalConIva' => $precioConIVA * $cantidad,
        ];

        return (object) $valores;
    }
}
