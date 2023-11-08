<?php

namespace App\Http\Controllers;

use App\Mail\NotificarPago;
use App\Models\Cobros;
use App\Models\Factura;
use App\Models\RenovacionLicencias;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class CobrosController extends Controller
{
    private $client;
    private $cobrosClientesController;

    public function __construct()
    {
        $this->client = new Client();
        $this->cobrosClientesController = new CobrosClientesController();
    }

    public function listado_vendedor()
    {
        return view('auth.cobros.index');
    }

    public function filtrado_listado_vendedor(Request $request)
    {
        if ($request->ajax()) {

            $estado = $request->estado;

            $cobros = Cobros::select('cobrosid', 'secuencias', 'estado', 'obs_vendedor', 'obs_revisor', 'fecha_registro')
                ->where('usuariosid', Auth::user()->usuariosid)
                ->get();

            if ($estado != "") {
                $cobros = $cobros->where('estado', $estado);
            }

            return DataTables::of($cobros)
                ->editColumn('secuencias', function ($cobro) {
                    try {
                        $secuencias = json_decode($cobro->secuencias);
                        $string = '';
                        foreach ($secuencias as $item) {
                            $string .= $item->value . ', ';
                        }
                        $string = rtrim($string, ', ');
                        return $string;
                    } catch (\Throwable $th) {
                        return $cobro->secuencias;
                    }
                })
                ->editColumn('estado', function ($cobro) {
                    if ($cobro->estado == 1) {
                        return '<span class="label label-lg font-weight-bold label-primary label-inline">Registrado</span>';
                    } else if ($cobro->estado == 2) {
                        return '<span class="label label-lg font-weight-bold label-success label-inline">Verificado</span>';
                    } else {
                        return '<span class="label label-lg font-weight-bold label-danger label-inline">Rechazado</span>';
                    }
                })
                ->editColumn('action', function ($cobro) {
                    if ($cobro->estado == 0) return;

                    $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('cobros.editar', $cobro->cobrosid) . '" title="Editar cobro"> <i class="la la-edit"></i></a>';

                    if ($cobro->estado == 10) {
                        $botones .= '<a class="btn btn-icon btn-light btn-hover-danger btn-sm" href="' . route('cobros.eliminar', $cobro->cobrosid) . '" title="Eliminar cobro"> <i class="la la-trash"></i></a>';
                    }

                    return $botones;
                })
                ->rawColumns(['action', 'estado'])
                ->make(true);
        }
    }

    public function agregar()
    {
        $bancos = $this->cobrosClientesController->obtener_bancos(Auth::user());
        return view('auth.cobros.agregar', compact('bancos'));
    }

    public function guardar(Request $request)
    {
        $request->validate(
            [
                'secuencias' => 'required',
                'numero_comprobante' => 'required:min:6',
                'banco_origen' => 'required',
                'banco_destino' => 'required',
                'estado' => 'required',
                'obs_vendedor' => 'min:0|max:255',
                'comprobante' => 'required',
            ],
            [
                'secuencias.required' => 'Se debe ingresar al menos una secuencia',
                'numero_comprobante.required' => 'El campo numero de comprobante es obligatorio',
                'numero_comprobante.min' => 'El campo numero de comprobante debe tener al menos 6 caracteres',
                'banco_origen.required' => 'El campo banco de origen es obligatorio',
                'banco_destino.required' => 'El campo banco de destino es obligatorio',
                'estado.required' => 'El campo estado es obligatorio',
                'comprobante.required' => 'Se debe subir al menos 1 comprobante',
                'obs_vendedor.max' => 'El campo observaciones no puede tener mas de 255 caracteres',
            ]
        );

        try {

            $cobro = new Cobros();
            $cobro->secuencias = $request->secuencias;
            $cobro->estado = $request->estado;
            $cobro->obs_vendedor = $request->obs_vendedor;
            $cobro->numero_comprobante = $request->numero_comprobante;
            $cobro->banco_origen = $request->banco_origen;
            $cobro->banco_destino = $request->banco_destino;

            $temp = [];
            if (isset($request->comprobante)) {
                foreach ($request->comprobante as $file) {
                    $id = uniqid("comprobante-");
                    $temp[$id] = base64_encode(file_get_contents($file->getRealPath()));
                }
            }

            $cobro->comprobante = json_encode($temp);
            $cobro->usuariosid = Auth::user()->usuariosid;
            $cobro->distribuidoresid = Auth::user()->distribuidoresid;
            $cobro->fecha_registro = now();
            $cobro->fecha_actualizacion = now();

            $cobro->save();
            $this->notificar_pago_correo($cobro);

            flash("Cobro registrado correctamente")->success();
            return redirect()->route('cobros.listado.vendedor');
        } catch (\Throwable $th) {
            flash("Error al registrar el cobro")->error();
            return back();
        }
    }

    public function editar(Cobros $cobro)
    {
        $bancos = $this->cobrosClientesController->obtener_bancos(Auth::user());
        return view('auth.cobros.editar', compact('cobro', 'bancos'));
    }

    public function actualizar(Cobros $cobro, Request $request)
    {
        $request->validate(
            [
                'secuencias' => 'required',
                'numero_comprobante' => 'required:min:6',
                'banco_origen' => 'required',
                'banco_destino' => 'required',
                'estado' => 'required',
                'obs_vendedor' => 'min:0|max:255',
            ],
            [
                'secuencias.required' => 'Se debe ingresar al menos una secuencia',
                'numero_comprobante.required' => 'El campo numero de comprobante es obligatorio',
                'numero_comprobante.min' => 'El campo numero de comprobante debe tener al menos 6 caracteres',
                'banco_origen.required' => 'El campo banco de origen es obligatorio',
                'banco_destino.required' => 'El campo banco de destino es obligatorio',
                'estado.required' => 'El campo estado es obligatorio',
                'obs_vendedor.max' => 'El campo observaciones no puede tener mas de 255 caracteres',
            ]
        );

        try {
            $cobro->secuencias = $request->secuencias;
            $cobro->estado = 1;
            $cobro->obs_vendedor = $request->obs_vendedor;
            $cobro->numero_comprobante = $request->numero_comprobante;
            $cobro->banco_origen = $request->banco_origen;
            $cobro->banco_destino = $request->banco_destino;

            $temp = [];
            if (isset($request->comprobante)) {
                foreach ($request->comprobante as $file) {
                    $id = uniqid("comprobante-");
                    $temp[$id] = base64_encode(file_get_contents($file->getRealPath()));
                }
                $cobro->comprobante = json_encode($temp);
            }

            $cobro->fecha_registro = now();
            $cobro->fecha_actualizacion = now();

            $cobro->save();
            flash("Cobro actualizado correctamente")->success();
            return back();
        } catch (\Throwable $th) {
            flash("Error al registrar el cobro")->error();
            return back();
        }
    }

    public function descargar_comprobante($cobroid, $id_unique)
    {
        $comprobantes = Cobros::select('comprobante')->where('cobrosid', $cobroid)->first();

        $comprobantes = $comprobantes->comprobante;
        $comprobantes = json_decode($comprobantes, true);

        $archivo = base64_decode($comprobantes[$id_unique]);

        return response($archivo)->header('Content-type', 'image/png');
    }

    /* -------------------------------------------------------------------------- */
    /*                     Funciones para el revisor de cobros                    */
    /* -------------------------------------------------------------------------- */

    public function listado_revisor()
    {
        return view('auth2.revisor_facturas.cobros.index');
    }

    public function filtrado_listado_revisor(Request $request)
    {
        if ($request->ajax()) {

            $estado = $request->estado;

            $cobros = Cobros::select('cobrosid', 'secuencias', 'estado', 'obs_vendedor', 'obs_revisor', 'fecha_registro')
                ->where('distribuidoresid', Auth::user()->distribuidoresid);

            if ($estado != "") {
                $cobros = $cobros->where('estado', $estado)->get();
            } else {
                $cobros = $cobros->whereIn('estado', [1, 2])->get();
            }

            return DataTables::of($cobros)
                ->editColumn('secuencias', function ($cobro) {
                    try {
                        $secuencias = json_decode($cobro->secuencias);
                        $string = '';
                        foreach ($secuencias as $item) {
                            $string .= $item->value . ', ';
                        }
                        $string = rtrim($string, ', ');
                        return $string;
                    } catch (\Throwable $th) {
                        return $cobro->secuencias;
                    }
                })
                ->editColumn('estado', function ($cobro) {
                    if ($cobro->estado == 1) {
                        return '<span class="label label-lg font-weight-bold label-primary label-inline">Registrado</span>';
                    } else if ($cobro->estado == 2) {
                        return '<span class="label label-lg font-weight-bold label-success label-inline">Verificado</span>';
                    } else {
                        return '<span class="label label-lg font-weight-bold label-danger label-inline">Rechazado</span>';
                    }
                })
                ->editColumn('action', function ($cobro) {

                    $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('cobros.editar_revisor', $cobro->cobrosid) . '" title="Editar cobro"> <i class="la la-edit"></i></a>';

                    return $botones;
                })
                ->rawColumns(['action', 'estado'])
                ->make(true);
        }
    }

    public function editar_revisor(Cobros $cobro)
    {
        $renovacion = null;
        if ($cobro->renovacionid) {
            $renovacion = RenovacionLicencias::where('renovacionid', $cobro->renovacionid)->first();
        }

        $usuario = User::find($cobro->usuariosid);
        $bancos = $this->cobrosClientesController->obtener_bancos(Auth::user());

        return view('auth2.revisor_facturas.cobros.editar', ['cobro' => $cobro, 'vendedor' => $usuario, 'renovacion' => $renovacion, 'bancos' => $bancos]);
    }

    public function actualizar_revisor(Cobros $cobro, Request $request)
    {
        try {
            $request->validate(
                [
                    'numero_comprobante' => 'required:min:6',
                    'banco_origen' => 'required',
                    'banco_destino' => 'required',
                    'estado' => 'required',
                    'obs_vendedor' => 'min:0|max:255',
                ],
                [
                    'numero_comprobante.required' => 'El campo numero de comprobante es obligatorio',
                    'numero_comprobante.min' => 'El campo numero de comprobante debe tener al menos 6 caracteres',
                    'banco_origen.required' => 'El campo banco de origen es obligatorio',
                    'banco_destino.required' => 'El campo banco de destino es obligatorio',
                    'estado.required' => 'El campo estado es obligatorio',
                    'obs_revisor.max' => 'El campo observaciones no puede tener mas de 255 caracteres',
                ]
            );

            $cobro->update($request->all());
            flash("Cobro actualizado correctamente")->success();
            return back();
        } catch (\Throwable $th) {
            flash("Error al actualizar el cobro")->error();
            return back();
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                  Funciones para registrar cobros en perseo                 */
    /* -------------------------------------------------------------------------- */

    public function registrar_cobro_sistema(Request $request)
    {
        if (!$request->forma_pago) {
            flash("Debe seleccionar la forma del pago")->error();
            return back();
        }

        if (!$request->monto || $request->monto <= 0) {
            flash("El monto debe ser mayor a 0")->error();
            return back();
        }

        if (!$request->fecha) {
            flash("Debe seleccionar la fecha del pago")->error();
            return back();
        }

        if (!$request->facturaid && !$request->cobrosid) {
            flash("No existe ninguna referencia para registrar el cobro")->warning();
            return back();
        }

        $esFactura = isset($request->facturaid) ? true : false;

        try {
            if ($esFactura) {
                $factura = Factura::findOrFail($request->facturaid, ['facturaid', 'facturaid_perseo', 'secuencia_perseo', 'total_venta', 'detalle_pagos', 'usuariosid', 'estado_pago']);
                $datos_cobro = json_decode($factura->detalle_pagos);
                $datos_cobro->forma_pago = $request->forma_pago;
                $datos_cobro->monto = $request->monto;
                $datos_cobro->fecha = date("Ymd", strtotime($request->fecha));

                $facturaid = $factura->facturaid_perseo;
            } else {
                $cobro = Cobros::findOrFail($request->cobrosid, ['banco_destino', 'numero_comprobante', 'renovacionid', 'estado', 'cobros_id_perseo']);
                $renovaciones = RenovacionLicencias::findOrFail($cobro->renovacionid, ['renovacionid', 'datos']);

                $datos = json_decode($renovaciones->datos);

                $facturaid = $datos->factura->facturaid;
                $datos_cobro = (object)[
                    'numero_comprobante' => $cobro->numero_comprobante,
                    'banco_destino' => $cobro->banco_destino,
                    'banco_origen' => $cobro->banco_origen,
                    'forma_pago' => $request->forma_pago,
                    'monto' => $request->monto,
                    'fecha' => date("Ymd", strtotime($request->fecha)),
                ];
            }

            $factura_perseo = $this->obtener_factura_perseo($facturaid);
            $cobro_registrado = $this->registro_del_cobro($factura_perseo, $datos_cobro);
            $datos_cobro->cobros_id_perseo = $cobro_registrado->cobrosid_nuevo;
            $datos_cobro->cobros_cod_perseo = $cobro_registrado->codigo_nuevo;

            if ($esFactura) {
                $factura->update(['detalle_pagos' => json_encode($datos_cobro), 'estado_pago' => 2]);
            } else {
                $data = [
                    'estado' => 2,
                    'cobros_id_perseo' => json_encode([
                        'cobros_id_perseo' => $datos_cobro->cobros_id_perseo,
                        'cobros_cod_perseo' => $datos_cobro->cobros_cod_perseo,
                        'forma_pago' => $datos_cobro->forma_pago,
                        'monto' => $datos_cobro->monto,
                    ]),
                ];
                Cobros::where('cobrosid', $request->cobrosid)->update($data);
            }

            flash("Cobro registrado correctamente")->success();
            return back();
        } catch (\Throwable $th) {
            flash("Error al registrar el cobro: " . $th->getMessage())->error();
            return back();
        }
    }

    private function obtener_factura_perseo($facturaid)
    {
        try {
            $url = Auth::user()->api;

            $body = [
                "api_key" => Auth::user()->token,
                "facturaid" => $facturaid,
            ];

            $factura = $this->client->post($url . "/facturas_consulta", ["json" => $body]);
            $factura = json_decode($factura->getBody()->getContents());

            return $factura->facturas[0];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function registro_del_cobro($factura, $datos_cobro)
    {
        try {
            $fecha = date('Ymd');

            $cobro = [
                'api_key' => Auth::user()->token,
                'registros' => [
                    0 => [
                        'cobros' => [
                            'clientesid' => $factura->clientesid,
                            'cobroscodigo' => '1', // Default
                            'cobradoresid' => Auth::user()->vendedoresid,
                            'tipo' => 'AB', // Default
                            'movimientos_conceptosid' => 3, //Default 
                            'forma_pago_empresaid' => $datos_cobro->forma_pago,
                            'concepto' => $factura->concepto,
                            'fechaemision' => $datos_cobro->fecha,
                            'fecharecepcion' => $datos_cobro->fecha,
                            'fechavencimiento' => $datos_cobro->fecha,
                            'importe' => floatval($datos_cobro->monto),
                            'cajasid' => Auth::user()->cajasid,
                            'bancosid' => $datos_cobro->banco_destino,
                            'usuariocreacion' => Auth::user()->identificacion,
                            'usuarioid' => Auth::user()->vendedoresid,
                            'detalles' => [
                                0 => [
                                    'bancoid' => 0, // Solo si es cheque o TC
                                    'cajasid' => $datos_cobro->banco_destino,
                                    'comprobante' => $datos_cobro->numero_comprobante,
                                    'importe' => floatval($datos_cobro->monto),
                                    'documentosid' => $factura->facturasid,
                                    'formapago' => $datos_cobro->forma_pago,
                                    'saldo' => 0, // Default
                                    'fechaemision' => $fecha,
                                    'fecharecepcion' => $fecha,
                                    'fechavence' => $fecha,
                                    'secuencia' => $factura->secuencial,
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $request = $this->client->post(Auth::user()->api . "/cobros_crear", ["json" => $cobro]);
            $response = json_decode($request->getBody()->getContents());
            $response_cobro = $response->cobros[0];
            return $response_cobro;
        } catch (\Throwable $th) {
            throw new \Exception("el servicio API fallo");
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                            Notificacion de cobro                           */
    /* -------------------------------------------------------------------------- */
    private function notificar_pago_correo($cobro)
    {
        try {
            $vendedor = User::firstWhere('usuariosid', $cobro->usuariosid);
            $revisor = User::where('rol', 2)->where('distribuidoresid', $vendedor->distribuidoresid)->first();

            $secuenciasAux = json_decode($cobro->secuencias);
            $secuencias = [];

            foreach ($secuenciasAux as $item) {
                array_push($secuencias, $item->value);
            }

            $correoRevisor = $revisor->correo;

            $array = [
                'from' => "noresponder@perseo.ec",
                'subject' => "Nuevo cobro registrado",
                'revisora' => $revisor->nombres,
                'sencuencias' => $secuencias,
            ];

            Mail::to($correoRevisor)->queue(new NotificarPago($array));
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                         Functiones para leer el CSV                        */
    /* -------------------------------------------------------------------------- */
    public function csv()
    {
        return view('index');
    }

    public function csv_post(Request $request)
    {
        $pagos = collect();
        $pathCSV = $request->csv->getRealPath();
        $file = fopen($pathCSV, 'r');

        $headers = array_map(function ($item) {
            return trim(strtolower($item));
        }, fgetcsv($file));

        while ($row = fgetcsv($file)) {
            $data = (object) array_combine($headers, $row);
            $documento = preg_replace('/^0+/', '', $data->documento);
            $monto = str_replace(',', '', $data->monto);
            $saldo = str_replace(',', '', $data->saldo);

            $data->documento = $documento;
            $data->monto = floatval($monto);
            $data->saldo = floatval($saldo);
            $pagos->push($data);
        }
        fclose($file);

        return view('index', compact('pagos', 'headers'));
    }
}
