<?php

namespace App\Http\Controllers;

use App\Models\Cotizaciones;
use App\Models\CotizacionesDetalle;
use App\Models\Log;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use Yajra\DataTables\DataTables as DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use NumberFormatter;

class cotizarController extends Controller
{
    /* -------------------------------------------------------------------------- */
    /*                          Funciones para plantillas                         */
    /* -------------------------------------------------------------------------- */
    public function crearPlantilla()
    {
        return view('soporte.admin.capacitaciones.cotizar.plantilla1.index');
    }

    public function guardarPlantilla(Request $request)
    {
        if ($request->botonDescargaCrear != "guardar") {
            flash('Ocurrió un error, vuelva a intentarlo')->error();
            return back();
        }

        $request->validate(
            [
                'fecha' => ['required'],
                'tipo_plantilla' => ['required'],
                'forma_pagoid' => ['required'],
                'arrayDetalles' => ['required'],
                'identificacion_cliente' => ['required'],
                'nombre_cliente' => ['required'],
            ],
            [
                'fecha.required' => 'Ingrese la fecha',
                'tipo_plantilla.required' => 'Seleccione el tipo de plantilla',
                'forma_pagoid.required' => 'Seleccione la forma de pago',
                'arrayDetalles.required' => 'Debe ingresar al menos un detalle',
                'identificacion_cliente.required' => 'Ingrese la identificación del cliente',
                'nombre_cliente.required' => 'Ingrese el nombre del cliente',
            ],
        );

        DB::beginTransaction();
        try {
            $cotizaciones = new Cotizaciones();
            $cotizaciones->fecha =  date('Y-m-d', strtotime($request->fecha));
            $cotizaciones->identificacion_cliente = $request->identificacion_cliente;
            $cotizaciones->nombre_cliente = $request->nombre_cliente;
            $cotizaciones->plantillasid = $request->tipo_plantilla;
            $cotizaciones->detalle_pago = $request->forma_pagoid;
            $cotizaciones->usuariocreacion = $this->obtenerDatosUsuarioLoggeado()->nombres;
            $cotizaciones->asesorid = $this->obtenerDatosUsuarioLoggeado()->id;
            $cotizaciones->fechacreacion = now();

            $array = $request->arrayDetalles;

            $totalprecio = 0;
            $descuentototal = 0;
            $totalneto = 0;

            for ($i = 0; $i < count($array); $i++) {
                $convertirArray =  explode(",", $array[$i]);
                $data[$i] = [
                    'detalle' => $convertirArray[0],
                    'cantidad' => $convertirArray[1],
                    'descuento' => $convertirArray[2],
                ];
                $consultaDetalle = CotizacionesDetalle::select('detalle', 'precio')->where('detallesid', $convertirArray[0])->first();
                $precioFinal = $consultaDetalle->precio * $convertirArray[1];
                $descuentoFinal = ($precioFinal * $convertirArray[2]) / 100;
                $valorneto = $precioFinal  - $descuentoFinal;
                $totalprecio = floatval($precioFinal) + $totalprecio;
                $descuentototal = floatval($descuentoFinal) + $descuentototal;
                $totalneto = floatval($valorneto) + $totalneto;
            }

            $cotizaciones->detalle_cotizacion = json_encode($data);
            $iva = ($totalneto * 12) / 100;
            $totaliva = $totalneto + $iva;

            $cotizaciones->subtotal = number_format($totalneto, 2, '.', '');
            $cotizaciones->iva = number_format($iva, 2, '.', '');
            $cotizaciones->total = number_format($totaliva, 2, '.', '');

            $cotizaciones->save();
            $historial = new Log();
            $historial->usuario = $this->obtenerDatosUsuarioLoggeado()->nombres;
            $historial->pantalla = "Cotizar";
            $historial->operacion = "Crear";
            $historial->fecha = now();
            $historial->detalle = $cotizaciones;
            $historial->save();
            DB::commit();
            flash('Guardado Correctamente')->success();
            return redirect()->route('cotizarPlantilla.editar', $cotizaciones->cotizacionesid);
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
            return back();
        }
    }

    public function editarCotizaciones($cotizaciones)
    {
        $cotizaciones = Cotizaciones::where('cotizacionesid', $cotizaciones)->first();

        $cotizaciones->fecha = date("d-m-Y", strtotime($cotizaciones->fecha));
        return view('soporte.admin.capacitaciones.cotizar.plantilla1.indexEditar', compact('cotizaciones'));
    }

