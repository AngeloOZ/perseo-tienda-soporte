<?php

namespace App\Http\Controllers;

use App\Mail\CalificarSoporte;
use App\Mail\EnviarTicketSoporte;
use App\Models\ActividadTicket;
use App\Models\EncuestaSoporte;
use App\Models\Log;
use App\Models\SoporteEspecial;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserSoporte;
use App\Rules\ValidarCelular;
use App\Rules\ValidarCorreo;
use App\Rules\ValidarRUC;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class TicketSoporteController extends Controller
{
    public function index($producto = "web", $distribuidorid = "6")
    {
        return view('soporte.ticket.index', ["producto" => $producto, "distribuidor" => $distribuidorid]);
    }

    public function validar_ticket_activo($ruc)
    {
        if (strlen($ruc) != 13) {
            $message = json_encode(["status" => 400, "message" => "El ruc no tiene longitud válida"]);
            return $message;
        }

        $tickets = Ticket::select('ticketid', 'numero_ticket', 'ruc', 'estado', 'fecha_creado', 'calificado')->where('ruc', $ruc)->get();

        $message = json_encode(["status" => 200, "message" => "Sin tickets abiertos"]);

        foreach ($tickets as $ticket) {

            if ($ticket->estado <= 2) {
                $message = json_encode(["status" => 400, "message" => "El ruc $ticket->ruc tiene un ticket abierto, creado el $ticket->fecha_creado"]);
                break;
            }

            if ($ticket->calificado == 0) {
                $message = json_encode(["status" => 400, "message" => "El ruc $ticket->ruc tiene ticket pendiente de calificar: <a href='" . route('soporte.calificar_ticket', $ticket->ticketid) . "' target='_blank' ><strong>calificar aquí</strong></a>"]);
                break;
            }
        }

        return $message;
    }

    public function crear_ticket(Request $request)
    {
        $request->validate([
            'ruc' => ['required', new ValidarRUC],
            'razon_social' => 'required',
            'nombres' => 'required',
            'apellidos' => 'required',
            'correo' => ['required', new ValidarCorreo],
            'whatsapp' => ['required', new ValidarCelular],
            'motivo' => 'required|min:50',
        ], [
            'ruc.required' => 'Debe ingresar un RUC',
            'razon_social.required' => 'Debe ingresar un nombre de empresa',
            'nombres.required' => 'Debe ingresar sus nombres',
            'apellidos.required' => 'Debe ingresar sus apellidos',
            'correo.required' => 'Debe ingresar un correo',
            'whatsapp.required' => 'Debe ingresar un número de teléfono',
            'motivo.required' => 'Debe ingresar el motivo',
            'motivo.min' => 'El motivo debe tener como mínimo 50 caracteres',
        ]);

        try {
            $ticket = new Ticket();
            $ticket->ruc = $request->ruc;
            $ticket->razon_social = $request->razon_social;
            $ticket->nombres = $request->nombres;
            $ticket->apellidos = $request->apellidos;
            $ticket->correo = $request->correo;
            $ticket->whatsapp = $request->whatsapp;
            $ticket->motivo = $request->motivo;
            $ticket->producto = $request->producto;
            $ticket->distribuidor = $this->obtener_distribuidor_ticket($request->distribuidor);
            $ticket->numero_ticket = uniqid();
            $ticket->estado = 1;

            if ($ticket->save()) {
                $this->asignacion_tickets();

                $sms = new WhatsappController();
                $sendMessage = $sms->enviar_personalizado([
                    "numero" => $ticket->whatsapp,
                    "mensaje" =>  "¡Buen día! ☀️ {$ticket->razon_social}, saludos del equipo de soporte del Sistema Contable Perseo. 👋\n\nHemos recibido tu solicitud de soporte (Ticket No: *{$ticket->numero_ticket}*). Pronto nos comunicaremos contigo a través de WhatsApp o correo electrónico. 📞📧\n\nNúmeros de WhatsApp para contacto:\n📞 0988349407\n📞0958878881\n📞0979391799\n\nTiempo de espera estimado: *30 min*⌛️ (*8 AM - 5 PM*⌚️). Si enviaste tu solicitud fuera de ese horario, te responderemos al día siguiente.\n\n¡Gracias por confiar en Perseo! 🙏 No es necesario responder a este número. 📞 ¡Que tengas un excelente día! 😊",
                    "nombre" => $ticket->razon_social,
                ]);

                return redirect()->route('soporte.resultado_registro', $ticket->numero_ticket);
            } else {
                return redirect()->route('soporte.resultado_registro');
            }
        } catch (\Throwable $th) {
            flash("Error interno, inténtalo más tarde: " . $th->getMessage())->error();
            return back();
        }
    }

    public function resultado_registro($numero = null)
    {
        if ($numero) {
            return view("soporte.ticket.resultado", ["estado" => true, "numero_ticker" => $numero]);
        } else {
            return view("soporte.ticket.resultado", ["estado" => false, "numero_ticker" => $numero]);
        }
    }

    public function editar_ticket(Ticket $ticket)
    {
        $desarrolladores = User::where('rol', 6)->get();
        $supervisores = User::where('rol', 7)
            ->where('distribuidoresid', Auth::user()->distribuidoresid)
            ->get();

        $tecnicoAsignado = User::findOrFail($ticket->tecnicosid);

        if (!$ticket->actividad_empresa) {
            $actividad = Ticket::select('actividad_empresa')
                ->where('ruc', $ticket->ruc)
                ->whereNotNull('actividad_empresa')
                ->orderBy('fecha_creado', 'desc')
                ->first();

            $ticket->actividad_empresa = $actividad->actividad_empresa ?? null;
        }

        $bind = [
            "ticket" => $ticket,
            "desarrolladores" => $desarrolladores,
            "supervisores" => $supervisores,
            "tecnicoAsignado" => $tecnicoAsignado,
            "historialTickets" => $this->obtener_historial_tickets($ticket->ruc, $ticket->numero_ticket),
            "historialCapacitaciones" => $this->obtener_historial_implementaciones($ticket->ruc),
        ];

        return view("soporte.admin.tecnico.editar", $bind);
    }

    public function actualizar_estado_ticket(Ticket $ticket,  Request $request)
    {
        DB::beginTransaction();
        try {
            $estado = intval($request->estado);

            if ($request->tecnicosid) {
                if ($request->tecnicosid != $ticket->tecnicosid) {

                    $this->liberar_tecnico($ticket);
                    $this->asignar_tecnico_manual($request->tecnicosid);
                    $this->notificar_asignacion_ticket($request->tecnicosid, $ticket);

                    $ticket->estado = $estado;
                    $ticket->tecnicosid = $request->tecnicosid;
                    $ticket->fecha_asignacion = now();
                } else {
                    $ticket->tecnicosid = $request->tecnicosid;
                }
            }

            if ($estado >= 3) {
                $tiempo = $this->obtener_tiempo_activo_ticket($ticket);
                $ticket->tiempo_activo = $tiempo;
                $ticket->fecha_cierre = now();
                $this->liberar_tecnico($ticket);
            }

            // 4 => estado cerrado
            if ($estado == 4) {
                if ($estado != $ticket->estado) {
                    $this->enviar_correo_calificacion($ticket);
                }
            }

            // 5 => estado sin respuesta
            // 6 => estado problema general
            if ($estado >= 5) {
                $ticket->calificado = 1;
            }

            if ($request->distribuidor || $request->producto) {
                if (($request->distribuidor != $ticket->distribuidor) || ($request->producto != $ticket->producto)) {
                    $this->liberar_tecnico($ticket);
                    $ticket->fecha_asignacion = null;
                    $ticket->fecha_cierre = null;
                    $ticket->tiempo_activo = null;
                    $ticket->tecnicosid = null;
                }
                $ticket->distribuidor = $request->distribuidor;
            }

            if ($request->producto) {
                $ticket->producto = $request->producto;
            }

            if ($request->actividad_empresa) {
                $ticket->actividad_empresa = $request->actividad_empresa;
            }

            $ticket->estado = $estado;

            $this->asignacion_tickets();
            $ticket->save();

            $ticketLog =  Ticket::find($ticket->ticketid);
            $log = new Log();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Soporte";
            $log->operacion = "Modificar";
            $log->fecha = now();
            $log->detalle = $ticketLog;
            $log->save();

            DB::commit();
            flash('Estado del ticket actualizado')->success();
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            flash('Hubo un error al actualizar el estado del ticket: ' . $th->getMessage())->error();
            return back();
        }
    }

    public function enviar_correo_cliente(Request $request)
    {
        try {
            $ticket = Ticket::findOrFail($request->ticketid);
            $destinatarios = [];

            if ($request->cliente) {
                $destinatarios["cliente"] = $request->cliente;
            }
            if ($request->desarrollo) {
                $destinatarios["desarrollador"] = $request->desarrollo;
            }
            if ($request->supervisor) {
                $destinatarios["supervisor"] = $request->supervisor;
            }
            if ($request->tecnico) {
                $destinatarios["tecnico"] = $request->tecnico;
            }

            $actividad = new ActividadTicket();
            $actividad->contenido = $request->contenido;
            $actividad->ticketid = $ticket->ticketid;
            $actividad->dirigido_a = json_encode($destinatarios);
            $actividad->fecha_creado = now();
            $actividad->save();

            $contentEmail = [
                "from" => env('MAIL_FROM_ADDRESS'),
                "subject" => "Soporte técnico - Ticket: $ticket->numero_ticket",
                "tecnico" => trim(Auth::user()->nombres),
                "ticketid" => $ticket->ticketid,
                "numero_ticket" => trim($ticket->numero_ticket),
                "contenido" => $request->contenido,
            ];

            if ($request->enviar_mail) {
                $contentEmail = [
                    "from" => env('MAIL_FROM_ADDRESS'),
                    "subject" => "Soporte técnico - Ticket: $ticket->numero_ticket",
                    "tecnico" => trim(Auth::user()->nombres),
                    "ticketid" => $ticket->ticketid,
                    "numero_ticket" => trim($ticket->numero_ticket),
                    "contenido" => $request->contenido,
                ];

                Mail::to($destinatarios)->cc(Auth::user()->correo)->queue(new EnviarTicketSoporte($contentEmail));

                $sms = new WhatsappController();
                $sendMessage = $sms->enviar_mensaje([
                    "numero" => $ticket->whatsapp,
                    "mensaje" =>  $request->contenido,
                    "nombre" => $ticket->razon_social,
                ]);
            }

            $log = new Log();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Soporte";
            $log->operacion = "Enviar correo";
            $log->fecha = now();
            $log->detalle = json_encode($contentEmail);
            $log->save();

            return response(["status" => 200, "message" => "correo o nota registrado"], 200)->header('Content-Type', 'application/json');
        } catch (\Throwable $th) {
            return response(["status" => 500, "message" => $th->getMessage()], 500)->header('Content-Type', 'application/json');
        }
    }

    private function enviar_correo_calificacion($ticket)
    {
        $sms = new WhatsappController();
        $sendMessage = $sms->enviar_personalizado([
            "numero" => $ticket->whatsapp,
            "mensaje" =>  "¡Hola {$ticket->razon_social}! 🌞 Queríamos informarte que tu soporte ha concluido. 🛠️ Recuerda es obligatorio calificar tu experiencia con el soporte en este enlace: " . route('soporte.calificar_ticket', $ticket->ticketid) . " 🌟\n\n¡Tu opinión es importante para nosotros! 🙏 Este número es solo para comunicados, no es necesario responder. 📞 ¡Gracias y que tengas un gran día! 😊",
        ], 8);

        if ($sendMessage) {
            $thirdMessage = $sms->enviar_personalizado([
                "numero" => $ticket->whatsapp,
                "mensaje" => "¡Mejora tus habilidades en PERSEO con nuestras capacitaciones integrales! 🤓💻 Aprende todos los módulos de nuestro sistema contable con nuestros horarios flexibles. 📚💡 Conéctate a través de Google Meet en los siguientes enlaces:\n\n🔵 Capacitación a las 10:00 AM: meet.google.com/ast-hfoi-upx\n🔵 Capacitación a las 3:00 PM: meet.google.com/isr-ywnp-prf\n🔵 Calendario : https://perseo.ec/implementaciones-globales/\n\n¡No te lo pierdas! 🙌🏼",
            ]);
        }

        try {
            $contentEmail = [
                "from" => env('MAIL_FROM_ADDRESS'),
                "subject" => "Soporte técnico - Ticket: $ticket->numero_ticket",
                "numero_ticket" => trim($ticket->numero_ticket),
                "razon_social" => $ticket->razon_social,
                "ticketid" => $ticket->ticketid,
            ];

            Mail::to($ticket->correo)->queue(new CalificarSoporte($contentEmail));
            flash("Encuesta enviada al cliente corretamente")->success();
        } catch (\Throwable $th) {
            flash("No se pudo enviar al cliente la encuesta")->warning();
        }
    }

    public function ver_resporte_calificacione_tecnico()
    {
        return view("soporte.admin.tecnico.reporte_calificaciones");
    }

    public function filtrado_reporte_calificaciones_tecnico(Request $request)
    {
        $desde = null;
        $hasta = null;

        if ($request->fecha) {
            $date1 = explode(" / ", $request->fecha)[0];
            $date1 = strtotime($date1);
            $desde = date('Y-m-d H:i:s', $date1);

            $date2 = explode(" / ", $request->fecha)[1];
            $date2 = strtotime($date2);
            $date2 = strtotime('+1 days', $date2);
            $date2 = strtotime('-1 second', $date2);
            $hasta = date('Y-m-d H:i:s', $date2);
        }

        $encuestas = EncuestaSoporte::orderBy('comentario')
            ->join('usuarios', 'encuesta_soporte.tecnicoid', '=', 'usuarios.usuariosid')
            ->whereBetween("fecha_creacion", [$desde, $hasta])
            ->where('tecnicoid', Auth::user()->usuariosid)
            ->where('justificado', '=', 0)
            ->get();

        $resultsPregunta1 = EncuestaSoporte::selectRaw("pregunta_1 as 'puntaje' , COUNT(pregunta_1) as 'total'")
            ->join('usuarios', 'encuesta_soporte.tecnicoid', '=', 'usuarios.usuariosid')
            ->whereBetween("fecha_creacion", [$desde, $hasta])
            ->where('tecnicoid', Auth::user()->usuariosid)
            ->where('justificado', '=', 0)
            ->groupBy('pregunta_1')->get();

        $resultsPregunta2 = EncuestaSoporte::selectRaw("pregunta_2 as 'puntaje' , COUNT(pregunta_2) as 'total'")
            ->join('usuarios', 'encuesta_soporte.tecnicoid', '=', 'usuarios.usuariosid')
            ->whereBetween("fecha_creacion", [$desde, $hasta])
            ->where('tecnicoid', Auth::user()->usuariosid)
            ->where('justificado', '=', 0)
            ->groupBy('pregunta_2')->get();


        $data = [
            "pregunta_1" => [
                "labels" => [],
                "values" => [],
                "total_puntaje" => 0,
                "promedio" => 0,
            ],
            "pregunta_2" => [
                "labels" => [],
                "values" => [],
                "total_puntaje" => 0,
                "promedio" => 0,
            ],
            "total" => 0,
            "tabla" => "",
        ];

        foreach ($resultsPregunta1 as $key => $item) {
            $text = ($item->puntaje == 1) ? "{$item->puntaje} punto" : "{$item->puntaje} puntos";
            array_push($data["pregunta_1"]["labels"], strtoupper($text));
            array_push($data["pregunta_1"]["values"], $item->total);
            $data["pregunta_1"]["total_puntaje"] += $item->total * $item->puntaje;
            $data["total"] += $item->total;
        }

        foreach ($resultsPregunta2 as $key => $item) {
            $text = ($item->puntaje == 1) ? "{$item->puntaje} punto" : "{$item->puntaje} puntos";
            array_push($data["pregunta_2"]["labels"], strtoupper($text));
            array_push($data["pregunta_2"]["values"], $item->total);
            $data["pregunta_2"]["total_puntaje"] += $item->total * $item->puntaje;
        }

        $tabla = "";
        foreach ($encuestas as $key => $encuesta) {
            $ticket = Ticket::where('ticketid', $encuesta->ticketid)->first();

            if (!$ticket) continue;

            $fecha = date('d-m-Y', strtotime($ticket->fecha_cierre));

            $tabla = $tabla . "<tr style='font-size: 14px'>
                <td>$ticket->ruc</td>
                <td>$ticket->razon_social</td>
                <td>$ticket->whatsapp</td>
                <td style='width: 100px;'>$ticket->correo</td>
                <td>$encuesta->pregunta_1/5</td>
                <td>$encuesta->pregunta_2/5</td>
                <td>$encuesta->comentario</td>
                <td>$fecha</td>
                </tr>";
        }

        if (count($resultsPregunta1) > 0) {
            $data["pregunta_1"]["promedio"] = number_format($data["pregunta_1"]["total_puntaje"] / $data["total"], 2) . "/5.00";
            $data["pregunta_2"]["promedio"] = number_format($data["pregunta_2"]["total_puntaje"] / $data["total"], 2) . "/5.00";
            $data["tabla"] = $tabla;
        }

        return $data;
    }

    /* -------------------------------------------------------------------------- */
    /*                     Funciones para calificar el soporte                    */
    /* -------------------------------------------------------------------------- */
    public function calificar_soporte_vista(Ticket $ticket)
    {
        return view('soporte.ticket.encuesta', compact('ticket'));
    }

    public function registrar_calificacion_soporte(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::findOrFail($request->ticketid);
            $ticket->calificado = 1;

            $encuesta = EncuestaSoporte::firstOrCreate(
                ['ticketid' =>  $request->ticketid],
            );

            $encuesta->pregunta_1 = $request->pregunta_1;
            $encuesta->pregunta_2 = $request->pregunta_2;
            $encuesta->ticketid = $ticket->ticketid;
            $encuesta->tecnicoid = $ticket->tecnicosid;
            $encuesta->estado_revision = 3;
            if ($request->comentario) {
                $encuesta->comentario = $request->comentario;
                $encuesta->estado_revision = 1;
            }

            $encuesta->save();
            $ticket->save();
            DB::commit();
            flash("Gracias por registar tu calificación")->success();
            return redirect()->route('soporte.crear.ticket', ['producto' => $ticket->producto, 'distribuidorid' => $ticket->distribuidor]);
        } catch (\Throwable $th) {
            DB::rollBack();
            flash("No se registro la encuesta " . $th->getMessage())->error();
            return back();
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                       Funciones para rol de tecnicos                       */
    /* -------------------------------------------------------------------------- */
    public function listado_de_tickets_activos(Request $request)
    {
        $this->asignacion_tickets();
        if ($request->ajax()) {
            $data = Ticket::select()->where([['tecnicosid', Auth::user()->usuariosid], ['estado', '<=', '2',]])->get();

            return DataTables::of($data)
                ->editColumn('tiempo_activo', function ($ticket) {
                    if ($ticket->estado <= 2) {
                        return $this->obtener_tiempo_activo_ticket($ticket);
                    } else {
                        return $ticket->tiempo_activo;
                    }
                })
                ->editColumn('estado', function ($ticket) {
                    if ($ticket->estado == 1) {
                        return '<a class="bg-primary text-white rounded p-1">Abierto</a>';
                    } else if ($ticket->estado == 2) {
                        return '<a class="bg-info text-white rounded p-1">En progreso</a>';
                    }
                })
                ->editColumn('action', function ($ticket) {
                    $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('soporte.editar', $ticket->ticketid) . '"  title="Editar"> <i class="la la-edit"></i> </a>';

                    return $botones;
                })
                ->rawColumns(['action', 'estado'])
                ->make(true);
        }
        return view('soporte.admin.tecnico.index');
    }

    public function listado_de_tickets_desarrollo(Request $request)
    {
        $this->asignacion_tickets();
        $tecnicos = $this->obtener_tecnicos_distribuidor();
        return view('soporte.admin.tecnico.desarrollo', compact('tecnicos'));
    }

    public function filtrado_listado_de_tickets_desarrollo(Request $request)
    {
        if ($request->ajax()) {
            $data = Ticket::where('estado', '3')
                ->when($request->tecnico, function ($query, $tecnico) {
                    return $query->where('tecnicosid', $tecnico);
                })
                ->when($request->fecha, function ($query, $fecha) {
                    $dates = explode(" / ", $fecha);

                    $date1 = strtotime($dates[0]);
                    $desde = date('Y-m-d H:i:s', $date1);

                    $date2 = strtotime($dates[1] . ' +1 day -1 second');
                    $hasta = date('Y-m-d H:i:s', $date2);
                    return $query->whereBetween("fecha_asignacion", [$desde, $hasta]);
                })
                ->get();

            return DataTables::of($data)
                ->editColumn('tiempo_activo', function ($ticket) {
                    if ($ticket->estado <= 2) {
                        return $this->obtener_tiempo_activo_ticket($ticket);
                    } else {
                        return $ticket->tiempo_activo;
                    }
                })
                ->editColumn('estado', function () {
                    return '<a class="bg-success text-white rounded p-1">Desarrollo</a>';
                })
                ->editColumn('action', function ($ticket) {
                    $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('soporte.editar', $ticket->ticketid) . '"  title="Editar"> <i class="la la-eye"></i> </a>';
                    return $botones;
                })
                ->rawColumns(['action', 'estado'])
                ->make(true);
        }
    }

    public function listado_de_tickets_cerrados(Request $request)
    {
        $this->asignacion_tickets();
        if ($request->ajax()) {
            $data = Ticket::select()->where([['tecnicosid', Auth::user()->usuariosid], ['estado', '>=', '4',]])->get();

            return DataTables::of($data)
                ->editColumn('tiempo_activo', function ($ticket) {
                    if ($ticket->estado <= 2) {
                        return $this->obtener_tiempo_activo_ticket($ticket);
                    } else {
                        return $ticket->tiempo_activo;
                    }
                })
                ->editColumn('estado', function ($ticket) {
                    switch ($ticket->estado) {
                        case 4:
                            return '<a class="bg-danger text-white rounded p-1">Cerrado</a>';
                        case 5:
                            return '<a class="bg-warning text-white rounded p-1">Sin respuesta</a>';
                        case 6:
                            return '<a class="bg-danger text-white rounded p-1">Problema general</a>';
                    }
                })
                ->editColumn('action', function ($ticket) {
                    $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('soporte.editar', $ticket->ticketid) . '"  title="Editar"> <i class="la la-eye"></i> </a>';

                    return $botones;
                })
                ->rawColumns(['action', 'estado'])
                ->make(true);
        }
        return view('soporte.admin.tecnico.cerrados');
    }

    public function cambiar_disponibilidad($id_user = null)
    {
        try {
            $user = UserSoporte::firstWhere('usuariosid', Auth::user()->usuariosid);
            if ($id_user) {
                $user = UserSoporte::find($id_user);
            }

            $estado = "";
            if ($user) {
                if ($user->estado == 1) {
                    $user->estado = 0;
                    $estado = "Desconectado";
                } else {
                    $user->estado = 1;
                    $estado = "Disponible";
                }
                $user->save();
                flash("Estado actualizado a: " . $estado)->success();
                return back();
            }
            flash("Usuario no encontrado")->warning();
            return back();
        } catch (\Throwable $th) {
            flash("No se pudo actualizar el estado del perfil")->error();
            return back();
        }
    }

    public function ver_calificaciones_tecnicos(Request $request)
    {
        if ($request->ajax()) {
            $encuestas = EncuestaSoporte::all()->where('comentario', '<>', null)->where('tecnicoid', Auth::user()->usuariosid);

            foreach ($encuestas as $key => $encuesta) {
                $ticket = Ticket::firstWhere('ticketid', $encuesta->ticketid);
                $encuesta->motivo = $ticket->motivo;
                $encuesta->whatsapp = $ticket->whatsapp;
                $encuesta->razon_social = $ticket->razon_social;
                $encuesta->calificado = $ticket->calificado;
            }

            return DataTables::of($encuestas)
                ->editColumn('comentario', function ($encuesta) {
                    return "<div class='width_comentario'>{$encuesta->comentario}</div>";
                })
                ->editColumn('motivo', function ($encuesta) {
                    return "<div class='width_motivo'><strong>{$encuesta->razon_social}</strong><br /><br />{$encuesta->motivo}</div>";
                })
                ->editColumn('contacto', function ($encuesta) {
                    return $encuesta->whatsapp;
                })
                ->editColumn('action', function ($encuesta) {
                    if ($encuesta->calificado == 1) {
                        $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('soporte.reactivar_ecuesta', $encuesta->ticketid) . '"title="Reactivar encuesta"> <i class="la 
                        la-redo-alt"></i> </a>';
                        return $botones;
                    }
                })
                ->rawColumns(['action', 'comentario', 'motivo'])
                ->make(true);
        }
        return view('soporte.admin.tecnico.calificaciones');
    }

    public function reactivar_ecuesta(Ticket $ticket)
    {
        try {
            $ticket->calificado = 0;
            $ticket->save();

            $this->enviar_correo_calificacion($ticket);

            flash("Encuesta reactivada")->success();
            return back();
        } catch (\Throwable $th) {
            flash("No se pudo reactivar la encuesta")->error();
            return back();
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                      funciones para rol de desarrollo                      */
    /* -------------------------------------------------------------------------- */
    public function listado_de_tickets_desarrollo_revisor(Request $request)
    {
        $this->asignacion_tickets();
        if ($request->ajax()) {
            $data = Ticket::select()->where([['estado', '3',]])->get();

            return DataTables::of($data)
                ->editColumn('tiempo_activo', function ($ticket) {
                    if ($ticket->estado <= 2) {
                        return $this->obtener_tiempo_activo_ticket($ticket);
                    } else {
                        return $ticket->tiempo_activo;
                    }
                })
                ->editColumn('estado', function () {
                    return '<a class="bg-success text-white rounded p-1">Desarrollo</a>';
                })
                ->editColumn('action', function ($ticket) {
                    $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('soporte.editar.desarrollo', $ticket->ticketid) . '"  title="Editar"> <i class="la la-eye"></i> </a>';
                    return $botones;
                })
                ->rawColumns(['action', 'estado'])
                ->make(true);
        }
        return view('soporte.admin.desarrollo.index');
    }

    public function editar_ticket_desarrollo(Ticket $ticket)
    {
        $tecnicoAsignado = User::findOrFail($ticket->tecnicosid);
        $supervisores = User::where('rol', 7)->where('distribuidoresid', $tecnicoAsignado->distribuidoresid)->get();

        return view("soporte.admin.desarrollo.editar", ["ticket" => $ticket, "supervisores" => $supervisores, "tecnicoAsignado" => $tecnicoAsignado]);
    }

    /* -------------------------------------------------------------------------- */
    /*                         funciones para rol revisor                         */
    /* -------------------------------------------------------------------------- */
    public function listado_tickets_revisor(Request $request)
    {
        $this->asignacion_tickets();
        $tecnicos = $this->obtener_tecnicos_distribuidor();
        return view('soporte.admin.revisor.index', ['tecnicos' => $tecnicos,]);
    }

    public function filtrado_tickets_revisor(Request $request)
    {
        $this->asignacion_tickets();
        if ($request->ajax()) {
            $data = Ticket::when($request->asignados, function ($query, $asignados) {
                if ($asignados == "si") {
                    return $query->whereNotNull('tecnicosid');
                } else {
                    return $query->whereNull('tecnicosid');
                }
            })
                ->when($request->tecnico, function ($query, $tecnico) {
                    return $query->where('tecnicosid', $tecnico);
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

                    return $query->whereBetween("fecha_creado", [$desde, $hasta]);
                })
                ->when($request->distribuidor, function ($query, $distribuidor) {
                    return $query->where('distribuidor', $distribuidor);
                })
                ->when($request->producto, function ($query, $producto) {
                    return $query->where('producto', $producto);
                })
                ->get();

            return DataTables::of($data)
                ->editColumn('tiempo_activo', function ($ticket) {
                    if ($ticket->estado <= 2) {
                        return $this->obtener_tiempo_activo_ticket($ticket);
                    } else {
                        return $ticket->tiempo_activo;
                    }
                })
                ->editColumn('estado', function ($ticket) {
                    switch ($ticket->estado) {
                        case 1:
                            return '<a class="bg-primary text-white rounded p-1">Abierto</a>';
                            break;
                        case 2:
                            return '<a class="bg-info text-white rounded p-1">En progreso</a>';
                            break;
                        case 3:
                            return '<a class="bg-success text-white rounded p-1">Desarrollo</a>';
                            break;
                        case 4:
                            return '<a class="bg-danger text-white rounded p-1">Cerrado</a>';
                            break;
                        case 5:
                            return '<a class="bg-info text-white rounded p-1">Sin respuesta</a>';
                            break;
                        case 6:
                            return '<a class="bg-danger text-white rounded p-1">Problema general</a>';
                            break;
                    }
                })
                ->editColumn('action', function ($ticket) {
                    $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('soporte.editar.revisor', $ticket->ticketid) . '"  title="Editar"> <i class="la la-edit"></i> </a>';

                    if ($ticket->estado >= 3 && $ticket->calificado == 0) {
                        $botones = $botones . '<a class="btn btn-icon btn-light btn-hover-info btn-sm mr-2" href="' . route('soporte.calificar_ticket', $ticket->ticketid) . '" target="_blank"  title="Enlace calificar"> <i class="la la-external-link-alt"></i> </a>';
                    }

                    if ($ticket->estado <= 2) {
                        $botones = $botones . '<a class="btn btn-sm btn-light btn-icon btn-hover-danger confirm-delete" href="javascript:void(0)" data-href="' . route('soporte.eliminar_ticket', $ticket->ticketid) . '" title="Eliminar"> <i class="la la-trash"></i> </a>';
                    }

                    return $botones;
                })
                ->rawColumns(['action', 'estado'])
                ->make(true);
        }
    }

    public function eliminar_soporte_revisor(Ticket $ticket)
    {
        try {

            $log = new Log();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Soporte";
            $log->operacion = "Modificar";
            $log->fecha = now();
            $log->detalle = $ticket;
            $log->save();

            $ticket->delete();

            flash('Se ha eliminado el ticket')->success();
            return redirect()->route('soporte.listado.revisor');
        } catch (\Throwable $th) {
            flash('Ha ocurrido un error')->error();
            return redirect()->route('soporte.listado.revisor');
        }
    }

    public function editar_ticket_revisor(Ticket $ticket)
    {
        $desarrolladores = User::where('rol', 6)->get();
        $tecnicoAsignado = User::find($ticket->tecnicosid);
        $tecnicos = $this->obtener_tecnicos_distribuidor();

        $bind = [
            "ticket" => $ticket,
            "tecnicos" => $tecnicos,
            "desarrolladores" => $desarrolladores,
            "tecnicoAsignado" => $tecnicoAsignado,
            "historialTickets" => $this->obtener_historial_tickets($ticket->ruc, $ticket->numero_ticket),
            "historialCapacitaciones" => $this->obtener_historial_implementaciones($ticket->ruc),
        ];

        return view("soporte.admin.revisor.editar", $bind);
    }

    public function listado_estado_tecnicos(Request $request)
    {
        $usuarios = UserSoporte::orderBy('estado', 'DESC')
            ->select('usuarios_soporte.*', 'usuarios.identificacion', 'usuarios.nombres', 'usuarios.distribuidoresid')
            ->join('usuarios', 'usuarios.usuariosid', '=', 'usuarios_soporte.usuariosid')
            ->when(Auth::user()->distribuidoresid, function ($query, $distribuidor) {
                if ($distribuidor == 1) {
                    return $query->where('usuarios.distribuidoresid', 1);
                } else if ($distribuidor == 2) {
                    return $query->where('usuarios.distribuidoresid', '<=', 2);
                } else {
                    return $query->where('usuarios.distribuidoresid', $distribuidor);
                }
            })
            ->get();

        foreach ($usuarios as $usuario) {
            $usuario->identificacion = str_replace("-SOP", "", $usuario->identificacion);
            $usuario->fecha_de_ingreso = date("H:i:s - d/m/Y", strtotime($usuario->fecha_de_ingreso));
            $usuario->fecha_de_salida = date("H:i:s - d/m/Y", strtotime($usuario->fecha_de_salida));
        }

        return view("soporte.admin.revisor.estado_tecnicos", compact('usuarios'));
    }

    public function ver_resporte_soportes()
    {
        $tecnicos = $this->obtener_tecnicos_distribuidor();
        return view("soporte.admin.revisor.reporte_soportes", ["tecnicos" => $tecnicos]);
    }

    public function filtrado_reporte_soporte(Request $request)
    {
        $desde = null;
        $hasta = null;

        if ($request->fecha) {
            $dates = explode(" / ", $request->fecha);

            $date1 = strtotime($dates[0]);
            $desde = date('Y-m-d H:i:s', $date1);

            $date2 = strtotime($dates[1] . ' +1 day -1 second');
            $hasta = date('Y-m-d H:i:s', $date2);
        }

        $results = Ticket::selectRaw("COUNT(producto) as 'cantidad', producto")
            ->join('usuarios', 'ticket_tienda.tecnicosid', '=', 'usuarios.usuariosid')
            ->when($request->distribuidor, function ($query, $distribuidor) {
                return $query->where('usuarios.distribuidoresid', $distribuidor);
            })
            ->when($request->tecnicoid, function ($query, $tecnicoid) {
                return $query->where('tecnicosid', $tecnicoid);
            })
            ->whereBetween("fecha_asignacion", [$desde, $hasta])
            ->groupBy('producto')
            ->get();

        $ticketsPorTecnicos = Ticket::selectRaw("tecnicosid, COUNT(tecnicosid) as 'cantidad'")
            ->join('usuarios', 'ticket_tienda.tecnicosid', '=', 'usuarios.usuariosid')
            ->when($request->distribuidor, function ($query, $distribuidor) {
                return $query->where('usuarios.distribuidoresid', $distribuidor);
            })
            ->when($request->tecnicoid, function ($query, $tecnicoid) {
                return $query->where('tecnicosid', $tecnicoid);
            })
            ->whereBetween("fecha_asignacion", [$desde, $hasta])
            ->orderBy('cantidad', 'DESC')
            ->groupBy('tecnicosid')->get();

        $ticketsPorEstado = Ticket::selectRaw("ticket_tienda.estado, COUNT(ticket_tienda.estado) as 'cantidad'")
            ->join('usuarios', 'ticket_tienda.tecnicosid', '=', 'usuarios.usuariosid')
            ->when($request->distribuidor, function ($query, $distribuidor) {
                return $query->where('usuarios.distribuidoresid', $distribuidor);
            })
            ->when($request->tecnicoid, function ($query, $tecnicoid) {
                return $query->where('tecnicosid', $tecnicoid);
            })
            ->whereBetween("fecha_asignacion", [$desde, $hasta])
            ->groupBy('ticket_tienda.estado')
            ->get();

        $ticketsPorTiempo = Ticket::selectRaw("DATE_FORMAT(fecha_asignacion, '%Y-%m-%d %H') as 'fecha', COUNT(DATE_FORMAT(fecha_asignacion, '%Y-%m-%d %H')) as 'cantidad'")
            ->join('usuarios', 'ticket_tienda.tecnicosid', '=', 'usuarios.usuariosid')
            ->when($request->distribuidor, function ($query, $distribuidor) {
                return $query->where('usuarios.distribuidoresid', $distribuidor);
            })
            ->when($request->tecnicoid, function ($query, $tecnicoid) {
                return $query->where('tecnicosid', $tecnicoid);
            })
            ->whereBetween("fecha_asignacion", [$desde, $hasta])
            ->groupByRaw("DATE_FORMAT(fecha_asignacion, '%Y-%m-%d %H')")
            ->get();

        $data = [
            "labels" => [],
            "values" => [],
            "total" => 0,
            "tecnicos" => [
                "labels" => [],
                "values" => [],
            ],
            "estados" => [
                "labels" => [],
                "values" => [],
            ],
            "tiempo" => [
                "labels" => [],
                "values" => [],
            ],
        ];

        foreach ($results as $key => $result) {
            array_push($data["labels"], strtoupper($result->producto));
            array_push($data["values"], $result->cantidad);
            $data["total"] += $result->cantidad;
        }

        foreach ($ticketsPorTecnicos as $key => $item) {
            $tempUser = User::select('nombres')->firstWhere('usuariosid', $item->tecnicosid);
            if (!$tempUser) continue;
            array_push($data["tecnicos"]["labels"], strtoupper($tempUser->nombres));
            array_push($data["tecnicos"]["values"], $item->cantidad);
        }

        foreach ($ticketsPorEstado as $key => $item) {
            $estado = "abierto";
            switch ($item->estado) {
                case 2:
                    $estado = "En progreso";
                    break;
                case 3:
                    $estado = "Desarrollo";
                    break;
                case 4:
                    $estado = "Cerrado";
                    break;
                case 5:
                    $estado = "Sin respuesta";
                    break;
            }
            array_push($data["estados"]["labels"], strtoupper($estado));
            array_push($data["estados"]["values"], $item->cantidad);
        }

        foreach ($ticketsPorTiempo as $key => $item) {
            array_push($data["tiempo"]["labels"], date("Y-m-d\TH:i:s.u\Z", strtotime($item->fecha . ':00:00')));
            array_push($data["tiempo"]["values"], $item->cantidad);
        }

        return $data;
    }

    public function ver_resporte_calificaciones()
    {
        $tecnicos = $this->obtener_tecnicos_distribuidor();
        return view("soporte.admin.revisor.reporte_calificaciones", ["tecnicos" => $tecnicos]);
    }

    public function filtrado_reporte_calificaciones(Request $request)
    {
        $encuestas = $this->filtrado_base_query($request)
            ->select('encuesta_soporte.pregunta_1', 'encuesta_soporte.pregunta_2', 'encuesta_soporte.comentario', 'ticket_tienda.ruc', 'ticket_tienda.razon_social', 'ticket_tienda.whatsapp', 'ticket_tienda.correo', 'usuarios.nombres as tecnico')
            ->join('ticket_tienda', 'encuesta_soporte.ticketid', '=', 'ticket_tienda.ticketid')
            ->orderBy('comentario')
            ->get();

        $resultsPregunta1 = $this->filtrado_base_query($request)
            ->selectRaw("pregunta_1 as 'puntaje' , C  11OUNT(pregunta_1) as 'total'")
            ->groupBy('pregunta_1')->get();

        $resultsPregunta2 = $this->filtrado_base_query($request)
            ->selectRaw("pregunta_2 as 'puntaje' , COUNT(pregunta_2) as 'total'")
            ->groupBy('pregunta_2')->get();

        $data = [
            "pregunta_1" => [
                "labels" => [],
                "values" => [],
                "total_puntaje" => 0,
                "promedio" => 0,
            ],
            "pregunta_2" => [
                "labels" => [],
                "values" => [],
                "total_puntaje" => 0,
                "promedio" => 0,
            ],
            "total" => count($encuestas),
            "tabla" => $encuestas,
            "calificaciones_por_tecnico" => $this->calificaciones_por_tecnico($request),
        ];

        if (count($resultsPregunta1) > 0) {
            $data["pregunta_1"] = $this->procesarResultados($resultsPregunta1);
            $data["pregunta_2"] = $this->procesarResultados($resultsPregunta2);
        }

        return $data;
    }

    private function filtrado_base_query(Request $request)
    {
        $desde = null;
        $hasta = null;

        if ($request->fecha) {
            $dates = explode(" / ", $request->fecha);

            $date1 = strtotime($dates[0]);
            $desde = date('Y-m-d H:i:s', $date1);

            $date2 = strtotime($dates[1] . ' +1 day -1 second');
            $hasta = date('Y-m-d H:i:s', $date2);
        }

        return EncuestaSoporte::join('usuarios', 'encuesta_soporte.tecnicoid', '=', 'usuarios.usuariosid')
            ->when($request->distribuidor, function ($query, $distribuidor) {
                return $query->where('usuarios.distribuidoresid', $distribuidor);
            })
            ->whereBetween("fecha_creacion", [$desde, $hasta])
            ->when($request->tecnicoid, function ($query, $tecnico) {
                return $query->where('tecnicoid', $tecnico);
            })
            ->where('justificado', '=', 0);
    }

    public function procesarResultados($results)
    {
        $data = [
            "labels" => [],
            "values" => [],
            "total_puntaje" => 0,
            "promedio" => 0,
        ];
        $total = 0;

        foreach ($results as $item) {
            $text = ($item->puntaje == 1) ? "{$item->puntaje} punto" : "{$item->puntaje} puntos";
            array_push($data["labels"], strtoupper($text));
            array_push($data["values"], $item->total);
            $data["total_puntaje"] += $item->total * $item->puntaje;
            $total += $item->total;
        }

        $data["promedio"] = number_format($data["total_puntaje"] / $total, 2) . "/5.00";

        return $data;
    }

    private function calificaciones_por_tecnico(Request $request)
    {
        $resultadosPregunta1 = EncuestaSoporte::select(
            'usuarios.nombres as tecnico',
            DB::raw('SUM(CASE WHEN pregunta_1 = 1 THEN 1 ELSE 0 END) AS puntaje_1'),
            DB::raw('SUM(CASE WHEN pregunta_1 = 2 THEN 1 ELSE 0 END) AS puntaje_2'),
            DB::raw('SUM(CASE WHEN pregunta_1 = 3 THEN 1 ELSE 0 END) AS puntaje_3'),
            DB::raw('SUM(CASE WHEN pregunta_1 = 4 THEN 1 ELSE 0 END) AS puntaje_4'),
            DB::raw('SUM(CASE WHEN pregunta_1 = 5 THEN 1 ELSE 0 END) AS puntaje_5'),
            DB::raw('COUNT(pregunta_1) AS total_respuestas')
        )
            ->join('usuarios', 'encuesta_soporte.tecnicoid', '=', 'usuarios.usuariosid')
            ->when($request->tecnicoid, function ($query, $tecnico) {
                return $query->where('tecnicoid', $tecnico);
            })
            ->when($request->distribuidor, function ($query, $distribuidor) {
                return $query->where('usuarios.distribuidoresid', $distribuidor);
            })
            ->when($request->fecha, function ($query, $fecha) {
                $dates = explode(" / ", $fecha);

                $date1 = strtotime($dates[0]);
                $desde = date('Y-m-d H:i:s', $date1);

                $date2 = strtotime($dates[1] . ' +1 day -1 second');
                $hasta = date('Y-m-d H:i:s', $date2);
                return $query->whereBetween('fecha_creacion', [$desde, $hasta]);
            })
            ->groupBy('usuarios.nombres')
            ->get();

        $resultadosPregunta2 = EncuestaSoporte::select(
            'usuarios.nombres as tecnico',
            DB::raw('SUM(CASE WHEN pregunta_2 = 1 THEN 1 ELSE 0 END) AS puntaje_1'),
            DB::raw('SUM(CASE WHEN pregunta_2 = 2 THEN 1 ELSE 0 END) AS puntaje_2'),
            DB::raw('SUM(CASE WHEN pregunta_2 = 3 THEN 1 ELSE 0 END) AS puntaje_3'),
            DB::raw('SUM(CASE WHEN pregunta_2 = 4 THEN 1 ELSE 0 END) AS puntaje_4'),
            DB::raw('SUM(CASE WHEN pregunta_2 = 5 THEN 1 ELSE 0 END) AS puntaje_5'),
            DB::raw('COUNT(pregunta_2) AS total_respuestas')
        )
            ->join('usuarios', 'encuesta_soporte.tecnicoid', '=', 'usuarios.usuariosid')
            ->when($request->tecnicoid, function ($query, $tecnico) {
                return $query->where('tecnicoid', $tecnico);
            })
            ->when($request->distribuidor, function ($query, $distribuidor) {
                return $query->where('usuarios.distribuidoresid', $distribuidor);
            })
            ->when($request->fecha, function ($query, $fecha) {
                $dates = explode(" / ", $fecha);

                $date1 = strtotime($dates[0]);
                $desde = date('Y-m-d H:i:s', $date1);

                $date2 = strtotime($dates[1] . ' +1 day -1 second');
                $hasta = date('Y-m-d H:i:s', $date2);
                return $query->whereBetween('fecha_creacion', [$desde, $hasta]);
            })
            ->groupBy('usuarios.nombres')
            ->get();

        $resultados = $resultadosPregunta1->concat($resultadosPregunta2)
            ->groupBy('tecnico')
            ->map(function ($items) {
                return [
                    'name' => $items[0]->tecnico,
                    'puntaje_1' => $items->sum('puntaje_1'),
                    'puntaje_2' => $items->sum('puntaje_2'),
                    'puntaje_3' => $items->sum('puntaje_3'),
                    'puntaje_4' => $items->sum('puntaje_4'),
                    'puntaje_5' => $items->sum('puntaje_5'),
                ];
            });

        $res = (object)[
            "labels" => [],
            "values" => [
                [
                    "name" => "1 punto",
                    "data" => [],
                ],
                [
                    "name" => "2 puntos",
                    "data" => [],
                ],
                [
                    "name" => "3 puntos",
                    "data" => [],
                ],
                [
                    "name" => "4 puntos",
                    "data" => [],
                ],
                [
                    "name" => "5 puntos",
                    "data" => [],
                ],
            ],
        ];

        foreach ($resultados as $key => $tecnico) {
            $res->labels = [...$res->labels, $tecnico['name']];
            $res->values[0]['data'] = [...$res->values[0]['data'], $tecnico['puntaje_1']];
            $res->values[1]['data'] = [...$res->values[1]['data'], $tecnico['puntaje_2']];
            $res->values[2]['data'] = [...$res->values[2]['data'], $tecnico['puntaje_3']];
            $res->values[3]['data'] = [...$res->values[3]['data'], $tecnico['puntaje_4']];
            $res->values[4]['data'] = [...$res->values[4]['data'], $tecnico['puntaje_5']];
        }
        return $res;
    }

    /* -------------------------------------------------------------------------- */
    /*                  Funciones para revisor de calificaciones                  */
    /* -------------------------------------------------------------------------- */
    public function listado_calificaciones(Request $request)
    {
        $this->asignacion_tickets();
        $tecnicos = $this->obtener_tecnicos_distribuidor();
        return view('soporte.admin.calificaciones.index', ['tecnicos' => $tecnicos]);
    }

    public function filtro_listado_calificaciones(Request $request)
    {
        if ($request->ajax()) {
            $encuestas = EncuestaSoporte::select('encuesta_soporte.*', 'ticket_tienda.razon_social', 'ticket_tienda.whatsapp', 'ticket_tienda.numero_ticket', 'ticket_tienda.correo', 'ticket_tienda.ruc', 'usuarios.nombres as nombre_tecnico')
                ->join('ticket_tienda', 'ticket_tienda.ticketid', 'encuesta_soporte.ticketid')
                ->join('usuarios', 'usuarios.usuariosid', 'encuesta_soporte.tecnicoid')
                ->when($request->distribuidor, function ($query, $distribuidor) {
                    return $query->where('usuarios.distribuidoresid', $distribuidor);
                })
                ->when($request->tecnico, function ($query, $tecnico) {
                    return $query->where('tecnicoid', $tecnico);
                })
                ->when($request->calificacion, function ($query, $calificacion) {
                    if ($calificacion == 1) {
                        return $query->whereNull('comentario');
                    } else if ($calificacion == 2) {
                        return $query->whereNotNull('comentario');
                    }
                })
                ->when($request->estado, function ($query, $estado) {
                    return $query->where('estado_revision', $estado);
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
                ->get();

            return DataTables::of($encuestas)
                ->editColumn('contacto', function ($encuesta) {
                    return "<span style='word-break: break-all; width: 100%'>{$encuesta->whatsapp}<br /><br />{$encuesta->correo}</span>";
                })
                ->editColumn('razon_social', function ($encuesta) {
                    return trim($encuesta->razon_social);
                })
                ->editColumn('comentario', function ($encuesta) {
                    return trim($encuesta->comentario);
                })
                ->editColumn('estado', function ($encuesta) {
                    if ($encuesta->estado_revision == 1) {
                        return "<span class='label label-lg font-weight-bold label-danger label-inline m-1'>pendiente</span>";
                    } else if ($encuesta->estado_revision == 2) {
                        return "<span class='label label-lg font-weight-bold label-primary label-inline m-1'>En revisión</span>";
                    } else if ($encuesta->estado_revision == 3) {
                        return "<span class='label label-lg font-weight-bold label-success label-inline m-1'>Revisado</span>";
                    } else {
                        return "estado no definido";
                    }
                })
                ->editColumn('action', function ($encuesta) {
                    $botones = "";

                    if ($encuesta->estado_revision == 1) {
                        $botones = $botones . '<a class="btn btn-sm btn-light btn-icon btn-hover-primary change-state-modal" href="javascript:void(0)" data-href="' . route('calificaciones.actualizar.estado', $encuesta->encuesta_soporte_id) . '" data-estado="' . $encuesta->estado_revision . '" title="Cambiar estado"> <i class="la la-sync"></i> </a>';
                    }

                    if ($encuesta->estado_revision == 2) {
                        $botones = $botones . '<a class="btn btn-sm btn-light btn-icon btn-hover-info justificacion-modal" href="javascript:void(0)" data-href="' . route('calificaciones.registrar.justificacion', $encuesta->encuesta_soporte_id) . '" title="Agregar justificación"> <i class="la la-comment"></i> </a>';
                    }

                    return $botones;
                })
                ->rawColumns(['action', 'contacto', 'estado'])
                ->make(true);
        }
    }

    public function actualizar_estado_encuesta(EncuestaSoporte $encuesta, Request $request)
    {
        try {
            DB::beginTransaction();
            $encuesta->estado_revision = $request->estado_revision;
            $encuesta->save();
            DB::commit();
            flash('Estado actualizado correctamente')->success();
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Error al actualizar el estado')->error();
            return back();
        }
    }

    // TODO: funcion de pruebas
    public function pruebas()
    {
        $encuestas = EncuestaSoporte::all();

        $encuestas->forEach(function ($encuesta) {
            $encuesta->estado_revision = 1;
            $encuesta->save();
        });

        $encuestas->ftp_fget(1, "prueba.txt", "prueba.txt", FTP_ASCII);

        // $encuests->

        $encuestas->flatten();


        return $encuestas;
    }

    public function registrar_justificacion(EncuestaSoporte $encuesta, Request $request)
    {
        try {
            DB::beginTransaction();

            $encuesta->justificado = $request->justificado;
            $encuesta->comentario_revision = $request->comentario_revision;
            $encuesta->estado_revision = $request->estado_revision;

            if ($encuesta->save()) {
                $log = new Log();
                $log->usuario = Auth::user()->nombres;
                $log->pantalla = "Calificacion";
                $log->operacion = "Registrar justificacion";
                $log->fecha = now();
                $log->detalle = $encuesta;
                $log->save();
            }
            DB::commit();
            flash('Estado actualizado correctamente')->success();
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Error al actualizar el estado')->error();
            return back();
        }
    }

    public function listado_justificadas(Request $request)
    {
        return view('soporte.admin.calificaciones.justificadas');
    }

    public function filtro_listado_justificadas(Request $request)
    {
        if ($request->ajax()) {
            $encuestas = EncuestaSoporte::select('encuesta_soporte.*', 'ticket_tienda.razon_social', 'ticket_tienda.numero_ticket', 'ticket_tienda.whatsapp', 'ticket_tienda.motivo', 'ticket_tienda.correo', 'ticket_tienda.ruc', 'usuarios.nombres as nombre_tecnico')
                ->join('ticket_tienda', 'ticket_tienda.ticketid', 'encuesta_soporte.ticketid')
                ->join('usuarios', 'usuarios.usuariosid', 'encuesta_soporte.tecnicoid')
                ->where('usuarios.distribuidoresid', Auth::user()->distribuidoresid)
                ->whereNotNull('encuesta_soporte.comentario_revision')
                ->where('justificado', $request->justificadas)
                ->get();

            return DataTables::of($encuestas)
                ->editColumn('contacto', function ($encuesta) {
                    return "<span style='word-break: break-all; width: 100%'>{$encuesta->whatsapp}<br /><br />{$encuesta->correo}</span>";
                })
                ->editColumn('action', function ($ticket) {
                    $botones = "";
                    return $botones;
                })
                ->rawColumns(['action', 'estado'])
                ->make(true);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                    Funciones para asignacion de tickets                    */
    /* -------------------------------------------------------------------------- */
    private function liberar_tecnico($ticket)
    {
        if (!$ticket->tecnicosid) return;

        DB::beginTransaction();
        $tecnico = UserSoporte::firstWhere('usuariosid', $ticket->tecnicosid);
        $ticketsActivos = $this->obtener_numero_tickets_activos($tecnico->usuariosid);

        try {
            $numTickets = $ticketsActivos - 1;
            if ($numTickets < 0) {
                $numTickets = 0;
            }
            $tecnico->tickets_activos = $numTickets;

            if ($tecnico->save()) {
                $this->asignacion_tickets();
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }


    private function asignar_tecnico_manual(int $tecnicosId)
    {
        DB::beginTransaction();
        try {
            $tecnico = UserSoporte::firstWhere('usuariosid', $tecnicosId);
            $ticketsActivos = $this->obtener_numero_tickets_activos($tecnico->usuariosid);

            $numTickets = $ticketsActivos + 1;
            $tecnico->tickets_activos = $numTickets;
            $tecnico->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }

    private function asignacion_tickets()
    {
        try {
            $horaEntrada = env('HORA_ENTRADA') || "08:10";
            $horaSalida = env('HORA_SALIDA') || "16:55";

            $current = date('G:i');
            $current = strtotime($current);
            $entrada = strtotime($horaEntrada);
            $salida = strtotime($horaSalida);

            if (!($current >= $entrada && $current <= $salida)) return;

            $tickets = Ticket::all()->where('tecnicosid', null);

            foreach ($tickets as $ticket) {

                if ($ticket->producto == 'pc' && $ticket->distribuidor == 5) {
                    $fechaActual = new DateTime();
                    $fechaTicket = new DateTime($ticket->fecha_creado);

                    $intervalo = $fechaActual->diff($fechaTicket);
                    $minutosTranscurridos = $intervalo->i;

                    if ($minutosTranscurridos > 30) {
                        $ticket->distribuidor = 2;
                        $ticket->save();
                    }
                }

                $tecnicoLibre = $this->buscar_tecnico_libre($ticket->producto, $ticket->distribuidor);

                if ($tecnicoLibre == null) continue;


                $ticket->tecnicosid = $tecnicoLibre->usuariosid;
                $ticket->fecha_asignacion = now();

                if ($ticket->save()) {
                    $ticketsActivos = $this->obtener_numero_tickets_activos($tecnicoLibre->usuariosid);
                    $tecnicoLibre->tickets_activos = $ticketsActivos;
                    $tecnicoLibre->save();
                    $this->notificar_asignacion_ticket($tecnicoLibre->usuariosid, $ticket);
                }
            }
        } catch (\Throwable $th) {
        }
    }

    private function buscar_tecnico_libre($producto = "web", $distribuidor = 1)
    {
        $maximoTickets = 50;
        $tecnicoLibre = null;
        $tecnicos = [];

        if ($producto == "pc") {
            $tecnicos = UserSoporte::where('estado', 1)->where('distribuidor', $distribuidor)->where('productos', 'LIKE', '%' . $producto . '%')->get();
        } else {
            $tecnicos = UserSoporte::where('estado', 1)->where('distribuidor', 2)->where('productos', 'LIKE', '%' . $producto . '%')->get();
        }

        foreach ($tecnicos as $key => $tecnico) {
            if ($tecnico->tickets_activos < $tecnico->tickets_maximos) {
                if ($tecnico->tickets_activos < $maximoTickets) {
                    $maximoTickets = $tecnico->tickets_activos;
                    $tecnicoLibre = $tecnico;
                }
            }
        }

        return $tecnicoLibre;
    }

    /* -------------------------------------------------------------------------- */
    /*                          Otras funciones genericas                         */
    /* -------------------------------------------------------------------------- */

    private function obtener_tiempo_activo_ticket($ticket)
    {
        if ($ticket->fecha_asignacion == null) {
            return "";
        }

        $date1 = new DateTime($ticket->fecha_asignacion);
        $date2 = now();
        $diferencia = $date1->diff($date2);

        $horas = $diferencia->h;
        $minutos = $diferencia->i;

        if ($diferencia->d != 0) {
            $horas = $horas + ($diferencia->d * 24);
        }

        if ($horas < 10) {
            $horas = "0{$horas}";
        }
        if ($minutos < 10) {
            $minutos = "0{$minutos}";
        }

        return "{$horas}H:{$minutos}m";
    }

    private function notificar_asignacion_ticket($idTecnico, $ticket)
    {
        try {
            if (!$idTecnico) return false;

            $tecnico = User::find($idTecnico);
            $tecnicoSoporte = UserSoporte::firstWhere('usuariosid', $idTecnico);

            if (!$tecnico) return false;

            $sms = new WhatsappController();

            return $sendMessage = $sms->enviar_personalizado([
                "numero" => $ticket->whatsapp,
                "mensaje" =>  "¡Hola! 👋 Solo quería informarte que hemos recibido tu solicitud de soporte para el Sistema Contable Perseo. Tu ticket No: *{$ticket->numero_ticket}* ha sido asignado a nuestro técnico *{$tecnico->nombres}*. Pronto recibirás atención de primera. ¡Gracias por tu paciencia! 🛠️🔧",
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }
    }

    private function obtener_numero_tickets_activos(int $idTecnico)
    {
        $numero = Ticket::selectRaw("COUNT(*) as 'total'")
            ->where('estado', '<=', 2)
            ->where('tecnicosid', $idTecnico)
            ->first();

        return intval($numero->total);
    }

    private function obtener_distribuidor_ticket($distribuidor)
    {
        /**
         * 1 => "Alfa"
         * 2 => "Matriz"
         * 3 => "Delta"
         * 4 => "Omega"
         * 5 => "Otros"
         */
        switch ($distribuidor) {
            case '1':
                return 1;
            case '6':
                return 2;
            case '2':
                return 3;
            case '3':
                return 4;
            default:
                return 5;
        }
    }

    private function obtener_producto_ticket($producto)
    {
        $producto = strtolower($producto);
        switch ($producto) {
            case 'facturito':
                return 1;
            case 'web':
                return 2;
            case 'pc':
                return 3;
            case 'contafacil':
                return 4;
            default:
                return 5;
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
            ->where('estado', 1)
            ->get();

        return $tecnicos;
    }

    public function obtener_historial_tickets(string $ruc, string $numero_ticket = null)
    {
        return Ticket::select('ticketid', 'numero_ticket', 'motivo', 'fecha_asignacion', 'usuarios.nombres as tecnico')
            ->join('usuarios', 'usuarios.usuariosid', '=', 'ticket_tienda.tecnicosid')
            ->where('ruc', $ruc)
            ->when($numero_ticket, function ($query, $numero_ticket) {
                return $query->where('numero_ticket', '<>', $numero_ticket);
            })
            ->orderBy('fecha_asignacion', 'desc')
            ->get();
    }

    public function obtener_historial_implementaciones(string $ruc, int $current = null)
    {
        return SoporteEspecial::select('soporteid', 'fecha_agendado', 'tipo', 'usuarios.nombres as tecnico')
            ->selectRaw(
                'CASE tipo
            WHEN 1 THEN "Demostración"
            WHEN 2 THEN "Capacitación"
            ELSE "LITE"
            END as tipo'
            )
            ->join('usuarios', 'usuarios.usuariosid', '=', 'soportes_especiales.tecnicoid')
            ->where('ruc', $ruc)
            ->when($current, function ($query, $soporteid) {
                return $query->where('soporteid', '<>', $soporteid);
            })
            ->orderBy('fecha_creacion', 'desc')
            ->get();
    }
}