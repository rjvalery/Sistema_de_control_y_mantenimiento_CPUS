<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\GarantiaPortatil;
use App\Models\InventarioGeneral;
use App\Models\SopladoRegistro;
use App\Models\Usuario;
use App\Services\BitacoraExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Meta de intervenciones por día para cada línea técnica (capacidad de referencia).
     * Se puede sobreescribir con DASHBOARD_META_DIARIA_LINEA en el .env.
     */
    private const META_DIARIA_LINEA = 10;

    /** Días hábiles equivalentes por período para escalar la capacidad. */
    private const DIAS_POR_PERIODO = ['dia' => 1, 'semana' => 5, 'mes' => 22, 'anio' => 260];

    public function index(Request $request, \App\Services\DashboardMetricasService $service)
    {
        $data = $service->resolverDashboard($request);

        if ($request->ajax() || $request->get('ajax') == '1') {
            return response()->json($this->formatearRespuestaJson($data));
        }

        return view('dashboard.index', $data);
    }

    public function metricas(Request $request, \App\Services\DashboardMetricasService $service)
    {
        $data = $service->resolverDashboard($request);

        return response()->json($this->formatearRespuestaJson($data));
    }

    private function formatearRespuestaJson(array $data): array
    {
        $paginator = $data['maquinasIntervenidas'];
        $items = ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator || $paginator instanceof \Illuminate\Contracts\Pagination\Paginator)
            ? $paginator->items()
            : $paginator;

        return [
            'status'               => 'success',
            'periodo'              => $data['periodo'],
            'labelPeriodo'         => $data['labelPeriodo'],
            'analista_id'          => $data['analista_id'],
            'nombreAnalista'       => $data['nombreAnalista'],
            'statsInventario'      => $data['statsInventario'],
            'totalIntervenciones'  => $data['totalIntervenciones'],
            'totalEquipos'         => $data['totalEquipos'],
            'totalCPUs'            => $data['totalCPUs'],
            'totalSoplado'         => $data['totalSoplado'],
            'totalPortatiles'      => $data['totalPortatiles'],
            'porcEq'               => $data['porcEq'],
            'porcSp'               => $data['porcSp'],
            'porcPt'               => $data['porcPt'],
            'totalTraslados'       => $data['totalTraslados'],
            'maquinasIntervenidas' => $items,
            'total_registros'      => $data['total_registros'],
            'total_paginas'        => $data['total_paginas'],
            'pagina_actual'        => $data['pagina_actual'],
            'limite'               => $data['limite'],
            'totalAnalistas'       => $data['totalAnalistas'],
            'analistasActivosHoy'  => $data['analistasActivosHoy'],
            'deltaSemanal'         => $data['deltaSemanal'],
            'totalBaja'            => $data['totalBaja'],
            'lineas'               => $data['lineas'],
        ];
    }



    public function exportarBitacora(Request $request, BitacoraExportService $exportService)
    {
        $user = auth()->user();
        $periodo = strtolower(trim($request->get('periodo', 'dia')));
        
        $esAdmin = $user && $user->hasRole('admin');
        $esRestringido = !$esAdmin && ($user && ($user->can('dashboard.ver_solo_propio') || $user->hasRole('analista') || $user->rol === 'analista'));
        
        $nombreUsuario = $user ? ($user->name ?? $user->nombre) : null;
        
        $filtroAnalistaNombre = null;
        if ($esRestringido) {
            $filtroAnalistaNombre = $nombreUsuario;
        } else {
            $paramAnalista = $request->get('analista_id');
            if ($paramAnalista && $paramAnalista !== 'todos') {
                $analistaObj = Usuario::find($paramAnalista);
                if ($analistaObj) {
                    $filtroAnalistaNombre = $analistaObj->name ?? $analistaObj->nombre;
                }
            }
        }

        $service = app(\App\Services\DashboardMetricasService::class);
        [$periodo, $inicio, $fin] = $service->resolverRangoFechas($periodo);

        $query = InventarioGeneral::query()->with('garantiasPortatiles')->where('intervenido', 1);

        if ($filtroAnalistaNombre) {
            $query->where('analista_intervencion', $filtroAnalistaNombre);
        }

        if ($inicio && $fin) {
            $query->whereRaw('COALESCE(fecha_intervencion, created_at) BETWEEN ? AND ?', [
                $inicio->toDateTimeString(),
                $fin->toDateTimeString()
            ]);
        }

        $query->orderBy('id', 'desc');

        $encabezados = ['ID / RADICADO', 'PLACA', 'SERIAL', 'MÓDULO', 'MARCA', 'MODELO', 'FALLA / CONDICIÓN', 'TRASLADO', 'ANALISTA', 'FECHA INTERVENCIÓN'];
        
        $nombreArchivo = 'Bitacora_Maquinas_Intervenidas_' . Carbon::now('America/Bogota')->format('Y-m-d') . '.csv';

        return $exportService->exportarCsvStream($nombreArchivo, $encabezados, $query, function ($row) {
            $fecha = $row->fecha_intervencion ?? $row->created_at;
            if ($fecha) {
                if (is_string($fecha)) {
                    $fecha = Carbon::parse($fecha);
                }
                $fechaStr = $fecha->setTimezone('America/Bogota')->format('d/m/Y H:i:s');
            } else {
                $fechaStr = '';
            }

            // Buscar traslado de manera robusta usando la función del modelo
            $traslado = \App\Models\InventarioGeneral::buscarTrasladoEnSistema($row->identificador_1 ?: ($row->placa_id ?: ''), $row);

            // Determinar la falla o condición real
            $fallaCondicion = $row->observaciones ?? ($row->estado ?? 'N/A');
            $estadoRaw = strtoupper(trim((string)$row->estado));
            
            // Traducir estado 1 o similares a BAJA
            if (in_array($estadoRaw, ['1', 'BAJA', 'DADO DE BAJA', 'DESCARTE', 'SCRAP'])) {
                $fallaCondicion = 'BAJA';
            }

            // Detectar si es un equipo de garantía
            if (stripos((string)$row->modulo_intervencion, 'portat') !== false || stripos((string)$row->modulo_intervencion, 'garant') !== false) {
                $garantia = $row->garantiasPortatiles->first();
                if ($garantia) {
                    $estAct = strtoupper(trim((string)$garantia->estado_actual_equipo));
                    $gar = strtoupper(trim((string)$garantia->garantia));
                    if (str_contains($estAct, 'GARANT') || str_contains($gar, 'APLICA') || str_contains($gar, 'GARANT')) {
                        $fallaCondicion = 'GARANTÍA';
                    }
                }
            } elseif (str_contains($estadoRaw, 'GARANT')) {
                $fallaCondicion = 'GARANTÍA';
            }

            return [
                $row->id,
                $row->identificador_1 ?: ($row->placa_id ?: 'N/A'),
                $row->identificador_2 ?: ($row->serial ?: 'N/A'),
                $row->modulo_intervencion ?: 'N/A',
                $row->marca ?: 'N/A',
                $row->modelo ?: 'N/A',
                $fallaCondicion,
                $traslado ?: ($row->num_traslado ?: 'N/A'),
                $row->analista_intervencion ?: 'N/A',
                $fechaStr
            ];
        });
    }
}

