<?php

namespace App\Http\Controllers;

use App\Models\EncuestaSoporte;
use App\Models\Ticket;
use App\Models\User;
use DateTime;

class SoporteApiController extends Controller
{
    private function devolverDatos($ticket)
    {
        $fechaInicio = new DateTime($ticket->fecha_asignacion);
        $fechaFin = new DateTime($ticket->fecha_cierre);
        $intervalo = $fechaInicio->diff($fechaFin);

        $tecnico = User::find($ticket->tecnicosid, ["nombres"]);
        if (!$tecnico) return null;

        $temp = [
            "id_ticket" => $ticket->ticketid,
            "numero_ticket" => $ticket->numero_ticket,
            "fecha_de_inicio" => $ticket->fecha_asignacion,
            "tiempo_de_soporte" => $intervalo->format('%H:%I:%S'),
            "nombre_tecnico" => $tecnico->nombres,
            "pregunta_1" => 0,
            "pregunta_2" => 0,
            "producto" => strtoupper($ticket->producto),
        ];

        $calificaionQuery = EncuestaSoporte::firstWhere("ticketid", $ticket->ticketid)->where("justificado", 0);
        if ($calificaionQuery) {
            if ($calificaionQuery->pregunta_1) {
                $temp['pregunta_1'] = $calificaionQuery->pregunta_1;
            }
            if ($calificaionQuery->pregunta_2) {
                $temp['pregunta_2'] = $calificaionQuery->pregunta_2;
            }
        }
        return $temp;
    }

    public function obtener_soporte_powerbi()
    {
        try {
            $soportes = Ticket::where("estado", '>=', 3)->get();

            $listadoSoportes = $soportes->map(function ($soporte) {
                return $this->devolverDatos($soporte);
            });

            $listadoSoportes = [...$listadoSoportes];
            $listadoSoportes = array_filter($listadoSoportes, function ($soporte) {
                return $soporte != null;
            });
            $listadoSoportes = array_values($listadoSoportes);

            return response()->json(["total_soportes" => count($listadoSoportes), "soportes" => $listadoSoportes,], 200);
        } catch (\Throwable $th) {
            return response()->json(["status" => 500, "message" => $th->getMessage()], 500);
        }
    }
}
