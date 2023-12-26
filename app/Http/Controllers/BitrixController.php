<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\User;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BitrixController extends Controller
{
    // private $urlBit = 'https://b24-mh9fll.bitrix24.es/rest/1/8d1mnav2yurzdqk3';
    private $urlBit = 'https://b24-mh9fll.bitrix24.es/rest/1/682pyo0670ml6xu9';

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

    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

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
            $filter = [];
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
                ],
                'categories' => [],
            ];
            $sendRequest = [
                'convertido' => false,
                'no_util' => true,
                'utiles' => true,
            ];

            if ($request->fecha_inicio && $request->fecha_fin) {
                if ($request->tipoBusqueda === "created") {
                    $filter['>=DATE_CREATE'] = $request->fecha_inicio;
                    $filter['<=DATE_CREATE'] = $request->fecha_fin;
                } else {
                    $filter['>=DATE_CLOSED'] = $request->fecha_inicio;
                    $filter['<=DATE_CLOSED'] = $request->fecha_fin;
                }
            }

            foreach ($this->obtener_id_asignados($request->vendedor) as $bitrixId) {
                $vendedor = User::select('nombres', 'usuariosid', 'bitrix_id')->firstWhere('bitrix_id', $bitrixId);

                if (!$vendedor) continue;

                $filter['ASSIGNED_BY_ID'] = $bitrixId;
                $datos = $this->beta_obtener_datos_utilidad($filter, $sendRequest);

                $invalidos = $datos['no_util']->total;
                $validos = $datos['utiles']->total;

                array_push($datosChart['categories'], $vendedor->nombres);
                array_push($datosChart['series'][0]['data'], $invalidos);
                array_push($datosChart['series'][1]['data'], $validos);
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
                'STATUS_ID' => $this->STATUS_BITRIX['CONVERTIDO'],
            ];

            if ($request->fecha_inicio && $request->fecha_fin) {
                if ($request->tipoBusqueda === "created") {
                    $filter['>=DATE_CREATE'] = $request->fecha_inicio;
                    $filter['<=DATE_CREATE'] = $request->fecha_fin;
                } else {
                    $filter['>=DATE_CLOSED'] = $request->fecha_inicio;
                    $filter['<=DATE_CLOSED'] = $request->fecha_fin;
                }
            }

            $prospectosAgrupados = collect([]);
            foreach ($this->obtener_id_asignados($request->vendedor) as $bitrixId) {
                $filter['ASSIGNED_BY_ID'] = $bitrixId;
                $prospectosAgrupados[$bitrixId] = $this->obtener_todos_prospectos($filter);
            }

            $estadisticaVendedores = [];

            $prospectosAgrupados->each(function ($leads, $key) use (&$estadisticaVendedores) {
                $totalHoras = 0;

                $leads->each(function ($prospecto) use (&$totalHoras) {
                    $horas = $this->calcular_diferencia_fechas($prospecto);
                    $totalHoras += $horas;
                });
                $numeroProspectos = $leads->count() == 0 ? 1 : $leads->count();
                $promedioHoras = $totalHoras / $numeroProspectos;
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

    public function obtener_tasa_de_conversion(Request $request)
    {
        try {
            $filter = [
                "CLOSED" => "Y",
                'ASSIGNED_BY_ID' => $this->obtener_id_asignados($request->vendedor),
            ];
            $datosChart = [
                "series" => [
                    [
                        "name" => 'Leads convertidos',
                        "group" => 'convertidos',
                        "data" => []
                    ],
                    [
                        "name" => 'Negociaciones ganadas',
                        "group" => 'negociaciones',
                        "data" => []
                    ]
                ],
                "porcentaje" => [],
                "categories" => []
            ];

            if ($request->fecha_inicio && $request->fecha_fin) {
                if ($request->tipoBusqueda === "created") {
                    $filter['>=DATE_CREATE'] = $request->fecha_inicio;
                    $filter['<=DATE_CREATE'] = $request->fecha_fin;
                } else {
                    $filter['>=CLOSEDATE'] = $request->fecha_inicio;
                    $filter['<=CLOSEDATE'] = $request->fecha_fin;
                }
            }

            $listadoNegociaciones = $this->obtener_todas_negociaciones($filter);
            $negociacionesAgrupadas = $listadoNegociaciones->groupBy('ASSIGNED_BY_ID');

            foreach ($negociacionesAgrupadas as $key => $negociaciones) {
                $vendedor = User::select('nombres', 'usuariosid', 'bitrix_id')->firstWhere('bitrix_id', $key);

                if (!$vendedor) continue;

                $filterAux = [
                    "ASSIGNED_BY_ID" => $key,
                ];

                $sendRequest = [
                    'convertido' => true,
                    'utiles' => true,
                    'no_util' => false,
                ];

                if ($request->fecha_inicio && $request->fecha_fin) {
                    if ($request->tipoBusqueda === "created") {
                        $filterAux['>=DATE_CREATE'] = $request->fecha_inicio;
                        $filterAux['<=DATE_CREATE'] = $request->fecha_fin;
                    } else {
                        $filterAux['>=DATE_CLOSED'] = $request->fecha_inicio;
                        $filterAux['<=DATE_CLOSED'] = $request->fecha_fin;
                    }
                }

                $datos = $this->beta_obtener_datos_utilidad($filterAux, $sendRequest);

                $numeroConvertidos = $datos['convertido']->total;
                $numeroUtiles = $datos['utiles']->total == 0 ? 1 : $datos['utiles']->total;
                $numeroNegociaciones = $negociaciones->count();

                $porcentajeConvertidos = ($numeroConvertidos / $numeroUtiles) * 100;
                $porcentajeConvertidos = floatval(number_format($porcentajeConvertidos, 2));

                $datosChart['porcentaje'][$vendedor->nombres] = $porcentajeConvertidos;
                array_push($datosChart['categories'], $vendedor->nombres);
                array_push($datosChart['series'][0]['data'], $numeroConvertidos);
                array_push($datosChart['series'][1]['data'], $numeroNegociaciones);
            }

            return response($datosChart, 200, ['Content-Type' => 'application/json']);
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                             Funciones genericas                            */
    /* -------------------------------------------------------------------------- */

    private function beta_obtener_datos_utilidad($filter = [], $sendRequest = null)
    {
        if ($sendRequest == null) {
            $sendRequest = [
                'convertido' => true,
                'no_util' => true,
                'utiles' => true,
            ];
        }

        $promises = [];
        $body = [
            'order' => [
                'ID' => 'ASC'
            ],
            'filter' => $filter,
            'select' => ['ID', 'TITLE', 'OPPORTUNITY', 'PHONE', 'HAS_MAIL', 'EMAIL', 'STATUS_ID', 'DATE_CREATE', 'DATE_CLOSED', 'ASSIGNED_BY_ID'],
            'start' => 1,
        ];

        try {

            if ($sendRequest['convertido']) {
                $body['filter']['STATUS_ID'] = $this->STATUS_BITRIX['CONVERTIDO'];
                $promises['convertido'] = $this->client->postAsync($this->urlBit . '/crm.lead.list', ['json' => $body]);
            }

            if ($sendRequest['no_util']) {
                $body['filter']['STATUS_ID'] = $this->STATUS_BITRIX['NO_UTIL'];
                $promises['no_util'] = $this->client->postAsync($this->urlBit . '/crm.lead.list', ['json' => $body]);
            }

            if ($sendRequest['utiles']) {
                $body['filter']['STATUS_ID'] = [
                    $this->STATUS_BITRIX['ASIGNADO_RESPONSABLE'],
                    $this->STATUS_BITRIX['COTIZACION'],
                    $this->STATUS_BITRIX['DEMOSTRACION'],
                    $this->STATUS_BITRIX['LLAMADA'],
                    $this->STATUS_BITRIX['WHATSAPP'],
                    $this->STATUS_BITRIX['CONVERTIDO'],
                ];
                $promises['utiles'] = $this->client->postAsync($this->urlBit . '/crm.lead.list', ['json' => $body]);
            }

            $results = Promise\Utils::all($promises)->wait();

            $data = [];
            foreach ($results as $key => $result) {
                $data[$key] = json_decode($result->getBody()->getContents());
            }

            return $data;
        } catch (\Exception $e) {
            dd($e);
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
        $promises = [];
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
            $initialProps = $this->obtener_prospectos($filter);
            $prospectos = $prospectos->merge($initialProps->result);

            $startIndex = $initialProps->next ?? null;
            $perPage = count($initialProps->result) == 0 ? 1 : count($initialProps->result);
            $total = $initialProps->total;
            $totalPages = ceil($total / $perPage);

            if ($startIndex != null) {
                for ($i = 1; $i < $totalPages; $i++) {
                    $body['start'] = $startIndex + ($perPage * $i);
                    $promises[] = $client->postAsync($this->urlBit . '/crm.lead.list', ['json' => $body]);
                }
                $results = Promise\Utils::all($promises);
                $results = $results->wait();

                foreach ($results as $result) {
                    $data = json_decode($result->getBody()->getContents());
                    $prospectos = $prospectos->merge($data->result);
                }
            }
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

    private function obtener_negociaciones($filter = [], $other = [])
    {
        $client = new Client();

        $body = [
            'order' => [
                'ID' => 'ASC'
            ],
            'filter' => $filter,
            'start' => 1,
            ...$other
        ];

        try {
            $response = $client->post($this->urlBit . '/crm.deal.list', [
                'json' => $body
            ]);
            $data = $response->getBody()->getContents();
            return json_decode($data);
        } catch (\Exception $e) {
            dd($e);
        }
    }

    private function obtener_todas_negociaciones($filter = [], $other = [])
    {
        $client = new Client();
        $negociaciones = collect([]);
        $body = [
            'order' => [
                'ID' => 'ASC'
            ],
            'filter' => $filter,
            'start' => 0,
            ...$other
        ];

        try {
            $initialNegot = $this->obtener_negociaciones($filter);
            $negociaciones = $negociaciones->merge($initialNegot->result);

            $startIndex = $initialNegot->next ?? null;
            $perPage = count($initialNegot->result) == 0 ? 1 : count($initialNegot->result);
            $total = $initialNegot->total;
            $totalPages = ceil($total / $perPage);

            if ($startIndex != null) {
                for ($i = 1; $i < $totalPages; $i++) {
                    $body['start'] = $startIndex + ($perPage * $i);
                    $promises[] = $client->postAsync($this->urlBit . '/crm.lead.list', ['json' => $body]);
                }

                $results = Promise\Utils::all($promises);
                $results = $results->wait();

                foreach ($results as $result) {
                    $data = json_decode($result->getBody()->getContents());
                    $negociaciones = $negociaciones->merge($data->result);
                }
            }

            return $negociaciones;
        } catch (\Exception $e) {
            dd($e);
        }
    }

    private function calcular_diferencia_fechas($prospecto)
    {
        $fechaInicio = new DateTime($prospecto->DATE_CREATE);
        $fechaFin = new DateTime($prospecto->DATE_CLOSED);

        $diferencia = $fechaInicio->diff($fechaFin);
        return $diferencia->h + ($diferencia->days * 24);
    }
}