    public function actualizarCotizaciones(Request $request, $cotizaciones)
    {
        if (str_contains($request->botonDescargaCrear, "descargar")) {
            $totalprecio = 0;
            $descuentototal = 0;
            $totalneto = 0;
            $formatotalp = 0;
            $totalformap = 0;
            $formasPago = [];
            $valorEstaciones = 0;
            $valorMoviles = 0;
            $cadBuscar = array("á", "Á", "é", "É", "í", "Í", "ó", "Ó", "ú", "Ú");
            $cadPoner = array("a", "A", "e", "E", "i", "I", "o", "O", "u", "U");

            $fecha = new \Carbon\Carbon($request->fecha);
            $fechaP =   $fecha->locale('es')->translatedFormat(' d \d\e F \d\e\l Y');
            $array = $request->arrayDetalles;
            $arrayForma = $request->forma_pagoid;

            for ($i = 0; $i < count($array); $i++) {
                $convertirArray =  explode(",", $array[$i]);
                $data[$i] = [
                    'detalle' => $convertirArray[0],
                    'cantidad' => $convertirArray[1],
                    'descuento' => $convertirArray[2],
                ];

                $consultaDetalle = CotizacionesDetalle::select('detalle', 'precio')->where('detallesid', $convertirArray[0])->first();
                $precioFinal = $consultaDetalle->precio * $convertirArray[1];
                $descuentoFinal = ($precioFinal * $convertirArray[2]) / 100;
                $valorneto = $precioFinal  - $descuentoFinal;
                $detalles[$i] = ['detalle' => $consultaDetalle->detalle, 'cantidad' => $convertirArray[1], 'precio' =>  number_format($precioFinal, 2, '.', ''), 'porcentaje' => number_format($convertirArray[2], 2, '.', ''), 'descuento' =>  number_format($descuentoFinal, 2, '.', ''), 'neto' =>  number_format($valorneto, 2, '.', '')];
                $totalprecio = floatval($precioFinal) + $totalprecio;
                $descuentototal = floatval($descuentoFinal) + $descuentototal;
                $totalneto = floatval($valorneto) + $totalneto;

                similar_text(trim(strtoupper($consultaDetalle->detalle)), 'ESTACIONES DE TRABAJO', $porcentajeEstaciones);
                $cadenaAplicaciones = str_replace($cadBuscar, $cadPoner, $consultaDetalle->detalle);

                similar_text(trim(strtoupper($cadenaAplicaciones)), 'APLICACION MOVIL', $porcentajeAplicaciones);

                if ($porcentajeEstaciones > 92) {
                    $valorEstaciones = $convertirArray[1] * 20;
                }
                if ($porcentajeAplicaciones > 92) {
                    $valorMoviles = $convertirArray[1] * 30;
                }
            }
            $valor_mantenimiento =  $valorEstaciones + $valorMoviles;
            if ($valor_mantenimiento < 150) {
                $valor_mantenimiento = 150;
            }
            $iva = ($totalneto * 12) / 100;
            $totaliva = $totalneto + $iva;
            if ($arrayForma > 0) {
                $cuotas = $totaliva / $arrayForma;
                $porcentaje = 100 / $arrayForma;
            }

            for ($i = 0; $i < $arrayForma; $i++) {
                $formasPago[$i] = ['formapago' => 'Pago ' . ($i + 1), 'porcentajeforma' =>  number_format($porcentaje, 2, '.', ''), 'totalforma' =>  number_format($cuotas, 2, '.', '')];
                $formatotalp = floatval($porcentaje) + $formatotalp;
                $totalformap = floatval($cuotas) + $totalformap;
            }

            $porcentajeTarjetaContado = ($totaliva * 10) / 100;
            $nombre_cliente = str_replace(" ", "_", strtolower($request->nombre_cliente));


            $pathTemplate = $this->obtenerPlantillaDistribuidor();

            switch ($request->tipo_plantilla) {
                case 1:
                    $pathTemplate .= 'pcContable.docx';
                    $fileName = 'Perseo_pc_contable_';
                    break;
                case 2:
                    $pathTemplate .= 'pcPractico.docx';
                    $fileName = 'Perseo_pc_practico_';
                    $valor_mantenimiento = 80;
                    break;
                case 3:
                    $pathTemplate .= 'pcControl.docx';
                    $fileName = 'Perseo_pc_control_';
                    break;
                case 4:
                    $pathTemplate .= 'webComercial.docx';
                    $fileName = 'Perseo_web_comercial_';
                    break;
                case 5:
                    $pathTemplate .= 'webFacturacion.docx';
                    $fileName = 'Perseo_web_facturacion_';
                    break;
                case 6:
                    $pathTemplate .= 'webServicios.docx';
                    $fileName = 'Perseo_web_servicios_';
                    break;
            }

            if (!file_exists($pathTemplate)) {
                flash('No se encontró la plantilla seleccionada')->error();
                return back();
            }
            $template = new TemplateProcessor($pathTemplate);

            $fileName .= $nombre_cliente;
            $template->setValue('nombre_firma', $this->obtenerDatosUsuarioLoggeado()->nombres);
            $template->setValue('celular_firma', $this->obtenerDatosUsuarioLoggeado()->telefono);
            $template->setValue('correo_firma', $this->obtenerDatosUsuarioLoggeado()->correo);
            $template->setValue('fecha', $fechaP);
            $template->setValue('valor_mantenimiento', $valor_mantenimiento);
            $template->setValue('name', $request->nombre_cliente);
            $template->setValue('totalprecio',  number_format($totalprecio, 2, '.', ''));
            $template->setValue('descuentototal',  number_format($descuentototal, 2, '.', ''));
            $template->setValue('totalneto',  number_format($totalneto, 2, '.', ''));
            $template->setValue('iva',  number_format($iva, 2, '.', ''));
            $template->setValue('totaliva',  number_format($totaliva, 2, '.', ''));
            $template->setValue('formatotalp',  number_format($formatotalp, 2, '.', ''));
            $template->setValue('totalformap',  number_format($totalformap, 2, '.', ''));
            $template->setValue('pago_contado',  number_format(($totaliva - $porcentajeTarjetaContado), 2, '.', ''));
            $template->cloneRowAndSetValues('detalle', $detalles);
            $template->cloneRowAndSetValues('formapago', $formasPago);
            $template->saveAs($fileName . '.docx');

            $actualizar = Cotizaciones::where('cotizacionesid', $cotizaciones)->first();
            $actualizar->fecha = date('Y-m-d', strtotime($request->fecha));
            $actualizar->identificacion_cliente = $request->identificacion_cliente;
            $actualizar->nombre_cliente = $request->nombre_cliente;
            $actualizar->plantillasid = $request->tipo_plantilla;
            $actualizar->detalle_pago = $request->forma_pagoid;
            $actualizar->fechamodificacion = now();
            $actualizar->usuariomodificacion = $this->obtenerDatosUsuarioLoggeado()->nombres;
            $actualizar->detalle_cotizacion = json_encode($data);

            $actualizar->subtotal = number_format($totalneto, 2, '.', '');
            $actualizar->iva = number_format($iva, 2, '.', '');
            $actualizar->total = number_format($totaliva, 2, '.', '');

            if ($actualizar->save()) {
                $historial = new Log();
                $historial->usuario = $this->obtenerDatosUsuarioLoggeado()->nombres;
                $historial->pantalla = "Cotizar";
                $historial->operacion = "Modificar";
                $historial->fecha = now();
                $historial->detalle = $actualizar;
                $historial->save();
                flash('Actualizado Correctamente')->success();
            } else {
                flash('Ocurrió un error, vuelva a intentarlo')->error();
            }

            // Validar SO Linux
            $so = strtoupper(PHP_OS);
            if (str_contains($so, 'LINUX') === true && str_contains($request->botonDescargaCrear, 'pdf')) {
                // Comando para ejecutar el script de Python
                $python = 'python3';
                $BasePath = base_path();

                // Path files
                $pathWordInput = "$BasePath/public/$fileName.docx";
                $pathPDFOutput = "$BasePath/public/";

                // Comando para ejecutar el script de Python
                $command = escapeshellcmd("$python $BasePath/scripts/convert_to_pdf.py $pathWordInput $pathPDFOutput");

                $output = shell_exec($command);

                if (str_contains($output, 'Error')) {
                    return response()->download($fileName . '.docx')->deleteFileAfterSend(true);
                } else {
                    unlink($pathWordInput);
                    return response()->download($fileName . '.pdf')->deleteFileAfterSend(true);
                }
            }
            return response()->download($fileName . '.docx')->deleteFileAfterSend(true);
        } elseif ($request->botonDescargaCrear == "guardar") {
            $totalneto = 0;
            $totalprecio = 0;
            $descuentototal = 0;

            $actualizar = Cotizaciones::where('cotizacionesid', $cotizaciones)->first();
            $actualizar->fecha = date('Y-m-d', strtotime($request->fecha));
            $actualizar->identificacion_cliente = $request->identificacion_cliente;
            $actualizar->nombre_cliente = $request->nombre_cliente;
            $actualizar->plantillasid = $request->tipo_plantilla;
            $actualizar->detalle_pago = $request->forma_pagoid;
            $actualizar->fechamodificacion = now();
            $actualizar->usuariomodificacion = $this->obtenerDatosUsuarioLoggeado()->nombres;
            $array = $request->arrayDetalles;

            for ($i = 0; $i < count($array); $i++) {
                $convertirArray =  explode(",", $array[$i]);
                $data[$i] = [
                    'detalle' => $convertirArray[0],
                    'cantidad' => $convertirArray[1],
                    'descuento' => $convertirArray[2],
                ];
                $consultaDetalle = CotizacionesDetalle::select('detalle', 'precio')->where('detallesid', $convertirArray[0])->first();
                $precioFinal = $consultaDetalle->precio * $convertirArray[1];
                $descuentoFinal = ($precioFinal * $convertirArray[2]) / 100;
                $valorneto = $precioFinal  - $descuentoFinal;
                $totalprecio = floatval($precioFinal) + $totalprecio;
                $descuentototal = floatval($descuentoFinal) + $descuentototal;
                $totalneto = floatval($valorneto) + $totalneto;
            }

            $actualizar->detalle_cotizacion = json_encode($data);
            $iva = ($totalneto * 12) / 100;
            $totaliva = $totalneto + $iva;

            $actualizar->subtotal = number_format($totalneto, 2, '.', '');
            $actualizar->iva = number_format($iva, 2, '.', '');
            $actualizar->total = number_format($totaliva, 2, '.', '');


            if ($actualizar->save()) {
                $historial = new Log();
                $historial->usuario = $this->obtenerDatosUsuarioLoggeado()->nombres;
                $historial->pantalla = "Cotizar";
                $historial->operacion = "Modificar";
                $historial->fecha = now();
                $historial->detalle = $actualizar;
                $historial->save();
                flash('Actualizado Correctamente')->success();
            } else {
                flash('Ocurrió un error, vuelva a intentarlo')->error();
            }
            return back();
        }
    }

