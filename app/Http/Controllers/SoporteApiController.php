<?php

namespace App\Http\Controllers;

use App\Models\EncuestaSoporte;
use App\Models\Tecnicos;
use App\Models\Ticket;
use DateTime;

class SoporteApiController extends Controller
{
    private function devolverDatos($ticket)
    {
        $fechaInicio = new DateTime($ticket->fecha_asignacion);
        $fechaFin = new DateTime($ticket->fecha_cierre);
        $intervalo = $fechaInicio->diff($fechaFin);

        $tecnico = Tecnicos::find($ticket->tecnicosid, ["nombres"]);
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

        $calificaionQuery = EncuestaSoporte::where("ticketid", $ticket->ticketid)->where("justificado", 0)->first();

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
            $TicketsSoportes = Ticket::where("estado", '>=', 3)->get();

            $listadoSoportes = $TicketsSoportes->map(function ($ticket) {
                return $this->devolverDatos($ticket);
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
