<?php

namespace App\Http\Controllers;

use App\Mail\NuevaCompraEmail;
use App\Models\Cupones;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\ProductoHomologado;
use App\Models\ProductosLicenciador;
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
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\DataTables;
use App\Models\Log;
use App\Models\Tecnicos;
use Illuminate\Support\Facades\Mail;

class FacturasController extends Controller
{
    private function listarProductos2($listado)
    {
        $nuevoListado = [];
        foreach ($listado as $key => $item) {
            $producto = Producto::find($item->id_producto_local);
            $nuevoProducto = [
                "productosid" => $producto->productosid,
                "descripcion" => $producto->descripcion,
                "contenido" => $producto->contenido,
                "categoria" => $item->categoria,
                "precioiva" => $item->precioiva,
                "precio" => $item->precio,
                "iva" => $item->iva,
                "costo" => $item->costo,
                "descuento" => $item->descuento,
                "preciobase" => $item->preciobase,
                "precioivabase" => $item->precioivabase,
                "productos_homologados_id" => $item->productos_homologados_id,
            ];
            $temp = json_encode($nuevoProducto);
            $nuevoProducto = json_decode($temp);
            $nuevoListado = [...$nuevoListado, $nuevoProducto];
        }
        return $nuevoListado;
    }

    public function listar_productos($referido)
    {
        $vendedor = User::findOrFail($referido);
        $stateCupon = [
            "exists" => false,
            "activo" => false,
        ];

        if (isset($_GET["cupon"]) || isset($_COOKIE["cupon_code"])) {
            CuponesController::validarCupones();
            $stateCupon["exists"] = true;

            $codigo = isset($_GET["cupon"]) ? $_GET["cupon"] : $_COOKIE["cupon_code"];
            $cupon = Cupones::where('codigo', $codigo)->where('estado', '1')->first();

            if ($cupon) {
                if ($cupon->vendedor != $vendedor->usuariosid) {
                    flash('El cupón no es válido para este vendedor')->error();
                    return redirect()->route('tienda', ['referido' => $vendedor->usuariosid]);
                }
                $stateCupon["activo"] = true;
                setcookie("cupon_code", $cupon->codigo, time() + (60 * 8), "/");
            } else {
                setcookie("cupon_code", "", time() - 3600);
            }
        }

        $facturito = ProductoHomologado::all()->where('categoria', 1)->where('distribuidoresid', $vendedor->distribuidoresid)->where('estado', 1);
        $firmas = ProductoHomologado::all()->where('categoria', 2)->where('distribuidoresid', $vendedor->distribuidoresid)->where('estado', 1);
        $perseoPC = ProductoHomologado::all()->where('categoria', 3)->where('distribuidoresid', $vendedor->distribuidoresid)->where('estado', 1);
        $contafacil = ProductoHomologado::all()->where('categoria', 4)->where('distribuidoresid', $vendedor->distribuidoresid)->where('estado', 1);
        $perseoWEB = ProductoHomologado::all()->where('categoria', 5)->where('distribuidoresid', $vendedor->distribuidoresid)->where('estado', 1);
        $whapi = ProductoHomologado::all()->where('categoria', 6)->where('distribuidoresid', $vendedor->distribuidoresid)->where('estado', 1);

        $productosList = [
            "contafacil" => $this->listarProductos2([...$contafacil]),
            "facturito" => $this->listarProductos2([...$facturito]),
            "firmas" => $this->listarProductos2([...$firmas]),
            "perseo_pc" => $this->listarProductos2([...$perseoPC]),
            "perseo_web" => $this->listarProductos2([...$perseoWEB]),
            "whapi" => $this->listarProductos2([...$whapi]),
        ];

        return view('tienda.tienda', ["productos" => $productosList, "vendedor" => $vendedor, "cupon" => $stateCupon]);
    }

    public function resumen_compra($referido)
    {
        $cliente = null;
        $cupon = [
            "cupon" => null,
            "exists" => false,
            "isValid" => false,
        ];

        if (isset($_COOKIE["cxt"])) {
            $cliente = json_decode($_COOKIE["cxt"]);
        }

        if (isset($_COOKIE["cupon_code"])) {
            $cupon = Cupones::where('codigo', $_COOKIE["cupon_code"])->where('estado', '1')->first();
            if ($cupon == null) {
                setcookie("cupon_code", "", time() - 3600);
                $cupon = [
                    "cupon" => null,
                    "exists" => true,
                    "isValid" => false,
                ];
            } else {
                $cupon = [
                    "cupon" => $cupon,
                    "exists" => true,
                    "isValid" => true,
                ];
            }
        }

        $vendedor = User::findOrFail($referido);
        return view('tienda.checkout', ["vendedor" => $vendedor, "cliente" => $cliente, "cupon" => $cupon]);
    }

