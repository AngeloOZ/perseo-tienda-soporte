<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class WhatsappRenovacionesController extends Controller
{
    private $URL_BASE = "http://perseo-marketing.com";
    private $APIWhatsapp = '';
    private $bearToken = null;
    private $pathToken = '';
    private $client = null;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function index()
    {
        $this->init_config();
        $estado = $this->obtener_estado();
        return view('auth2.revisor_facturas.whatsapp', ["estado_whatsapp" => $estado]);
    }

    public function iniciar_whatsapp(Request $request)
    {
        $this->init_config();
        try {
            if ($this->bearToken != null) {
                return ["status" => 200, "sms" => "El servicio ya se encuentra activo"];
            }

            $url = "{$this->APIWhatsapp}/CLAVESECRETAPERSEO/generate-token";

            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8',])
                ->withOptions(["verify" => false])
                ->post($url)
                ->json();

            if (!isset($resultado["status"]) && !isset($resultado["token"])) {
                return response(["status" => 400, "message" => "No se pudo iniciar el servicio 1"], 400)->header('Content-Type', 'application/json');
            }

            if ($resultado["status"] != "success" && $resultado["token"] == "") {
                return response(["status" => 400, "message" => "No se pudo iniciar el servicio 2"], 400)->header('Content-Type', 'application/json');
            }

            $data = ["token" => $resultado["token"]];
            $json_data = json_encode($data);
            file_put_contents($this->pathToken, $json_data);
            $this->bearToken = $resultado["token"];

            return ["status" => 200, "sms" => "El servicio se inicio correctamente"];
        } catch (\Throwable $th) {
            return response(["status" => 500, "message" => $th->getMessage()], 500)->header('Content-Type', 'application/json');
        }
    }

    public function obtener_qr_whatsapp(Request $request)
    {
        $this->init_config();
        try {
            if ($this->bearToken == null) {
                return response(["status" => 400, "message" => "El servicio no se encuentra iniciado"], 400)->header('Content-Type', 'application/json');
            }

            $solicitud = [
                "webhook" => null,
                "waitQrCode" => true
            ];

            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'Authorization' => "Bearer " . $this->bearToken])
                ->withOptions(["verify" => false])
                ->post($this->APIWhatsapp . "/start-session", $solicitud)
                ->json();

            if (isset($resultado["qrcode"]) && $resultado["qrcode"] != "") {
                if (str_starts_with($resultado["qrcode"], "data:image/png;base64,")) {
                    return $resultado;
                }
                return response(["status" => 400, "message" => "No se pudo obtener el código QR - 1"], 400)->header('Content-Type', 'application/json');
            }

            return response(["status" => 400, "message" => "No se pudo obtener el código QR - 2"], 400)->header('Content-Type', 'application/json');
        } catch (\Throwable $th) {
            return response(["status" => 500, "message" => $th->getMessage()], 500)->header('Content-Type', 'application/json');
        }
    }

    public function obtener_estado()
    {
        $this->init_config();
        try {
            $resultado = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'Authorization' => "Bearer " . $this->bearToken])
                ->withOptions([
                    "verify" => false,
                    'timeout' => 5
                ])
                ->get($this->APIWhatsapp . "/status-session")
                ->json();

            if (isset($resultado["status"]) && $resultado["status"] != "") {
                if ($resultado["status"] == "CONNECTED") {
                    return true;
                }
                return false;
            }

            return false;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function cerrar_whatsapp(Request $request)
    {
        $this->init_config();
        try {
            if (!unlink($this->pathToken)) {
                return response(["status" => 400, "message" => "No se pudo cerrar sesion de whatsapp - 1"], 400)->header('Content-Type', 'application/json');
            }

            $result = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'Authorization' => "Bearer " . $this->bearToken])
                ->withOptions(["verify" => false])
                ->post($this->APIWhatsapp . "/logout-session")
                ->json();

            if (isset($result["status"]) && $result["status"]) {
                return ["status" => 200, "message" => "La sesión se cerro correctamente"];
            }
            return response(["status" => 400, "message" => "No se pudo cerrar sesion de whatsapp - 2"], 400)->header('Content-Type', 'application/json');
        } catch (\Throwable $th) {
            return response(["status" => 500, "message" => $th->getMessage()], 500)->header('Content-Type', 'application/json');
        }
    }

    public function eliminar_token()
    {
        $this->init_config();
        try {
            if (file_exists($this->pathToken)) {
                if (unlink($this->pathToken)) {
                    return ["status" => 200, "message" => "El token se elimino correctamente"];
                }
                return response(["status" => 400, "message" => "No se pudo eliminar el token"], 400)->header('Content-Type', 'application/json');
            }
            return ["status" => 200, "message" => "El token se elimino correctamente"];
        } catch (\Throwable $th) {
            return response(["status" => 500, "message" => $th->getMessage()], 500)->header('Content-Type', 'application/json');
        }
    }

    private function init_config($dasArgs = null)
    {
        $das = $dasArgs != null ? $dasArgs : Auth::user()->distribuidoresid;
        switch ($das) {
            case 1:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/renovacionesalfa2";
                $this->pathToken = 'ws/whatsapp.config.renovacionesalfa2.json';
                break;
            case 2:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/renovacionesmatriz";
                $this->pathToken = 'ws/whatsapp.config.renovacionesmatriz.json';
                break;
            case 3:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/renovacionesdelta";
                $this->pathToken = 'ws/whatsapp.config.renovacionesdelta.json';
                break;
            case 4:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/renovacionesomega";
                $this->pathToken = 'ws/whatsapp.config.renovacionesomega.json';
                break;
            default:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/renovacionesalfa2";
                $this->pathToken = 'ws/whatsapp.config.renovacionesalfa2.json';
                break;
        }
        $this->validar_existe_token();
    }

    private function validar_existe_token()
    {
        if (file_exists($this->pathToken)) {
            $token = file_get_contents($this->pathToken);
            $token = json_decode($token, true);
            if (isset($token["token"]) && $token["token"] != "") {
                $this->bearToken = $token["token"];
            }
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                          Funciones para tarea CRON                         */
    /* -------------------------------------------------------------------------- */

    private function init_config_cron($das = 1)
    {
        switch ($das) {
            case 1:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/renovacionesalfa2";
                $this->pathToken = 'public/ws/whatsapp.config.renovacionesalfa2.json';
                break;
            case 2:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/renovacionesmatriz";
                $this->pathToken = 'public/ws/whatsapp.config.renovacionesmatriz.json';
                break;
            case 3:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/renovacionesdelta";
                $this->pathToken = 'public/ws/whatsapp.config.renovacionesdelta.json';
                break;
            case 4:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/renovacionesomega";
                $this->pathToken = 'public/ws/whatsapp.config.renovacionesomega.json';
                break;
            default:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/renovacionesalfa2";
                $this->pathToken = 'public/ws/whatsapp.config.renovacionesalfa2.json';
                break;
        }
    }

    public static function enviar_mensaje($data, $cron = true)
    {
        $data = (object)$data;
        $instancia = new self();
        if ($cron) {
            $instancia->init_config_cron($data->distribuidor);
        } else {
            $instancia->init_config();
        }
        $instancia->validar_existe_token();

        try {
            $data = (object)$data;

            $phone = str_replace(" ", "", $data->phone);
            if (str_starts_with($phone, "0")) {
                $phone = "593" . substr($phone, 1);
            }

            $solicitud = [
                "phone" => $phone,
                "message" => $data->message,
                "isGroup" => false,
            ];
            // $res = $instancia->client->post($instancia->APIWhatsapp . "/send-message", [
            //     'headers' => [
            //         'Content-Type' => 'application/json; charset=UTF-8',
            //         'Authorization' => "Bearer " . $instancia->bearToken,
            //     ],
            //     'json' => $solicitud,
            //     'verify' => false,
            //     'timeout' => 10
            // ]);

            $res = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'Authorization' => "Bearer " . $instancia->bearToken])
                ->withOptions([
                    "verify" => false,
                    'timeout' => 3
                ])
                ->post($instancia->APIWhatsapp . "/send-message", $solicitud)
                ->json();

            if (isset($res['status']) && $res['status'] == 'success') {
                return true;
            }
            return false;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public static function enviar_archivo_mensaje($data, $timeout = 5, $cron = true, $intentos = 0)
    {
        $intentos++;
        $data = (object)$data;
        $instancia = new self();

        if ($cron)
            $instancia->init_config_cron($data->distribuidor);
        else
            $instancia->init_config($data->distribuidor);

        $instancia->validar_existe_token();

        try {
            $phone = str_replace(" ", "", $data->phone);
            if (str_starts_with($phone, "0")) {
                $phone = "593" . substr($phone, 1);
            }

            $solicitud = [
                "phone" => $phone,
                "caption" => $data->caption,
                "filename" => $data->filename,
                "base64" => $data->filebase64,
                "isGroup" => false,
            ];

            $res = Http::withHeaders([
                'Content-Type' => 'application/json; charset=UTF-8',
                'Authorization' => "Bearer " . $instancia->bearToken
            ])
                ->withOptions([
                    "verify" => false,
                    'timeout' => $timeout
                ])
                ->post($instancia->APIWhatsapp . "/send-file-base64", $solicitud)
                ->json();

            if (isset($res['status']) && $res['status'] == 'success') {
                return true;
            }

            $errorMessage = isset($res['message']) ? $res['message'] : '';

            if (
                $intentos <= 2 &&
                !str_contains($errorMessage, 'não existe') &&
                !str_contains($errorMessage, "não está ativa.")
            ) {
                return self::enviar_archivo_mensaje($data, $timeout + 3, $cron, $intentos);
            }

            echo "Error enviar sms: $intentos intentos: {$phone} - {$data->filename} DAS {$data->distribuidor}: response API {$errorMessage}\n";
            return false;
        } catch (\Throwable $th) {
            echo "Error_catch enviar whatsapp: {$phone} - {$data->filename} DAS {$data->distribuidor}: {$th->getMessage()}\n";
            return false;
        }
    }
}