    public function generarContrato(Request $request, Cotizaciones $cotizacion)
    {
        $totalprecio = 0;
        $descuentototal = 0;
        $totalneto = 0;
        $formatotalp = 0;
        $totalformap = 0;
        $formasPago = [];
        $valorEstaciones = 0;
        $valorMoviles = 0;
        $cadBuscar = array("á", "Á", "é", "É", "í", "Í", "ó", "Ó", "ú", "Ú");
        $cadPoner = array("a", "A", "e", "E", "i", "I", "o", "O", "u", "U");

        $detallesCotizacion = json_decode($cotizacion->detalle_cotizacion);

        $fecha = new \Carbon\Carbon($request->fecha);
        $fechaP =   $fecha->locale('es')->translatedFormat('d \d\í\a\s \d\e\l \m\e\s \d\e F \d\e\l Y');

        $numeroPagos = $cotizacion->detalle_pago;

        foreach ($detallesCotizacion as $key => $detalle) {
            $consultaDetalle = CotizacionesDetalle::select('detalle', 'precio')->where('detallesid', $detalle->detalle)->first();

            $precioFinal = $consultaDetalle->precio * $detalle->cantidad;
            $descuentoFinal = ($precioFinal * $detalle->descuento) / 100;
            $valorneto = $precioFinal  - $descuentoFinal;

            $detalles[$key] = ['detalle' => $consultaDetalle->detalle, 'cantidad' => $detalle->cantidad, 'precio' =>  number_format($precioFinal, 2, '.', ''), 'porcentaje' => number_format($detalle->descuento, 2, '.', ''), 'descuento' =>  number_format($descuentoFinal, 2, '.', ''), 'neto' =>  number_format($valorneto, 2, '.', '')];
            $totalprecio = floatval($precioFinal) + $totalprecio;
            $descuentototal = floatval($descuentoFinal) + $descuentototal;
            $totalneto = floatval($valorneto) + $totalneto;

            similar_text(trim(strtoupper($consultaDetalle->detalle)), 'ESTACIONES DE TRABAJO', $porcentajeEstaciones);
            $cadenaAplicaciones = str_replace($cadBuscar, $cadPoner, $consultaDetalle->detalle);

            similar_text(trim(strtoupper($cadenaAplicaciones)), 'APLICACION MOVIL', $porcentajeAplicaciones);

            if ($porcentajeEstaciones > 92) {
                $valorEstaciones = $detalle->cantidad * 20;
            }
            if ($porcentajeAplicaciones > 92) {
                $valorMoviles = $detalle->cantidad * 30;
            }
        }

        $valor_mantenimiento =  $valorEstaciones + $valorMoviles;
        if ($valor_mantenimiento < 150) {
            $valor_mantenimiento = 150;
        }

        $iva = ($totalneto * 12) / 100;
        $totaliva = $totalneto + $iva;

        if ($numeroPagos > 0) {
            $cuotas = $totaliva / $numeroPagos;
            $porcentaje = 100 / $numeroPagos;
        }

        $dias = 0;
        for ($i = 0; $i < $numeroPagos; $i++) {
            $texto = $i == 0 ? "ENTRADA" : $dias . " DÍAS";
            $formasPago[$i] = ['formapago' => $texto, 'porcentajeforma' =>  number_format($porcentaje, 2, '.', ''), 'totalforma' =>  number_format($cuotas, 2, '.', '')];
            $formatotalp = floatval($porcentaje) + $formatotalp;
            $totalformap = floatval($cuotas) + $totalformap;
            $dias += 30;
        }

        $formatter = new NumberFormatter('es', NumberFormatter::SPELLOUT);
        $totalEnTexto = strtoupper($formatter->format($totalneto)) . ', ' . number_format(($totalneto - intval($totalneto)), 2, '.', '') * 100 . '/100';

        $porcentajeTarjetaContado = ($totaliva * 10) / 100;
        $nombre_cliente = str_replace(" ", "_", strtolower($cotizacion->nombre_cliente));

        $pathTemplate = $this->obtenerPlantillaDistribuidor();
        switch ($cotizacion->plantillasid) {
            case 1:
                $pathTemplate .= 'contratos/contrato_pc_contable.docx';
                $fileName = 'Contrato_Perseo_PC_Contable_';
                break;
            case 2:
                $pathTemplate .= 'contratos/contrato_pc_practico.docx';
                $fileName = 'Contrato_Perseo_PC_Practico_';
                $valor_mantenimiento = 80;
                break;
            case 3:
                $pathTemplate .= 'contratos/contrato_pc_control.docx';
                $fileName = 'Contrato_Perseo_PC_Control_';
                break;
        }

        if (!file_exists($pathTemplate)) {
            flash('No se encontró la plantilla seleccionada')->error();
            return back();
        }
        $template = new TemplateProcessor($pathTemplate);

        $fileName .= $nombre_cliente;
        $template->setValue('fecha_contrato', $fechaP);
        $template->setValue('nombre_cli', ucwords(strtolower($cotizacion->nombre_cliente)));
        $template->setValue('identificacion_cli', $cotizacion->identificacion_cliente);
        $template->setValue('direccion_cli', $request->direccion);
        $template->setValue('totalnetotexto', $totalEnTexto);
        $template->setValue('totalprecio',  number_format($totalprecio, 2, '.', ''));
        $template->setValue('descuentototal',  number_format($descuentototal, 2, '.', ''));
        $template->setValue('totalneto',  number_format($totalneto, 2, '.', ''));
        $template->setValue('iva',  number_format($iva, 2, '.', ''));
        $template->setValue('totaliva',  number_format($totaliva, 2, '.', ''));
        $template->setValue('formatotalp',  number_format($formatotalp, 2, '.', ''));
        $template->setValue('totalformap',  number_format($totalformap, 2, '.', ''));
        $template->setValue('pago_contado',  number_format(($totaliva - $porcentajeTarjetaContado), 2, '.', ''));
        $template->cloneRowAndSetValues('detalle', $detalles);
        $template->cloneRowAndSetValues('formapago', $formasPago);
        $template->saveAs($fileName . '.docx');

        return response()->download($fileName . '.docx')->deleteFileAfterSend(true);
    }