    public function finalizar_compra($referido, $pago = null)
    {
        if (isset($_COOKIE["cxt"])) {

            $vendedor = User::findOrFail($referido);
            if ($pago == "tarjeta" && $vendedor->correo_pagoplux == null) {
                flash('El pago con tarjeta no esta disponible')->warning();
                return redirect()->route('tienda.finalizar_compra', ['referido' => $vendedor->usuariosid, 'pago' => 'transferencia']);
            }

            $cart = isset($_COOKIE['cart_tienda']) ? json_decode($_COOKIE['cart_tienda']) : [];

            $cupon = null;
            if (isset($_COOKIE["cupon_code"])) {
                $codigo = $_COOKIE["cupon_code"];
                $cupon = Cupones::where('codigo', $codigo)->where('estado', '1')->first();
            }
            $valorDescuento = $cupon != null ? $cupon->descuento : 0;

            $newCart = [
                "items" => [],
                "subTotal" => 0,
                "descuento" => 0,
                "iva" => 0,
                "total" => 0,
                "recargo" => 0,
                "tipo_pago" => $pago,
            ];

            if (!empty($cart)) {
                foreach ($cart as $item) {
                    $producto = ProductoHomologado::findOrFail($item->productos_homologados_id);
                    $productoAux = [
                        "productos_homologados_id" => $producto->productos_homologados_id,
                        "productosid" => $item->productosid,
                        "descripcion" => $item->descripcion,
                        "categoria" => $item->categoria,
                        "cantidad" => $item->cantidad,
                        "precioiva" => $producto->precioiva,
                        "precio" => $producto->precio,
                        "iva" => $producto->iva,
                    ];

                    $newCart["items"] = [...$newCart["items"], $productoAux];

                    $precioBase = $producto->precio;

                    $decuento = ($precioBase * $valorDescuento) / 100;
                    $decuento = floatval(number_format($decuento, 2));
                    $precioBaseConDescuento = $precioBase - $decuento;

                    $iva = ($precioBaseConDescuento * $producto->iva) / 100;
                    $iva = floatval(number_format($iva, 3));
                    $precioConIVA = $precioBaseConDescuento + $iva;


                    $newCart["subTotal"] += $precioBase * $item->cantidad;
                    $newCart["descuento"] += $decuento * $item->cantidad;
                    $newCart["iva"] += $iva * $item->cantidad;
                    $newCart["total"] += $precioConIVA * $item->cantidad;
                }
            }

            if ($pago == "tarjeta" && $vendedor->recargo_pagoplux != 0) {
                $recargo = ($newCart["total"] * $vendedor->recargo_pagoplux) / 100;
                $newCart["recargo"] = $recargo;
                $newCart["total"] = $newCart["total"] + $recargo;
            }

            $newCart = json_encode($newCart);
            $newCart = json_decode($newCart);
            $cliente = json_decode($_COOKIE["cxt"]);

            return view("tienda.finalizar_compra", ["vendedor" => $vendedor, "cliente" => $cliente, "carrito" => $newCart]);
        }
        return redirect()->route('tienda.checkout', $referido);
    }

