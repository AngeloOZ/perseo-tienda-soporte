<?php

namespace App\Http\Controllers;

use App\Events\NotificacionNuevoVentaCobro;
use App\Models\Cupones;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\ProductoHomologado;
use App\Models\SoporteEspecial;
use App\Models\Tarjeta;
use App\Models\TarjetaHomologado;
use App\Models\User;
use App\Rules\ValidarCelular;
use App\Rules\ValidarCorreo;
use DateTime;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\DataTables;
use App\Models\Log;
use App\Models\Tecnicos;


class FacturasController extends Controller
{
    public function listado(Request $request)
    {
        return view('auth.facturas.facturas');
    }

    public function filtrado_listado(Request $request)
    {
        if ($request->ajax()) {
            $data = Factura::select('facturas.facturaid', 'facturas.identificacion',  'facturas.nombre', 'facturas.estado_pago', 'facturas.telefono', 'facturas.secuencia_perseo', 'facturas.facturado', 'facturas.autorizado', 'facturas.fecha_creacion', 'facturas.liberado', 'facturas.productos', 'facturas.origen')
                ->where('usuariosid', Auth::user()->usuariosid)
                ->when($request->facturado, function ($query, $facturado) {
                    switch ($facturado) {
                        case 1:
                            return $query->where('facturado', 0);
                        case 2:
                            return $query->where('facturado', 1);
                        case 3:
                            return $query->where('facturado', 2);
                    }
                })
                ->when($request->liberado, function ($query, $liberado) {
                    if ($liberado == 2) {
                        return $query->where('liberado', 1);
                    }
                    return $query->where('liberado', 0);
                })
                ->when($request->fecha, function ($query, $fecha) {
                    $dates = explode(" / ", $fecha);

                    $date1 = strtotime($dates[0]);
                    $desde = date('Y-m-d H:i:s', $date1);

                    $date2 = strtotime($dates[1] . ' +1 day -1 second');
                    $hasta = date('Y-m-d H:i:s', $date2);

                    return $query->whereBetween('fecha_creacion', [$desde, $hasta]);
                })
                ->get();

            return DataTables::of($data)
                ->editColumn('total_factura', function ($factura) {
                    if ($factura->facturado != 1) return '';

                    // return $factura->productos;

                    $productos = json_decode($factura->productos);
                    $cupon = null;

                    if ($factura->cuponid != null) {
                        $cupon = Cupones::find($factura->cuponid);
                    }

                    $valorDescuento = $cupon->descuento ?? 0;
                    $subTotal = 0;
                    $iva = 0;
                    $total = 0;
                    $descuento = 0;

                    foreach ($productos as $item) {
                        $precioBase = $item->precio ?? 0;

                        $descuentoFor = ($precioBase * $valorDescuento) / 100;
                        $descuentoFor = floatval(number_format($descuentoFor, 2));
                        $precioBaseConDescuento = $precioBase - $descuentoFor;

                        $ivaFor = ($precioBaseConDescuento * 12) / 100;
                        $ivaFor = floatval(number_format($ivaFor, 3));
                        $precioConIVA = $precioBaseConDescuento + $ivaFor;

                        $subTotal += $item->cantidad * $precioBase;
                        $descuento += $item->cantidad * $descuentoFor;
                        $iva += $item->cantidad * $ivaFor;
                        $total += $item->cantidad * $precioConIVA;
                    }

                    return '$' . number_format($total, 2);
                })
                ->editColumn('fecha_creacion', function ($fecha) {
                    $date = new DateTime($fecha->fecha_creacion);
                    return $date->format('d-m-Y');
                })
                ->editColumn('estado_pago', function ($estado) {
                    if ($estado->estado_pago == 0) {
                        return '<a class="bg-danger text-white rounded p-1">Por pagar</a>';
                    } else if ($estado->estado_pago >= 1) {
                        return '<a class="bg-primary text-white rounded p-1">Pagado</a>';
                    }
                })
                ->editColumn('liberado', function ($factura) {
                    switch ($factura->liberado) {
                        case 0:
                            return '<a class="bg-warning text-white rounded p-1">Por liberar</a>';
                        case 1:
                            return '<a class="bg-info text-white rounded p-1">Liberado</a>';
                    }
                })
                ->editColumn('estado', function ($estado) {
                    switch ($estado->facturado) {
                        case 0:
                            return '<a class="bg-danger text-white rounded p-1">Por facturar</a>';
                        case 1:
                            return '<a class="bg-success text-white rounded p-1">Facturado</a>';
                        case 2:
                            return '<a class="bg-secondary text-dark rounded p-1">Cancelada</a>';
                    }
                })
                ->editColumn('action', function ($factura) {
                    $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('facturas.editar', $factura->facturaid) . '"  title="Editar"> <i class="la la-edit"></i> </a>' .
                        '<a class="btn btn-sm btn-light btn-icon btn-hover-danger confirm-delete" href="javascript:void(0)" data-href="' . route('facturas.eliminar', $factura->facturaid) . '" title="Eliminar"> <i class="la la-trash"></i> </a>';

                    if ($factura->facturado == 1 && $factura->autorizado == 0) {
                        $botones = '<a class="btn btn-icon btn-light btn-hover-primary btn-sm mr-2" href="' . route('facturas.editar', $factura->facturaid) . '"  title="Visualizar"> <i class="la la-eye"></i> </a>' . '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" data-action="autorizar" href="' . route('factura.autorizar', $factura->facturaid) . '"  title="Autorizar factura"> <i class="la la-check-circle-o" data-action="autorizar"></i></a>';
                    }

                    if ($factura->facturado == 1 && $factura->autorizado == 1) {
                        $botones = '<a class="btn btn-icon btn-light btn-hover-primary btn-sm mr-2" href="' . route('facturas.editar', $factura->facturaid) . '"  title="Visualizar"> <i class="la la-eye"></i> </a>' . '<a class="btn btn-icon btn-light btn-hover-danger btn-sm mr-2" href="' . route('factura.visualizar', $factura->facturaid) . '"  title="Ver PDF"> <i class="la la-file-pdf"></i> </a>';
                    }

                    if ($factura->facturado == 2) {
                        $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('facturas.editar', $factura->facturaid) . '"  title="Visualizar"> <i class="la la-eye"></i> </a>';
                    }

                    return $botones;
                })
                ->rawColumns(['action', 'estado', 'fecha_creacion', 'estado_pago', 'liberado'])
                ->make(true);
        }
    }

