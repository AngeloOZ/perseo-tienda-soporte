<?php

namespace App\Http\Controllers;

use App\Events\RegistrarCobro;
use App\Mail\NotificarPago;
use App\Models\Cobros;
use App\Models\Factura;
use App\Models\Log;
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

            $log = new Log();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Cobros";
            $log->operacion = "registrar";
            $log->fecha = now();
            $log->detalle = json_encode($cobro);
            $log->save();

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

            $log = new Log();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Cobros";
            $log->operacion = "actualizar";
            $log->fecha = now();
            $log->detalle = json_encode($cobro);
            $log->save();

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

            $log = new Log();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Cobros - Revisor";
            $log->operacion = "actualizar";
            $log->fecha = now();
            $log->detalle = json_encode($cobro);
            $log->save();

            flash("Cobro actualizado correctamente")->success();
            return back();
        } catch (\Throwable $th) {
            flash("Error al actualizar el cobro")->error();
            return back();
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                       Funciones para la API de cobros                      */
    /* -------------------------------------------------------------------------- */

    public function verificar_estado_cobro(Request $request)
    {
        $das = 1;
        $secuencia = $request->input('secuencia', null);
        $ruc = $request->input('ruc', null);
        $vendedorid = $request->input('vendedorid', null);

        $vendedor = User::where('rol', 1)
            ->where('distribuidoresid', $das)
            ->where('vendedoresid', $vendedorid)
            ->first(['usuariosid']);

        $respuesta = (object)[
            "modulo" => "n/a",
            "estado" => "Cobro no registrado en la tienda",
            "comprobantes" => [],
        ];

        if (!$vendedor) {
            $respuesta->estado = "Vendedor no registrado en la tienda";
            return $respuesta;
        }

        try {
            $factura = Factura::select('facturaid', 'estado_pago', 'comprobante_pago')
                ->where('distribuidoresid', $das)
                ->where('facturado', 1)
                ->where('usuariosid', $vendedor->usuariosid)
                ->where('identificacion', $ruc)
                ->where('secuencia_perseo', 'like', "%$secuencia%")
                ->latest('facturaid')
                ->first();

            if ($factura) {
                $respuesta->modulo = "facturacion";
                $comprobantes = json_decode($factura->comprobante_pago, true);
                $respuesta->comprobantes = array_values($comprobantes);

                switch ($factura->estado_pago) {
                    case 0:
                        $respuesta->estado = "No pagado";
                        break;
                    case 1:
                        $respuesta->estado = "Pagado no verificado";
                        break;
                    case 2:
                        $respuesta->estado = "Pagado y verificado";
                        break;
                }
            } else {
                $cobro = Cobros::select('estado', 'comprobante')
                    ->where('distribuidoresid', $das)
                    ->where('usuariosid', $vendedor->usuariosid)
                    ->where('secuencias', 'like', "%$secuencia%")
                    ->latest('cobrosid')
                    ->first();

                if ($cobro) {
                    $respuesta->modulo = "cobros";
                    switch ($cobro->estado) {
                        case 1:
                            $respuesta->estado = "Pagado no verificado";
                            break;
                        case 2:
                            $respuesta->estado = "Pagado y verificado";
                            break;
                        case 3:
                            $respuesta->estado = "Pago rechazado";
                            break;
                    }

                    $comprobantes = json_decode($cobro->comprobante, true);
                    $respuesta->comprobantes = array_values($comprobantes);
                }
            }

            return $respuesta;
        } catch (\Throwable $th) {
            $respuesta->estado = "error servidor";
            $respuesta->mensaje = $th->getMessage();
            return $respuesta;
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

            RegistrarCobro::dispatch($request, $esFactura);

            $log = new Log();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Cobros - Revisor";
            $log->operacion = "registro sistema";
            $log->fecha = now();
            $log->detalle = json_encode($request->all());
            $log->save();

            flash("Cobro registrado correctamente")->success();
            return back();
        } catch (\Throwable $th) {
            flash("Error: " . $th->getMessage())->error();
            return back();
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