    public function registar_compra(Request $request)
    {
        try {
            $request->validate(
                [
                    'identificacion' => 'required',
                    'nombre' => 'required',
                    'direccion' => 'required',
                    'telefono' => ['required', new ValidarCelular],
                    'correo' => ['required', new ValidarCorreo],
                    'productos' => 'required',
                    'observacion' => 'max:150'
                ],
                [
                    'identificacion.required' => 'Ingrese la identificación',
                    'nombre.required' => 'Ingrese el nombre',
                    'direccion.required' => 'Ingrese la dirección',
                    'telefono.required' => 'Ingrese el teléfono',
                    'correo.required' => 'Ingrese el correo',
                    'productos.required' => 'La factura debe tener productos',
                ],
            );

            DB::beginTransaction();
            $cupon = null;
            if (isset($request->cupon_code)) {
                CuponesController::validarCupones();
                $cupon = Cupones::where('codigo', $request->cupon_code)->where('estado', '1')->first();

                if ($cupon == null) {
                    setcookie("cupon_code", "", time() - 3600, "/");
                    flash('El cupón que intentas usar ya ha expirado')->warning();
                    return redirect()->route('tienda', ['referido' => $request->referido]);
                }
            }

            $comproFirma = $request->redireccion;
            $comproFirma = ($comproFirma == "true") ? true : false;

            $vendedor = User::findOrFail($request->referido);
            $factura = new Factura();
            $factura->identificacion = $request->identificacion;
            $factura->nombre = $request->nombre;
            $factura->direccion = $request->direccion;
            $factura->correo = $request->correo;
            $factura->telefono = str_replace(' ', '', $request->telefono);
            $factura->productos = $request->productos;
            $factura->observacion = $request->observacion;
            $factura->usuariosid = $vendedor->usuariosid;
            $factura->facturado = 0;
            $factura->fecha_creacion = now();
            $factura->cuponid = $cupon != null ? $cupon->cuponid : null;
            $factura->distribuidoresid = $vendedor->distribuidoresid;

            if (isset($request->id_transaccion) && isset($request->voucher)) {
                $factura->observacion_pago_vendedor = "Pago realizado mediante tarjeta de credito/debito\nTipo: " . $request->nombre_tarjeta . "\nVaucher: " . $request->voucher . "\nNo de transaccion: " . $request->id_transaccion;

                $factura->pago_tarjeta = json_encode([
                    "id_transaccion" => $request->id_transaccion,
                    "voucher" => $request->voucher,
                    "nombre_tarjeta" => $request->nombre_tarjeta,
                ]);
            }

            if (isset($request->comprobante_pago)) {
                $temp = [];
                foreach ($request->comprobante_pago as $file) {
                    $id = uniqid("comprobante-");
                    $temp[$id] = base64_encode(file_get_contents($file->getRealPath()));
                }
                $factura->comprobante_pago = json_encode($temp);
            }

            if ($factura->save()) {

                $this->notificar_nueva_venta($factura);

                Cookie::forget('cxt');
                Cookie::forget('cart_tienda');
                Cookie::forget('cupon_code');
                if ($cupon != null) {
                    $cupon->veces_usado += 1;
                    $cupon->save();
                    CuponesController::validarCupones();
                }

                DB::commit();

                if ($comproFirma) {
                    flash('Compra registrada, complete los datos de la firma')->success();
                    return redirect()->route('inicio', ['id' => $request->referido]);
                }
                flash('Compra registrada')->success();
                return redirect()->route('tienda', ['referido' => $request->referido]);
            } else {
                DB::rollBack();
                flash('No se pudo guardar la compra, reinténtalo de nuevo')->error();
                return redirect()->route('tienda', ['referido' => $request->referido]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            flash('Error interno, por favor contacte con soporte: ' . $th->getMessage())->error();
            return redirect()->route('tienda', ['referido' => $request->referido]);
        }
    }

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
    /*                      funciones para liberar productos                      */
    /* -------------------------------------------------------------------------- */

    private function validar_lincecias($ruc)
    {
        try {
            if (!$ruc) {
                return json_decode(json_encode([]));
            }

            $url = "https://perseo.app/api/consultar_licencia_web";

            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'Perseo', "clave" => "Perseo1232*"])
                ->withOptions(["verify" => false])
                ->post($url, ['identificacion' => $ruc])
                ->json();

            return json_decode(json_encode($resultado));
        } catch (\Throwable $th) {
            return json_decode(json_encode([]));
        }
    }

    public function vista_liberar_producto(Factura $factura, $ruc = null)
    {
        try {
            $licencias =  $this->validar_lincecias($ruc);
            $productos_contadores = [62, 63, 64, 65];
            $vendedorSIS = null;
            $promocion = 0;
            $contador = [
                "esContador" => false,
                "error" => false,
            ];

            if ($factura->cuponid) {
                $cupon = Cupones::where('cuponid', $factura->cuponid)->first();
                if ($cupon->tipo == 2) {
                    $promocion = 1;
                }
            }

            if ($factura->liberado == 0) {
                $url = "https://perseo.app/api/vendedores_consulta";

                $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'Perseo', "clave" => "Perseo1232*"])
                    ->withOptions(["verify" => false])
                    ->post($url, ['identificacion' => substr(Auth::user()->identificacion, 0, 10)])
                    ->json();

                $vendedorSIS = $resultado["vendedor"][0];

                if ($vendedorSIS == null) {
                    flash("Usuario no registrado en el licenciador como vendedor")->warning();
                    return back();
                }
                $vendedorSIS = json_decode(json_encode($vendedorSIS));
            }

