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

    public function index(Request $request)
    {
        $data = $this->resolverDatosDashboard($request);

        if ($request->ajax() || $request->get('ajax') == '1') {
            return response()->json($this->formatearRespuestaJson($data));
        }

        // Analistas y administradores comparten la vista; el RBAC filtra los datos en resolverDatosDashboard()
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
            'totalAnalistas'       => $data['totalAnalistas'],
            'analistasActivosHoy'  => $data['analistasActivosHoy'],
            'deltaSemanal'         => $data['deltaSemanal'],
            'totalBaja'            => $data['totalBaja'],
            'lineas'               => $data['lineas'],
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

        // Filas del cronograma con hora/estado/URL ya resueltos (conserva las claves originales del modelo)
        $maquinasIntervenidas->setCollection(
            $maquinasIntervenidas->getCollection()->map(fn ($m) => $this->mapearIntervencion($m, $user))
        );

        // 5. Tarjetas de Inventario General y Estadísticas
        $cargadosQuery = InventarioGeneral::query();        // Ingresadas en el período (KPI 1)
        $cargadosGlobalQuery = InventarioGeneral::query();  // Inventario acumulado (base de efectividad)
        $intervenidosQuery = InventarioGeneral::where('intervenido', 1);

        if ($inicio && $fin) {
            $intervenidosQuery->whereRaw('COALESCE(fecha_intervencion, created_at) BETWEEN ? AND ?', [
                $inicio->toDateTimeString(),
                $fin->toDateTimeString()
            ]);
            $cargadosQuery->whereBetween('created_at', [
                $inicio->toDateTimeString(),
                $fin->toDateTimeString()
            ]);
        }

        $analistaFiltroInventario = $esRestringido ? $nombreUsuario : $filtroAnalistaNombre;
        $filtroCargados = function ($q) use ($analistaFiltroInventario, $colAnalistaMatriz) {
            $q->where($colAnalistaMatriz, $analistaFiltroInventario)
              ->orWhere('usuario_cargue', $analistaFiltroInventario);
        };
        if ($analistaFiltroInventario) {
            $cargadosQuery->where($filtroCargados);
            $cargadosGlobalQuery->where($filtroCargados);
            $intervenidosQuery->where($colAnalistaMatriz, $analistaFiltroInventario);
        }

        $totalCargados = $cargadosQuery->count();
        $totalCargadosGlobal = $cargadosGlobalQuery->count();
        $totalIntervenidos = $intervenidosQuery->count();

        // Pendientes de diagnóstico (no intervenidos) o con estado 'Baja' — sin duplicar equipos
        $tieneEstado = Schema::hasColumn('inventario_general', 'estado');
        $qPendBaja = InventarioGeneral::query()->where(function ($q) use ($tieneEstado) {
            $q->where(function ($p) {
                $p->where('intervenido', 0)->orWhereNull('intervenido');
            });
            if ($tieneEstado) {
                $q->orWhereRaw('LOWER(estado) LIKE ?', ['%baja%']);
            }
        });
        if ($analistaFiltroInventario) {
            $qPendBaja->where(function ($q) use ($analistaFiltroInventario, $colAnalistaMatriz) {
                $q->where($colAnalistaMatriz, $analistaFiltroInventario)
                  ->orWhere('usuario_cargue', $analistaFiltroInventario);
            });
        }
        $totalPendientesBaja = $qPendBaja->count();

        $statsInventario = [
            'total_cargados'        => $totalCargados,
            'total_cargados_global' => $totalCargadosGlobal,
            'total_intervenidos'    => $totalIntervenidos,
            'pendientes'            => $totalPendientesBaja,
            // Efectividad = intervenidas del período / total cargadas * 100
            'porcentaje'            => $totalCargadosGlobal > 0
                ? min(100, round(($totalIntervenidos / $totalCargadosGlobal) * 100, 1))
                : 0,
        ];

        // Conteo de Traslados
        $trasladosQuery = InventarioGeneral::whereNotNull('num_traslado')
                            ->where('num_traslado', '!=', '');
        if ($analistaFiltroInventario) {
            $trasladosQuery->where($colAnalistaMatriz, $analistaFiltroInventario);
        }
        $totalTraslados = $trasladosQuery->distinct('num_traslado')->count('num_traslado');

        $totalAnalistas = Usuario::where('rol', 'analista')->where('activo', true)->count();

        // Analistas con actividad hoy (turno actual): inventario + CPUs + soplado + portátiles
        $hoyBogota = Carbon::now('America/Bogota');
        $hoyIni = $hoyBogota->copy()->startOfDay()->toDateTimeString();
        $hoyFin = $hoyBogota->copy()->endOfDay()->toDateTimeString();

        $nombresActivos = collect();
        $recolectar = function ($query, string $colNombre) use (&$nombresActivos, $esRestringido, $nombreUsuario) {
            $query->whereNotNull($colNombre)->where($colNombre, '!=', '');
            if ($esRestringido) {
                $query->where($colNombre, $nombreUsuario);
            }
            $nombresActivos = $nombresActivos->merge($query->distinct()->pluck($colNombre));
        };

        $recolectar(
            InventarioGeneral::where('intervenido', 1)
                ->whereRaw('COALESCE(fecha_intervencion, created_at) BETWEEN ? AND ?', [$hoyIni, $hoyFin]),
            $colAnalistaMatriz
        );
        $recolectar(Equipo::whereBetween($colFechaCPUs, [$hoyIni, $hoyFin]), $colAnalistaCPUs);
        $recolectar(SopladoRegistro::whereBetween($colFechaSoplado, [$hoyIni, $hoyFin]), $colAnalistaSoplado);
        $recolectar(GarantiaPortatil::whereBetween($colFechaPortatiles, [$hoyIni, $hoyFin]), $colAnalistaPortatiles);

        $analistasActivosHoy = $nombresActivos
            ->map(fn ($n) => mb_strtolower(trim((string) $n)))
            ->filter()
            ->unique()
            ->count();

        // Variación de intervenciones: semana actual vs semana anterior
        $contarSemana = function (Carbon $desde, Carbon $hasta) use ($colAnalistaMatriz, $analistaFiltroInventario) {
            $q = InventarioGeneral::where('intervenido', 1)
                ->whereRaw('COALESCE(fecha_intervencion, created_at) BETWEEN ? AND ?', [
                    $desde->toDateTimeString(),
                    $hasta->toDateTimeString(),
                ]);
            if ($analistaFiltroInventario) {
                $q->where($colAnalistaMatriz, $analistaFiltroInventario);
            }
            return $q->count();
        };
        $semanaActual = $contarSemana($hoyBogota->copy()->startOfWeek(), $hoyBogota->copy()->endOfWeek());
        $semanaPrevia = $contarSemana(
            $hoyBogota->copy()->subWeek()->startOfWeek(),
            $hoyBogota->copy()->subWeek()->endOfWeek()
        );
        if ($semanaPrevia > 0) {
            $deltaSemanal = round((($semanaActual - $semanaPrevia) / $semanaPrevia) * 100, 1);
        } else {
            $deltaSemanal = $semanaActual > 0 ? 100.0 : 0.0;
        }

        // Equipos dados de baja (si la columna existe)
        $totalBaja = 0;
        if (Schema::hasColumn('inventario_general', 'estado')) {
            $qBaja = InventarioGeneral::whereRaw('LOWER(estado) LIKE ?', ['%baja%']);
            if ($analistaFiltroInventario) {
                $qBaja->where($colAnalistaMatriz, $analistaFiltroInventario);
            }
            $totalBaja = $qBaja->count();
        }

        // Capacidad por línea técnica (meta diaria x días hábiles del período)
        $metaDiaria = (int) env('DASHBOARD_META_DIARIA_LINEA', self::META_DIARIA_LINEA);
        $diasPeriodo = self::DIAS_POR_PERIODO[$periodo] ?? null;
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
            $construirLinea('cpu', 'Diagnóstico CPU', 'fa-desktop', $totalCPUs),
            $construirLinea('soplado', 'Mantenimiento Soplado', 'fa-wind', $totalSoplado),
            $construirLinea('portatiles', 'Portátiles', 'fa-laptop', $totalPortatiles),
        ];

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
            'analistasActivosHoy'  => $analistasActivosHoy,
            'deltaSemanal'         => $deltaSemanal,
            'totalBaja'            => $totalBaja,
            'lineas'               => $lineas,
        ];
    }

    /**
     * Normaliza un registro de inventario intervenido para el cronograma del dashboard.
     * Conserva los atributos originales y agrega hora, módulo, estado y URL de detalle.
     */
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
        if (str_contains($estadoRaw, 'baja')) {
            [$estadoLabel, $estadoTono] = ['Baja', 'amber'];
        } elseif (str_contains($estadoRaw, 'pend')) {
            [$estadoLabel, $estadoTono] = ['Pendiente', 'amber'];
        } else {
            [$estadoLabel, $estadoTono] = ['Completado', 'teal'];
        }

        $url = ($user && $user->can($permiso)) ? route($ruta) : null;

        return array_merge($m->attributesToArray(), [
            'placa'              => $m->identificador_1 ?: ($m->placa_id ?: '—'),
            'serial'             => $m->identificador_2 ?: ($m->serial ?: '—'),
            'fecha_intervencion' => $fecha ? $fecha->format('Y-m-d H:i:s') : null,
            'hora'               => $fecha ? Carbon::parse($fecha)->format('H:i') : '--:--',
            'fecha_corta'        => $fecha ? $fecha->format('d/m/Y') : null,
            'modulo'             => $modulo,
            'modulo_label'       => $moduloLabel,
            'estado_label'       => $estadoLabel,
            'estado_tono'        => $estadoTono,
            'detalle_url'        => $url,
        ]);
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

        [$periodo, $inicio, $fin] = $this->resolverRangoFechas($periodo);

        $query = InventarioGeneral::query()->where('intervenido', 1);

        $colAnalistaMatriz = $this->obtenerColumnaExistente('inventario_general', ['analista_intervencion']);
        if ($filtroAnalistaNombre) {
            $query->where($colAnalistaMatriz, $filtroAnalistaNombre);
        }

        if ($inicio && $fin) {
            $query->whereRaw('COALESCE(fecha_intervencion, created_at) BETWEEN ? AND ?', [
                $inicio->toDateTimeString(),
                $fin->toDateTimeString()
            ]);
        }

        $query->orderBy('id', 'desc');

        $encabezados = ['ID / RADICADO', 'PLACA', 'SERIAL', 'MÓDULO', 'MARCA', 'MODELO', 'FALLA / CONDICIÓN', 'ANALISTA', 'FECHA INTERVENCIÓN'];
        
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

            return [
                $row->id,
                $row->identificador_1 ?: ($row->placa_id ?: 'N/A'),
                $row->identificador_2 ?: ($row->serial ?: 'N/A'),
                $row->modulo_intervencion ?: 'N/A',
                $row->marca ?: 'N/A',
                $row->modelo ?: 'N/A',
                $row->observaciones ?? ($row->estado ?? 'N/A'),
                $row->analista_intervencion ?: 'N/A',
                $fechaStr
            ];
        });
    }
}

