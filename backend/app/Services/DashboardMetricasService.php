<?php

namespace App\Services;

use App\Models\Equipo;
use App\Models\GarantiaPortatil;
use App\Models\InventarioGeneral;
use App\Models\SopladoRegistro;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardMetricasService
{
    private const META_DIARIA_LINEA = 10;
    private const DIAS_POR_PERIODO = ['dia' => 1, 'semana' => 5, 'mes' => 22, 'anio' => 260];

    public function resolverDashboard(Request $request): array
    {
        $user = $request->user();
        $periodo = strtolower(trim($request->get('periodo', 'dia')));
        $page = max(1, (int)$request->get('page', 1));

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

        [$periodoStr, $inicio, $fin, $labelPeriodo] = $this->resolverRangoFechas($periodo);

        // Auto-saneamiento removido para evitar overhead (UPDATE en GET).
        // Se ejecuta por comando Artisan (SanearFechasInventario).

        $kpis = $this->calcularKpis($inicio, $fin, $filtroAnalistaNombre, $esRestringido, $nombreUsuario);
        $matriz = $this->obtenerMatrizIntervenciones($inicio, $fin, $filtroAnalistaNombre, $esRestringido, $nombreUsuario, $page, $user);
        $deltaSemanal = $this->obtenerDeltaSemanal($filtroAnalistaNombre);
        
        // Capacidad por línea técnica
        $metaDiaria = (int) env('DASHBOARD_META_DIARIA_LINEA', self::META_DIARIA_LINEA);
        $diasPeriodo = self::DIAS_POR_PERIODO[$periodoStr] ?? null;
        $construirLinea = function (string $clave, string $nombre, string $icono, int $total) use ($metaDiaria, $diasPeriodo) {
            $capacidad = $diasPeriodo ? $metaDiaria * $diasPeriodo : max($total, 1);
            return [
                'clave'     => $clave,
                'nombre'    => $nombre,
                'icono'     => $icono,
                'total'     => $total,
                'capacidad' => $capacidad,
                'pct'       => (int) min(100, round(($total / max($capacidad, 1)) * 100)),
            ];
        };
        
        $lineas = [
            $construirLinea('cpu', 'Diagnóstico CPU', 'fa-desktop', $kpis['totalCPUs']),
            $construirLinea('soplado', 'Mantenimiento Soplado', 'fa-wind', $kpis['totalSoplado']),
            $construirLinea('portatiles', 'Portátiles', 'fa-laptop', $kpis['totalPortatiles']),
        ];

        return array_merge([
            'periodo'              => $periodoStr,
            'labelPeriodo'         => $labelPeriodo,
            'fechaDesde'           => $inicio ? $inicio->toDateTimeString() : null,
            'fechaHasta'           => $fin ? $fin->toDateTimeString() : null,
            'analista_id'          => $analistaIdFiltro,
            'nombreAnalista'       => $filtroAnalistaNombre,
            'analistas'            => $analistasDisponibles,
            'deltaSemanal'         => $deltaSemanal,
            'lineas'               => $lineas,
        ], $kpis, $matriz);
    }

    public function calcularKpis($inicio, $fin, $filtroAnalistaNombre, $esRestringido, $nombreUsuario): array
    {
        // Equipos
        $qCPUs = Equipo::query();
        if ($esRestringido) { $qCPUs->where('nombre_analista', $nombreUsuario); } 
        elseif ($filtroAnalistaNombre) { $qCPUs->where('nombre_analista', $filtroAnalistaNombre); }
        if ($inicio && $fin) { $qCPUs->whereBetween('fecha_creacion', [$inicio->toDateTimeString(), $fin->toDateTimeString()]); }
        $totalCPUs = $qCPUs->count();

        // Soplado
        $qSoplado = SopladoRegistro::query();
        if ($esRestringido) { $qSoplado->where('nombre_analista', $nombreUsuario); } 
        elseif ($filtroAnalistaNombre) { $qSoplado->where('nombre_analista', $filtroAnalistaNombre); }
        if ($inicio && $fin) { $qSoplado->whereBetween('created_at', [$inicio->toDateTimeString(), $fin->toDateTimeString()]); }
        $totalSoplado = $qSoplado->count();

        // Portatiles
        $qPortatiles = GarantiaPortatil::query();
        if ($esRestringido) { $qPortatiles->where('nombre_analista', $nombreUsuario); } 
        elseif ($filtroAnalistaNombre) { $qPortatiles->where('nombre_analista', $filtroAnalistaNombre); }
        if ($inicio && $fin) { $qPortatiles->whereBetween('created_at', [$inicio->toDateTimeString(), $fin->toDateTimeString()]); }
        $totalPortatiles = $qPortatiles->count();

        $totalIntervenciones = $totalCPUs + $totalSoplado + $totalPortatiles;

        // Tarjetas Inventario
        $cargadosQuery = InventarioGeneral::query();
        $cargadosGlobalQuery = InventarioGeneral::query();
        $intervenidosQuery = InventarioGeneral::where('intervenido', 1);

        if ($inicio && $fin) {
            $intervenidosQuery->whereRaw('COALESCE(fecha_intervencion, created_at) BETWEEN ? AND ?', [$inicio->toDateTimeString(), $fin->toDateTimeString()]);
            $cargadosQuery->whereBetween('created_at', [$inicio->toDateTimeString(), $fin->toDateTimeString()]);
        }

        $analistaFiltroInventario = $esRestringido ? $nombreUsuario : $filtroAnalistaNombre;
        if ($analistaFiltroInventario) {
            $cargadosQuery->where(function ($q) use ($analistaFiltroInventario) {
                $q->where('analista_intervencion', $analistaFiltroInventario)->orWhere('usuario_cargue', $analistaFiltroInventario);
            });
            $cargadosGlobalQuery->where(function ($q) use ($analistaFiltroInventario) {
                $q->where('analista_intervencion', $analistaFiltroInventario)->orWhere('usuario_cargue', $analistaFiltroInventario);
            });
            $intervenidosQuery->where('analista_intervencion', $analistaFiltroInventario);
        }

        $totalCargados = $cargadosQuery->count();
        $totalCargadosGlobal = $cargadosGlobalQuery->count();
        $totalIntervenidos = $intervenidosQuery->count();

        $qPendBaja = InventarioGeneral::query()->where(function ($q) {
            $q->where(function ($p) {
                $p->where('intervenido', 0)->orWhereNull('intervenido');
            })->orWhereRaw('LOWER(estado) LIKE ?', ['%baja%']);
        });
        if ($analistaFiltroInventario) {
            $qPendBaja->where(function ($q) use ($analistaFiltroInventario) {
                $q->where('analista_intervencion', $analistaFiltroInventario)->orWhere('usuario_cargue', $analistaFiltroInventario);
            });
        }
        $totalPendientesBaja = $qPendBaja->count();

        $trasladosQuery = InventarioGeneral::whereNotNull('num_traslado')->where('num_traslado', '!=', '');
        if ($analistaFiltroInventario) {
            $trasladosQuery->where('analista_intervencion', $analistaFiltroInventario);
        }
        $totalTraslados = $trasladosQuery->distinct('num_traslado')->count('num_traslado');

        $qBaja = InventarioGeneral::whereRaw('LOWER(estado) LIKE ?', ['%baja%']);
        if ($analistaFiltroInventario) {
            $qBaja->where('analista_intervencion', $analistaFiltroInventario);
        }
        $totalBaja = $qBaja->count();

        $hoyBogota = Carbon::now('America/Bogota');
        $hoyIni = $hoyBogota->copy()->startOfDay()->toDateTimeString();
        $hoyFin = $hoyBogota->copy()->endOfDay()->toDateTimeString();

        $qEquipos = DB::table('equipos')
            ->selectRaw('LOWER(TRIM(nombre_analista)) as analista')
            ->whereBetween('fecha_creacion', [$hoyIni, $hoyFin])
            ->whereNotNull('nombre_analista')
            ->where('nombre_analista', '!=', '');
            
        $qSoplado = DB::table('soplado_registros')
            ->selectRaw('LOWER(TRIM(nombre_analista)) as analista')
            ->whereBetween('created_at', [$hoyIni, $hoyFin])
            ->whereNotNull('nombre_analista')
            ->where('nombre_analista', '!=', '');

        $qPortatiles = DB::table('garantias_portatiles')
            ->selectRaw('LOWER(TRIM(nombre_analista)) as analista')
            ->whereBetween('created_at', [$hoyIni, $hoyFin])
            ->whereNotNull('nombre_analista')
            ->where('nombre_analista', '!=', '');

        $qInventario = DB::table('inventario_general')
            ->selectRaw('LOWER(TRIM(analista_intervencion)) as analista')
            ->where('intervenido', 1)
            ->whereRaw('COALESCE(fecha_intervencion, created_at) BETWEEN ? AND ?', [$hoyIni, $hoyFin])
            ->whereNotNull('analista_intervencion')
            ->where('analista_intervencion', '!=', '');

        if ($esRestringido) {
            $qEquipos->where('nombre_analista', $nombreUsuario);
            $qSoplado->where('nombre_analista', $nombreUsuario);
            $qPortatiles->where('nombre_analista', $nombreUsuario);
            $qInventario->where('analista_intervencion', $nombreUsuario);
        }

        $subquery = $qEquipos->union($qSoplado)->union($qPortatiles)->union($qInventario);
        $analistasActivosHoy = DB::query()->fromSub($subquery, 'analistas')->count();

        return [
            'totalEquipos'         => $totalCPUs,
            'totalCPUs'            => $totalCPUs,
            'totalSoplado'         => $totalSoplado,
            'totalPortatiles'      => $totalPortatiles,
            'totalIntervenciones'  => $totalIntervenciones,
            'porcEq'               => $totalIntervenciones > 0 ? round(($totalCPUs / $totalIntervenciones) * 100, 1) : 0,
            'porcSp'               => $totalIntervenciones > 0 ? round(($totalSoplado / $totalIntervenciones) * 100, 1) : 0,
            'porcPt'               => $totalIntervenciones > 0 ? round(($totalPortatiles / $totalIntervenciones) * 100, 1) : 0,
            'totalTraslados'       => $totalTraslados,
            'totalAnalistas'       => Usuario::where('rol', 'analista')->where('activo', true)->count(),
            'analistasActivosHoy'  => $analistasActivosHoy,
            'totalBaja'            => $totalBaja,
            'statsInventario'      => [
                'total_cargados'        => $totalCargados,
                'total_cargados_global' => $totalCargadosGlobal,
                'total_intervenidos'    => $totalIntervenidos,
                'pendientes'            => $totalPendientesBaja,
                'porcentaje'            => $totalCargadosGlobal > 0 ? min(100, round(($totalIntervenidos / $totalCargadosGlobal) * 100, 1)) : 0,
            ],
        ];
    }

    public function obtenerDeltaSemanal($filtroAnalistaNombre): float
    {
        $hoyBogota = Carbon::now('America/Bogota');
        $contarSemana = function (Carbon $desde, Carbon $hasta) use ($filtroAnalistaNombre) {
            $q = InventarioGeneral::where('intervenido', 1)
                ->whereRaw('COALESCE(fecha_intervencion, created_at) BETWEEN ? AND ?', [
                    $desde->toDateTimeString(), $hasta->toDateTimeString()
                ]);
            if ($filtroAnalistaNombre) {
                $q->where('analista_intervencion', $filtroAnalistaNombre);
            }
            return $q->count();
        };
        $semanaActual = $contarSemana($hoyBogota->copy()->startOfWeek(), $hoyBogota->copy()->endOfWeek());
        $semanaPrevia = $contarSemana($hoyBogota->copy()->subWeek()->startOfWeek(), $hoyBogota->copy()->subWeek()->endOfWeek());
        
        return $semanaPrevia > 0 ? round((($semanaActual - $semanaPrevia) / $semanaPrevia) * 100, 1) : ($semanaActual > 0 ? 100.0 : 0.0);
    }

    public function obtenerMatrizIntervenciones($inicio, $fin, $filtroAnalistaNombre, $esRestringido, $nombreUsuario, $page, $user): array
    {
        $qMatriz = InventarioGeneral::query()->where('intervenido', 1);
        if ($esRestringido) { $qMatriz->where('analista_intervencion', $nombreUsuario); } 
        elseif ($filtroAnalistaNombre) { $qMatriz->where('analista_intervencion', $filtroAnalistaNombre); }
        if ($inicio && $fin) {
            $qMatriz->whereRaw('COALESCE(fecha_intervencion, created_at) BETWEEN ? AND ?', [$inicio->toDateTimeString(), $fin->toDateTimeString()]);
        }
        $limite = 10;
        $maquinasIntervenidas = $qMatriz->orderBy('id', 'desc')->paginate($limite, ['*'], 'page', $page)->withQueryString();
        $maquinasIntervenidas->setCollection($maquinasIntervenidas->getCollection()->map(fn ($m) => $this->mapearIntervencion($m, $user)));

        return [
            'maquinasIntervenidas' => $maquinasIntervenidas,
            'total_registros'      => $maquinasIntervenidas->total(),
            'total_paginas'        => $maquinasIntervenidas->lastPage(),
            'pagina_actual'        => $maquinasIntervenidas->currentPage(),
            'limite'               => $maquinasIntervenidas->perPage(),
        ];
    }

    private function mapearIntervencion(InventarioGeneral $m, $user = null): array
    {
        $fecha = $m->fecha_intervencion ?? $m->created_at;
        $mod = strtolower((string) $m->modulo_intervencion);

        if (str_contains($mod, 'portat') || str_contains($mod, 'laptop')) {
            $modulo = 'portatiles'; $moduloLabel = 'Portátil'; $ruta = 'portatiles.index'; $permiso = 'portatiles.ver_bitacora';
        } elseif (str_contains($mod, 'sopla') || str_contains($mod, 'limpie')) {
            $modulo = 'soplado'; $moduloLabel = 'Soplado'; $ruta = 'soplado.index'; $permiso = 'soplado.ver_bitacora';
        } else {
            $modulo = 'cpu'; $moduloLabel = 'Diagnóstico CPU'; $ruta = 'equipos.index'; $permiso = 'cpus.ver_bitacora';
        }

        $estadoRaw = strtolower((string) $m->estado);
        if (str_contains($estadoRaw, 'baja')) { [$estadoLabel, $estadoTono] = ['Baja', 'amber']; } 
        elseif (str_contains($estadoRaw, 'pend')) { [$estadoLabel, $estadoTono] = ['Pendiente', 'amber']; } 
        else { [$estadoLabel, $estadoTono] = ['Completado', 'teal']; }

        $url = ($user && $user->can($permiso)) ? route($ruta) : null;

        return array_merge($m->attributesToArray(), [
            'placa'              => $m->identificador_1 ?: ($m->placa_id ?: '—'),
            'serial'             => $m->identificador_2 ?: ($m->serial ?: '—'),
            'fecha_intervencion' => $fecha ? Carbon::parse($fecha)->format('Y-m-d H:i:s') : null,
            'hora'               => $fecha ? Carbon::parse($fecha)->format('H:i') : '--:--',
            'fecha_corta'        => $fecha ? Carbon::parse($fecha)->format('d/m/Y') : null,
            'modulo'             => $modulo,
            'modulo_label'       => $moduloLabel,
            'estado_label'       => $estadoLabel,
            'estado_tono'        => $estadoTono,
            'detalle_url'        => $url,
        ]);
    }

    public function resolverRangoFechas(string $periodo): array
    {
        $now = Carbon::now('America/Bogota');
        $inicio = null; $fin = null; $labelPeriodo = 'Histórico Completo';

        switch ($periodo) {
            case 'dia': case 'hoy':
                $periodo = 'dia'; $inicio = $now->copy()->startOfDay(); $fin = $now->copy()->endOfDay();
                $labelPeriodo = 'Hoy (' . $now->format('d/m/Y') . ')'; break;
            case 'semana':
                $periodo = 'semana'; $inicio = $now->copy()->startOfWeek(); $fin = $now->copy()->endOfWeek();
                $labelPeriodo = 'Esta Semana (' . $inicio->format('d/m') . ' al ' . $fin->format('d/m/Y') . ')'; break;
            case 'mes':
                $periodo = 'mes'; $inicio = $now->copy()->startOfMonth(); $fin = $now->copy()->endOfMonth();
                $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                $labelPeriodo = 'Este Mes (' . $meses[$now->month - 1] . ' ' . $now->year . ')'; break;
            case 'anio': case 'ano':
                $periodo = 'anio'; $inicio = $now->copy()->startOfYear(); $fin = $now->copy()->endOfYear();
                $labelPeriodo = 'Este Año (' . $now->year . ')'; break;
            case 'todos': default:
                $periodo = 'todos'; $inicio = null; $fin = null; $labelPeriodo = 'Histórico Completo'; break;
        }
        return [$periodo, $inicio, $fin, $labelPeriodo];
    }
}
