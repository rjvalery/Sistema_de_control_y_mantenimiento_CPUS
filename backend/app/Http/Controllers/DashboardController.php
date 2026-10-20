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

        $query = InventarioGeneral::query()->with(['garantiasPortatiles', 'equipos'])->where('intervenido', 1);

        if ($filtroAnalistaNombre) {
            $query->where('analista_intervencion', $filtroAnalistaNombre);
        }

        $paramModulo = strtolower(trim((string)$request->get('modulo', '')));
        if ($paramModulo && $paramModulo !== 'todos') {
            if (str_contains($paramModulo, 'cpu')) {
                $query->where(function($q) {
                    $q->where('modulo_intervencion', 'like', '%CPU%')
                      ->orWhere('tipo_equipo', 'like', '%CPU%');
                });
            } elseif (str_contains($paramModulo, 'portat') || str_contains($paramModulo, 'laptop')) {
                $query->where('modulo_intervencion', 'like', '%Portát%');
            } elseif (str_contains($paramModulo, 'sopla')) {
                $query->where('modulo_intervencion', 'like', '%sopla%');
            } else {
                $query->where('modulo_intervencion', 'like', "%{$paramModulo}%");
            }
        }

        if ($inicio && $fin) {
            $query->whereRaw('COALESCE(fecha_intervencion, created_at) BETWEEN ? AND ?', [
                $inicio->toDateTimeString(),
                $fin->toDateTimeString()
            ]);
        }

        $query->orderBy('id', 'desc');

        $encabezados = [
            'ID / RADICADO',
            'PLACA',
            'SERIAL',
            'MÓDULO',
            'MARCA',
            'MODELO',
            'FALLA / CONDICIÓN',
            'INTERVENCIÓN REALIZADA',
            'TRASLADO',
            'ANALISTA',
            'FECHA INTERVENCIÓN'
        ];
        
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

            // Usar traslado directo del registro si ya existe para máxima velocidad de exportación
            $traslado = (!empty($row->num_traslado) && $row->num_traslado !== 'Sin Traslado')
                ? $row->num_traslado
                : \App\Models\InventarioGeneral::buscarTrasladoEnSistema($row->identificador_1 ?: ($row->placa_id ?: ''), $row);

            $moduloRaw = strtolower(trim((string)$row->modulo_intervencion));
            $fallaCondicion = '';
            $intervencionRealizada = '';

            // 1. Diagnóstico CPU / Equipos
            $esCpu = str_contains($moduloRaw, 'cpu') 
                || strtolower((string)$row->tipo_equipo) === 'cpu' 
                || ($row->equipos && $row->equipos->isNotEmpty());

            if ($esCpu) {
                $equipos = $row->equipos ?? collect();
                if ($equipos->isEmpty()) {
                    $candidatos = array_unique(array_filter([
                        $row->placa_id,
                        $row->serial,
                        $row->identificador_1,
                        $row->identificador_2
                    ]));
                    if (!empty($candidatos)) {
                        $equipos = \App\Models\Equipo::whereIn('placa_id', $candidatos)->orderBy('id', 'asc')->get();
                    }
                }

                if ($equipos->isNotEmpty()) {
                    $listaInterv = [];
                    foreach ($equipos as $eq) {
                        $partes = [];
                        $tipo = trim((string)$eq->tipo_gestion);
                        $estadoAct = trim((string)$eq->estado_actual);

                        if (strcasecmp($tipo, 'Intervencion') === 0 || strcasecmp($tipo, 'Intervención') === 0) {
                            $comp = $eq->que_va_intervenir ?: 'Intervención Técnica';
                            if (!empty($eq->origen_pieza)) {
                                $comp .= " ({$eq->origen_pieza})";
                            }
                            $partes[] = "Intervención: {$comp}";
                        } elseif (strcasecmp($tipo, 'Baja') === 0) {
                            $partes[] = 'Baja' . (!empty($eq->motivo_baja) ? ": {$eq->motivo_baja}" : '');
                        } elseif (strcasecmp($tipo, 'Diagnostico') === 0 || strcasecmp($tipo, 'Diagnóstico') === 0) {
                            $partes[] = 'Diagnóstico: ' . ($estadoAct ?: 'Funcional');
                        } elseif (strcasecmp($tipo, 'Novedad') === 0) {
                            $partes[] = 'Novedad: ' . ($eq->descripcion_novedad ?: ($estadoAct ?: 'Reportada'));
                        } elseif (strcasecmp($tipo, 'IT') === 0) {
                            $partes[] = 'Gestión IT: ' . ($eq->novedad_it ?: 'IT');
                        } else {
                            $partes[] = $tipo ?: 'Diagnóstico CPU';
                            if (!empty($eq->que_va_intervenir)) {
                                $comp = $eq->que_va_intervenir . (!empty($eq->origen_pieza) ? " ({$eq->origen_pieza})" : '');
                                $partes[] = $comp;
                            }
                        }

                        if (!empty($estadoAct) && !in_array(strtolower($tipo), ['diagnostico', 'diagnóstico', 'baja'])) {
                            $partes[] = "Estado: {$estadoAct}";
                        }
                        if (!empty($eq->descripcion_novedad) && strcasecmp($tipo, 'Novedad') !== 0) {
                            $partes[] = "Obs: {$eq->descripcion_novedad}";
                        }
                        if (!empty($eq->solucion_garantias)) {
                            $partes[] = "Garantía: {$eq->solucion_garantias}";
                        }
                        if (!empty($eq->novedad_it) && strcasecmp($tipo, 'IT') !== 0) {
                            $partes[] = "IT: {$eq->novedad_it}";
                        }
                        if (!empty($eq->ubicacion_destino)) {
                            $partes[] = "Destino: {$eq->ubicacion_destino}";
                        }

                        $itemStr = implode(' | ', $partes);
                        if ($equipos->count() > 1 && $eq->fecha_creacion) {
                            $itemStr .= ' [' . Carbon::parse($eq->fecha_creacion)->format('d/m/Y') . ']';
                        }
                        $listaInterv[] = $itemStr;
                    }

                    $intervencionRealizada = implode(' // ', $listaInterv);

                    $ultimoEquipo = $equipos->sortByDesc('id')->first();
                    $estFinal = strtoupper(trim((string)$ultimoEquipo->estado_actual));
                    $tipoFinal = strtoupper(trim((string)$ultimoEquipo->tipo_gestion));

                    if ($tipoFinal === 'BAJA' || $estFinal === 'BAJA') {
                        $fallaCondicion = 'BAJA' . ($ultimoEquipo->motivo_baja ? " ({$ultimoEquipo->motivo_baja})" : '');
                    } elseif (str_contains($estFinal, 'GARANT')) {
                        $fallaCondicion = 'GARANTÍA';
                    } elseif (str_contains($estFinal, 'PENDIENTE')) {
                        $fallaCondicion = 'PENDIENTE REPUESTO';
                    } elseif (str_contains($estFinal, 'FUNCIONAL')) {
                        $fallaCondicion = 'FUNCIONAL';
                    } elseif ($tipoFinal === 'NOVEDAD') {
                        $fallaCondicion = 'NOVEDAD' . ($ultimoEquipo->descripcion_novedad ? ": {$ultimoEquipo->descripcion_novedad}" : '');
                    } elseif (!empty($estFinal)) {
                        $fallaCondicion = $estFinal;
                    } else {
                        $fallaCondicion = 'FUNCIONAL';
                    }
                }
            }

            // 2. Diagnóstico Portátiles / Garantías
            if (empty($intervencionRealizada) && (str_contains($moduloRaw, 'portat') || str_contains($moduloRaw, 'laptop') || str_contains($moduloRaw, 'garant') || ($row->garantiasPortatiles && $row->garantiasPortatiles->isNotEmpty()))) {
                $garantias = $row->garantiasPortatiles ?? collect();
                if ($garantias->isEmpty()) {
                    $candidatosPort = array_unique(array_filter([
                        $row->placa_id,
                        $row->serial,
                        $row->identificador_1,
                        $row->identificador_2
                    ]));
                    if (!empty($candidatosPort)) {
                        $garantias = \App\Models\GarantiaPortatil::whereIn('placa_id_equipo', $candidatosPort)->orderBy('id', 'asc')->get();
                    }
                }

                if ($garantias->isNotEmpty()) {
                    $listaPort = [];
                    foreach ($garantias as $g) {
                        $pG = [];
                        $tipoG = trim((string)$g->tipo_gestion);
                        if (!empty($tipoG)) {
                            $pG[] = $tipoG;
                        }

                        if (!empty($g->pieza_intervenida)) {
                            $pz = $g->pieza_intervenida;
                            if (!empty($g->origen_pieza)) { $pz .= " ({$g->origen_pieza})"; }
                            if (!empty($g->indique_fru)) { $pz .= " [FRU: {$g->indique_fru}]"; }
                            $pG[] = "Pieza: {$pz}";
                        } elseif (!empty($g->indique_pieza)) {
                            $pz = $g->indique_pieza;
                            if (!empty($g->indique_fru)) { $pz .= " [FRU: {$g->indique_fru}]"; }
                            $pG[] = "Pieza: {$pz}";
                        }

                        if (!empty($g->diagnostico_laptop_intervenido)) {
                            $pG[] = "Diag: {$g->diagnostico_laptop_intervenido}";
                        }
                        if (!empty($g->motivo_baja)) {
                            $pG[] = "Baja: {$g->motivo_baja}";
                        }
                        if (!empty($g->numero_ticket)) {
                            $pG[] = "Ticket: {$g->numero_ticket}";
                        }

                        $itemG = implode(' | ', $pG);
                        if ($garantias->count() > 1 && $g->created_at) {
                            $itemG .= ' [' . Carbon::parse($g->created_at)->format('d/m/Y') . ']';
                        }
                        $listaPort[] = $itemG;
                    }

                    $intervencionRealizada = implode(' // ', $listaPort);

                    $ultimaG = $garantias->sortByDesc('id')->first();
                    $estActG = strtoupper(trim((string)$ultimaG->estado_actual_equipo));
                    $garG = strtoupper(trim((string)$ultimaG->garantia));
                    $estFinG = strtoupper(trim((string)$ultimaG->estado_final_equipo));

                    if (str_contains($estActG, 'GARANT') || str_contains($garG, 'APLICA') || str_contains($garG, 'GARANT')) {
                        $fallaCondicion = 'GARANTÍA';
                    } elseif (str_contains($estActG, 'BAJA') || str_contains($estFinG, 'BAJA') || !empty($ultimaG->motivo_baja)) {
                        $fallaCondicion = 'BAJA' . ($ultimaG->motivo_baja ? " ({$ultimaG->motivo_baja})" : '');
                    } elseif (str_contains($estActG, 'PENDIENTE') || str_contains($estFinG, 'PENDIENTE')) {
                        $fallaCondicion = 'PENDIENTE REPUESTO';
                    } elseif (str_contains($estActG, 'FUNCIONAL') || str_contains($estFinG, 'FUNCIONAL')) {
                        $fallaCondicion = 'FUNCIONAL';
                    } elseif (!empty($estActG)) {
                        $fallaCondicion = $estActG;
                    } else {
                        $fallaCondicion = 'REVISADO';
                    }
                }
            }

            // 3. Soplado
            if (empty($intervencionRealizada) && str_contains($moduloRaw, 'sopla')) {
                $fallaCondicion = 'COMPLETADO';
                $intervencionRealizada = 'Mantenimiento preventivo, limpieza y soplado';
            }

            // 4. Fallback general para otros casos
            if (empty($fallaCondicion)) {
                $fallaCondicion = $row->observaciones ?? ($row->estado ?? 'N/A');
                $estadoRaw = strtoupper(trim((string)$row->estado));
                if (in_array($estadoRaw, ['BAJA', 'DADO DE BAJA', 'DESCARTE', 'SCRAP'])) {
                    $fallaCondicion = 'BAJA';
                } elseif (in_array($estadoRaw, ['1', 'INTERVENIDO', 'CARGADO'])) {
                    $fallaCondicion = 'INTERVENIDO';
                }
            }

            if (empty($intervencionRealizada)) {
                $intervencionRealizada = $row->observaciones ?: 'Intervención registrada en sistema';
            }

            return [
                $row->id,
                $row->identificador_1 ?: ($row->placa_id ?: 'N/A'),
                $row->identificador_2 ?: ($row->serial ?: 'N/A'),
                $row->modulo_intervencion ?: 'N/A',
                $row->marca ?: 'N/A',
                $row->modelo ?: 'N/A',
                $fallaCondicion,
                $intervencionRealizada,
                $traslado ?: ($row->num_traslado ?: 'N/A'),
                $row->analista_intervencion ?: 'N/A',
                $fechaStr
            ];
        });
    }
}