            $productos = json_decode($factura->productos);
            $productos_liberables = [];

            foreach ($productos as $producto) {
                $queryProducto = Producto::find($producto->productoid);
                $producto->descripcion = $queryProducto->descripcion;
                $producto->tipo = $queryProducto->tipo;
                $producto->licenciador = $queryProducto->licenciador;

                if (in_array($producto->productoid, $productos_contadores)) {
                    $contador["esContador"] = true;
                }

                if (!isset($producto->liberado)) {
                    $producto->liberado = 1;

                    if ($factura->liberado == 0) {
                        $producto->liberado = 0;
                        if ($producto->licenciador == 0) {
                            $producto->liberado = 2;
                        }
                    }

                    if ($producto->categoria == 2) {
                        $producto->liberado = 3;
                    }
                }

                if ($producto->licenciador == 1) {
                    $productos_liberables = [...$productos_liberables, [
                        "producto_id" => $queryProducto->productosid,
                        "tipo" => $queryProducto->tipo,
                    ]];
                }
            }

            if (count($productos_liberables) > 1) {
                $contador["error"] = true;
            }

            if ($licencias != null) {
                if ($licencias->liberar &&  $licencias->id_producto != 0) {
                    foreach ($productos_liberables as $producto) {
                        $productoAux = ProductosLicenciador::firstWhere('id_producto_local', $producto['producto_id']);
                        if ($productoAux->id_licenciador != $licencias->id_producto) {
                            $licencias->liberar = false;
                        }
                    }
                }
            }

