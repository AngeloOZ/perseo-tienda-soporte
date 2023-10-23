<?php

namespace App\Http\Controllers;

use App\Models\NumeroWhatsapp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    private $URL_BASE = "http://perseo-marketing.com";
    private $APIWhatsapp = '';
    private $bearToken = null;
    private $pathToken = '';

    private function init_config()
    {
        $das = isset(Auth::guard('tecnico')->user()->distribuidoresid) ? Auth::guard('tecnico')->user()->distribuidoresid : 2;
        switch ($das) {
            case 1:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/soporte";
                $this->pathToken = 'ws/whatsapp.config.das2.json';
                break;
            case 2:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/soporte";
                $this->pathToken = 'ws/whatsapp.config.das2.json';
                break;
            case 3:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/soportedelta";
                $this->pathToken = 'ws/whatsapp.config.das3.json';
                break;
            case 4:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/soporteomega";
                $this->pathToken = 'ws/whatsapp.config.das4.json';
                break;
        }
        $this->validar_existe_token($das);
    }

    public function index()
    {
        $this->init_config();
        $numeros = NumeroWhatsapp::when(Auth::guard('tecnico')->user()->distribuidoresid, function ($query, $das) {
            if ($das <= 2) {
                $query->where('das', 2);
            } else {
                $query->where('das', $das);
            }
        })->get();
        $estado = $this->obtener_estado();
        return view('soporte.admin.whatsapp', ["estado_whatsapp" => $estado, "numeros" => $numeros]);
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


            if (isset($resultado["status"]) && isset($resultado["token"])) {
                if ($resultado["status"] == "success" && $resultado["token"] != "") {
                    $data = ["token" => $resultado["token"]];
                    $json_data = json_encode($data);
                    file_put_contents($this->pathToken, $json_data);
                    $this->bearToken = $resultado["token"];
                    return ["status" => 200, "sms" => "El servicio se inicio correctamente"];
                }
                return response(["status" => 400, "message" => "No se pudo iniciar el servicio 1"], 400)->header('Content-Type', 'application/json');
            }
            return response(["status" => 400, "message" => "No se pudo iniciar el servicio 2"], 400)->header('Content-Type', 'application/json');
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

    public function enviar_sms_whatsapp(Request $request)
    {
        $this->init_config();
        $this->enviar_mensaje($request);
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

    public function enviar_mensaje($data)
    {
        $this->init_config();
        try {
            $data = json_decode(json_encode($data));

            $numero = str_replace(" ", "", $data->numero);
            $mensaje = strip_tags($data->mensaje, '<br>');
            $mensaje = str_replace('<br>', "\n", $mensaje);
            $mensaje = "Hola {$data->nombre} buen día, reciba un cordial saludo del *equipo de soporte*.\n\n" . $mensaje . "\n\n*Nota:* Este número es solo para comunicados, por favor no responder.";

            if (str_starts_with($data->numero, "0")) {
                $numero = "593" . substr($data->numero, 1);
            }

            $solicitud = [
                "phone" => $numero,
                "message" => $mensaje,
                "isGroup" => false,
            ];

            $res = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'Authorization' => "Bearer " . $this->bearToken])
                ->withOptions([
                    "verify" => false,
                    'timeout' => 3
                ])
                ->post($this->APIWhatsapp . "/send-message", $solicitud)
                ->json();


            if (isset($res['status']) && $res['status'] == 'success') {
                return true;
            }
            return false;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function enviar_personalizado($data, $timeout = 3)
    {
        $this->init_config();
        try {
            $data = json_decode(json_encode($data));
            $numero = $data->numero;
            $mensaje = $data->mensaje;

            if (str_starts_with($data->numero, "0")) {
                $numero = "593" . substr($data->numero, 1);
            }

            $solicitud = [
                "phone" => $numero,
                "message" => $mensaje,
                "isGroup" => false,
            ];

            $res = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8', 'Authorization' => "Bearer " . $this->bearToken])
                ->withOptions([
                    "verify" => false,
                    'timeout' => $timeout
                ])
                ->post($this->APIWhatsapp . "/send-message", $solicitud)
                ->json();


            if (isset($res['status']) && $res['status'] == 'success') {
                return true;
            }
            return false;
        } catch (\Throwable $th) {
            return false;
        }
    }

    private function validar_existe_token($das)
    {
        if (file_exists($this->pathToken)) {
            $token = file_get_contents($this->pathToken);
            $token = json_decode($token, true);
            if (isset($token["token"]) && $token["token"] != "") {
                $this->bearToken = $token["token"];
            }
        }
    }
}