    public function visualizar_factura(Factura $factura)
    {
        $vendedor = User::find($factura->usuariosid);

        if ($vendedor->token == null) {
            flash("Ud no esta autorizado para realizar esta accion")->warning();
            return back();
        }

        $solicitud = [
            "api_key" => $vendedor->token,
            "facturasid" => $factura->facturaid_perseo,
            "enviomail" => false,
        ];

        $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
            ->withOptions(["verify" => false])
            ->post($vendedor->api . "/facturas_autorizar", $solicitud)
            ->json();

        if (isset($resultado["pdf"])) {
            return view("auth.facturas.visualizar", ["pdf" => $resultado["pdf"]]);
        }
        flash('La factura no está disponible')->success();
        return back();
    }

    public function editar(Factura $factura)
    {
        $noLiberado = $factura->liberado === 0 ? true : false;
        $listaProductos1 = json_decode($factura->productos);
        $listaProductos2 = [];
        $capacitacion = null;
        $esSoloFirma = false;
        $liberable = false;
        $cupon = null;

        foreach ($listaProductos1 as $item) {
            $current = Producto::select('productosid', 'descripcion', 'tipo', 'licenciador', 'precio_matriz_nv', 'precio_matriz_rnv')->find($item->productoid);
            $currentHomologado = ProductoHomologado::where([
                ['id_producto_local', $current->productosid],
                ['distribuidoresid', Auth::user()->distribuidoresid],
            ])->first();

            if (!$liberable && $current->licenciador == 1) {
                $liberable = true;
            }

            $precio = isset($item->precio) ? $item->precio : $currentHomologado->precio;
            $precioIva = isset($item->precioiva) ? $item->precioiva : $currentHomologado->precioiva;

            $current->productoid_homo = $currentHomologado->productos_homologados_id;
            $current->cantidad = $item->cantidad;
            $current->precio = $precio;
            $current->precioiva = $precioIva;
            $current->iva = $currentHomologado->iva;
            $listaProductos2 = [...$listaProductos2, $current];
        }

        if ($factura->capacitacionid != null) {
            $capacitacion = SoporteEspecial::find($factura->capacitacionid);

            if ($capacitacion) {
                $tecnico = Tecnicos::find($capacitacion->tecnicoid);

                $estado = "";
                switch ($capacitacion->estado) {
                    case "1":
                        $estado = "Asignados";
                        break;
                    case "2":
                        $estado = "Agendados";
                        break;
                    case "3":
                        $estado = "Parametrizados";
                        break;
                    case "4":
                        $estado = "Implementados";
                        break;
                    case "5":
                        $estado = "Revisados 1";
                        break;
                    case "6":
                        $estado = "Finalizados";
                        break;
                    case "7":
                        $estado = "Reagendados";
                        break;
                    case "8":
                        $estado = "Revisados 2";
                        break;
                    case "9":
                        $estado = "Aprobados";
                        break;
                    case "10":
                        $estado = "Rechazados";
                        break;
                    case "11":
                        $estado = "Sin Respuesta";
                        break;
                    default:
                        $estado = "Sin asignar";
                        break;
                }

                $capacitacion->estado = $estado;
                $capacitacion->tecnico = $tecnico->nombres ?? "Sin asignar";

                $capacitacion = (object) $capacitacion;
            }
        }

        if ($factura->cuponid != null) {
            $cupon = Cupones::find($factura->cuponid);
        }

        if ($noLiberado && count($listaProductos2) === 1 && !$liberable) {
            $descripcion = $listaProductos2[0]->descripcion;
            $descripcion = strtolower($descripcion);
            $esSoloFirma = str_contains($descripcion, 'firma');
        }

        if ($noLiberado && $esSoloFirma) {
            $factura->liberado = 1;
            $factura->save();
        }

        $factura->productos2 = $listaProductos2;
        return view('auth.facturas.editar_factura', ['factura' => $factura, "liberable" => $liberable, "soporte" => $capacitacion, "cupon" => $cupon]);
    }

