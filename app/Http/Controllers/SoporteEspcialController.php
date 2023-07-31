<?php

namespace App\Http\Controllers;

use App\Mail\NotificacionCapacitacion;
use App\Mail\NotificacionEstadoCapacitacion;
use App\Models\Log;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\SoporteEspecial;
use App\Models\User;
use App\Rules\ValidarCelular;
use App\Rules\ValidarCorreo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class SoporteEspcialController extends Controller
{
    //
    public function listar_soporte_especial(Request $request)
    {
        return view('soporte.admin.tecnico.demos.index');
    }

    public function filtrado_soporte_especial_tecnico(Request $request)
    {
        if ($request->ajax()) {
            $data = SoporteEspecial::select('soporteid', 'ruc', 'razon_social', 'correo', 'whatsapp', 'estado', 'tipo', 'plan')
                ->where('tecnicoid', Auth::user()->usuariosid)
                ->when($request->plan, function($query, $plan){
                    $query->where('plan', $plan);
                })
                ->when($request->tipo, function ($query, $tipo) {
                    return $query->where('tipo', $tipo);
                })
                ->when($request->estado, function ($query, $estado) {
                    return $query->where('estado', $estado);
                })
                ->when($request->fecha, function ($query, $fecha) {
                    $dates = explode(" / ", $fecha);

                    $date1 = strtotime($dates[0]);
                    $desde = date('Y-m-d H:i:s', $date1);

                    $date2 = strtotime($dates[1] . ' +1 day -1 second');
                    $hasta = date('Y-m-d H:i:s', $date2);

                    return $query->whereBetween("fecha_creacion", [$desde, $hasta]);
                })
                ->get();

            return DataTables::of($data)
                ->editColumn('estado', function ($soporte) {
                    return $this->obtener_estado_soporte($soporte->estado);
                })
                ->editColumn('tipo', function ($soporte) {
                    return $this->obtener_tipo_soporte($soporte->tipo);
                })
                ->editColumn('plan', function($soporte){
                    return $this->obtener_plan_soporte($soporte->plan);
                })
                ->editColumn('acciones', function ($soporte) {
                    $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('sop.editar_soporte_especial', $soporte->soporteid) . '"  title="Editar"> <i class="la la-edit"></i> </a>';
                    return $botones;
                })
                ->rawColumns(['acciones', 'estado'])
                ->make(true);
        }
    }

    public function agregar_soporte_especial()
    {
        $tecnicos = $this->obtener_tecnicos_distribuidor();
        return view('soporte.admin.tecnico.demos.agregar', compact('tecnicos'));
    }

    public function registrar_soporte_especial(Request $request)
    {
        $validate1 = [
            'tipo' => 'required',
            'ruc' => 'required',
            'razon_social' => 'required',
            'correo' => ['required', new ValidarCorreo],
            'whatsapp' => ['required', new ValidarCelular],
            'estado' => 'required',
            'fecha_agendado' => 'required',
            'plan' => 'required',
            'tecnico' => 'required',
        ];
        $validate2 = [
            'tipo.required' => 'Seleccione un tipo de soporte',
            'ruc.required' => 'Ingrese el RUC',
            'razon_social.required' => 'Ingrese la razón social',
            'correo.required' => 'Ingrese un correo electrónico',
            'whatsapp.required' => 'Ingrese un número celular',
            'estado.required' => 'Seleccione un estado',
            'fecha_agendado.required' => 'Debe seleccionar una fecha',
            'plan.required' => 'Seleccione un plan',
            'tecnico.required' => 'Seleccione un técnico',
        ];

        $request->validate($validate1, $validate2);

        try {
            $soporte = new SoporteEspecial();
            $soporte->ruc = $request->ruc;
            $soporte->razon_social = $request->razon_social;
            $soporte->correo = $request->correo;
            $soporte->whatsapp = $request->whatsapp;
            $soporte->estado = $request->estado;
            $soporte->tipo = $request->tipo;
            $soporte->fecha_creacion = now();
            $soporte->fecha_agendado = $request->fecha_agendado;
            $soporte->plan = $request->plan;
            $soporte->tecnicoid = $request->tecnico;

            $soporte->save();
            flash("Soporte registrado")->success();

            $this->notificar_asignacion($soporte->tecnicoid);

            $log = new Log();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Soporte Especial";
            $log->operacion = "Agregar";
            $log->fecha = now();
            $log->detalle = $soporte;
            $log->save();

            if (Auth::user()->rol == 7) {
                return redirect()->route('soporte.revisor_listar_soporte_especial');
            }
            return redirect()->route('sop.listar_soporte_especial');
        } catch (\Throwable $th) {
            flash("Hubo un error al registrar el soporte: " . $th->getMessage())->error();
            return back();
        }
    }

    public function editar_soporte_especial(SoporteEspecial $soporte)
    {
        $tecnicos = $this->obtener_tecnicos_distribuidor();
        $controller = new TicketSoporteController();

        $bindings = [
            'soporte' => $soporte,
            'tecnicos' => $tecnicos,
            "historialTickets" => $controller->obtener_historial_tickets($soporte->ruc),
            "historialCapacitaciones" => $controller->obtener_historial_implementaciones($soporte->ruc, $soporte->soporteid),
        ];

        return view('soporte.admin.tecnico.demos.editar', $bindings);
    }

    public function actualizar_soporte_especial(SoporteEspecial $soporte, Request $request)
    {
        $request->validate(
            [
                'ruc' => 'required',
                'razon_social' => 'required',
                'correo' => ['required', new ValidarCorreo],
                'whatsapp' => ['required', new ValidarCelular],
                'estado' => 'required',
                'fecha_agendado' => 'required',
            ],
            [
                'ruc.required' => 'Ingrese el RUC',
                'razon_social.required' => 'Ingrese la razón social',
                'correo.required' => 'Ingrese un correo electrónico',
                'whatsapp.required' => 'Ingrese un número celular',
                'estado.required' => 'Seleccione un estado',
                'fecha_agendado.required' => 'Debe seleccionar una fecha',
            ],
        );

        $data = $request->except(['_method', '_token', 'tecnico']);
        $data['fecha_actualizado'] = now();


        if ($request->tecnico) {
            $data["tecnicoid"] = $request->tecnico;
        }

        switch ($request->estado) {
            case 3:
                if (!$soporte->fecha_iniciado) $data['fecha_iniciado'] = now();
                break;
            case 3:
                if (!$soporte->fecha_iniciado) $data['fecha_iniciado'] = now();
                break;
            case 7:
                if ($request->estado != $soporte->estado) {
                    if (strtotime($request->fecha_agendado) == strtotime($soporte->fecha_agendado)) {
                        flash("La fecha de reagendamiento es la misma que la anterior")->warning();
                        return back();
                    }
                    $data["veces_reagendado"] = $soporte->veces_reagendado + 1;
                }
                break;
        }

        if (in_array($request->estado, [9, 10, 11])) {
            $data['fecha_finalizado'] = now();
        }

        if (in_array($request->estado, [4, 6])) {
            if ($request->estado != $soporte->estado) {
                $this->notificar_nuevo_estado($soporte, $request->estado);
            }
        }

        if ($request->tecnico != $soporte->tecnicoid) {
            $this->notificar_asignacion($request->tecnico);
        }

        try {

            $soporte->update($data);

            $log = new Log();
            $soporteLog =  SoporteEspecial::find($soporte->soporteid);
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Soporte Especial";
            $log->operacion = "Actualizar";
            $log->fecha = now();
            $log->detalle = $soporteLog;
            $log->save();

            flash("Soporte actualizado correctamente")->success();
            return back();
        } catch (\Throwable $th) {
            flash("No se pudo actualizar el soporte: " . $th->getMessage())->error();
            return back();
        }
    }

    public function registrar_actividad_soporte(SoporteEspecial $soporte, Request $request)
    {
        try {
            $data = [];
            if ($soporte->actividades) {
                $data = json_decode($soporte->actividades, true);
                $data[] = ["fecha" => now(), "escritor" => Auth::user()->nombres, "contenido" => $request->contenido];
            } else {
                $data[] = ["fecha" => now(), "escritor" => Auth::user()->nombres, "contenido" => $request->contenido];
            }

            $soporte->update(["actividades" => json_encode($data)]);
            return response(["status" => 200, "message" => "Actividad registrada"], 200)->header('Content-Type', 'application/json');
        } catch (\Throwable $th) {
            return response(["status" => 500, "message" => $th->getMessage()], 500)->header('Content-Type', 'application/json');
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                    Funciones crear desde los vendedores                    */
    /* -------------------------------------------------------------------------- */

    public function registrar_capacitacion_ventas(Factura $factura, Request $request)
    {
        $request->validate(
            [
                'nombre2' => 'required',
                'correo2' => ['required', new ValidarCorreo],
                'whatsapp' => ['required', new ValidarCelular],
            ],
            [
                'nombre2.required' => 'Ingrese el nombre de la persona que asistirá a la capacitación',
                'correo2.required' => 'Ingrese un correo electrónico',
                'whatsapp.required' => 'Ingrese un número celular',
            ],
        );


        $productos = json_decode($factura->productos);

        $soporte = new SoporteEspecial();
        $soporte->ruc = $factura->identificacion;
        $soporte->razon_social = $request->nombre2 ?? $factura->nombre;
        $soporte->correo = $request->correo2 ?? $factura->correo;
        $soporte->whatsapp = $request->whatsapp;
        $soporte->estado = 1;
        $soporte->tipo = 2;
        $soporte->fecha_creacion = now();

        $contenido = "<h3>Capacitación registrada desde la tienda</h3><br/>";
        $contenido .= "<p><strong>Observación: </strong> {$factura->observacion}</p>";
        $contenido .= "<p><strong>Facturado a: </strong> {$factura->nombre}</p>";
        $contenido .= "<p><strong>Secuencia de factura: </strong> {$factura->secuencia_perseo}</p>";
        $contenido .= "<p>Listado de productos</p><ul>";

        foreach ($productos as $producto) {
            $productoAux = Producto::find($producto->productoid, ["descripcion"]);
            $contenido .= "<li>" . $productoAux->descripcion . "</li>";
        }

        $contenido .= "</ul>";
        $soporte->actividades = json_encode([["fecha" => now(), "escritor" => Auth::user()->nombres, "contenido" => $contenido]]);

        try {
            $soporte->save();
            $factura->capacitacionid = $soporte->soporteid;
            $factura->save();

            $this->notificar_nuevo_registro($soporte, $factura);
            ComisionesController::actualizar_comision($factura, $soporte->soporteid);

            $log = new Log();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Soporte Especial";
            $log->operacion = "Agregar";
            $log->fecha = now();
            $log->detalle = $soporte;
            $log->save();

            flash("Capacitación registrada exitosamente")->success();
            return back();
        } catch (\Throwable $th) {
            flash("Hubo un error al registrar el soporte: " . $th->getMessage())->error();
            return back();
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                       Funciones para rol de revisor                        */
    /* -------------------------------------------------------------------------- */

    public function revisor_listar_soporte_especial(Request $request)
    {
        $tecnicos = $this->obtener_tecnicos_distribuidor();
        return view('soporte.admin.revisor.demos.index', ['tecnicos' => $tecnicos]);
    }

    public function filtrado_soporte_especial(Request $request)
    {
        if ($request->ajax()) {
            $data = SoporteEspecial::select('soporteid', 'ruc', 'razon_social', 'correo', 'whatsapp', 'estado', 'tipo', 'tecnicoid')
                ->whereNull('tecnicoid')
                ->get();

            if ($request->asignados != "" && $request->asignados != null) {
                $data = SoporteEspecial::select(
                    'soportes_especiales.soporteid',
                    'soportes_especiales.ruc',
                    'soportes_especiales.razon_social',
                    'soportes_especiales.correo',
                    'soportes_especiales.estado',
                    'soportes_especiales.whatsapp',
                    'soportes_especiales.tipo',
                    'soportes_especiales.tecnicoid',
                    'usuarios.usuariosid',
                    'usuarios.nombres',
                    'usuarios.distribuidoresid',
                )
                    ->join('usuarios', 'usuarios.usuariosid', 'soportes_especiales.tecnicoid')
                    ->when($request->tecnico, function ($query, $tecnico) {
                        return $query->where('tecnicoid', $tecnico);
                    })
                    ->when($request->distribuidor, function ($query, $distribuidor) {
                        return $query->where('usuarios.distribuidoresid', $distribuidor);
                    })
                    ->when($request->estado, function ($query, $estado) {
                        return $query->where('soportes_especiales.estado', $estado);
                    })
                    ->when($request->tipo, function ($query, $tipo) {
                        return $query->where('soportes_especiales.tipo', $tipo);
                    })
                    ->when($request->fecha, function ($query, $fecha) {
                        if ($fecha != "") {
                            $date1 = explode(" / ", $fecha)[0];
                            $date1 = strtotime($date1);
                            $desde = date('Y-m-d H:i:s', $date1);

                            $date2 = explode(" / ", $fecha)[1];
                            $date2 = strtotime($date2);
                            $date2 = strtotime('+1 days', $date2);
                            $date2 = strtotime('-1 second', $date2);
                            $hasta = date('Y-m-d H:i:s', $date2);

                            return $query->whereBetween("fecha_creacion", [$desde, $hasta]);
                        }
                    })
                    ->get();
            }

            return DataTables::of($data)
                ->editColumn('estado', function ($soporte) {
                    return $this->obtener_estado_soporte($soporte->estado);
                })
                ->editColumn('distribuidor', function ($soporte) {
                    return $this->obtener_distribuidor_soporte($soporte->distribuidoresid);
                })
                ->editColumn('tipo', function ($soporte) {
                    return $this->obtener_tipo_soporte($soporte->tipo);
                })
                ->editColumn('acciones', function ($soporte) {
                    $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('sop.editar_soporte_especial', $soporte->soporteid) . '"  title="Editar"> <i class="la la-edit"></i> </a>';
                    return $botones;
                })
                ->rawColumns(['acciones', 'estado', 'distribuidor'])
                ->make(true);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*              Funciones para supervisor de soportes especiales              */
    /* -------------------------------------------------------------------------- */

    public function supervisor_listar_soporte(Request $request)
    {
        $tecnicos = $this->obtener_tecnicos_distribuidor();
        return view('soporte.admin.tecnico.demos.supervisor', ['tecnicos' => $tecnicos]);
    }

    public function filtrado_supervisor_soporte(Request $request)
    {
        $data = SoporteEspecial::select(
            'soportes_especiales.soporteid',
            'soportes_especiales.ruc',
            'soportes_especiales.razon_social',
            'soportes_especiales.whatsapp',
            'soportes_especiales.correo',
            'soportes_especiales.estado',
            'soportes_especiales.fecha_creacion',
            'soportes_especiales.fecha_iniciado',
            'soportes_especiales.tipo',
            'soportes_especiales.plan',
            'soportes_especiales.tecnicoid',
            'usuarios.nombres',
            'usuarios.distribuidoresid',
        )
            ->join('usuarios', 'usuarios.usuariosid', 'soportes_especiales.tecnicoid')
            ->where('usuarios.distribuidoresid', Auth::user()->distribuidoresid)
            ->when($request->tecnico, function ($query, $tecnico) {
                return $query->where('tecnicoid', $tecnico);
            })
            ->when($request->estado, function ($query, $estado) {
                return $query->where('soportes_especiales.estado', $estado);
            })
            ->when($request->fecha, function ($query, $fecha) {
                if ($fecha != "") {
                    $dates = explode(" / ", $fecha);

                    $date1 = strtotime($dates[0]);
                    $desde = date('Y-m-d H:i:s', $date1);

                    $date2 = strtotime($dates[1] . ' +1 day -1 second');
                    $hasta = date('Y-m-d H:i:s', $date2);

                    return $query->whereBetween("fecha_creacion", [$desde, $hasta]);
                }
            })
            ->when($request->plan, function ($query, $plan) {
                return $query->where('soportes_especiales.plan', $plan);
            })
            ->when($request->tipo, function ($query, $tipo) {
                return $query->where('soportes_especiales.tipo', $tipo);
            })
            ->get();


        return DataTables::of($data)
            ->editColumn('estado', function ($soporte) {
                return $this->obtener_estado_soporte($soporte->estado);
            })
            ->editColumn('distribuidor', function ($soporte) {
                return $this->obtener_distribuidor_soporte($soporte->distribuidoresid);
            })
            ->editColumn('tipo', function ($soporte) {
                return $this->obtener_tipo_soporte($soporte->tipo);
            })
            ->editColumn('plan', function ($soporte) {
                return $this->obtener_plan_soporte($soporte->plan);
            })
            ->editColumn('fecha_creacion', function ($soporte) {
                return date('d/m/Y', strtotime($soporte->fecha_creacion));
            })
            ->editColumn('fecha_iniciado', function ($soporte) {
                if ($soporte->fecha_iniciado) {
                    return date('d/m/Y', strtotime($soporte->fecha_iniciado));
                }
            })
            ->editColumn('acciones', function ($soporte) {
                $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('sop.editar_soporte_especial', $soporte->soporteid) . '"  title="Editar"> <i class="la la-edit"></i> </a>';
                return $botones;
            })
            ->rawColumns(['acciones', 'estado', 'distribuidor'])
            ->make(true);
    }

    /* -------------------------------------------------------------------------- */
    /*                     Funciones de mensaje de asignacion                     */
    /* -------------------------------------------------------------------------- */
    private function notificar_asignacion($idTecnico)
    {
        try {
            if (!$idTecnico) return false;

            $tecnico = User::find($idTecnico);

            if (!$tecnico) return false;

            $sms = new WhatsappController();
            return $sendMessage = $sms->enviar_personalizado([
                "numero" => $tecnico->telefono,
                "mensaje" =>  "Buen día {$tecnico->nombres} usted tiene una nueva asignación, para más detalles revise la plataforma en la sección de soportes especiales."
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }
    }

    private function notificar_nuevo_registro($soporte, $factura)
    {
        try {
            $productos = json_decode($factura->productos);
            $listProduct = [];

            foreach ($productos as $producto) {
                $productoAux = Producto::find($producto->productoid, ["descripcion"]);
                array_push($listProduct, $productoAux->descripcion);
            }

            $vendedor = User::firstWhere('usuariosid', $factura->usuariosid);

            $nombreRevisor = "Katherine Sarabia";
            // $mailRevisor = "katherine.sarabia@perseo.ec";
            $mailRevisor = "angello.ordonez@hotmail.com";
            // $mailRevisor = "desarrollo@perseo.ec";

            $array = [
                'from' => "noresponder@perseo.ec",
                'subject' => "Notificación de nueva implementacion",
                'revisora' => $nombreRevisor,
                'asesor' => $vendedor->nombres,
                'ruc' => $soporte->ruc,
                'razon_social' => $soporte->razon_social,
                'correo' => $soporte->correo,
                'whatsapp' => $soporte->whatsapp,
                'planes' => $listProduct,
            ];

            Mail::to($mailRevisor)->queue(new NotificacionCapacitacion($array));
        } catch (\Throwable $th) {
            flash("No se pudo enviar el correo de notificación")->error();
        }
    }

    private function notificar_nuevo_estado($soporte, $estadoNum)
    {
        try {
            $revisor = User::where('rol', 9)->first();

            $estado = "";

            if ($estadoNum == 4) {
                $estado = "Implementado";
            } else if ($estadoNum == 6) {
                $estado = "Finalizado";
            }

            $array = [
                'from' => "noresponder@perseo.ec",
                'subject' => "Notificación de actualización de estado",
                'revisora' => $revisor->nombres,
                'soporteid' => $soporte->soporteid,
                'estado' => $estado,
                'ruc' => $soporte->ruc,
                'fecha' => $soporte->fecha_actualizado,
                'razon_social' => $soporte->razon_social,
                'correo' => $soporte->correo,
                'whatsapp' => $soporte->whatsapp,
            ];

            Mail::to($revisor->correo)->queue(new NotificacionEstadoCapacitacion($array));
        } catch (\Throwable $th) {
            dd($th);
            flash("No se pudo enviar el correo de notificación")->error();
        }
    }

    private function obtener_tecnicos_distribuidor()
    {
        $tecnicos = User::select('usuariosid', 'nombres', 'correo')
            ->when(Auth::user()->distribuidoresid, function ($query, $distribuidor) {
                if ($distribuidor == 1) {
                    return $query->where('distribuidoresid', 1);
                } else if ($distribuidor == 2) {
                    return $query->where('distribuidoresid', '<=', 2);
                } else {
                    return $query->where('distribuidoresid', $distribuidor);
                }
            })
            ->where('rol', 5)
            ->get();

        return $tecnicos;
    }

    /* -------------------------------------------------------------------------- */
    /*                             Funciones genericas                            */
    /* -------------------------------------------------------------------------- */

    private function obtener_estado_soporte($estado)
    {
        switch ($estado) {
            case 1:
                return '<div class="badge bg-asignado">Asignado</div>';
            case 2:
                return '<div class="badge bg-agendado">Agendado</div>';
            case 3:
                return '<div class="badge bg-parametrizado">Contactado</div>';
            case 4:
                return '<div class="badge bg-implementacion">Implementación</div>';
            case 5:
                return '<div class="badge bg-revisado1">Revisado 1</div>';
            case 6:
                return '<div class="badge bg-finalizado">Finalizado</div>';
            case 7:
                return '<div class="badge bg-reagendado">Reagendado</div>';
            case 8:
                return '<div class="badge bg-revisado2">Revisado 2</div>';
            case 9:
                return '<div class="badge bg-aprobado">Aprobado</div>';
            case 10:
                return '<div class="badge bg-rechazado">Rechazado</div>';
            case 11:
                return '<div class="badge bg-sinrespuesta">Sin respuesta</div>';
            case 12:
                return '<div class="badge bg-secondary">Autoimplementado</div>';
        }
    }

    private function obtener_tipo_soporte($tipo)
    {
        switch ($tipo) {
            case 1:
                return 'DEMO';
            case 2:
                return 'CAPACITACIÓN';
            case 3:
                return 'LITE';
        }
    }

    private function obtener_distribuidor_soporte($distribuidor)
    {
        switch ($distribuidor) {
            case 1:
                return '<div class="badge badge-primary">Perseo Alfa</div>';
            case 2:
                return '<div class="badge badge-info">Perseo Matriz</div>';
            case 3:
                return '<div class="badge badge-secondary">Perseo Delta</div>';
            case 4:
                return '<div class="badge badge-warning">Perseo Omega</div>';
            default:
                return '<div class="badge badge-info">S/N</div>';
        }
    }

    private function obtener_plan_soporte($plan){
        switch ($plan) {
            case 1:
                return "WEB";
            case 2:
                return "PC";
            case 3:
                return 'FACTURITO';
        }
    }
}
