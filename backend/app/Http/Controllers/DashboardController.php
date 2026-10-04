<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\GarantiaPortatil;
use App\Models\InventarioGeneral;
use App\Models\SopladoRegistro;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $periodo = strtolower(trim($request->get('periodo', 'todos')));
        $analistaId = $request->get('analista_id');
        $user = auth()->user();

        // Control dinámico de permisos para el Dashboard
        if ($user && !$user->hasRole('admin') && $user->tienePermiso('dashboard.ver_solo_propio')) {
            // Forzar a ver solo sus propios registros si no tiene acceso global
            $analistaId = clone $user->id; 
            // Para asegurarnos de que la consulta busque por el nombre del usuario actual
            // porque en la BD legacy muchas relaciones están por nombre_analista
            $request->merge(['analista_id' => clone $user->id]);
        }

        if ($analistaId === 'todos' || $analistaId === '') {
            $analistaId = null;
        }

        $page = max(1, (int)$request->get('page', 1));

        $data = $this->obtenerDatosMetricas($periodo, $analistaId, $page);
        
        // Solo mostrar selector de analistas si tiene permisos globales o es admin
        if ($user && ($user->hasRole('admin') || $user->tienePermiso('dashboard.metricas_globales'))) {
            $data['analistas'] = Usuario::where('rol', 'analista')->where('activo', true)->orderBy('nombre')->get();
        } else {
            $data['analistas'] = collect(); // Lista vacía para evitar selectores a no autorizados
        }

        if ($request->ajax() || $request->get('ajax') == '1') {
            return response()->json($this->formatearRespuestaJson($data));
        }

        if (auth()->user() && auth()->user()->rol === 'analista') {
            return view('dashboard.analista', $data);
        }

        return view('dashboard.index', $data);
    }

    public function metricas(Request $request)
    {
        $periodo = strtolower(trim($request->get('periodo', 'todos')));
        $analistaId = $request->get('analista_id');
        $user = auth()->user();

        if ($user && !$user->hasRole('admin') && $user->tienePermiso('dashboard.ver_solo_propio')) {
            $analistaId = clone $user->id; 
        }

        if ($analistaId === 'todos' || $analistaId === '') {
            $analistaId = null;
        }
        $page = max(1, (int)$request->get('page', 1));

        $data = $this->obtenerDatosMetricas($periodo, $analistaId, $page);
        return response()->json($this->formatearRespuestaJson($data));
    }

    private function formatearRespuestaJson(array $data): array
    {
        return [
            'status'               => 'success',
            'periodo'              => $data['periodo'],
            'labelPeriodo'         => $data['labelPeriodo'],
            'analista_id'          => $data['analista_id'],
            'nombreAnalista'       => $data['nombreAnalista'],
            'statsInventario'      => $data['statsInventario'],
            'totalIntervenciones'  => $data['totalIntervenciones'],
            'totalEquipos'         => $data['totalEquipos'],
            'totalSoplado'         => $data['totalSoplado'],
            'totalPortatiles'      => $data['totalPortatiles'],
            'porcEq'               => $data['porcEq'],
            'porcSp'               => $data['porcSp'],
            'porcPt'               => $data['porcPt'],
            'totalTraslados'       => $data['totalTraslados'],
            'maquinasIntervenidas' => $data['maquinasIntervenidas'],
            'total_registros'      => $data['total_registros'],
            'total_paginas'        => $data['total_paginas'],
            'pagina_actual'        => $data['pagina_actual'],
            'limite'               => $data['limite'],
        ];
    }

    private function obtenerDatosMetricas(string $periodo, ?int $analistaId = null, int $page = 1): array
    {
        [$periodo, $fechaDesde, $fechaHasta, $labelPeriodo] = $this->resolverRangoFechas($periodo);

        $nombreAnalista = null;
        if ($analistaId) {
            $analista = Usuario::find($analistaId);
            if ($analista) {
                $nombreAnalista = $analista->nombre;
            }
        }

        $countQuery = function($model, $dateField) use ($fechaDesde, $fechaHasta, $nombreAnalista) {
            return $model::when($fechaDesde, fn($q) => $q->where($dateField, '>=', $fechaDesde))
                ->when($fechaHasta, fn($q) => $q->where($dateField, '<=', $fechaHasta))
                ->when($nombreAnalista, fn($q) => $q->where('nombre_analista', $nombreAnalista))
                ->count();
        };

        $totalEquipos = $countQuery(Equipo::class, 'fecha_creacion');
        $totalSoplado = $countQuery(SopladoRegistro::class, 'fecha_creacion');
        
        // garantias_portatiles uses fecha_creacion or created_at in CI4 but let's just use fecha_creacion which is nullable
        $totalPortatiles = $countQuery(GarantiaPortatil::class, 'fecha_creacion'); 
        
        $totalAnalistas = Usuario::where('rol', 'analista')->where('activo', true)->count();
        $totalIntervenciones = $totalEquipos + $totalSoplado + $totalPortatiles;

        // Inventario Stats
        $invQuery = InventarioGeneral::when($fechaDesde, fn($q) => $q->where('fecha_intervencion', '>=', $fechaDesde))
                    ->when($fechaHasta, fn($q) => $q->where('fecha_intervencion', '<=', $fechaHasta))
                    ->when($nombreAnalista, fn($q) => $q->where('analista_intervencion', $nombreAnalista));
        
        $totalCargadosQuery = InventarioGeneral::when($nombreAnalista, fn($q) => $q->where('analista_intervencion', $nombreAnalista));
        $totalCargados = $totalCargadosQuery->count();
        $totalIntervenidos = $invQuery->where('intervenido', 1)->count();

        $statsInventario = [
            'total_cargados' => $totalCargados,
            'total_intervenidos' => $totalIntervenidos,
            'pendientes' => max(0, $totalCargados - $totalIntervenidos),
            'porcentaje' => $totalCargados > 0 ? round(($totalIntervenidos / $totalCargados) * 100, 1) : 0
        ];

        // Pagination
        $limite = 10;
        $paginator = InventarioGeneral::where('intervenido', 1)
                    ->when($fechaDesde, fn($q) => $q->where('fecha_intervencion', '>=', $fechaDesde))
                    ->when($fechaHasta, fn($q) => $q->where('fecha_intervencion', '<=', $fechaHasta))
                    ->when($nombreAnalista, fn($q) => $q->where('analista_intervencion', $nombreAnalista))
                    ->orderBy('fecha_intervencion', 'desc')
                    ->paginate($limite, ['*'], 'page', $page);

        $totalTraslados = InventarioGeneral::whereNotNull('num_traslado')
                            ->where('num_traslado', '!=', '')
                            ->distinct('num_traslado')
                            ->count('num_traslado');

        $porcEq = $totalIntervenciones > 0 ? round(($totalEquipos / $totalIntervenciones) * 100, 1) : 0;
        $porcSp = $totalIntervenciones > 0 ? round(($totalSoplado / $totalIntervenciones) * 100, 1) : 0;
        $porcPt = $totalIntervenciones > 0 ? round(($totalPortatiles / $totalIntervenciones) * 100, 1) : 0;

        return [
            'periodo'              => $periodo,
            'labelPeriodo'         => $labelPeriodo,
            'fechaDesde'           => $fechaDesde,
            'fechaHasta'           => $fechaHasta,
            'analista_id'          => $analistaId,
            'nombreAnalista'       => $nombreAnalista,
            'statsInventario'      => $statsInventario,
            'totalIntervenciones'  => $totalIntervenciones,
            'totalEquipos'         => $totalEquipos,
            'totalSoplado'         => $totalSoplado,
            'totalPortatiles'      => $totalPortatiles,
            'porcEq'               => $porcEq,
            'porcSp'               => $porcSp,
            'porcPt'               => $porcPt,
            'totalAnalistas'       => $totalAnalistas,
            'totalTraslados'       => $totalTraslados,
            'maquinasIntervenidas' => $paginator->items(),
            'total_registros'      => $paginator->total(),
            'total_paginas'        => $paginator->lastPage(),
            'pagina_actual'        => $paginator->currentPage(),
            'limite'               => $paginator->perPage(),
        ];
    }

    private function resolverRangoFechas(string $periodo): array
    {
        $fechaDesde = null;
        $fechaHasta = null;
        $labelPeriodo = 'Histórico Completo';
        $now = Carbon::now();

        switch ($periodo) {
            case 'dia':
            case 'hoy':
                $periodo = 'dia';
                $fechaDesde = $now->copy()->startOfDay()->toDateTimeString();
                $fechaHasta = $now->copy()->endOfDay()->toDateTimeString();
                $labelPeriodo = 'Hoy (' . $now->format('d/m/Y') . ')';
                break;
            case 'semana':
                $fechaDesde = $now->copy()->startOfWeek()->toDateTimeString();
                $fechaHasta = $now->copy()->endOfWeek()->toDateTimeString();
                $labelPeriodo = 'Esta Semana (' . $now->copy()->startOfWeek()->format('d/m') . ' al ' . $now->copy()->endOfWeek()->format('d/m/Y') . ')';
                break;
            case 'mes':
                $fechaDesde = $now->copy()->startOfMonth()->toDateTimeString();
                $fechaHasta = $now->copy()->endOfMonth()->toDateTimeString();
                $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                $labelPeriodo = 'Este Mes (' . $meses[$now->month - 1] . ' ' . $now->year . ')';
                break;
            case 'anio':
            case 'ano':
                $periodo = 'anio';
                $fechaDesde = $now->copy()->startOfYear()->toDateTimeString();
                $fechaHasta = $now->copy()->endOfYear()->toDateTimeString();
                $labelPeriodo = 'Este Año (' . $now->year . ')';
                break;
            case 'todos':
            default:
                $periodo = 'todos';
                $fechaDesde = null;
                $fechaHasta = null;
                $labelPeriodo = 'Histórico Completo';
                break;
        }

        return [$periodo, $fechaDesde, $fechaHasta, $labelPeriodo];
    }
}
