<?php

namespace App\Http\Controllers;

use App\Constants\ConstantesTecnicos;
use App\Events\NuevoRegistroSopEsp;
use App\Mail\NotificacionEstadoCapacitacion;
use App\Mail\NotificacionVendedores;
use App\Models\LogSoporte;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\SoporteEspecial;
use App\Models\Tecnicos;
use App\Models\User;
use App\Rules\ValidarCelular;
use App\Rules\ValidarCorreo;
use App\Rules\ValidarRUC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
                ->where('tecnicoid', Auth::guard('tecnico')->user()->tecnicosid)
                ->when($request->plan, function ($query, $plan) {
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
                ->editColumn('plan', function ($soporte) {
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

        $vendedores = User::where('rol', 1)
            ->where('estado', 1)
            ->where('nombres', '<>', 'PREDETERMINADO')
            ->select('usuariosid', 'nombres')
            ->orderBy('distribuidoresid', 'asc')
            ->get();

        return view('soporte.admin.tecnico.demos.agregar', compact('tecnicos', 'vendedores'));
    }

    public function registrar_soporte_especial(Request $request)
    {
        $validate1 = [
            'tipo' => 'required',
            'ruc' => 'required|min:13',
            'razon_social' => 'required',
            'correo' => ['required', new ValidarCorreo],
            'whatsapp' => ['required', new ValidarCelular],
            'estado' => 'required',
            'fecha_agendado' => 'required',
            'plan' => 'required',
            'tecnico' => 'required',
            'vededorid' => 'required',
            'actividad_empresa' => 'required|min:10|max:255',
        ];
        $validate2 = [
            'tipo.required' => 'Seleccione un tipo de soporte',
            'ruc.required' => 'Ingrese el RUC',
            'ruc.min' => 'Ingrese un numero de RUC válido',
            'razon_social.required' => 'Ingrese la razón social',
            'correo.required' => 'Ingrese un correo electrónico',
            'whatsapp.required' => 'Ingrese un número celular',
            'estado.required' => 'Seleccione un estado',
            'fecha_agendado.required' => 'Debe seleccionar una fecha',
            'plan.required' => 'Seleccione un plan',
            'tecnico.required' => 'Seleccione un técnico',
            'vededorid.required' => 'Seleccione un vendedor',
            'actividad_empresa.required' => 'Ingrese una actividad de la empresa',
            'actividad_empresa.min' => 'La actividad de la empresa debe tener al menos 10 caracteres',
            'actividad_empresa.max' => 'La actividad de la empresa debe tener máximo 255 caracteres',            
        ];

        $request->validate($validate1, $validate2);


        $soporteAnterior = SoporteEspecial::where('ruc', $request->ruc)
            ->where('tipo', $request->tipo)
            ->where('plan', $request->plan)
            ->where('estado', '!=', 6)
            ->first();

        if ($soporteAnterior) {
            flash("Ya existe un soporte registrado para este cliente")->warning();
            return back();
        }


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
            $soporte->vededorid = $request->vededorid;
            $soporte->actividad_empresa = $request->actividad_empresa;

            $soporte->save();
            flash("Soporte registrado")->success();

            $this->notificar_asignacion($soporte->tecnicoid, $soporte->tipo);

            $log = new LogSoporte();
            $log->usuario = Auth::guard('tecnico')->user()->nombres;
            $log->pantalla = "Soporte Especial";
            $log->operacion = "Agregar";
            $log->fecha = now();
            $log->detalle = $soporte;
            $log->save();

            if (Auth::guard('tecnico')->user()->rol == 7) {
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

        $soporteAnteriores = SoporteEspecial::where('ruc', $soporte->ruc)
            ->where('soporteid', '<>', $soporte->soporteid)
            ->when($soporte->tipo, function ($query, $tipo) {
                if ($tipo == 1) {
                    // demo
                    return $query->whereIn('tipo', [2, 3]);
                } else if ($tipo == 2) {
                    // Capacitacion
                    return $query->whereIn('tipo', [1, 3]);
                } else {
                    // lite
                    return $query->whereIn('tipo', [1, 2]);
                }
            })
            ->count();

        // $bloquearTecnico = ($soporteAnteriores > 0 && Auth::guard('tecnico')->user()->rol == 7) ? true : false;
        $bloquearTecnico = false;

        $bindings = [
            'soporte' => $soporte,
            'tecnicos' => $tecnicos,
            "historialTickets" => $controller->obtener_historial_tickets($soporte->ruc),
            "historialCapacitaciones" => $controller->obtener_historial_implementaciones($soporte->ruc, $soporte->soporteid),
            "bloquearTecnico" => $bloquearTecnico,
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
            //case 3:
              //  if (!$soporte->fecha_iniciado) $data['fecha_iniciado'] = now();
                //break;
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
            $this->notificar_asignacion($request->tecnico, $soporte->tipo);
        }

        try {

            $soporte->update($data);

            $log = new LogSoporte();
            $soporteLog =  SoporteEspecial::find($soporte->soporteid);
            $log->usuario = Auth::guard('tecnico')->user()->nombres;
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
                $data[] = ["fecha" => now(), "escritor" => Auth::guard('tecnico')->user()->nombres, "contenido" => $request->contenido];
            } else {
                $data[] = ["fecha" => now(), "escritor" => Auth::guard('tecnico')->user()->nombres, "contenido" => $request->contenido];
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
                'identificacion2' => ['required', new ValidarRUC],
            ],
            [
                'nombre2.required' => 'Ingrese el nombre de la persona que asistirá a la capacitación',
                'correo2.required' => 'Ingrese un correo electrónico',
                'whatsapp.required' => 'Ingrese un número celular',
                'identificacion2.required' => 'Ingrese un RUC',
            ],
        );

        $identificacionActual = $request->identificacion2 ?? $factura->identificacion;

        $soporteAnteriorPro = SoporteEspecial::select('tecnicoid', 'vededorid', 'razon_social', 'ruc')
            ->where('ruc', $identificacionActual)
            ->where('vededorid', Auth::user()->usuariosid)
            ->where('tipo', 2)
            ->orderBy('soporteid', 'desc')
            ->first();

        $soporteAnteriorGb = SoporteEspecial::select('tecnicoid', 'vededorid', 'razon_social', 'ruc')
            ->where('ruc', $identificacionActual)
            ->where('vededorid', '<>', Auth::user()->usuariosid)
            ->orderBy('soporteid', 'desc')
            ->first();

        if ($soporteAnteriorPro || $soporteAnteriorGb) {
            $mensaje = "Ya existe una capacitación registrada para este cliente";
    
            if ($soporteAnteriorGb) {
                $mensaje = $this->validar_soportes_anteriores($soporteAnteriorGb);
            }
    
            flash($mensaje)->warning();
            return back();
        }
    
        $soporteAnteriorPro = SoporteEspecial::select('tecnicoid', 'soporteid', 'razon_social', 'ruc')
            ->where('ruc', $identificacionActual)
            ->where('vededorid', Auth::user()->usuariosid)
            ->whereIn('tipo', [1, 3])
            ->orderBy('soporteid', 'desc')
            ->first();

        $productos = json_decode($factura->productos);

        $soporte = new SoporteEspecial();
        $soporte->ruc = $identificacionActual;
        $soporte->razon_social = $request->nombre2 ?? $factura->nombre;
        $soporte->correo = $request->correo2 ?? $factura->correo;
        $soporte->whatsapp = $request->whatsapp ?? $factura->telefono;
        $soporte->estado = 1;
        $soporte->tipo = 2;
        $soporte->fecha_creacion = now();
        $soporte->vededorid = Auth::user()->usuariosid;

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

        NuevoRegistroSopEsp::dispatch($soporte, $factura, $soporteAnteriorPro);

        try {
            ComisionesController::actualizar_comision($factura, $soporte->soporteid);
            if ($soporteAnteriorPro) {
                $soporte->tecnicoid = $soporteAnteriorPro->tecnicoid;
                $this->notificar_asignacion($soporte->tecnicoid, $soporte->tipo);
            } 

            $soporte->save();
            $factura->capacitacionid = $soporte->soporteid;
            $factura->save();

            $log = new LogSoporte();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Soporte Especial";
            $log->operacion = "Agregar";
            $log->fecha = now();
            $log->detalle = $soporte;
            $log->save();

            flash("Capacitación registrada exitosamente")->success();
            return redirect()->route('facturas.editar', $factura->facturaid);
        } catch (\Throwable $th) {
            flash("Hubo un error al registrar el soporte: " . $th->getMessage())->error();
            return redirect()->route('facturas.editar', $factura->facturaid);
        }
    }

    public function listado_demos_lites()
    {
        return view('auth.demos.index');
    }

    public function filtrado_listado_demos_lites(Request $request)
    {
        if ($request->ajax()) {

            $data = SoporteEspecial::select(
                'soportes_especiales.soporteid',
                'soportes_especiales.ruc',
                'soportes_especiales.razon_social',
                'soportes_especiales.correo',
                'soportes_especiales.estado',
                'soportes_especiales.whatsapp',
                'soportes_especiales.tipo',
                'soportes_especiales.plan',
                'soportes_especiales.tecnicoid',
                'soportes_especiales.vededorid',
            )
                ->where('vededorid', Auth::user()->usuariosid)
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


            return DataTables::of($data)
                ->editColumn('estado', function ($soporte) {
                    return $this->obtener_estado_soporte($soporte->estado);
                })
                ->editColumn('tipo', function ($soporte) {
                    return $this->obtener_tipo_soporte($soporte->tipo);
                })
                ->editColumn('plan', function ($soporte) {
                    return $this->obtener_plan_soporte($soporte->plan);
                })
                ->editColumn('acciones', function ($soporte) {
                    $botones = '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('demos.ver', $soporte->soporteid) . '"  title="Ver"> <i class="la la-eye"></i> </a>';
                    return $botones;
                })
                ->rawColumns(['acciones', 'estado'])
                ->make(true);
        }
    }

    public function crear_demo_lite()
    {
        $soporte = new SoporteEspecial();
        return view('auth.demos.crear', compact('soporte'));
    }

    public function guardar_demo_lite(Request $request)
    {
        $validate1 = [
            'plan' => 'required',
            'tipo' => 'required',
            'ruc' => 'required|min:13|max:13',
            'razon_social' => 'required',
            'correo' => ['required', new ValidarCorreo],
            'whatsapp' => ['required', new ValidarCelular],
            'actividad_empresa' => 'required|min:10|max:255',
        ];
        $validate2 = [
            'plan.required' => 'Seleccione un plan',
            'tipo.required' => 'Seleccione un tipo de soporte',
            'ruc.required' => 'Ingrese el RUC',
            'ruc.min' => 'El RUC debe tener 13 dígitos',
            'razon_social.required' => 'Ingrese la razón social',
            'correo.required' => 'Ingrese un correo electrónico',
            'whatsapp.required' => 'Ingrese un número celular',
            'actividad_empresa.required' => 'Ingrese una actividad de la empresa',
            'actividad_empresa.min' => 'La actividad de la empresa debe tener al menos 10 caracteres',
            'actividad_empresa.max' => 'La actividad de la empresa debe tener máximo 255 caracteres',
        ];

        $request->validate($validate1, $validate2);

        $soporteAnterior = SoporteEspecial::where('ruc', $request->ruc)
            ->orderBy('soporteid', 'desc')
            ->first();

        if ($soporteAnterior) {
            $mensaje = $this->validar_soportes_anteriores($soporteAnterior, $request);

            if ($mensaje != null) {
                flash($mensaje)->warning();
                return back();
            }
        }

        try {
            $soporte = new SoporteEspecial();
            $soporte->ruc = $request->ruc;
            $soporte->razon_social = $request->razon_social;
            $soporte->correo = $request->correo;
            $soporte->whatsapp = $request->whatsapp;
            $soporte->estado = 1;
            $soporte->tipo = $request->tipo;
            $soporte->fecha_creacion = now();
            $soporte->plan = $request->plan;
            $soporte->actividad_empresa = $request->actividad_empresa;
            $soporte->vededorid = Auth::user()->usuariosid;

            $soporte->save();

            flash("Nuevo registro creado")->success();

            $log = new LogSoporte();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Soporte Especial";
            $log->operacion = "Agregar";
            $log->fecha = now();
            $log->detalle = $soporte;
            $log->save();

            return redirect()->route('demos.ver', $soporte->soporteid);
        } catch (\Throwable $th) {
            flash("Hubo un error al registrar: " . $th->getMessage())->error();
            return back();
        }
    }

    public function ver_demo_lite(SoporteEspecial $soporte)
    {
        $readOnly = true;
        $vendedorSIS = null;
        $lite = SoporteEspecial::where('ruc', $soporte->ruc)
            ->where('tipo', 3)
            ->count();

        $isRegisterLite = $lite > 0 ? true : false;

        $tecnico = Tecnicos::find($soporte->tecnicoid, ["nombres"]);
        $soporte->nombreTecnico = $tecnico->nombres ?? null;

        if ($soporte->lite_liberado == 0) {
            $url = "https://perseo.app/api/vendedores_consulta";

            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'Perseo', "clave" => "Perseo1232*"])
                ->withOptions(["verify" => false])
                ->post($url, ['identificacion' => substr(Auth::user()->identificacion, 0, 10)])
                ->json();


            $vendedorSIS = $resultado["vendedor"][0];
            $vendedorSIS = ($vendedorSIS != null) ? json_decode(json_encode($vendedorSIS)) : null;
        }
        return view('auth.demos.ver', compact('soporte', 'readOnly', 'isRegisterLite', 'vendedorSIS'));
    }

    public function convertir_lite(SoporteEspecial $soporte)
    {
        try {
            $lite = new SoporteEspecial();

            $lite->ruc = $soporte->ruc;
            $lite->razon_social = $soporte->razon_social;
            $lite->correo = $soporte->correo;
            $lite->whatsapp = $soporte->whatsapp;
            $lite->estado = 1;
            $lite->tipo = 3;
            $lite->fecha_creacion = now();
            $lite->plan = $soporte->plan;
            $lite->actividad_empresa = $soporte->actividad_empresa;
            $lite->vededorid = $soporte->vededorid;
            $lite->tecnicoid = $soporte->tecnicoid;

            $lite->save();

            $this->notificar_asignacion($lite->tecnicoid, $lite->tipo);


            $log = new LogSoporte();
            $log->usuario = Auth::user()->nombres;
            $log->pantalla = "Soporte Especial";
            $log->operacion = "Agregar";
            $log->fecha = now();
            $log->detalle = $soporte;
            $log->save();

            flash("Lite creada correctamente")->success();
            return redirect()->route('demos.ver', $lite->soporteid);
        } catch (\Throwable $th) {
            flash("Hubo un error al registrar: " . $th->getMessage())->error();
            return back();
        }
    }

    public function liberar_lite(SoporteEspecial $soporte, Request $request)
    {
        try {
            $sisVendedor = $request->licenciador['cliente']['sis_vendedoresid'];
            if (!$sisVendedor) {
                return response(["status" => 400, "message" => "No cuentas con permisos necesarios en el ADMIN"], 400)->header('Content-Type', 'application/json');
            }

            $url = "https://perseo.app/api/registrar_licencia";
            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'Perseo', "clave" => "Perseo1232*"])
                ->withOptions(["verify" => false])
                ->post($url, $request->licenciador)
                ->json();

            if (!isset($resultado["licencia"])) {
                return response(["status" => 400, "message" => "No se pudo liberar las licencias"], 400)->header('Content-Type', 'application/json');
            }

            if ($resultado["licencia"][0] == "Creado correctamente") {
                try {
                    $soporte->lite_liberado = 1;
                    $soporte->save();

                    $log = new LogSoporte();
                    $log->usuario = Auth::user()->nombres;
                    $log->pantalla = "Demos";
                    $log->operacion = "Liberar Lite";
                    $log->fecha = now();
                    $log->detalle =  $soporte;
                    $log->save();

                    return response(["status" => 200, "message" => "Licencias liberadas correctamente", "sms" => $resultado["licencia"][0]], 200)->header('Content-Type', 'application/json');
                } catch (\Throwable $th) {

                    return response(["status" => 201, "message" => "Licencias liberadas con errores: " . $th->getMessage(), "sms" => $resultado["licencia"][0]], 201)->header('Content-Type', 'application/json');
                }
            }

            return response(["status" => 400, "message" => $resultado["licencia"][0], "sms" => $resultado["licencia"][0]], 400)->header('Content-Type', 'application/json');
        } catch (\Throwable $th) {
            return response(["status" => 500, "message" => $th->getMessage()], 500)->header('Content-Type', 'application/json');
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
                    'tecnicos.tecnicosid',
                    'tecnicos.nombres',
                    'tecnicos.distribuidoresid',
                )
                    ->join('tecnicos', 'tecnicos.tecnicosid', 'soportes_especiales.tecnicoid')
                    ->when($request->tecnico, function ($query, $tecnico) {
                        return $query->where('tecnicoid', $tecnico);
                    })
                    ->when($request->distribuidor, function ($query, $distribuidor) {
                        return $query->where('tecnicos.distribuidoresid', $distribuidor);
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

                    if ($soporte->estado == 1) {
                        $botones .= '<a class="btn btn-sm btn-light btn-icon btn-hover-danger confirm-delete" href="javascript:void(0)" data-href="' . route('sop.eliminar', $soporte->soporteid) . '" title="Eliminar"> <i class="la la-trash"></i> </a>';
                    }
                    return $botones;
                })
                ->rawColumns(['acciones', 'estado', 'distribuidor'])
                ->make(true);
        }
    }

    public function eliminar_soporte_especial(SoporteEspecial $soporte)
    {
        try {
            $log = new LogSoporte();
            $log->usuario = Auth::guard('tecnico')->user()->nombres;
            $log->pantalla = "Demos";
            $log->operacion = "Eliminar";
            $log->fecha = now();
            $log->detalle =  $soporte;
            $log->save();

            $soporte->delete();
            flash("Soporte eliminado correctamente")->success();
            return back();
        } catch (\Throwable $th) {
            flash("No se pudo eliminar el soporte: " . $th->getMessage())->error();
            return back();
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
            'tecnicos.nombres as tecnico',
            'tecnicos.distribuidoresid',
        )
            ->join('tecnicos', 'tecnicos.tecnicosid', 'soportes_especiales.tecnicoid')
            ->where('tecnicos.distribuidoresid', Auth::guard('tecnico')->user()->distribuidoresid)
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
    private function notificar_asignacion($idTecnico, $tipo = 0)
    {
        try {
            if (!$idTecnico) return false;

            $tecnico = Tecnicos::find($idTecnico);

            if (!$tecnico) return false;

            $tipos = [
                0 => "",
                1 => "DEMO",
                2 => "de CAPACITACIÓN",
                3 => "de LITE",
            ];

            $sms = new WhatsappController();
            return $sendMessage = $sms->enviar_personalizado([
                "numero" => $tecnico->telefono,
                "mensaje" =>  "Buen día {$tecnico->nombres} usted tiene una nueva asignación {$tipos[$tipo]}, para más detalles revise la plataforma en la sección de soportes especiales."
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }
    }

    private function notificar_nuevo_estado($soporte, $estadoNum)
    {
        try {
            $revisor = Tecnicos::where('rol', ConstantesTecnicos::ROL_REVISOR)->first();

            if (!$revisor) return false;

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
        $tecnicos = Tecnicos::select('tecnicosid', 'nombres', 'correo')
            ->when(Auth::guard('tecnico')->user()->distribuidoresid, function ($query, $distribuidor) {
                if ($distribuidor == 1) {
                    return $query->where('distribuidoresid', 1);
                } else if ($distribuidor == 2) {
                    return $query->where('distribuidoresid', '<=', 2);
                } else {
                    return $query->where('distribuidoresid', $distribuidor);
                }
            })
            ->where('rol', ConstantesTecnicos::ROL_TECNICOS)
            ->where('estado', 1)
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

    private function obtener_plan_soporte($plan)
    {
        switch ($plan) {
            case 1:
                return "WEB";
            case 2:
                return "PC";
            case 3:
                return 'FACTURITO';
        }
    }

    public function validar_soportes_anteriores(SoporteEspecial $soporte, Request $request = null)
    {
        $vendedor = null;
        $mensaje = "Este cliente ya tiene una ficha creada, para mas informacion comuniquese con el vendedor asignado";

        if ($soporte->vededorid) {
            $vendedor = User::where('usuariosid', $soporte->vededorid)->first();
        } else {
            $vendedor = Factura::where('capacitacionid', $soporte->soporteid)
                ->join('usuarios', 'usuarios.usuariosid', 'facturas.usuariosid')
                ->select('facturas.facturaid', 'usuarios.nombres', 'usuarios.correo', 'usuarios.usuariosid')
                ->orderBy('facturas.facturaid', 'desc')
                ->first();
        }

        if (!$vendedor) return $mensaje;

        if ($vendedor->usuariosid == Auth::user()->usuariosid) {
            $planActual = $request->plan ?? null;

            if ($soporte->plan != $planActual) return null;

            $mensaje = "Este cliente ya tiene una ficha creada por usted";
        } else {
            $mensaje = "Este cliente ya tiene una ficha creada, el vendedor asignado es: {$vendedor->nombres}";
            $this->notificacion_de_cruce($soporte->ruc, $vendedor);
        }

        return $mensaje;
    }

    public function notificacion_de_cruce($cliente, $vendedor)
    {
        try {

            $incorrecto = [
                'tipo' => 'incorrecto',
                'vendedor' => Auth::user()->nombres,
                'cliente' => $cliente,
                'from' => "noresponder@perseo.ec",
                'fromName' => "Perseo Tienda",
                'subject' => "Notificación de cruce de fichas",
            ];

            $correcto = [
                'tipo' => 'correcto',
                'vendedor1' => $vendedor->nombres,
                'vendedor2' => Auth::user()->nombres,
                'cliente' => $cliente,
                'from' => "noresponder@perseo.ec",
                'fromName' => "Perseo Tienda",
                'subject' => "Notificación de cruce de fichas",
            ];

            Mail::to(Auth::user()->correo)->queue(new NotificacionVendedores($incorrecto));

            Mail::to($vendedor->correo)->queue(new NotificacionVendedores($correcto));
        } catch (\Throwable $th) {
            dd($th);
        }
    }
}