    public function actualizar(Factura $factura, Request $request)
    {
        // TODO: required origen
        // origen => 'required'
        $request->validate(
            [
                'identificacion' => 'required',
                'nombre' => 'required',
                'direccion' => 'required',
                'telefono' => ['required', new ValidarCelular],
                'correo' => ['required', new ValidarCorreo],
                'productos' => 'required',
                'concepto' => 'required',
                'concepto_abv' => 'required',
            ],
            [
                'identificacion.required' => 'Ingrese la identificación',
                'nombre.required' => 'Ingrese el nombre',
                'direccion.required' => 'Ingrese la dirección',
                'telefono.required' => 'Ingrese el teléfono',
                'correo.required' => 'Ingrese el correo',
                'productos.required' => 'La factura debe tener productos',
                'concepto.required' => 'Ingrese un complemento del concepto',
                'concepto_abv.required' => 'Selecione un concepto para la factura',
            ],
        );

        $datos = $request->all();

        $conceptoAux = explode("@", $request->concepto_abv);
        $conceptoAbr = "";
        if (count($conceptoAux) > 1) {
            $datos["id_concepto"] = $conceptoAux[0];
            $conceptoAbr = $conceptoAux[1];
        } else {
            $conceptoAbr = $conceptoAux[0];
            $datos["id_concepto"] = "";
        }

        $concepto = explode("-", $request->concepto);
        if (isset($concepto[1])) {
            $concepto = $concepto[1];
        } else {
            $concepto = $concepto[0];
        }
        $concepto = trim($concepto);

        $datos["concepto"] = $conceptoAbr . " " . $concepto;
        unset($datos["concepto_abv"]);

        if ($factura->update($datos)) {
            flash('Actualizado correctamente')->success();
            $log = new Log();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Facturas";
            $log->operacion = "Modificar";
            $log->fecha = now();
            $log->detalle = $factura;
            $log->save();
        }
        return back();
    }

