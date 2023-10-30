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
        'ASIGNADO_RESPONSABLE' => '1',
        'CONVERTIDO' => "CONVERTED",
        'COTIZACION' => "UC_43I4S7",
        'DEMOSTRACION' => "UC_A5770Y",
        'LLAMADA' => "IN_PROCESS",
        'NO_UTIL' => "JUNK",
        'SIN_ASIGNAR' => "NEW",
        "WHATSAPP" => 'PROCESSED',
    ];

    public function index()
    {

        $vendedores = User::select('usuariosid', 'nombres', 'bitrix_id')
            ->where('rol', 1)
            ->where('distribuidoresid', Auth::user()->distribuidoresid)
            ->where('bitrix_id', '!=', null)
            ->where('estado', '!=', 0)
            ->get();


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
                ->when($request->vendedor, function ($query, $vendedor) {
                    return $query->where('facturas.usuariosid', $vendedor);
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
                'ASSIGNED_BY_ID' => $this->obtener_id_asignados($request->vendedor),
            ];

            if ($request->fecha_inicio && $request->fecha_fin) {
                $filter['>=DATE_CREATE'] = $request->fecha_inicio;
                $filter['<=DATE_CREATE'] = $request->fecha_fin;
            }

            $estados = [
                $this->STATUS_BITRIX['ASIGNADO_RESPONSABLE'],
                $this->STATUS_BITRIX['CONVERTIDO'],
                $this->STATUS_BITRIX['COTIZACION'],
                $this->STATUS_BITRIX['DEMOSTRACION'],
                $this->STATUS_BITRIX['LLAMADA'],
                $this->STATUS_BITRIX['WHATSAPP'],
                $this->STATUS_BITRIX['NO_UTIL'],
            ];

            $prospectosValidos = $this->obtener_todos_prospectos([...$filter, 'STATUS_ID' => $estados]);
            $prospectosAgrupadosPorVendedor = $prospectosValidos->groupBy('ASSIGNED_BY_ID');
            $datosChart = [
                'series' => [
                    [
                        'name' => 'No utiles',
                        'data' => [],
                    ],
                    [
                        'name' => 'Utiles',
                        'data' => [],
                    ],
                    [
                        'name' => 'Convertidos',
                        'data' => [],
                    ]
                ],
                'categories' => [],
            ];

            foreach ($prospectosAgrupadosPorVendedor as $key => $prospectos) {

                $vendedor = User::select('nombres', 'usuariosid', 'bitrix_id')->firstWhere('bitrix_id', $key);

                if (!$vendedor) continue;

                $invalidos = $prospectos->filter(function ($pros) {
                    return $pros->STATUS_ID === $this->STATUS_BITRIX['NO_UTIL'];
                })->count();

                $validos = $prospectos->filter(function ($pros) {
                    return $pros->STATUS_ID !== $this->STATUS_BITRIX['NO_UTIL'];
                })->count();

                $convertidos = $prospectos->filter(function ($pros) {
                    return $pros->STATUS_ID === $this->STATUS_BITRIX['CONVERTIDO'];
                })->count();

                array_push($datosChart['categories'], $vendedor->nombres);
                array_push($datosChart['series'][0]['data'], $invalidos);
                array_push($datosChart['series'][1]['data'], $validos);
                array_push($datosChart['series'][2]['data'], $convertidos);
            }

            return response($datosChart, 200, ['Content-Type' => 'application/json']);
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function obtener_tiempo_de_cierre_de_conversion(Request $request)
    {
        try {
            $filter = [
                'ASSIGNED_BY_ID' => $this->obtener_id_asignados($request->vendedor),
                'STATUS_ID' => $this->STATUS_BITRIX['CONVERTIDO'],
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

    private function obtener_id_asignados($vendedor = null)
    {
        $isAdmin = Auth::user()->rol == 2;
        $ASSIGNED_BY_ID = [Auth::user()->bitrix_id];

        if ($isAdmin) {
            $ASSIGNED_BY_ID = [];
            User::where('rol', 1)
                ->where('distribuidoresid', Auth::user()->distribuidoresid)
                ->where('bitrix_id', '!=', null)
                ->where('estado', '!=', 0)
                ->when($vendedor, function ($query, $user) {
                    return $query->where('usuariosid', $user);
                })
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