    public function recuperarPrecio(Request $request)
    {
        $buscarPrecio = CotizacionesDetalle::select('precio')->where('detallesid', $request->idDetalle)->first();
        return $buscarPrecio;
    }

    public function listadoCotizaciones(Request $request)
    {
        if ($request->ajax()) {

            $info = Cotizaciones::select('cotizaciones.cotizacionesid', 'cotizaciones.fecha', 'plantillasdescarga.detalle as plantilla', 'cotizaciones.detalle_pago', 'cotizaciones.nombre_cliente as prospectosnombres', 'cotizaciones.subtotal', 'cotizaciones.iva', 'cotizaciones.total', 'cotizaciones.usuariocreacion')
                ->join('plantillasdescarga', 'plantillasdescarga.plantillaDescargaid', 'cotizaciones.plantillasid')
                ->when($this->obtenerDatosUsuarioLoggeado()->rol, function ($q) {
                    $usuario = $this->obtenerDatosUsuarioLoggeado();

                    if (in_array($usuario->rol, [1])) {
                        return $q->join('usuarios', 'usuarios.usuariosid', 'cotizaciones.asesorid')
                            ->where('usuarios.usuariosid', $usuario->id);
                    }

                    return $q->join('usuarios', 'usuarios.usuariosid', 'cotizaciones.asesorid')
                        ->where('usuarios.distribuidoresid', $usuario->distribuidoresid);
                })
                ->get();

            return DataTables::of($info)
                ->editColumn('fecha', function ($cotizaciones) {
                    return Carbon::parse($cotizaciones->fecha)->format('d-m-Y');
                })
                ->editColumn('action', function ($cotizaciones) {
                    return '<a class="btn btn-sm btn-clean btn-icon" href="' . route('cotizarPlantilla.editar', $cotizaciones->cotizacionesid) . '" title="Editar"> <i class="la la-edit"></i> </a>' . '<a class="btn btn-sm btn-clean btn-icon confirm-delete" href="javascript:void(0)" data-href="' . route('eliminarCotizaciones.eliminar', $cotizaciones->cotizacionesid) . '" title="Eliminar"> <i class="la la-trash"></i> </a>';
                })
                ->rawColumns(['action', 'fecha', 'plantillasid'])
                ->make(true);
        }
        return view('soporte.admin.capacitaciones.cotizar.plantilla1.listadoCotizaciones');
    }