    public function subir_comprobantes(Factura $factura, Request $request)
    {
        try {
            $datos = [];

            if (!$factura->comprobante_pago && !isset($request->comprobante_pago) && !$factura->pago_tarjeta) {
                flash("Debe subir un comprobante de pago")->error();
                return back();
            }

            if (isset($request->comprobante_pago)) {
                $temp = [];
                foreach ($request->comprobante_pago as $file) {
                    $id = uniqid("comprobante-");
                    $temp[$id] = base64_encode(file_get_contents($file->getRealPath()));
                }
                $datos["comprobante_pago"] = json_encode($temp);
            }

            $datos["estado_pago"] = $request->estado_pago;
            $datos["detalle_pagos"] = json_encode([
                "banco_origen" => $request->banco_origen,
                "banco_destino" => $request->banco_destino,
                "numero_comprobante" => $request->numero_comprobante,
            ]);

            if (isset($request->observacion_pago_vendedor)) {
                $datos["observacion_pago_vendedor"] = $request->observacion_pago_vendedor;
            }

            if (isset($request->observacion_pago)) {
                $datos["observacion_pago"] = $request->observacion_pago;
            }

            if ($request->estado_pago < 2) {
                $factura->fecha_actualizado = now();
            }


            if ($factura->update($datos)) {
                if ($factura->estado_pago > 0 && $factura->facturado != 0) {
                    NotificacionNuevoVentaCobro::dispatch($factura);
                }

                flash("Comprobante de pago registrado")->success();
                $log = new Log();
                $log->usuario = Auth::user()->nombres;
                $log->pantalla = "Facturas";
                $log->operacion = "Pagos";
                $log->fecha = now();
                $log->detalle = $factura;
                $log->save();
            }

            return back();
        } catch (\Throwable $th) {
            flash($th->getMessage())->error();
            return back();
        }
    }

    public function cancelar_factura(Request $request)
    {
        try {
            Factura::where('facturaid', $request->facturaid)->update(["facturado" => 2, "secuencia_nota_credito" => $request->secuencia_nota_credito]);
            flash('Factura cancelada')->success();

            $factura =  Factura::where('facturaid', $request->facturaid)->first();

            ComisionesController::eliminar_comision($factura);

            $log = new Log();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Facturas";
            $log->operacion = "Cancelar";
            $log->fecha = now();
            $log->detalle =  $factura;
            $log->save();

            return back();
        } catch (\Throwable $th) {
            flash('Hubo un error al cancelar ' . $th->getMessage())->error();
            return back();
        }
    }

    public function descargar_comprobante($id_factura, $id_unique)
    {
        $comprobantes = Factura::select('comprobante_pago')->where('facturaid', $id_factura)->first();
        $comprobantes = $comprobantes->comprobante_pago;
        $comprobantes = json_decode($comprobantes, true);

        $archivo = base64_decode($comprobantes[$id_unique]);

        return response($archivo)->header('Content-type', 'image/png');
    }

    public function eliminar(Factura $factura)
    {
        if ($factura->facturado == 0) {
            if ($factura->delete()) {
                flash('Eliminado correctamente')->success();
                $log = new Log();
                $log->usuario = Auth::user()->nombres;
                $log->pantalla = "Facturas";
                $log->operacion = "Eliminar";
                $log->fecha = now();
                $log->detalle =  $factura;
                $log->save();
            }
        } else {
            flash('No se puede una factura ya emitida')->warning();
        }
        return back();
    }

    /* -------------------------------------------------------------------------- */
    /*                    Funciones para administrador Facturas                   */
    /* -------------------------------------------------------------------------- */
    public function listado_revisor(Request $request)
    {
        return view('auth2.revisor_facturas.index');
    }

    public function listado_revisor_por_pagar()
    {
        return view('auth2.revisor_facturas.porpagar');
    }