            return view('auth.facturas.liberar.index', [
                "factura" => $factura,
                "productos" => $productos,
                "productos_liberables" => $productos_liberables,
                "vendedorSIS" => $vendedorSIS,
                "ruc_renovacion" => $ruc,
                "licencias" => $licencias,
                "promocion" => $promocion,
                "contador" => (object)$contador,
            ]);
        } catch (\Throwable $th) {
            flash("Error interno: " . $th->getMessage())->error();
            return back();
        }
    }

    public function liberar_producto(Factura $factura, Request $request)
    {
        try {
            $url = "https://perseo.app/api/registrar_licencia";
            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'Perseo', "clave" => "Perseo1232*"])
                ->withOptions(["verify" => false])
                ->post($url, $request->licenciador)
                ->json();

            if (isset($resultado["licencia"])) {
                if ($resultado["licencia"][0] == "Creado correctamente") {
                    try {
                        $productos = json_decode(json_encode($request->productos));
                        foreach ($productos as $item) {
                            if ($item->licenciador == 1 && $item->liberado == 0) {
                                $item->liberado = 1;
                            }
                        }

                        Factura::where('facturaid', $factura->facturaid)->update(["liberado" => 1, 'productos' => $productos]);
                        $nuevo = Factura::firstWhere('facturaid', $factura->facturaid);

                        $log = new Log();
                        $log->usuario = Auth::user()->nombres;
                        $log->pantalla = "Facturas";
                        $log->operacion = "Liberar Licencia";
                        $log->fecha = now();
                        $log->detalle =  $nuevo;
                        $log->save();

                        return response(["status" => 200, "message" => "Licencias liberadas correctamente", "sms" => $resultado["licencia"][0]], 200)->header('Content-Type', 'application/json');
                    } catch (\Throwable $th) {
                        return response(["status" => 201, "message" => "Licencias liberadas con errores: " . $th->getMessage(), "sms" => $resultado["licencia"][0]], 201)->header('Content-Type', 'application/json');
                    }
                } else {
                    return response(["status" => 400, "message" => $resultado["licencia"][0], "sms" => $resultado["licencia"][0]], 400)->header('Content-Type', 'application/json');
                }
            }
            return response(["status" => 400, "message" => "No se pudo liberar las licencias"], 400)->header('Content-Type', 'application/json');
        } catch (\Throwable $th) {
            return response(["status" => 500, "message" => $th->getMessage()], 500)->header('Content-Type', 'application/json');
        }
    }

    public function renovar_licencia(Factura $factura, Request $request)
    {
        try {
            $url = "https://perseo.app/api/renovar_web";
            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'Perseo', "clave" => "Perseo1232*"])
                ->withOptions(["verify" => false])
                ->post($url, $request->renovacion)
                ->json();

            if (isset($resultado["renovar"]) && $resultado["renovar"]) {
                try {
                    $productos = json_decode(json_encode($request->productos));

                    foreach ($productos as $item) {
                        if ($item->licenciador == 1 && $item->liberado == 0) {
                            $item->liberado = 1;
                        }
                    }

                    Factura::where('facturaid', $factura->facturaid)->update(["liberado" => 1, 'productos' => $productos]);

                    $nueva = Factura::firstWhere('facturaid', $factura->facturaid);

                    $log = new Log();
                    $log->usuario = Auth::user()->nombres;
                    $log->pantalla = "Facturas";
                    $log->operacion = "Renovacion Licencia";
                    $log->fecha = now();
                    $log->detalle = $nueva;
                    $log->save();

                    return response(["status" => 200, "message" => "Licencia renovada correctamente"], 200)->header('Content-Type', 'application/json');
                } catch (\Throwable $th) {
                    return response(["status" => 201, "message" => "Licencia renovada pero no se pudo actualizar el estado interno: " . $th->getMessage()], 201)->header('Content-Type', 'application/json');
                }
            } else {
                return response(["status" => 400, "message" => "Hubo un error al renovar la licencia, es posible que se haya perdido la conexión o el cliente al que tratas de renovar no te pertenezca"], 400)->header('Content-Type', 'application/json');
            }

            return $request->all();
        } catch (\Throwable $th) {
            dd($th);
            return response(["status" => 500, "message" => "Hubo un error al renovar la licencia, compruebe en el administrador si se renovo la licencia. " . $th->getMessage()], 500)->header('Content-Type', 'application/json');
        }
    }

    public function reactivar_liberacion(Factura $factura)
    {
        try {
            $productos = json_decode($factura->productos);
            foreach ($productos as $producto) {
                if ($producto->licenciador == 1 && $producto->liberado == 1) {
                    $producto->liberado = 0;
                }
            }

            $factura->productos = json_encode($productos);
            $factura->liberado = 0;
            $factura->save();

            $log = new Log();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Facturas";
            $log->operacion = "Reactivar liberacion";
            $log->fecha = now();
            $log->detalle = $factura;
            $log->save();

            flash("Liberacion reactivada")->success();
            return back();
        } catch (\Throwable $th) {
            flash("No se pudo reactivar la liberacion: " . $th->getMessage())->error();
            return back();
        }
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
            $data = Factura::select('facturas.facturaid', 'facturas.identificacion', 'facturas.nombre', 'facturas.concepto', 'facturas.telefono', 'facturas.secuencia_perseo', 'facturas.facturado', 'facturas.autorizado', 'facturas.fecha_creacion', 'facturas.estado_pago', 'facturas.liberado', 'usuarios.nombres as vendedor')
                ->join('usuarios', 'usuarios.usuariosid', '=', 'facturas.usuariosid')
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

            return DataTables::of($data)
                ->editColumn('fecha_creacion', function ($fecha) {
                    $date = new DateTime($fecha->fecha_creacion);
                    return $date->format('d-m-Y');
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
                ->rawColumns(['action', 'estado', 'fecha_creacion', 'liberado'])
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

    /* -------------------------------------------------------------------------- */
    /*                             funciones genericas                            */
    /* -------------------------------------------------------------------------- */

    private function notificar_nueva_venta(Factura $factura)
    {
        try {
            $vendedor = User::find($factura->usuariosid);
            $productosFac = json_decode($factura->productos);
            $productosFac = Collect($productosFac);

            $productos = $productosFac->map(function ($item, $key) {
                $producto = Producto::select('descripcion')->find($item->productoid);
                return $producto->descripcion;
            });

            $array = [
                'from_name' => 'Tiendita Perseo',
                'from' => "noresponder@perseo.ec",
                'subject' => "Nueva venta registrada",
                'revisora' => $vendedor->nombres,
                'ruc' => $factura->identificacion,
                'razon_social' => $factura->nombre,
                'correo' => $factura->correo,
                'whatsapp' => $factura->telefono,
                'productos' => $productos,
            ];

            Mail::to($vendedor->correo)->queue(new NuevaCompraEmail($array));
        } catch (\Throwable $th) {
            flash("No se pudo enviar el correo de notificación")->warning();
        }
    }
}
