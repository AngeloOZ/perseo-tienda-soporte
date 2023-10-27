<?php

namespace App\Http\Controllers;

use App\Models\Cupones;
use App\Models\Factura;
use App\Models\User;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BitrixController extends Controller
{
    private $urlBit = 'https://b24-mh9fll.bitrix24.es/rest/1/8d1mnav2yurzdqk3';

    private $STATUS_BITRIX = [
        'invalid' => "JUNK",
        'valid' => "NEW",
        'inProcess' => "IN_PROCESS",
        'converted' => "CONVERTED",
    ];

    public function index()
    {
        $vendedores = [];

        if (Auth::user()->rol == 2) {
            $vendedores = User::select('usuariosid', 'nombres', 'bitrix_id')
                ->where('rol', 1)
                ->where('distribuidoresid', Auth::user()->distribuidoresid)
                ->where('bitrix_id', '!=', null)
                ->where('estado', '!=', 0)
                ->get();
        }

        return view('auth.bitrix.index', compact('vendedores'));
    }

    public function obtener_promedio_ventas(Request $request)
    {
        try {
            $BaseConsulta = Factura::when(Auth::user()->rol, function ($query) {
                if (Auth::user()->rol == 1) {
                    return $query->where('facturas.usuariosid', Auth::user()->usuariosid);
                } else {
                    return $query->where('facturas.distribuidoresid', Auth::user()->distribuidoresid);
                }
            })
                ->when($request->fecha_inicio, function ($query, $fecha_inicio) {
                    $query->where('facturas.fecha_creacion', '>=', $fecha_inicio);
                })
                ->when($request->fecha_fin, function ($query, $fecha_fin) {
                    $query->where('facturas.fecha_creacion', '<=', $fecha_fin);
                })
                ->where('facturas.total_venta', '!=', 0)
                ->where('facturas.facturado', 1)
                ->where('facturas.estado_pago', '!=', 0);


            $totalVentas = collect($BaseConsulta->selectRaw("usuariosid, SUM(total_venta) as 'total'")
                ->groupBy('usuariosid')
                ->orderBy('usuariosid', 'ASC')
                ->get());

            $totalFacturas = collect($BaseConsulta->selectRaw("usuariosid, COUNT(total_venta) as 'total'")
                ->groupBy('usuariosid')
                ->orderBy('usuariosid', 'ASC')
                ->get());

            $data = [
                'data' => [],
                'categories' => []
            ];

            foreach ($totalVentas as $venta) {
                $vendedor = User::find($venta->usuariosid, 'nombres');

                $promedio = $venta->total / $totalFacturas->where('usuariosid', $venta->usuariosid)->first()->total;
                $promedio = floatval(number_format($promedio, 2));

                array_push($data['categories'], $vendedor->nombres);
                array_push($data['data'], $promedio);
            }

            return response($data, 200, ['Content-Type' => 'application/json']);
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function obtener_tasa_utilidad_prospectos(Request $request)
    {
        try {
            $filter = [
                'ASSIGNED_BY_ID' => $this->obtener_id_asignados(),
            ];

            if ($request->fecha_inicio && $request->fecha_fin) {
                $filter['>=DATE_CREATE'] = $request->fecha_inicio;
                $filter['<=DATE_CREATE'] = $request->fecha_fin;
            }

            $prospectosIvalidos = $this->obtener_prospectos([...$filter, 'STATUS_ID' => $this->STATUS_BITRIX['invalid'],])->total;
            $prospectosValidos = $this->obtener_prospectos([...$filter, 'STATUS_ID<>' => $this->STATUS_BITRIX['invalid'],])->total;
            $prospectosConvertidos = $this->obtener_prospectos([...$filter, 'STATUS_ID' => $this->STATUS_BITRIX['converted'],])->total;

            $data = [
                'data' => [$prospectosIvalidos, $prospectosValidos, $prospectosConvertidos],
                'categories' => ['Invalidos', 'Validos', 'Convertidos']
            ];

            return response($data, 200, ['Content-Type' => 'application/json']);
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function obtener_tiempo_de_cierre_de_conversion(Request $request)
    {
        try {
            $filter = [
                'ASSIGNED_BY_ID' => $this->obtener_id_asignados(),
                'STATUS_ID' => $this->STATUS_BITRIX['converted'],
            ];

            if ($request->fecha_inicio && $request->fecha_fin) {
                $filter['>=DATE_CREATE'] = $request->fecha_inicio;
                $filter['<=DATE_CREATE'] = $request->fecha_fin;
            }


            $prospectos = $this->obtener_todos_prospectos($filter);
            $prospectosAgrupados = $prospectos->groupBy('ASSIGNED_BY_ID');
            $estadisticaVendedores = [];

            $prospectosAgrupados->each(function ($leads, $key) use (&$estadisticaVendedores) {
                $totalHoras = 0;
                $leads->each(function ($prospecto) use (&$totalHoras) {
                    $horas = $this->calcular_diferencia_fechas($prospecto);
                    $totalHoras += $horas;
                });

                $promedioHoras = $totalHoras / $leads->count();
                $promedioHoras = floatval(number_format($promedioHoras, 2));

                $newData = [
                    'id' => $key,
                    'nombre' => User::where('bitrix_id', $key)->first()->nombres,
                    'promedio' => $promedioHoras,
                ];
                array_push($estadisticaVendedores, $newData);
            });

            $data = [
                'data' => [],
                'categories' => []
            ];

            foreach ($estadisticaVendedores as $vendedor) {
                array_push($data['categories'], $vendedor['nombre']);
                array_push($data['data'], $vendedor['promedio']);
            }

            return response($data, 200, ['Content-Type' => 'application/json']);
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    private function obtener_prospectos($filter = [], $other = [])
    {
        $client = new Client();

        $body = [
            'order' => [
                'TITLE' => 'ASC'
            ],
            'filter' => $filter,
            'select' => ['ID', 'TITLE', 'OPPORTUNITY', 'PHONE', 'HAS_MAIL', 'EMAIL', 'STATUS_ID', 'DATE_CREATE', 'DATE_CLOSED', 'ASSIGNED_BY_ID'],
            'start' => 1,
            ...$other
        ];

        try {
            $response = $client->post($this->urlBit . '/crm.lead.list', [
                'json' => $body
            ]);
            $data = $response->getBody()->getContents();
            return json_decode($data);
        } catch (\Exception $e) {
            dd($e);
            return null;
        }
    }

    private function obtener_todos_prospectos($filter = [], $other = [])
    {
        $client = new Client();
        $prospectos = collect([]);
        $band = true;
        $body = [
            'order' => [
                'ID' => 'ASC'
            ],
            'filter' => $filter,
            'select' => ['ID', 'TITLE', 'OPPORTUNITY', 'PHONE', 'HAS_MAIL', 'EMAIL', 'STATUS_ID', 'DATE_CREATE', 'DATE_CLOSED', 'ASSIGNED_BY_ID'],
            'start' => 0,
            ...$other
        ];

        try {
            do {
                $response = $client->post($this->urlBit . '/crm.lead.list', [
                    'json' => $body
                ]);
                $data = $response->getBody()->getContents();
                $data = json_decode($data);

                if (isset($data->result)) {
                    $prospectos = $prospectos->merge($data->result);
                }

                if (isset($data->next)) {
                    $body['start'] = $data->next;
                } else {
                    $band = false;
                }
            } while ($band);

            return $prospectos;
        } catch (\Exception $e) {
            dd($e);
        }
    }

    private function obtener_id_asignados()
    {
        $isAdmin = Auth::user()->rol == 2;
        $ASSIGNED_BY_ID = [Auth::user()->bitrix_id];

        if ($isAdmin) {
            $ASSIGNED_BY_ID = [];
            User::where('rol', 1)
                ->where('distribuidoresid', Auth::user()->distribuidoresid)
                ->where('bitrix_id', '!=', null)
                ->where('estado', '!=', 0)
                ->get()
                ->each(function ($user) use (&$ASSIGNED_BY_ID) {
                    array_push($ASSIGNED_BY_ID, $user->bitrix_id);
                });
        }

        return $ASSIGNED_BY_ID;
    }

    private function calcular_diferencia_fechas($prospecto)
    {
        $fechaInicio = new DateTime($prospecto->DATE_CREATE);
        $fechaFin = new DateTime($prospecto->DATE_CLOSED);

        $diferencia = $fechaInicio->diff($fechaFin);
        return $diferencia->h + ($diferencia->days * 24);
    }
}