    public function eliminarCotizaciones($cotizaciones)
    {
        DB::beginTransaction();
        try {
            $eliminar = Cotizaciones::where('cotizacionesid', $cotizaciones)->first();
            $historial = new Log();
            $historial->usuario = $this->obtenerDatosUsuarioLoggeado()->nombres;
            $historial->pantalla = "Cotizar";
            $historial->operacion = "Eliminar";
            $historial->fecha = now();
            $historial->detalle = $eliminar;
            $historial->save();
            $eliminar->delete();
            DB::commit();
            flash('Eliminado Correctamente')->success();
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
        }
        return back();
    }

    /* -------------------------------------------------------------------------- */
    /*                           Funciones para detalles                          */
    /* -------------------------------------------------------------------------- */

    public function listado(Request $request)
    {
        if ($request->ajax()) {

            $data = CotizacionesDetalle::select('detallesid', 'detalle', 'precio');

            return DataTables::of($data)
                ->editColumn('action', function ($detalles) {
                    $listadoDetalles = [45, 46, 47, 48, 49, 50, 51];
                    $botones = '<a class="btn btn-sm btn-clean btn-icon" href="' . route('detalles.editar', $detalles->detallesid) . '" title="Editar"> <i class="la la-edit"></i> </a>';

                    if (Auth::user()->rol == 2 && !in_array($detalles->detallesid, $listadoDetalles)) {
                        $botones .= '<a class="btn btn-sm btn-clean btn-icon confirm-delete" href="javascript:void(0)" data-href="' . route('detalles.eliminar', $detalles->detallesid) . '" title="Eliminar"> <i class="la la-trash"></i> </a>';
                    }

                    return $botones;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('soporte.admin.capacitaciones.cotizar.plantilla1.listado');
    }

    public function crear()
    {
        $detalles = new CotizacionesDetalle();
        return view('soporte.admin.capacitaciones.cotizar.plantilla1.crear', compact('detalles'));
    }

    public function guardar(Request $request)
    {
        $request->validate(
            [
                'detalle' => ['required'],
                'precio' => ['required'],
            ],
            [
                'detalle.required' => 'Ingrese detalles',
                'precio.required' => 'Ingrese el precio',

            ],
        );
        $detalles = new CotizacionesDetalle();
        $detalles->detalle = $request->detalle;
        $detalles->precio =  number_format(floatval($request->precio), 2, '.', '');
        $detalles->fechacreacion = now();
        $detalles->usuariocreacion = $this->obtenerDatosUsuarioLoggeado()->nombres;



        if ($detalles->save()) {
            $historial = new Log();
            $historial->usuario = $this->obtenerDatosUsuarioLoggeado()->nombres;
            $historial->pantalla = "Cotizar";
            $historial->operacion = "Crear Detalle ";
            $historial->fecha = now();
            $historial->detalle = $detalles;
            $historial->save();
            flash('Guardado Correctamente')->success();
            return redirect()->route('detalles.editar', $detalles->detallesid);
        } else {
            flash('Ocurrió un error, vuelva a intentarlo')->error();
            return back();
        }
    }

    public function editar(CotizacionesDetalle $detalles)
    {
        return view('soporte.admin.capacitaciones.cotizar.plantilla1.editar', compact('detalles'));
    }

    public function actualizar(Request $request, $detalles)
    {
        $request->validate(
            [
                'detalle' => ['required'],
                'precio' => ['required'],
            ],
            [
                'detalle.required' => 'Ingrese detalles',
                'precio.required' => 'Ingrese el precio',
            ],
        );

        $actualizar = CotizacionesDetalle::where('detallesid', $detalles)->first();
        $actualizar->detalle = $request->detalle;
        $actualizar->precio =  number_format(floatval($request->precio), 2, '.', '');
        $actualizar->fechamodificacion = now();
        $actualizar->usuariomodificacion = $this->obtenerDatosUsuarioLoggeado()->nombres;
        if ($actualizar->save()) {
            $historial = new Log();
            $historial->usuario = $this->obtenerDatosUsuarioLoggeado()->nombres;
            $historial->pantalla = "Cotizar";
            $historial->operacion = "Modificar Detalle";
            $historial->fecha = now();
            $historial->detalle = $actualizar;
            $historial->save();
            flash('Actualizado Correctamente')->success();
        } else {
            flash('Ocurrió un error, vuelva a intentarlo')->error();
        }
        return back();
    }

    public function eliminar($detalles)
    {
        DB::beginTransaction();
        try {
            $eliminar = CotizacionesDetalle::where('detallesid', $detalles)->first();
            $historial = new Log();
            $historial->usuario = $this->obtenerDatosUsuarioLoggeado()->nombres;
            $historial->pantalla = "Cotizar";
            $historial->operacion = "Eliminar Detalle";
            $historial->fecha = now();
            $historial->detalle = $eliminar;
            $historial->save();
            $eliminar->delete();
            DB::commit();
            flash('Eliminado Correctamente')->success();
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Ocurrió un error vuelva a intentarlo')->warning();
        }
        return redirect()->route('detalles.listado');
    }

    function obtenerDatosUsuarioLoggeado()
    {
        if (Auth::guard('tecnico')->check()) {
            $usuario = Auth::guard('tecnico')->user();
            return (object)[
                'id' => $usuario->tecnicosid,
                'nombres' => $usuario->nombres,
                'identificacion' => $usuario->identificacion,
                'telefono' => $usuario->telefono,
                'correo' => $usuario->correo,
                'esTecnico' => true,
                'distribuidoresid' => $usuario->distribuidoresid,
                'rol' => $usuario->rol,
            ];
        } else {
            $usuario = Auth::user();
            return (object)[
                'id' => $usuario->usuariosid,
                'nombres' => $usuario->nombres,
                'identificacion' => $usuario->identificacion,
                'telefono' => $usuario->telefono,
                'correo' => $usuario->correo,
                'esTecnico' => false,
                'distribuidoresid' => $usuario->distribuidoresid,
                'rol' => $usuario->rol,
            ];
        }
    }

    private function obtenerPlantillaDistribuidor()
    {
        $usuario = $this->obtenerDatosUsuarioLoggeado();
        $path = 'word/';
        switch ($usuario->distribuidoresid) {
            case 1:
                return $path . 'alfa/';
            case 2:
                return $path . 'matriz/';
            case 3:
                return $path . 'delta/';
            case 4:
                return $path . 'omega/';
        }
    }
}
