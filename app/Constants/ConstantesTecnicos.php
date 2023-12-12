<?php

namespace App\Constants;

class ConstantesTecnicos
{
    const ROL_TECNICOS = 5;
    const ROL_DESARROLLO = 6;
    const ROL_ADMINISTRADOR = 7;
    const ROL_REVISOR = 8;

    const ESTADO_TICKETS = [
        'abierto' => [
            'id' => 1,
            'nombre' => 'Abierto',
            'orden' => 1,
            'color' => 'bg-primary',
        ],
        'en_progreso' => [
            'id' => 2,
            'nombre' => 'En progreso',
            'orden' => 2,
            'color' => 'bg-info',
        ],
        'desarrollo' => [
            'id' => 3,
            'nombre' => 'Desarrollo',
            'orden' => 3,
            'color' => 'bg-success',
        ],
        'cerrado' => [
            'id' => 4,
            'nombre' => 'Cerrado',
            'orden' => 4,
            'color' => 'bg-danger',
        ],
        'cerrado_sr' => [
            'id' => 5,
            'nombre' => 'Cerrado (Sin respuesta)',
            'orden' => 5,
            'color' => 'bg-danger',
        ],
        'cerrado_pg' => [
            'id' => 6,
            'nombre' => 'Cerrado (Problema general)',
            'orden' => 6,
            'color' => 'bg-danger',
        ],
        'cerrado_vt' => [
            'id' => 7,
            'nombre' => 'Cerrado (Ventas)',
            'orden' => 7,
            'color' => 'bg-danger',
        ],
    ];


    public static function obtenerEstadosTickets()
    {
        $estados = collect(self::ESTADO_TICKETS)->sortBy('orden');

        return $estados->map(function ($item) {
            return (object) $item;
        });
    }

    public static function obtenerEstadosTicketsSelect($listadoId = array())
    {
        $estados = self::obtenerEstadosTickets();

        if (empty($listadoId)) {
            return $estados->map(function ($item) {
                return (object) [
                    'id' => $item->id,
                    'nombre' => $item->nombre,
                ];
            });
        }

        return $estados->whereIn('id', $listadoId)->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'nombre' => $item->nombre,
            ];
        });
    }

    public static function obtenerEstadoTicket($id)
    {
        $estado = self::obtenerEstadosTickets();
        return $estado->where('id', $id)->first();
    }
}
