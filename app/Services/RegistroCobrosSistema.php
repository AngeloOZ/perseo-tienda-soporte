<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistroCobrosSistema
{
    private $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
    }

    public function obtener_factura_perseo($facturaid)
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

    public function registro_del_cobro($factura, $datos_cobro)
    {
        $fechaActual = date("Ymd");
        try {
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
                            'fecharecepcion' => $fechaActual,
                            'fechavencimiento' => $fechaActual,
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
                                    'fechaemision' => $datos_cobro->fecha,
                                    'fecharecepcion' => $fechaActual,
                                    'fechavence' => $fechaActual,
                                    'fechavenceCH' => $datos_cobro->fecha,
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
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse()->getBody(true);
            $errorDetails = json_decode($responseBody);
            $mensaje = $errorDetails->fault->detail ?? $errorDetails->fault->faultstring ?? "Error al registrar el cobro en el sistema";
            throw new Exception($mensaje, 400);
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // Esto capturará errores HTTP como 500
            throw new Exception("Error de la API del sistema", 500);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
