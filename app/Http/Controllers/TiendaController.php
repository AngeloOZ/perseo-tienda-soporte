<?php

namespace App\Http\Controllers;

use App\Mail\NuevaCompraEmail;
use App\Models\Cupones;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\ProductoHomologado;
use App\Models\User;
use App\Rules\ValidarCelular;
use App\Rules\ValidarCorreo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TiendaController extends Controller
{
    private function consultar_productos($categoria, $das)
    {
        $tableName = ProductoHomologado::getTableName();

        $productos = ProductoHomologado::join('productos', "$tableName.id_producto_local", '=', 'productos.productosid')
            ->where("$tableName.categoria", $categoria)
            ->where("$tableName.distribuidoresid", $das)
            ->where("$tableName.estado", 1)
            ->select(
                'productos.productosid',
                'productos.descripcion',
                'productos.contenido',
                "$tableName.categoria",
                "$tableName.precioiva",
                "$tableName.precio",
                "$tableName.iva",
                "$tableName.costo",
                "$tableName.descuento",
                "$tableName.preciobase",
                "$tableName.precioivabase",
                "$tableName.productos_homologados_id",
            )
            ->get();
        return $productos;
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

        $productosList = collect([
            [
                "id" => 'facturito',
                "titulo" => "Facturito",
                "productos" => $this->consultar_productos(1, $vendedor->distribuidoresid),
            ],
            [
                "id" => 'firmas',
                "titulo" => "Firma electrónica",
                "productos" => $this->consultar_productos(2, $vendedor->distribuidoresid),
            ],
            [
                "id" => 'perseo_pc',
                "titulo" => "Perseo PC",
                'productos' => $this->consultar_productos(3, $vendedor->distribuidoresid),
            ],
            [
                "id" => 'contafacil',
                "titulo" => "Contafácil",
                "productos" => $this->consultar_productos(4, $vendedor->distribuidoresid),
            ],
            [
                "id" => 'perseo_web',
                "titulo" => "Perseo Web",
                'productos' => $this->consultar_productos(5, $vendedor->distribuidoresid),
            ],
            [
                "id" => 'whapi',
                "titulo" => "Whapi",
                'productos' => $this->consultar_productos(6, $vendedor->distribuidoresid),
            ],
            [
                "id" => 'punto_venta',
                "titulo" => "Punto de venta",
                'productos' => $this->consultar_productos(7, $vendedor->distribuidoresid),
            ],
        ])->filter(function ($item) {
            return count($item["productos"]) > 0;
        })->sortBy('titulo')->values()->toArray();

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

            if (!$factura->save()) {
                DB::rollBack();
                flash('No se pudo guardar la compra, reinténtalo de nuevo')->error();
                return redirect()->route('tienda', ['referido' => $request->referido]);
            }

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
        } catch (\Throwable $th) {
            DB::rollBack();
            flash('Error interno, por favor contacte con soporte: ' . $th->getMessage())->error();
            return redirect()->route('tienda', ['referido' => $request->referido]);
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