    public function filtrado_listado_revisor(Request $request)
    {
        if ($request->ajax()) {
            $data = Factura::select('facturas.facturaid', 'facturas.identificacion', 'facturas.nombre', 'facturas.concepto', 'facturas.telefono', 'facturas.secuencia_perseo', 'facturas.facturado', 'facturas.autorizado', 'facturas.productos', 'facturas.fecha_creacion', 'facturas.estado_pago', 'facturas.liberado', 'usuarios.nombres as vendedor', 'facturas.fecha_creacion', 'facturas.fecha_actualizado', 'facturas.origen', 'facturas.total_venta as total', 'cupones.descuento as descuento')
                ->join('usuarios', 'usuarios.usuariosid', '=', 'facturas.usuariosid')
                ->leftJoin('cupones', 'cupones.cuponid', '=', 'facturas.cuponid')
                ->where('facturas.distribuidoresid', '=', Auth::user()->distribuidoresid)
                ->where('facturas.facturado', 1)
                ->when($request->pago, function ($query, $pago) {
                    if ($pago === "no") {
                        return $query->where('facturas.estado_pago', 0);
                    } else if ($pago == 3) {
                        return $query->whereIn('facturas.estado_pago', [1, 2]);
                    } else {
                        return $query->where('facturas.estado_pago', $pago);
                    }
                })
                ->when($request->liberado, function ($query, $liberado) {
                    switch ($liberado) {
                        case 1:
                            return $query->where('facturas.liberado', 0);
                        case 2:
                            return $query->where('facturas.liberado', 1);
                    }
                })
                ->when($request->fecha, function ($query, $fecha) {
                    $dates = explode(" / ", $fecha);

                    $date1 = strtotime($dates[0]);
                    $desde = date('Y-m-d H:i:s', $date1);

                    $date2 = strtotime($dates[1] . ' +1 day -1 second');
                    $hasta = date('Y-m-d H:i:s', $date2);

                    return $query->whereBetween('facturas.fecha_creacion', [$desde, $hasta]);
                })
                ->get();

            $listadoProductos = Producto::select('productosid', 'descripcion')->get();

            return DataTables::of($data)
                ->editColumn('fecha_creacion', function ($fecha) {
                    $date = new DateTime($fecha->fecha_creacion);
                    return $date->format('d-m-Y');
                })
                ->editColumn('fecha_actualizado', function ($fecha) {
                    $date = new DateTime($fecha->fecha_actualizado);
                    return $date->format('d-m-Y');
                })
                ->editColumn('productos', function ($factura) use ($listadoProductos) {
                    $prodcutos = json_decode($factura->productos);
                    $items = "";

                    foreach ($prodcutos as $item) {
                        $nombre = $listadoProductos->where('productosid', $item->productoid)->first()->descripcion;
                        $items .= $nombre . " (" . $item->cantidad . ") <br />";
                    }
                    return $items;
                })
                ->editColumn('descuento', function ($factura) {
                    return $factura->descuento ?? 0 . "%";
                })
                ->editColumn('estado', function ($estado) {
                    if ($estado->estado_pago == 0) {
                        return '<a class="bg-danger text-white rounded p-1">Por pagar</a>';
                    } else if ($estado->estado_pago == 1) {
                        return '<a class="bg-success text-white rounded p-1">Pagado</a>';
                    } else if ($estado->estado_pago == 2) {
                        return '<a class="bg-info text-white rounded p-1">Pagado y revisado</a>';
                    }
                })
                ->editColumn('liberado', function ($factura) {
                    if ($factura->liberado == 0) {
                        return '<a class="bg-danger text-white rounded p-1">Por liberar</a>';
                    } else if ($factura->liberado == 1) {
                        return '<a class="bg-primary text-white rounded p-1">Liberado</a>';
                    }
                })
                ->editColumn('action', function ($factura) {
                    $botones = '<a class="btn btn-icon btn-light btn-hover-primary btn-sm mr-2" href="' . route('facturas.revisor_editar', $factura->facturaid) . '"  title="Visualizar"> <i class="la la-eye"></i> </a>' . '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('factura.autorizar', $factura->facturaid) . '"  title="Autorizar factura" data-action="autorizar"> <i class="la la-check-circle-o" data-action="autorizar"></i></a>';

                    if ($factura->autorizado == 1) {
                        $botones = '<a class="btn btn-icon btn-light btn-hover-primary btn-sm mr-2" href="' . route('facturas.revisor_editar', $factura->facturaid) . '"  title="Visualizar"> <i class="la la-eye"></i> </a>' . '<a class="btn btn-icon btn-light btn-hover-danger btn-sm mr-2" href="' . route('factura.visualizar', $factura->facturaid) . '"  title="Ver PDF"> <i class="la la-file-pdf"></i> </a>';
                    }

                    return $botones;
                })
                ->rawColumns(['action', 'estado', 'fecha_creacion', 'liberado', 'productos'])
                ->make(true);
        }
    }

    public function editar_revisor(Factura $factura)
    {
        $liberable = false;
        $vendedor = User::findOrFail($factura->usuariosid);
        $listaProductos1 = json_decode($factura->productos);
        $listaProductos2 = [];

        foreach ($listaProductos1 as $item) {
            $current = Producto::find($item->productoid);
            $currentHomologado = ProductoHomologado::where([
                ['id_producto_local', $current->productosid],
                ['distribuidoresid', Auth::user()->distribuidoresid],
            ])->first();

            if (!$liberable && $current->licenciador == 1) {
                $liberable = true;
            }

            $current->cantidad = $item->cantidad;
            $current->cantidad = $item->cantidad;
            $current->precio = $currentHomologado->precio;
            $current->precioiva = $currentHomologado->precioiva;
            $current->iva = $currentHomologado->iva;
            $listaProductos2 = [...$listaProductos2, $current];
        }

        $factura->productos2 = $listaProductos2;
        return view('auth2.revisor_facturas.editar', ['factura' => $factura, 'vendedor' => $vendedor, "liberable" => $liberable]);
    }

