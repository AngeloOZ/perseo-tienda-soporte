<?php

namespace App\Http\Controllers;

use App\Mail\enviarfirma;
use App\Mail\FirmaTracking;
use App\Models\Ciudades;
use App\Models\Firma;
use App\Models\Provincias;
use App\Models\User;
use App\Rules\ValidarCelular;
use App\Rules\ValidarCorreo;
use App\Rules\validarFecha;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use ZipArchive;


class FirmaController extends Controller
{
    private const URL_API = "https://api.uanataca.ec";

    private function enviar_correo_tranking($firma)
    {
        try {
            $estado = "En proceso";

            $id = base64_encode($firma->firmasid);
            $array = [
                "nombres" => $firma->apellido_paterno . " " . $firma->nombres,
                "id_firma" => $id,
                "subject" => "Enlace estado de firma electrónica",
                "from" => env('MAIL_FROM_ADDRESS'),
            ];

            Mail::to($firma->correo)->queue(new FirmaTracking($array));
        } catch (\Throwable $th) {
            throw $th;
            flash($th->getMessage())->warning();
        }
    }

    public function rastrearProceso($id)
    {
        $idFirma = intval(base64_decode($id));
        $firma = Firma::findOrFail($idFirma);

        if ($firma->uanatacaid != null) {
            $vendedor = User::findOrFail($firma->usuariosid);
            if ($vendedor->uanataca_key != null || $vendedor->uanataca_uuid != null) {
                $tipo_solicitud = $firma->tipo_persona == 1 ? "PERSONA NATURAL" : "REPRESENTANTE LEGAL";
                $identificacion = "";

                if ($firma->tipo_persona == 1) {
                    $identificacion = substr($firma->identificacion, 0, 10);
                } else {
                    $identificacion = $firma->ruc_empresa;
                }

                $body = [
                    "apikey" => $vendedor->uanataca_key,
                    "uid" => $vendedor->uanataca_uuid,
                    "numerodocumento" => $identificacion,
                    "tipo_solicitud" => $tipo_solicitud,
                ];

                $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
                    ->post(self::URL_API . "/v4/consultarEstado", $body)
                    ->json();

                if ($resultado['result'] == true) {
                    $ultimaSolicitud = end($resultado["data"]["solicitudes"]);
                    if ($ultimaSolicitud["estado"] == "APROBADO") {
                        $firma->estado = 5;
                    } else if ($ultimaSolicitud["estado"] == "NUEVO") {
                        $firma->estado = 4;
                    }
                    $firma->save();
                }
            }
        }

        $estado = $firma->estado;
        return view("firma.estado", ["estado" => $estado]);
    }

    public function validarProceso($cedula)
    {

        $cedula = substr($cedula, 0, 10);
        $datas = Firma::select('firma.identificacion', 'firma.nombres', 'firma.estado', 'firma.estado_pago')->where(DB::raw('substr(firma.identificacion, 1, 10)'), $cedula)->get();
        $message = json_encode(["status" => 200, "message" => "Sin procesos activos"]);

        foreach ($datas as $data) {

            if ($data->estado < 5) {
                $message = json_encode(["status" => 400, "message" => "La cédula $cedula ya tiene un proceso activo"]);
                break;
            }
        }

        return $message;
    }

