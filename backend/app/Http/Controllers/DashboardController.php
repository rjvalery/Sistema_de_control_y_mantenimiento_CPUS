<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\GarantiaPortatil;
use App\Models\InventarioGeneral;
use App\Models\SopladoRegistro;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->resolverDatosDashboard($request);

        if ($request->ajax() || $request->get('ajax') == '1') {
            return response()->json($this->formatearRespuestaJson($data));
        }

        $user = Auth::user();
        if ($user && $user->rol === 'analista') {
            return view('dashboard.analista', $data);
        }

        return view('dashboard.index', $data);
    }

    public function metricas(Request $request)
    {
        $data = $this->resolverDatosDashboard($request);

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
        ];
    }

    private function resolverDatosDashboard(Request $request): array
    {
        $user = auth()->user();
        $periodo = strtolower(trim($request->get('periodo', 'dia')));
        $page = max(1, (int)$request->get('page', 1));

        // 1. Resolución Dinámica de Usuario y RBAC (Para CUALQUIER usuario o administrador)
        $esAdmin = $user && $user->hasRole('admin');
        $esRestringido = !$esAdmin && ($user && ($user->can('dashboard.ver_solo_propio') || $user->hasRole('analista') || $user->rol === 'analista'));
        $nombreUsuario = $user ? ($user->name ?? $user->nombre) : null;
        $idUsuario = $user ? $user->id : null;

        $filtroAnalistaNombre = null;
        $analistaIdFiltro = null;
        $analistasDisponibles = collect();

        if ($esRestringido) {
            $filtroAnalistaNombre = $nombreUsuario;
            $analistaIdFiltro = $idUsuario;
        } else {
            $analistasDisponibles = Usuario::where('rol', 'analista')->where('activo', true)->orderBy('nombre')->get();
            $paramAnalista = $request->get('analista_id');
            if ($paramAnalista && $paramAnalista !== 'todos') {
                $analistaObj = Usuario::find($paramAnalista);
                if ($analistaObj) {
                    $filtroAnalistaNombre = $analistaObj->name ?? $analistaObj->nombre;
                    $analistaIdFiltro = $analistaObj->id;
                }
            }
        }

        // 2. Rango de Fechas Consistente en Zona Horaria ('America/Bogota')
        [$periodo, $inicio, $fin, $labelPeriodo] = $this->resolverRangoFechas($periodo);

        // 3. Auto-saneamiento preventivo de fecha_intervencion en inventario_general
        InventarioGeneral::where('intervenido', 1)
            ->whereNull('fecha_intervencion')
            ->whereNotNull('created_at')
            ->update(['fecha_intervencion' => DB::raw('created_at')]);

        // 4. Mapeo de Consultas con Columnas Reales Blindadas

        // Diagnóstico CPUs:
        $colAnalistaCPUs = $this->obtenerColumnaExistente('equipos', ['nombre_analista', 'analista', 'user_id']);
        $colFechaCPUs = $this->obtenerColumnaExistente('equipos', ['fecha_creacion', 'created_at', 'timestamp_registro']);
        $qCPUs = Equipo::query();
        if ($esRestringido) {
            $qCPUs->where($colAnalistaCPUs, $nombreUsuario);
        } elseif ($filtroAnalistaNombre) {
            $qCPUs->where($colAnalistaCPUs, $filtroAnalistaNombre);
        }
        if ($inicio && $fin) {
            $qCPUs->whereBetween($colFechaCPUs, [$inicio->toDateTimeString(), $fin->toDateTimeString()]);
        }
        $totalCPUs = $qCPUs->count();
        $totalEquipos = $totalCPUs;

        // Mantenimiento Soplado (Columna real: created_at / fecha_registro, NO fecha_creacion):
        $colAnalistaSoplado = $this->obtenerColumnaExistente('soplado_registros', ['nombre_analista', 'analista', 'tecnico']);
        $colFechaSoplado = $this->obtenerColumnaExistente('soplado_registros', ['created_at', 'fecha_registro', 'fecha']);
        $qSoplado = SopladoRegistro::query();
        if ($esRestringido) {
            $qSoplado->where($colAnalistaSoplado, $nombreUsuario);
        } elseif ($filtroAnalistaNombre) {
            $qSoplado->where($colAnalistaSoplado, $filtroAnalistaNombre);
        }
        if ($inicio && $fin) {
            $qSoplado->whereBetween($colFechaSoplado, [$inicio->toDateTimeString(), $fin->toDateTimeString()]);
        }
        $totalSoplado = $qSoplado->count();

        // Portátiles / Garantías (Columna real: created_at / fecha_creacion):
        $colAnalistaPortatiles = $this->obtenerColumnaExistente('garantias_portatiles', ['nombre_analista', 'analista']);
        $colFechaPortatiles = $this->obtenerColumnaExistente('garantias_portatiles', ['created_at', 'fecha_creacion', 'fecha']);
        $qPortatiles = GarantiaPortatil::query();
        if ($esRestringido) {
            $qPortatiles->where($colAnalistaPortatiles, $nombreUsuario);
        } elseif ($filtroAnalistaNombre) {
            $qPortatiles->where($colAnalistaPortatiles, $filtroAnalistaNombre);
        }
        if ($inicio && $fin) {
            $qPortatiles->whereBetween($colFechaPortatiles, [$inicio->toDateTimeString(), $fin->toDateTimeString()]);
        }
        $totalPortatiles = $qPortatiles->count();

        $totalIntervenciones = $totalEquipos + $totalSoplado + $totalPortatiles;

        // Matriz Inventario General:
        $colAnalistaMatriz = $this->obtenerColumnaExistente('inventario_general', ['analista_intervencion']);
        $qMatriz = InventarioGeneral::query()->where('intervenido', 1);
        if ($esRestringido) {
            $qMatriz->where($colAnalistaMatriz, $nombreUsuario);
        } elseif ($filtroAnalistaNombre) {
            $qMatriz->where($colAnalistaMatriz, $filtroAnalistaNombre);
        }
        if ($inicio && $fin) {
            $qMatriz->whereRaw('COALESCE(fecha_intervencion, created_at) BETWEEN ? AND ?', [
                $inicio->toDateTimeString(),
                $fin->toDateTimeString()
            ]);
        }
        $limite = 10;
        $maquinasIntervenidas = (clone $qMatriz)
            ->orderBy('id', 'desc')
            ->paginate($limite, ['*'], 'page', $page)
            ->withQueryString();

        // 5. Tarjetas de Inventario General y Estadísticas
        $cargadosQuery = InventarioGeneral::query();
        $intervenidosQuery = InventarioGeneral::where('intervenido', 1);

        if ($inicio && $fin) {
            $intervenidosQuery->whereRaw('COALESCE(fecha_intervencion, created_at) BETWEEN ? AND ?', [
                $inicio->toDateTimeString(),
                $fin->toDateTimeString()
            ]);
        }

        $analistaFiltroInventario = $esRestringido ? $nombreUsuario : $filtroAnalistaNombre;
        if ($analistaFiltroInventario) {
            $cargadosQuery->where(function($q) use ($analistaFiltroInventario, $colAnalistaMatriz) {
                $q->where($colAnalistaMatriz, $analistaFiltroInventario)
                  ->orWhere('usuario_cargue', $analistaFiltroInventario);
            });
            $intervenidosQuery->where($colAnalistaMatriz, $analistaFiltroInventario);
        }

        $totalCargados = $cargadosQuery->count();
        $totalIntervenidos = $intervenidosQuery->count();

        $statsInventario = [
            'total_cargados'     => $totalCargados,
            'total_intervenidos' => $totalIntervenidos,
            'pendientes'         => max(0, $totalCargados - $totalIntervenidos),
            'porcentaje'         => $totalCargados > 0 ? round(($totalIntervenidos / $totalCargados) * 100, 1) : 0
        ];

        // Conteo de Traslados
        $trasladosQuery = InventarioGeneral::whereNotNull('num_traslado')
                            ->where('num_traslado', '!=', '');
        if ($analistaFiltroInventario) {
            $trasladosQuery->where($colAnalistaMatriz, $analistaFiltroInventario);
        }
        $totalTraslados = $trasladosQuery->distinct('num_traslado')->count('num_traslado');

        $totalAnalistas = Usuario::where('rol', 'analista')->where('activo', true)->count();
        $porcEq = $totalIntervenciones > 0 ? round(($totalEquipos / $totalIntervenciones) * 100, 1) : 0;
        $porcSp = $totalIntervenciones > 0 ? round(($totalSoplado / $totalIntervenciones) * 100, 1) : 0;
        $porcPt = $totalIntervenciones > 0 ? round(($totalPortatiles / $totalIntervenciones) * 100, 1) : 0;

        return [
            'periodo'              => $periodo,
            'labelPeriodo'         => $labelPeriodo,
            'fechaDesde'           => $inicio ? $inicio->toDateTimeString() : null,
            'fechaHasta'           => $fin ? $fin->toDateTimeString() : null,
            'analista_id'          => $analistaIdFiltro,
            'nombreAnalista'       => $analistaFiltroInventario,
            'analistas'            => $analistasDisponibles,
            'statsInventario'      => $statsInventario,
            'totalIntervenciones'  => $totalIntervenciones,
            'totalEquipos'         => $totalEquipos,
            'totalCPUs'            => $totalCPUs,
            'totalSoplado'         => $totalSoplado,
            'totalPortatiles'      => $totalPortatiles,
            'porcEq'               => $porcEq,
            'porcSp'               => $porcSp,
            'porcPt'               => $porcPt,
            'totalAnalistas'       => $totalAnalistas,
            'totalTraslados'       => $totalTraslados,
            'maquinasIntervenidas' => $maquinasIntervenidas,
            'total_registros'      => $maquinasIntervenidas->total(),
            'total_paginas'        => $maquinasIntervenidas->lastPage(),
            'pagina_actual'        => $maquinasIntervenidas->currentPage(),
            'limite'               => $maquinasIntervenidas->perPage(),
        ];
    }

    /**
     * Resuelve dinámicamente la primera columna existente en la tabla física de MySQL.
     * Previene cualquier error SQLSTATE[42S22] (Unknown column).
     */
    private function obtenerColumnaExistente(string $tabla, array $candidatos): string
    {
        static $columnasPorTabla = [];
        if (!isset($columnasPorTabla[$tabla])) {
            try {
                $columnasPorTabla[$tabla] = Schema::getColumnListing($tabla);
            } catch (\Throwable $e) {
                \Log::warning("Error al listar columnas de la tabla {$tabla}: " . $e->getMessage());
                $columnasPorTabla[$tabla] = [];
            }
        }

        foreach ($candidatos as $candidato) {
            if (in_array($candidato, $columnasPorTabla[$tabla], true)) {
                return $candidato;
            }
        }

        return $candidatos[0];
    }

    private function resolverRangoFechas(string $periodo): array
    {
        $timezone = 'America/Bogota';
        $now = Carbon::now($timezone);
        $inicio = null;
        $fin = null;
        $labelPeriodo = 'Histórico Completo';

        switch ($periodo) {
            case 'dia':
            case 'hoy':
                $periodo = 'dia';
                $inicio = $now->copy()->startOfDay();
                $fin    = $now->copy()->endOfDay();
                $labelPeriodo = 'Hoy (' . $now->format('d/m/Y') . ')';
                break;
            case 'semana':
                $periodo = 'semana';
                $inicio = $now->copy()->startOfWeek();
                $fin    = $now->copy()->endOfWeek();
                $labelPeriodo = 'Esta Semana (' . $now->copy()->startOfWeek()->format('d/m') . ' al ' . $now->copy()->endOfWeek()->format('d/m/Y') . ')';
                break;
            case 'mes':
                $periodo = 'mes';
                $inicio = $now->copy()->startOfMonth();
                $fin    = $now->copy()->endOfMonth();
                $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                $labelPeriodo = 'Este Mes (' . $meses[$now->month - 1] . ' ' . $now->year . ')';
                break;
            case 'anio':
            case 'ano':
                $periodo = 'anio';
                $inicio = $now->copy()->startOfYear();
                $fin    = $now->copy()->endOfYear();
                $labelPeriodo = 'Este Año (' . $now->year . ')';
                break;
            case 'todos':
            default:
                $periodo = 'todos';
                $inicio = null;
                $fin = null;
                $labelPeriodo = 'Histórico Completo';
                break;
        }

        return [$periodo, $inicio, $fin, $labelPeriodo];
    }
}

