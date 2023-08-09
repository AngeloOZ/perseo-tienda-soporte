<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class WhatsappRenovacionesController extends Controller
{
    private $URL_BASE = "http://perseo-marketing.com";
    private $APIWhatsapp = '';
    private $bearToken = null;

    private function init_config($das = 1)
    {
        switch ($das) {
            case 1:
                $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/renovacionesalfa2";
                $this->bearToken = '$2b$10$cP6GXhvUy_IVSEyFP47ZB.OROcbKWR5JdU9bCQT3c3alazASDtrtO';
                break;
                // case 2:
                //     $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/soporte";
                //     $this->pathToken = '';
                //     break;
                // case 3:
                //     $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/soportedelta";
                //     $this->pathToken = '';
                //     break;
                // case 4:
                //     $this->APIWhatsapp = "{$this->URL_BASE}:8089/api/soporteomega";
                //     $this->pathToken = '';
                //     break;
        }
    }

    public static function enviar_mensaje($data)
    {
        $data = (object)$data;
        $instancia = new self();
        $instancia->init_config($data->distribuidor);

        try {
            $data = (object)$data;

            $phone = $data->phone;
            if (str_starts_with($data->phone, "0")) {
                $phone = "593" . substr($data->phone, 1);
            }

            $solicitud = [
                "phone" => $phone,
                "message" => $data->message,
                "isGroup" => false,
            ];

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

    public static function enviar_archivo_mensaje($data, $timeout = 5)
    {
        $data = (object)$data;
        $instancia = new self();
        $instancia->init_config($data->distribuidor);

        try {
            $phone = $data->phone;
            if (str_starts_with($data->phone, "0")) {
                $phone = "593" . substr($data->phone, 1);
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
            return false;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