    public function recuperardatos(Request $request)
    {
        $url = "https://www.perseo.app/api/datos/datos_consulta";
        $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false, 'usuario' => 'perseo', "clave" => "Perseo1232*"])
            ->withOptions(["verify" => false])
            ->post($url, ['identificacion' => $request->identificacion])
            ->json();
        return $resultado;
    }

    public function reenviar_correo(Request $request)
    {
        $idSolicitud = $request->id_solicitud;
        $currentEmail = $request->correo;

        $firma = Firma::find($idSolicitud);

        $contadorDigitos =  strlen($firma->identificacion);
        $verificacion = 1;
        $array = [];
        $array['view'] = 'emails.firmaemail';
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['subject'] = 'Nuevo registro de Firma Electrónica';
        $array['tipopersona'] = $firma->tipo_persona == 1 ? "Persona Natural" : "Persona Jurídica";
        $array['tipoidentificacion'] =  $contadorDigitos == 10 ? "Cédula" : "RUC";
        $array['identificacion'] =  $firma->identificacion;
        $array['ruc'] =  $firma->ruc;
        $array['nombres'] =  $firma->nombres;
        $array['apellido_paterno'] =  $firma->apellido_paterno;
        $array['apellido_materno'] =  $firma->apellido_materno;
        $array['nombres'] =  $firma->nombres;
        $array['correo'] =  $firma->correo;
        $array['codigo'] =  $firma->codigo_cedula;
        $array['celular'] =  $firma->celular;
        $array['convencional'] =  $firma->convencional;
        $array['sexo'] = $firma->sexo == "h" ? "Hombre" : "Mujer";
        $array['fechanacimiento'] =  $firma->fechanacimiento;

        $provincias = Provincias::select('provincia')->where('provinciasid', str_pad($firma->provinciasid, "2", "0", STR_PAD_LEFT))->first();
        $ciudad =  Ciudades::select('ciudad')->where('ciudadesid', str_pad($firma->ciudadesid, "4", "0", STR_PAD_LEFT))->first();

        $array['provincia'] = $provincias->provincia;
        $array['ciudad'] = $ciudad->ciudad;

        $array['direccion'] = $firma->direccion;
        $array['formato'] = $firma->formato == "1" ? "Archivo .P12" : "";

        $array['vigencia'] = "1 Año";
        if ($firma->vigencia >= 2 && $firma->vigencia <= 5) {
            $array['vigencia'] = $firma->vigencia . " Años";
        } else if ($firma->vigencia == 6) {
            $array['vigencia'] = "7 Días";
        } else if ($firma->vigencia == 7) {
            $array['vigencia'] = "30 Días";
        }

        $array['fecha'] =    $firma->fecha_creacion;

        $zip = new ZipArchive;
        $fileName = 'archivo-' . $firma->firmasid . '-' . $firma->identificacion . '.zip';
        $array['archivo'] =  $fileName;
        $array_files = $this->crear_archivos($firma);

        $res = $zip->open(public_path("/assets/archivos/$fileName"), ZipArchive::CREATE);
        if ($res === TRUE) {
            foreach ($array_files as $key => $file) {
                $zip->addFile($file, $key);
            }
            $zip->close();
        }
        try {
            Mail::to($currentEmail)->queue(new enviarfirma($array));
            foreach ($array_files as $file) {
                unlink($file);
            }
            unlink(public_path("/assets/archivos/" . $fileName));
            return "enviado";
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function guardar(Request $request)
    {
        $contadorDigitos =  strlen($request->identificacion);

        $firma = new Firma();
        $vendedor = User::findOrFail($request->usuarioid);

        $firma->tipo_identificacion = $contadorDigitos == 10 ? "C" : "R";
        $firma->identificacion = $request->identificacion;
        $firma->ruc = $request->ruc_hidden;
        $firma->nombres = $request->nombres;
        $firma->apellido_paterno = $request->apellido_paterno;
        $firma->apellido_materno = $request->apellido_materno;
        $firma->codigo_cedula = $request->codigo_cedula;
        $firma->correo = $request->correo;
        $firma->celular = $request->celular;
        $firma->telefono_contacto = $request->telefono_contacto;
        $firma->fechanacimiento = date('Y-m-d', strtotime($request->fechanacimiento));
        $firma->cargo = $request->cargo;
        $firma->sexo = $request->sexo;
        $firma->ruc_empresa = $request->ruc_empresa;
        $firma->razonsocial = $request->razonsocial;
        $firma->provinciasid = $request->provinciasid;
        $firma->ciudadesid = $request->ciudadesid;
        $firma->direccion = $request->direccion;
        $firma->estado_pago = 1;
        $firma->foto =  base64_encode(file_get_contents($request->foto->getRealPath()));
        $firma->foto_cedula_anverso = base64_encode(file_get_contents($request->foto_cedula_anverso));
        $firma->foto_cedula_reverso = base64_encode(file_get_contents($request->foto_cedula_reverso));

        $firma->doc_ruc = $request->doc_ruc != "" ? base64_encode(file_get_contents($request->doc_ruc)) : "";
        $firma->doc_constitucion = $request->doc_constitucion != "" ? base64_encode(file_get_contents($request->doc_constitucion)) : "";
        $firma->doc_nombramiento = $request->doc_nombramiento != "" ? base64_encode(file_get_contents($request->doc_nombramiento)) : "";
        $firma->doc_aceptacion =  $request->doc_aceptacion !=  "" ? base64_encode(file_get_contents($request->doc_aceptacion)) : "";
        $firma->tipo_persona = $request->tipo_persona;
        $firma->vigencia = $request->vigencia;
        $firma->formato = $request->formato;
        $firma->convencional = $request->convencional;
        $firma->usuariosid =  $vendedor->usuariosid;
        $firma->distribuidoresid =  $vendedor->distribuidoresid;
        $firma->estado =  1;

        /* Accion especial solo para vendedor MATRIZ SOCIO PERSEO QUITO */
        if ($vendedor->usuariosid == 17 && $vendedor->distribuidoresid == 2) {
            $firma->estado =  3;
        }

        $firma->fecha_creacion = now();

        try {
            if ($firma->save()) {
                $verificacion = 1;
                $array['view'] = 'emails.firmaemail';
                $array['from'] = env('MAIL_FROM_ADDRESS');
                $array['subject'] = 'Nuevo registro de Firma Electrónica';
                $array['tipopersona'] = $request->tipo_persona == 1 ? "Persona Natural" : "Persona Jurídica";
                $array['tipoidentificacion'] =  $contadorDigitos == 10 ? "Cédula" : "RUC";
                $array['identificacion'] =  $request->identificacion;
                $array['ruc'] =  $request->ruc_hidden;
                $array['nombres'] =  $request->nombres;
                $array['apellido_paterno'] =  $request->apellido_paterno;
                $array['apellido_materno'] =  $request->apellido_materno;
                $array['nombres'] =  $request->nombres;
                $array['correo'] =  $request->correo;
                $array['codigo'] =  $request->codigo_cedula;
                $array['celular'] =  $request->celular;
                $array['convencional'] =  $request->convencional;
                $array['sexo'] = $request->sexo == "h" ? "Hombre" : "Mujer";
                $array['fechanacimiento'] =  $request->fechanacimiento;

                $provincias = Provincias::select('provincia')->where('provinciasid', str_pad($request->provinciasid, "2", "0", STR_PAD_LEFT))->first();
                $ciudad =  Ciudades::select('ciudad')->where('ciudadesid', str_pad($request->ciudadesid, "4", "0", STR_PAD_LEFT))->first();

                $array['provincia'] = $provincias->provincia;
                $array['ciudad'] = $ciudad->ciudad;

                $array['direccion'] = $request->direccion;
                $array['formato'] = $request->formato == "1" ? "Archivo .P12" : "";

                $array['vigencia'] = "1 Año";
                if ($request->vigencia >= 2 && $request->vigencia <= 5) {
                    $array['vigencia'] = $request->vigencia . " Años";
                } else if ($request->vigencia == 6) {
                    $array['vigencia'] = "7 Días";
                } else if ($request->vigencia == 7) {
                    $array['vigencia'] = "30 Días";
                }

                $array['fecha'] =    $firma->fecha_creacion;


                $zip = new ZipArchive;
                $fileName = 'archivo-' . $firma->firmasid . '-' . $firma->identificacion . '.zip';
                $array['archivo'] =  $fileName;

                if ($zip->open(public_path("/assets/archivos/$fileName"), ZipArchive::CREATE) === TRUE) {
                    $zip->addFile($request->foto, "foto-personal.jpg");
                    $zip->addFile($request->foto_cedula_anverso, 'foto-cedula-anverso.jpg');
                    $zip->addFile($request->foto_cedula_reverso, 'foto-cedula-reverso.jpg');

                    if ($firma->doc_ruc != "") {
                        $zip->addFile($request->doc_ruc, 'documento-ruc.pdf');
                    }
                    if ($request->tipo_persona == 2) {

                        $zip->addFile($request->doc_nombramiento, 'documento-nombramiento.pdf');
                        $zip->addFile($request->doc_constitucion, 'documento-constitucion.pdf');
                        if ($firma->doc_aceptacion != "") {
                            $zip->addFile($request->doc_aceptacion, 'documento-aceptacion.pdf');
                        }
                    }
                    $zip->close();
                }
                $usuarioCorreo = User::select('correo')->where('usuariosid', $firma->usuariosid)->first();
                try {
                    Mail::to($usuarioCorreo->correo)->queue(new enviarfirma($array));
                    unlink(public_path("/assets/archivos/" . $fileName));
                } catch (\Exception $e) {
                    Flash('Error enviando email')->error();
                }
            } else {
                $verificacion = 2;
            }
            $id_firma = base64_encode($firma->firmasid);
            return view('firma.firma', ['verificacion' => $verificacion, 'id_firma' => $id_firma]);
        } catch (\Throwable $th) {
            Flash('Error al guardar la firma: ' . $th->getMessage())->error();
            return back();
        }
    }

    public function listado(Request $request)
    {
        return view('auth.principal');
    }

    public function filtrado_listado(Request $request)
    {
        $data = Firma::select('firma.firmasid',  'firma.identificacion', 'firma.nombres', 'firma.codigo_cedula', 'firma.correo', 'firma.telefono_contacto', 'firma.numero_secuencia', 'firma.estado', 'firma.tipo_persona', 'firma.fecha_creacion')
            ->where('usuariosid', Auth::user()->usuariosid)
            ->when($request->tipo, function ($query, $tipo) {
                return $query->where("tipo_persona", $tipo);
            })
            ->when($request->fecha, function ($query, $fecha) {
                $dates = explode(" / ", $fecha);

                $date1 = strtotime($dates[0]);
                $desde = date('Y-m-d H:i:s', $date1);

                $date2 = strtotime($dates[1] . ' +1 day -1 second');
                $hasta = date('Y-m-d H:i:s', $date2);
                return $query->whereBetween("fecha_creacion", [$desde, $hasta]);
            })
            ->when($request->estado, function ($query, $estado) {
                return $query->where("estado", $estado);
            })
            ->get();
        return DataTables::of($data)
            ->editColumn('fecha_creacion', function ($fecha) {
                $date = new DateTime($fecha->fecha_creacion);
                return $date->format('d-m-Y');
            })
            ->editColumn('tipo_persona', function ($tipo) {
                if ($tipo->tipo_persona == 1) {
                    return "Natural";
                } else {
                    return "Juridica";
                }
            })
            ->editColumn('estado', function ($estado) {

                if ($estado->estado == 1) {
                    return '<a class="bg-danger text-white rounded p-1">Recibido</a>';
                } elseif ($estado->estado == 2) {
                    return '<a class="bg-warning text-white rounded p-1">Revisado</a>';
                } elseif ($estado->estado == 3) {
                    return '<a class="bg-primary text-white rounded p-1">En proceso</a>';
                } elseif ($estado->estado == 4) {
                    return '<a class="bg-info text-white rounded p-1">Finalizado</a>';
                } elseif ($estado->estado == 5) {
                    return '<a class="bg-success text-white rounded p-1">Entregado al correo</a>';
                } elseif ($estado->estado == 6) {
                    return '<a class="bg-secondary text-dark rounded p-1">Anulado</a>';
                }
            })
            ->editColumn('action', function ($firma) {
                return '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('firma.editar', $firma->firmasid) . '"  title="Editar"> <i class="la la-edit"></i> </a>' .
                    '<a class="btn btn-sm btn-light btn-icon btn-hover-danger confirm-delete" href="javascript:void(0)" data-href="' . route('firma.eliminar', $firma->firmasid) . '" title="Eliminar"> <i class="la la-trash"></i> </a>' . '<a class="btn btn-icon btn-light btn-hover-info btn-sm ml-2" href="' . route('firma.estadosolicitud', base64_encode($firma->firmasid)) . '" target="_blank"  title="Enlace tracking"> <i class="la la-external-link-alt"></i> </a>';
            })

            ->rawColumns(['action', 'codigo', 'estado', 'fecha_creacion'])
            ->make(true);
    }

    public function editar(Firma $firma)
    {
        $estadoSolicitud = null;
        if ($firma->uanatacaid != null) {
            $vendedor = User::findOrFail($firma->usuariosid);
            if ($vendedor->uanataca_key != null || $vendedor->uanataca_uuid != null) {
                $tipo_solicitud = $firma->tipo_persona == 1 ? "PERSONA NATURAL" : "REPRESENTANTE LEGAL";
                $identificacion = "";

                if ($firma->tipo_persona == 1) {
                    $identificacion = substr($firma->identificacion, 0, 10);
                } else {
                    $identificacion = $firma->ruc_empresa;
                }

                $body = [
                    "apikey" => $vendedor->uanataca_key,
                    "uid" => $vendedor->uanataca_uuid,
                    "numerodocumento" => $identificacion,
                    "tipo_solicitud" => $tipo_solicitud,
                ];

                $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
                    ->post(self::URL_API . "/v4/consultarEstado", $body)
                    ->json();

                if ($resultado['result'] == true) {
                    $ultimaSolicitud = end($resultado["data"]["solicitudes"]);
                    $estadoSolicitud = $ultimaSolicitud;

                    if ($ultimaSolicitud["estado"] == "APROBADO") {
                        $firma->estado = 5;
                    } else if ($ultimaSolicitud["estado"] == "NUEVO") {
                        $firma->estado = 4;
                    } else if (str_contains($ultimaSolicitud["estado"], "EMITIDO")) {
                        $firma->estado = 5;
                    }
                    $firma->save();
                } else {
                    Flash($resultado['responce'])->error();
                }
            }
        }
        $firma->fechanacimiento = date("d-m-Y", strtotime($firma->fechanacimiento));
        return view('auth.editar', ["firma" => $firma, "estado_solicitud" => $estadoSolicitud]);
    }

    public function actualizar(Firma $firma, Request $request)
    {
        if ($request->tipo_persona == 1) {
            $request->validate(
                [
                    'identificacion' => 'required',
                    'codigo_cedula' => 'required',
                    'fechanacimiento' => ['required', new validarFecha],
                    'celular' => ['required', new ValidarCelular],
                    'telefono_contacto' => ['required', new ValidarCelular],
                    'correo' => ['required', new ValidarCorreo],
                    'nombres' => 'required',
                    'apellido_paterno' => 'required',
                    'numero_secuencia' => 'required',
                    'provinciasid' => 'required',
                    'ciudadesid' => 'required',
                    'direccion' => 'required',
                ],
                [
                    'identificacion.required' => 'Ingrese la identificación',
                    'codigo_cedula.required' => 'Ingrese el código de la cedula',
                    'fechanacimiento.required' => 'Ingrese la fecha de nacimiento',
                    'celular.required' => 'Ingrese el número de celular',
                    'telefono_contacto.required' => 'Ingrese el número de celular',
                    'correo.required' => 'Ingrese el correo electrónico',
                    'nombres.required' => 'Ingrese los nombres',
                    'apellido_paterno.required' => 'Ingrese el apellido paterno',
                    'numero_secuencia.required' => 'Ingrese una secuencia de factura',
                    'provinciasid.required' => 'Escoja una provincia',
                    'ciudadesid.required' => 'Escoja una ciudad',
                    'direccion.required' => 'Ingrese la dirección',
                ],
            );
        } else {

            $request->validate(
                [
                    'identificacion' => 'required',
                    'codigo_cedula' => 'required',
                    'fechanacimiento' => ['required', new validarFecha],
                    'celular' => ['required', new ValidarCelular],
                    'correo' => ['required', new ValidarCorreo],
                    'nombres' => 'required',
                    'apellido_paterno' => 'required',
                    'numero_secuencia' => 'required',
                    'provinciasid' => 'required',
                    'ciudadesid' => 'required',
                    'direccion' => 'required',
                    'cargo' => 'required',
                    'ruc_empresa' => 'required',
                    'razonsocial' => 'required',
                ],
                [
                    'identificacion.required' => 'Ingrese la identificación',
                    'codigo_cedula.required' => 'Ingrese el código de la cedula',
                    'fechanacimiento.required' => 'Ingrese la fecha de nacimiento',
                    'celular.required' => 'Ingrese el número de celular',
                    'correo.required' => 'Ingrese el correo electrónico',
                    'nombres.required' => 'Ingrese los nombres',
                    'apellido_paterno.required' => 'Ingrese el apellido paterno',
                    'numero_secuencia.required' => 'Ingrese una secuencia de factura',
                    'provinciasid.required' => 'Escoja una provincia',
                    'ciudadesid.required' => 'Escoja una ciudad',
                    'direccion.required' => 'Ingrese la dirección',
                    'cargo.required' => 'Ingrese el cargo',
                    'ruc_empresa.required' => 'Ingrese el RUC de la empresa',
                    'razonsocial.required' => 'Ingrese la razón social',
                ],
            );
        }

        $request['fechanacimiento'] = date("Y-m-d", strtotime($request['fechanacimiento']));
        $request['fecha_modificacion'] = now();

        $nuevosDatos = $request->all();

        if ($request['foto'] != NULL) {
            $nuevosDatos['foto'] = base64_encode(file_get_contents($request->all()['foto']->getRealPath()));
        }

        if ($request['foto_cedula_anverso'] != NULL) {
            $nuevosDatos['foto_cedula_anverso'] = base64_encode(file_get_contents($request['foto_cedula_anverso']));
        }

        if ($request['foto_cedula_reverso'] != NULL) {
            $nuevosDatos['foto_cedula_reverso'] = base64_encode(file_get_contents($request['foto_cedula_reverso']));
        }

        $firma->update($nuevosDatos);

        if ($request->estado == 3) {
            $this->enviar_correo_tranking($firma);
        }

        flash('Actualizado correctamente')->success();
        return back();
    }

    public function eliminar(Firma $firma)
    {

        if ($firma->estado == 6) {
            $firma->delete();
            flash('Eliminado correctamente')->success();
        } else {
            flash('No se puede eliminar porque el estado no es anulado')->warning();
        }
        return back();
    }

    public function descarga($firma, $tipo)
    {
        if ($tipo == 1) {
            $firmaBuscar = Firma::select('foto_cedula_anverso')->where('firmasid', $firma)->first();
            $contenido   = base64_decode($firmaBuscar->foto_cedula_anverso);
            $nombreDescarga = "imagen.png";
        } elseif ($tipo == 2) {
            $firmaBuscar = Firma::select('foto_cedula_reverso')->where('firmasid', $firma)->first();
            $contenido   = base64_decode($firmaBuscar->foto_cedula_reverso);
            $nombreDescarga = "imagen.png";
        } elseif ($tipo == 3) {
            $firmaBuscar = Firma::select('foto')->where('firmasid', $firma)->first();
            $contenido   = base64_decode($firmaBuscar->foto);
            $nombreDescarga = "imagen.png";
        } elseif ($tipo == 4) {
            $firmaBuscar = Firma::select('doc_ruc')->where('firmasid', $firma)->first();
            $contenido   = base64_decode($firmaBuscar->doc_ruc);
            $nombreDescarga = "archivo.pdf";
        } elseif ($tipo == 5) {
            $firmaBuscar = Firma::select('doc_constitucion')->where('firmasid', $firma)->first();
            $contenido   = base64_decode($firmaBuscar->doc_constitucion);
            $nombreDescarga = "archivo.pdf";
        } elseif ($tipo == 6) {
            $firmaBuscar = Firma::select('doc_nombramiento')->where('firmasid', $firma)->first();
            $contenido   = base64_decode($firmaBuscar->doc_nombramiento);
            $nombreDescarga = "archivo.pdf";
        } elseif ($tipo == 7) {
            $firmaBuscar = Firma::select('doc_aceptacion')->where('firmasid', $firma)->first();
            $contenido   = base64_decode($firmaBuscar->doc_aceptacion);
            $nombreDescarga = "archivo.pdf";
        }


        file_put_contents(public_path("/assets/archivos/" . $nombreDescarga), $contenido);
        return response()->download(public_path("/assets/archivos/" . $nombreDescarga))->deleteFileAfterSend(true);
    }

    public function visualizar_imagen($firma, $tipo)
    {
        $imagen = "";
        if ($tipo == 1) {
            $firmaBuscar = Firma::select('foto_cedula_anverso')->where('firmasid', $firma)->first();
            $imagen   = base64_decode($firmaBuscar->foto_cedula_anverso);
        } elseif ($tipo == 2) {
            $firmaBuscar = Firma::select('foto_cedula_reverso')->where('firmasid', $firma)->first();
            $imagen   = base64_decode($firmaBuscar->foto_cedula_reverso);
        } elseif ($tipo == 3) {
            $firmaBuscar = Firma::select('foto')->where('firmasid', $firma)->first();
            $imagen   = base64_decode($firmaBuscar->foto);
        }

        return response($imagen)->header('Content-type', 'image/png');
    }

    private function crear_archivos($firma)
    {
        $path = public_path('/assets/temp');
        $array_files = [];
        if (!file_exists($path)) {
            if (!mkdir($path)) {
                throw new Exception("Error al crear el directorio");
            }
        }
        $array_files["foto.png"] = $this->decodificar_archivos($firma->foto, "foto.png");
        $array_files["foto_cedula_anverso.png"] = $this->decodificar_archivos($firma->foto_cedula_anverso, "foto_cedula_anverso.png");
        $array_files["foto_cedula_reverso.png"] = $this->decodificar_archivos($firma->foto_cedula_reverso, "foto_cedula_reverso.png");

        if ($firma->doc_ruc != "") {
            $array_files["documento_ruc.pdf"] = $this->decodificar_archivos($firma->doc_ruc, "documento_ruc.pdf");
        }

        if ($firma->tipo_persona == 2) {
            $array_files["documento_nombramiento.pdf"] = $this->decodificar_archivos($firma->doc_nombramiento, 'documento_nombramiento.pdf');
            $array_files["documento_constitucion.pdf"] = $this->decodificar_archivos($firma->doc_constitucion, 'documento_constitucion.pdf');
            if ($firma->doc_aceptacion != "") {
                $array_files["documento_aceptacion.pdf"] = $this->decodificar_archivos($firma->doc_aceptacion, 'documento_aceptacion.pdf');
            }
        }
        return $array_files;
    }

    private function decodificar_archivos($archivoBase64, $nombre)
    {
        $path = public_path("/assets/temp/" . $nombre);
        $contenido   = base64_decode($archivoBase64);
        file_put_contents($path, $contenido);
        return $path;
    }

    /* -------------------------------------------------------------------------- */
    /*                        Funciones para usuario rol 4                        */
    /* -------------------------------------------------------------------------- */
    public function listado_revisor(Request $request)
    {
        return view('auth2.revisor_firmas.index');
    }

    public function filtrado_listado_revisor(Request $request)
    {
        if ($request->ajax()) {
            $data = Firma::select('firma.firmasid',  'firma.identificacion', 'firma.nombres', 'firma.codigo_cedula', 'firma.correo', 'firma.celular', 'firma.estado', 'firma.tipo_persona', 'firma.fecha_creacion')
                ->where('distribuidoresid', '=', Auth::user()->distribuidoresid)
                ->where('estado', 3)->orWhere('estado', 4)
                ->when($request->fecha, function ($query, $fecha) {
                    $dates = explode(" / ", $fecha);

                    $date1 = strtotime($dates[0]);
                    $desde = date('Y-m-d H:i:s', $date1);

                    $date2 = strtotime($dates[1] . ' +1 day -1 second');
                    $hasta = date('Y-m-d H:i:s', $date2);
                    return $query->whereBetween("fecha_creacion", [$desde, $hasta]);
                })
                ->when($request->estado, function ($query, $estado) {
                    return $query->where("estado", $estado);
                })
                ->when($request->vendedores, function ($query, $vendedores) {
                    return $query->where("usuariosid", $vendedores);
                })
                ->get();

            return DataTables::of($data)
                ->editColumn('fecha_creacion', function ($fecha) {
                    $date = new DateTime($fecha->fecha_creacion);
                    return $date->format('d-m-Y');
                })
                ->editColumn('tipo_persona', function ($tipo) {
                    if ($tipo->tipo_persona == 1) {
                        return "Natural";
                    } else {
                        return "Juridica";
                    }
                })
                ->editColumn('estado', function ($estado) {

                    if ($estado->estado == 1) {
                        return '<a class="bg-danger text-white rounded p-1">Recibido</a>';
                    } elseif ($estado->estado == 2) {
                        return '<a class="bg-warning text-white rounded p-1">Revisado</a>';
                    } elseif ($estado->estado == 3) {
                        return '<a class="bg-primary text-white rounded p-1">En proceso</a>';
                    } elseif ($estado->estado == 4) {
                        return '<a class="bg-info text-white rounded p-1">Finalizado</a>';
                    } elseif ($estado->estado == 5) {
                        return '<a class="bg-success text-white rounded p-1">Entregado al correo</a>';
                    } elseif ($estado->estado == 6) {
                        return '<a class="bg-secondary text-dark rounded p-1">Anulado</a>';
                    }
                })
                ->editColumn('action', function ($firma) {
                    return '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('firma.revisor_editar', $firma->firmasid) . '"  title="Editar"> <i class="la la-edit"></i> </a>' . '<a class="btn btn-icon btn-light btn-hover-info btn-sm ml-2" href="' . route('firma.estadosolicitud', base64_encode($firma->firmasid)) . '" target="_blank"  title="Enlace tracking"> <i class="la la-external-link-alt"></i> </a>';
                })

                ->rawColumns(['action', 'codigo', 'estado', 'fecha_creacion'])
                ->make(true);
        }
    }

    public function listado_revisor_enviadas_correo(Request $request)
    {
        return view('auth2.revisor_firmas.enviadas_corre');
    }

    public function filtrado_listado_revisor_enviadas_correo(Request $request)
    {
        if ($request->ajax()) {
            $data = Firma::select('firma.firmasid',  'firma.identificacion', 'firma.nombres', 'firma.codigo_cedula', 'firma.correo', 'firma.celular', 'firma.estado', 'firma.tipo_persona', 'firma.fecha_creacion')
                ->when($request->fecha, function ($query, $fecha) {
                    $dates = explode(" / ", $fecha);

                    $date1 = strtotime($dates[0]);
                    $desde = date('Y-m-d H:i:s', $date1);

                    $date2 = strtotime($dates[1] . ' +1 day -1 second');
                    $hasta = date('Y-m-d H:i:s', $date2);
                    return $query->whereBetween("fecha_creacion", [$desde, $hasta]);
                })
                ->when($request->vendedores, function ($query, $vendedores) {
                    return $query->where("usuariosid", $vendedores);
                })
                ->where('distribuidoresid', Auth::user()->distribuidoresid)
                ->where('estado', 5)
                ->get();

            return DataTables::of($data)
                ->editColumn('fecha_creacion', function ($fecha) {
                    $date = new DateTime($fecha->fecha_creacion);
                    return $date->format('d-m-Y');
                })
                ->editColumn('tipo_persona', function ($tipo) {
                    if ($tipo->tipo_persona == 1) {
                        return "Natural";
                    } else {
                        return "Juridica";
                    }
                })
                ->editColumn('estado', function ($estado) {

                    if ($estado->estado == 1) {
                        return '<a class="bg-danger text-white rounded p-1">Recibido</a>';
                    } elseif ($estado->estado == 2) {
                        return '<a class="bg-warning text-white rounded p-1">Revisado</a>';
                    } elseif ($estado->estado == 3) {
                        return '<a class="bg-primary text-white rounded p-1">En proceso</a>';
                    } elseif ($estado->estado == 4) {
                        return '<a class="bg-info text-white rounded p-1">Finalizado</a>';
                    } elseif ($estado->estado == 5) {
                        return '<a class="bg-success text-white rounded p-1">Entregado al correo</a>';
                    } elseif ($estado->estado == 6) {
                        return '<a class="bg-secondary text-dark rounded p-1">Anulado</a>';
                    }
                })
                ->editColumn('action', function ($firma) {
                    return '<a class="btn btn-icon btn-light btn-hover-success btn-sm mr-2" href="' . route('firma.revisor_editar', $firma->firmasid) . '"  title="Editar"> <i class="la la-edit"></i> </a>' . '<a class="btn btn-icon btn-light btn-hover-info btn-sm ml-2" href="' . route('firma.estadosolicitud', base64_encode($firma->firmasid)) . '" target="_blank"  title="Enlace tracking"> <i class="la la-external-link-alt"></i> </a>';
                })

                ->rawColumns(['action', 'codigo', 'estado', 'fecha_creacion'])
                ->make(true);
        }
    }

    public function editar_revisor(Firma $firma)
    {
        $estadoSolicitud = null;
        $vendedor = User::findOrFail($firma->usuariosid);

        if ($firma->uanatacaid != null) {
            if ($vendedor->uanataca_key != null || $vendedor->uanataca_uuid != null) {
                $tipo_solicitud = $firma->tipo_persona == 1 ? "PERSONA NATURAL" : "REPRESENTANTE LEGAL";
                $identificacion = "";

                if ($firma->tipo_persona == 1) {
                    $identificacion = substr($firma->identificacion, 0, 10);
                } else {
                    $identificacion = $firma->ruc_empresa;
                }

                $body = [
                    "apikey" => $vendedor->uanataca_key,
                    "uid" => $vendedor->uanataca_uuid,
                    "numerodocumento" => $identificacion,
                    "tipo_solicitud" => $tipo_solicitud,
                ];

                $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
                    ->post(self::URL_API . "/v4/consultarEstado", $body)
                    ->json();

                if ($resultado['result'] == true) {
                    $ultimaSolicitud = end($resultado["data"]["solicitudes"]);
                    $estadoSolicitud = $ultimaSolicitud;

                    if ($ultimaSolicitud["estado"] == "APROBADO") {
                        $firma->estado = 5;
                    } else if ($ultimaSolicitud["estado"] == "NUEVO") {
                        $firma->estado = 4;
                    } else if (str_contains($ultimaSolicitud["estado"], "EMITIDO")) {
                        $firma->estado = 5;
                    }
                    $firma->save();
                } else {
                    Flash($resultado['responce'])->error();
                }
            }
        }
        $firma->fechanacimiento = date("d-m-Y", strtotime($firma->fechanacimiento));
        return view('auth2.revisor_firmas.editar', ["firma" => $firma, "estado_solicitud" => $estadoSolicitud, "vendedor" => $vendedor]);
    }

    /* -------------------------------------------------------------------------- */
    /*                   funciones para integracion con Uanataca                  */
    /* -------------------------------------------------------------------------- */

    public function registrar_solicitud($firma_id)
    {
        try {
            $firma = Firma::findOrFail($firma_id);
            $vendedor = User::findOrFail($firma->usuariosid);

            if (Auth::user()->uanataca_key == null || Auth::user()->uanataca_uuid == null) {
                flash("Ud, no está autorizado para esta accion")->warning();
                return back();
            }

            $solicitud = [];

            if ($firma->tipo_persona == 1) {
                $solicitud = $this->crearArrayPersonaNatural($firma, $vendedor);
            } else if ($firma->tipo_persona == 2) {
                $solicitud = $this->crearArrayPersonaJuridica($firma, $vendedor);
            }

            if (count($solicitud) >= 5) {
                $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
                    ->post(self::URL_API . "/v4/solicitud", $solicitud)
                    ->json();

                if ($resultado['result']) {
                    Firma::where('firmasid', $firma->firmasid)->update(["uanatacaid" => $resultado["token"]]);
                    flash($resultado["message"])->success();
                } else {
                    if ($resultado["message"] == "Error, esta persona tiene una solicitud similar en proceso.") {
                        Firma::where('firmasid', $firma->firmasid)->update(["uanatacaid" => uniqid("2D")]);
                    }
                    flash($resultado['message'])->warning();
                }
                return back();
            } else {
                throw new Exception("Hubo un error al crear la solicitud");
            }
            return back();
        } catch (\Throwable $th) {
            flash($th->getMessage() . ",\ncontacte con soporte")->error();
            return back();
        }
    }

    function crearArrayPersonaNatural($firma, $vendedor)
    {
        $solicitudNatural = [
            "apikey" => Auth::user()->uanataca_key,
            "uid" => Auth::user()->uanataca_uuid,
            "tipo_solicitud" => "1",
            "contenedor" => "0",
            "nombres" => $firma->nombres,
            "apellido1" => $firma->apellido_paterno,
            "apellido2" => $firma->apellido_materno,
            "tipodocumento" => "CEDULA",
            "numerodocumento" => substr($firma->identificacion, 0, 10),
            "coddactilar" => $firma->codigo_cedula,
            "ruc_personal" => "",
            "sexo" => ($firma->sexo) == 'h' ? "HOMBRE" : "MUJER",
            "fecha_nacimiento" => $this->obtenerFechaFormateada($firma->fechanacimiento),
            "nacionalidad" => "ECUATORIANA",
            "telfCelular" =>  $firma->celular,
            "telfFijo" => "",
            "eMail" =>  $firma->correo,
            "provincia" => $this->obtenerProvincia($firma->provinciasid),
            "ciudad" => $this->obtenerCiudad($firma->ciudadesid),
            "direccion" => $firma->direccion,
            "vigenciafirma" => $this->obtenerVigenciaTexto($firma->vigencia),
            "f_cedulaFront" => $firma->foto_cedula_anverso,
            "f_cedulaBack" => $firma->foto_cedula_reverso,
            "f_selfie" => $firma->foto,
            "f_copiaruc" => "",
        ];
        if ($firma->apellido_materno) {
            $solicitudNatural["apellido2"] = $firma->apellido_materno;
        }
        if ($firma->ruc) {
            $solicitudNatural["ruc_personal"] = $firma->ruc;
        }
        if ($firma->convencional) {
            $solicitudNatural["telfFijo"] = $firma->convencional;
        }
        if ($firma->doc_ruc) {
            $solicitudNatural["f_copiaruc"] = $firma->doc_ruc;
        }

        return $solicitudNatural;
    }

    function crearArrayPersonaJuridica($firma, $vendedor)
    {
        $solicitudJuridica = [
            "apikey" => Auth::user()->uanataca_key,
            "uid" => Auth::user()->uanataca_uuid,
            "tipo_solicitud" => "2",
            "contenedor" => "0",
            "nombres" => $firma->nombres,
            "apellido1" => $firma->apellido_paterno,
            "apellido2" => "",
            "tipodocumento" => "CEDULA",
            "numerodocumento" => substr($firma->identificacion, 0, 10),
            "coddactilar" => $firma->codigo_cedula,
            "sexo" => ($firma->sexo) == 'h' ? "HOMBRE" : "MUJER",
            "fecha_nacimiento" => $this->obtenerFechaFormateada($firma->fechanacimiento),
            "nacionalidad" => "ECUATORIANA",
            "telfCelular" =>  $firma->celular,
            "telfFijo" => "",
            "eMail" =>  $firma->correo,
            "empresa" => $firma->razonsocial,
            "ruc_empresa" => $firma->ruc_empresa,
            "cargo" => $firma->cargo,
            "provincia" => $this->obtenerProvincia($firma->provinciasid),
            "ciudad" => $this->obtenerCiudad($firma->ciudadesid),
            "direccion" => $firma->direccion,
            "vigenciafirma" => $this->obtenerVigenciaTexto($firma->vigencia),
            "f_cedulaFront" => $firma->foto_cedula_anverso,
            "f_cedulaBack" => $firma->foto_cedula_reverso,
            "f_selfie" => $firma->foto,
            "f_copiaruc" => $firma->doc_ruc,
            "f_nombramiento" => $firma->doc_nombramiento,
            "f_nombramiento2" => $firma->doc_aceptacion,
            "f_constitucion" => $firma->doc_constitucion,
        ];
        if ($firma->apellido_materno) {
            $solicitudJuridica["apellido2"] = $firma->apellido_materno;
        }
        if ($firma->convencional) {
            $solicitudJuridica["telfFijo"] = $firma->convencional;
        }

        return $solicitudJuridica;
    }

    private function obtenerFechaFormateada($fecha)
    {
        $date = new DateTime($fecha);
        return date_format($date, 'Y/m/d');
    }

    private function obtenerVigenciaTexto($vigencia)
    {
        switch ($vigencia) {
            case 1:
                return "1 año";
                break;
            case 2:
                return "2 años";
                break;
            case 3:
                return "3 años";
                break;
            case 4:
                return "4 años";
                break;
            case 5:
                return "5 años";
                break;
            case 6:
                return "7 días";
                break;
            case 7:
                return "30 días";
                break;
        }
    }

    private function obtenerProvincia($idProvincia)
    {
        $provincia = Provincias::select('provincia')->where('provinciasid', str_pad($idProvincia, "2", "0", STR_PAD_LEFT))->first();
        return $provincia["provincia"];
    }

    private function obtenerCiudad($idCiudad)
    {
        $ciudad =  Ciudades::select('ciudad')->where('ciudadesid', str_pad($idCiudad, "4", "0", STR_PAD_LEFT))->first();
        return $ciudad["ciudad"];
    }

    /* -------------------------------------------------------------------------- */
    /*                  Funcion para consular y actualizar estado                 */
    /* -------------------------------------------------------------------------- */

    public static function consultarEstado($firma)
    {
        $vendedor = User::findOrFail($firma->usuariosid);

        if ($vendedor->uanataca_key == null || $vendedor->uanataca_uuid == null) {
            return false;
        }

        $tipo_solicitud = $firma->tipo_persona == 1 ? "PERSONA NATURAL" : "REPRESENTANTE LEGAL";
        $identificacion = "";

        if ($firma->tipo_persona == 1) {
            $identificacion = substr($firma->identificacion, 0, 10);
        } else {
            $identificacion = $firma->ruc_empresa;
        }

        $body = [
            "apikey" => $vendedor->uanataca_key,
            "uid" => $vendedor->uanataca_uuid,
            "numerodocumento" => $identificacion,
            "tipo_solicitud" => $tipo_solicitud,
        ];

        $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'verify' => false,])
            ->withOptions(["verify" => false])
            ->post(self::URL_API . "/v4/consultarEstado", $body)
            ->json();

        if ($resultado['result'] != true) {
            return false;
        }

        $ultimaSolicitud = end($resultado["data"]["solicitudes"]);

        if ($ultimaSolicitud["estado"] == "NUEVO") {
            Firma::updated(["estado" => 4]);
        } else if ($ultimaSolicitud["estado"] == "APROBADO") {
            Firma::updated(["estado" => 5]);
        } else if (str_contains($ultimaSolicitud["estado"], "EMITIDO")) {
            Firma::updated(["estado" => 5]);
        }

        return true;
    }
}