    public function liberar_producto_manual(Factura $factura)
    {
        Factura::where('facturaid', $factura->facturaid)->update(["liberado" => 1]);
        $facturas = Factura::where('facturaid', $factura->facturaid)->first();
        $log = new Log();
        $log->usuario = Auth::user()->nombres;
        $log->pantalla = "Facturas";
        $log->operacion = "Liberar - manual";
        $log->fecha = now();
        $log->detalle =  $facturas;
        $log->save();
        flash('Producto liberado')->success();
        return back();
    }

    /* -------------------------------------------------------------------------- */
    /*                       Funciones para generar facturas                      */
    /* -------------------------------------------------------------------------- */

    public function generar_factura(Factura $factura)
    {
        try {
            $vendedor = User::find($factura->usuariosid);

            if ($vendedor->token == null) {
                flash('Ud no esta autorizado para facturar')->warning();
                return back();
            }

            if ($factura->concepto == null) {
                flash('El concepto de la factura es requerido')->warning();
                return back();
            }

            $cliente = $this->crear_cliente($vendedor, $factura);

            $resp = $this->crear_factura($factura, $cliente["cliente"], $cliente["vendedor"]);

            if ($resp["estado"] == "ok") {
                try {
                    Factura::where('facturaid', $factura->facturaid)->update(["facturado" => 1, "facturaid_perseo" => $resp["facturaid"], "secuencia_perseo" => $resp["secuencia"], "fecha_actualizado" => now()]);
                    flash('Factura generada')->success();
                    $facturas = Factura::where('facturaid', $factura->facturaid)->first();
                    $log = new Log();
                    $log->usuario = Auth::user()->nombres;
                    $log->pantalla = "Facturas";
                    $log->operacion = "Generar";
                    $log->fecha = now();
                    $log->detalle =  $facturas;
                    $log->save();
                } catch (\Throwable $th) {
                    flash("Factura generada con secuencia {$resp["secuencia"]} y id: {$resp["facturaid"]}, pero existe un error: " . $th->getMessage())->warning();
                    $log = new Log();
                    $log->usuario = Auth::user()->nombres;
                    $log->pantalla = "Facturas";
                    $log->operacion = "Generar-Observacion";
                    $log->fecha = now();
                    $log->detalle =  $facturas;
                    $log->save();
                }

                $facturaAux =  Factura::where('facturaid', $factura->facturaid)->first();
                ComisionesController::registrar_comision($facturaAux);
            } else {
                flash('Hubo un error al generar la factura')->error();
            }
            return back();
        } catch (\Throwable $th) {
            // dd($th);
            flash('Hubo un error al generar la factura: ' . $th->getMessage())->error();
            return back();
        }
    }

    private function crear_cliente($vendedor, $factura)
    {
        try {
            $fecha = date("YmdHis");
            $cliente = [
                "api_key" => $vendedor->token,
                "registros" => [
                    [
                        "clientes" => [
                            "clientes_gruposid" => 1,
                            "razonsocial" => $factura->nombre,
                            "clientes_zonasid" => 1,
                            "clientes_rutasid" => 1,
                            "direccion" => $factura->direccion,
                            "tipoidentificacion" => strlen($factura->identificacion) == 13 ? "R" : "C",
                            "identificacion" => $factura->identificacion,
                            "email" => $factura->correo,
                            "telefono3" => $factura->telefono,
                            "vendedoresid" => $vendedor->vendedoresid,
                            "cobradoresid" => $vendedor->vendedoresid,
                            "estado" => true,
                            "tarifasid" => 1,
                            "forma_pago_empresaid" => 1,
                            "usuariocreacion" => $vendedor->identificacion,
                            "fechacreacion" => $fecha,
                        ],
                    ],
                ],
            ];

            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
                ->withOptions([
                    "verify" => false,
                    'timeout' => 5,
                ])
                ->post($vendedor->api . "/clientes_crear", $cliente)
                ->json();

            $cliente = $resultado["clientes"][0];

            if (isset($cliente["vendedoresid"])) {
                if ($cliente["vendedoresid"] != $vendedor->vendedoresid) {
                    $vendedorAux = User::where('vendedoresid', $cliente["vendedoresid"])->where('distribuidoresid', $vendedor->distribuidoresid)->first();
                    if ($vendedorAux != null) {
                        $vendedor = $vendedorAux;
                    }
                }
            }

            return ["cliente" => $cliente, "vendedor" => $vendedor];
        } catch (\Throwable $th) {
            throw new Error("No se pudo crear el cliente, revise la api del sistema");
        }
    }

    private function crear_factura($factura, $cliente, $vendedor)
    {
        $productos = json_decode($factura->productos);
        $fecha = date("Ymd");
        $valoresFactura = [
            "subtotal" => 0,
            "total_descuento" => 0,
            "subtotalconiva" => 0,
            "subtotalsiniva" => 0,
            "subtotalneto" => 0,
            "total_ice" => 0,
            "total_iva" => 0,
            "propina" => 0,
            "total" => 0,
            "totalneto" => 0,
        ];

        $detalleFactura = [];
        $cupon = Cupones::find($factura->cuponid);
        $valorDescuento = $cupon != null ? $cupon->descuento : 0;

        foreach ($productos as $item) {
            $producto = Producto::find($item->productoid);
            $producto2 = ProductoHomologado::where([
                ['id_producto_local', $producto->productosid],
                ['distribuidoresid', $vendedor->distribuidoresid],
            ])->first();

            $item->precio = $producto2->precio;
            $item->precioiva = $producto2->precioiva;

            // CALCULOS
            $precioBase = $producto2->precio;

            $descuentoFor = ($precioBase * $valorDescuento) / 100;
            $descuentoFor = floatval(number_format($descuentoFor, 2));
            $precioBaseConDescuento = $precioBase - $descuentoFor;

            $ivaFor = ($precioBaseConDescuento * $producto2->iva) / 100;
            $ivaFor = floatval(number_format($ivaFor, 3));
            $precioConIVA = $precioBaseConDescuento + $ivaFor;
            // FIN CALCULOS

            $detalle = [
                "centros_costosid" => $vendedor->centro_costosid,
                "almacenesid" => $vendedor->almacenesid,
                "productosid" => $producto2->id_producto_perseo,
                "medidasid" => 1,
                "cantidaddigitada" => $item->cantidad,
                "cantidad" => $item->cantidad,
                "precio" => $producto2->precio,
                "precioiva" => $producto2->precioiva,
                "descuento" => $valorDescuento,
                "costo" => $producto2->costo,
                "iva" => $producto2->iva,
                "descuentovalor" => $descuentoFor,
                "servicio" => true,
            ];

            array_push($detalleFactura, $detalle);

            $valoresFactura["subtotal"] = $valoresFactura["subtotal"] + $item->cantidad * $precioBase;
            $valoresFactura["total_descuento"] = $valoresFactura["total_descuento"] + $item->cantidad * $descuentoFor;
            $valoresFactura["subtotalconiva"] = $valoresFactura["subtotalconiva"] + $item->cantidad * $precioBaseConDescuento;
            $valoresFactura["subtotalneto"] = $valoresFactura["subtotalneto"] + $item->cantidad * $precioBaseConDescuento;
            $valoresFactura["total_iva"] = $valoresFactura["total_iva"] + $item->cantidad * $ivaFor;
            $valoresFactura["total"] = $valoresFactura["total"] + $item->cantidad * ($precioBaseConDescuento + $ivaFor);
            $valoresFactura["totalneto"] = $valoresFactura["totalneto"] + $item->cantidad * ($precioBaseConDescuento + $ivaFor);
        }

        $factura2 = [
            "api_key" => $vendedor->token,
            "registro" => [
                [
                    "facturas" => [
                        "secuenciasid" => $vendedor->secuenciasid,
                        "forma_pago_empresaid" => ($factura->pago_tarjeta) ? 4 : 2,
                        "forma_pago_sri_codigo" => "01",
                        "cajasid" => $vendedor->cajasid,
                        "centros_costosid" => $vendedor->centro_costosid,
                        "almacenesid" => $vendedor->almacenesid,
                        "facturadoresid" => $vendedor->vendedoresid,
                        "vendedoresid" => $vendedor->vendedoresid,
                        "clientesid" => $cliente["clientesid_nuevo"],
                        "tarifasid" => $vendedor->tarifasid,
                        "emision" => $fecha,
                        "vence" => $fecha,
                        "subtotal" => round($valoresFactura["subtotal"], 2),
                        "total_descuento" => round($valoresFactura["total_descuento"], 2),
                        "subtotalconiva" => round($valoresFactura["subtotalconiva"], 2),
                        "subtotalsiniva" => round($valoresFactura["subtotalsiniva"], 2),
                        "subtotalneto" => round($valoresFactura["subtotalneto"], 2),
                        "total_ice" => round($valoresFactura["total_ice"], 2),
                        "total_iva" => round($valoresFactura["total_iva"], 2),
                        "propina" => round($valoresFactura["propina"], 2),
                        "total" => round($valoresFactura["total"], 2),
                        "totalneto" => round($valoresFactura["totalneto"], 2),
                        "concepto" => $factura->concepto,
                        "observacion" => $factura->observacion,
                        "detalles" => $detalleFactura,
                        "usuariocreacion" => $vendedor->identificacion,
                    ],
                ],
            ],
        ];

        if ($factura->pago_tarjeta != null) {
            $tarjetaPago = json_decode($factura->pago_tarjeta);

            $tarjeta = Tarjeta::where("nombre_tarjeta", $tarjetaPago->nombre_tarjeta)->first();
            $tarjetaHomologado = TarjetaHomologado::where([
                ['id_tarjeta_local', $tarjeta->tarjetasid],
                ['distribuidoresid', $vendedor->distribuidoresid],
            ])->first();

            $factura2["registro"][0]["facturas"]["movimiento"] = [
                [
                    "forma_pago_empresaid" => 4,
                    "cajasid" => $vendedor->cajasid,
                    "bancotarjetaid" => $tarjetaHomologado->id_tarjeta_perseo,
                    "fechamovimiento" => $fecha,
                    "fechavence" => $fecha,
                    "numerochequevoucher" => $tarjetaPago->voucher,
                    "importe" => round($valoresFactura["total"], 2),
                    "beneficiario" => $factura->nombre
                ],
            ];
        }

        $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
            ->withOptions([
                "verify" => false,
                'timeout' => 5,
            ])
            ->post($vendedor->api . "/facturas_crear", $factura2)
            ->json();

        $response = [];

        if (isset($resultado["fault"])) {
            $response["estado"] = "error";
            $response["mensaje"] = $resultado["fault"]["detail"];
        } else {
            $response["estado"] = "ok";
            $response["mensaje"] = $resultado["facturas"];
            $response["facturaid"] = $resultado["facturas"][0]["facturasid_nuevo"];
            $response["secuencia"] = $resultado["facturas"][0]["facturas_secuencia"];

            try {
                $factura->productos = json_encode($productos);
                $factura->total_venta = round($valoresFactura["total"], 2);
                $factura->save();
            } catch (\Throwable $th) {
            }
        }
        return $response;
    }

    public function autorizar_factura(Factura $factura)
    {
        try {

            $vendedor = User::find($factura->usuariosid);

            if ($vendedor->token == null) {
                flash("Ud no esta autorizado para realizar esta accion")->warning();
                return back();
            }

            $solicitud = [
                "api_key" => $vendedor->token,
                "facturasid" => $factura->facturaid_perseo,
                "enviomail" => true,
            ];

            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
                ->withOptions([
                    "verify" => false,
                ])
                ->post($vendedor->api . "/facturas_autorizar", $solicitud)
                ->json();



            if (isset($resultado["fault"])) {
                $response = $resultado["fault"]["detail"];
                flash($response)->warning();
            } else {
                Factura::where('facturaid', $factura->facturaid)->update(["autorizado" => 1]);
                $facturas = Factura::where('facturaid', $factura->facturaid)->first();
                flash('Factura autorizada')->success();
                $log = new Log();
                $log->usuario = Auth::user()->nombres;
                $log->pantalla = "Facturas";
                $log->operacion = "Autorizar";
                $log->fecha = now();
                $log->detalle =  $facturas;
                $log->save();
            }
            return back();
        } catch (\Throwable $th) {
            flash($th->getMessage())->error();
            return back();
        }
    }

}
