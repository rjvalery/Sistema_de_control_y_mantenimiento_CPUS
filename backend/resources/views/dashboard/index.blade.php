@extends('layouts.app')

@section('title', 'Dashboard Operativo y Control de Equipos')

@section('container_class', 'container-fluid px-3 px-xl-4')

@section('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* ============ MES Platform · Design Tokens ============ */
    :root {
        --mes-teal: #00B69B;
        --mes-teal-hover: #009E86;
        --mes-teal-soft: #E6F8F5;
        --mes-amber: #F59E0B;
        --mes-amber-soft: #FEF3C7;
        --mes-amber-text: #B45309;
        --mes-danger-soft: #FEE2E2;
        --mes-danger-text: #B91C1C;
        --mes-page-bg: #F8FAFC;
        --mes-card-bg: #FFFFFF;
        --mes-item-bg: #F1F5F9;
        --mes-border: #E2E8F0;
        --mes-text: #0F172A;
        --mes-muted: #64748B;
        --mes-radius-card: 16px;
        --mes-radius-item: 12px;
    }

    body {
        background-color: var(--mes-page-bg);
        font-family: 'Inter', 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
        color: var(--mes-text);
    }

    .bg-teal { background-color: var(--mes-teal) !important; }
    .text-teal { color: var(--mes-teal) !important; }
    .text-muted { color: var(--mes-muted) !important; }

    /* ============ Tarjetas base ============ */
    .mes-card {
        background-color: var(--mes-card-bg);
        border: 1px solid var(--mes-border);
        border-radius: var(--mes-radius-card);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .mes-title {
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--mes-text);
        margin: 0;
        letter-spacing: -0.2px;
    }
    .mes-subtitle {
        font-size: 0.8rem;
        color: var(--mes-muted);
        margin: 0.15rem 0 0;
    }

    /* ============ KPI ============ */
    .mes-kpi {
        padding: 1.25rem 1.35rem;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .mes-kpi:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        border-color: rgba(0, 182, 155, 0.35);
    }
    .mes-kpi-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background-color: var(--mes-teal-soft);
        color: var(--mes-teal);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .mes-kpi-label {
        font-size: 0.82rem;
        font-weight: 500;
        color: var(--mes-muted);
        margin: 0;
    }
    .mes-kpi-value {
        font-size: 2.4rem;
        font-weight: 600;
        line-height: 1.1;
        letter-spacing: -1px;
        color: var(--mes-text);
        margin: 0.35rem 0 0.4rem;
        font-variant-numeric: tabular-nums;
    }
    .mes-kpi-value small {
        font-size: 1.35rem;
        font-weight: 500;
        color: var(--mes-muted);
        letter-spacing: 0;
    }
    .mes-kpi-sub {
        font-size: 0.78rem;
        color: var(--mes-muted);
        margin: 0;
    }

    /* ============ Badges ============ */
    .mes-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        white-space: nowrap;
    }
    .mes-badge-teal  { background: var(--mes-teal-soft);  color: var(--mes-teal-hover); }
    .mes-badge-amber { background: var(--mes-amber-soft); color: var(--mes-amber-text); }
    .mes-badge-red   { background: var(--mes-danger-soft); color: var(--mes-danger-text); }
    .mes-badge-cpu        { background: var(--mes-teal-soft); color: var(--mes-teal-hover); }
    .mes-badge-soplado    { background: #CFFAFE; color: #0E7490; }
    .mes-badge-portatiles { background: #EDE9FE; color: #6D28D9; }

    /* ============ Segmented control ============ */
    .mes-segmented {
        display: inline-flex;
        background-color: var(--mes-item-bg);
        border: 1px solid var(--mes-border);
        border-radius: 999px;
        padding: 4px;
        gap: 2px;
    }
    .mes-segmented .mes-seg-btn {
        border: 0;
        background: transparent;
        font-size: 0.8rem;
        font-weight: 500;
        padding: 0.4rem 1rem;
        border-radius: 999px;
        cursor: pointer;
        transition: background-color .18s ease, color .18s ease, box-shadow .18s ease;
        line-height: 1.2;
        white-space: nowrap;
    }
    .mes-segmented .mes-seg-btn:not(.bg-teal):hover { color: var(--mes-text) !important; background: rgba(0,0,0,.04); }
    .mes-segmented .mes-seg-btn.bg-teal { font-weight: 600; box-shadow: 0 2px 8px rgba(0, 182, 155, 0.35); }

    /* ============ Cronograma ============ */
    .pbi-schedule-item {
        display: grid;
        grid-template-columns: 72px minmax(0, 1.8fr) minmax(0, 1fr) auto;
        align-items: center;
        gap: 1rem;
        background-color: var(--mes-item-bg);
        border: 1px solid transparent;
        border-radius: var(--mes-radius-item);
        padding: 0.85rem 1.1rem;
        transition: background-color .18s ease, border-color .18s ease, transform .18s ease;
        animation: mesFadeUp .35s ease both;
    }
    .pbi-schedule-item:hover {
        background-color: #EAF0F6;
        border-color: var(--mes-border);
        transform: translateX(2px);
    }
    .pbi-time { font-size: 1.05rem; font-weight: 600; line-height: 1.1; font-variant-numeric: tabular-nums; }
    .pbi-time-label { font-size: 0.68rem; color: var(--mes-muted); text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }
    .pbi-plate { font-weight: 700; font-size: 0.95rem; word-break: break-word; }
    .pbi-serial { font-size: 0.74rem; color: var(--mes-muted); }
    .pbi-analyst { font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: .5rem; min-width: 0; }
    .pbi-avatar {
        width: 28px; height: 28px; border-radius: 50%;
        background: var(--mes-teal-soft); color: var(--mes-teal-hover);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .7rem; font-weight: 600; flex-shrink: 0;
    }
    .pbi-analyst span.txt { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .btn-mes-outline {
        border: 1px solid var(--mes-border);
        background: var(--mes-card-bg);
        color: var(--mes-text);
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.4rem 1rem;
        transition: all .18s ease;
        white-space: nowrap;
        text-decoration: none;
    }
    .btn-mes-outline:hover { border-color: var(--mes-teal); color: var(--mes-teal-hover); background: var(--mes-teal-soft); }
    .btn-mes-outline.disabled { opacity: .5; pointer-events: none; }

    .btn-mes-teal {
        background-color: var(--mes-teal);
        border: 0;
        color: #fff;
        font-weight: 600;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        box-shadow: 0 6px 16px rgba(0, 182, 155, 0.28);
        transition: background-color .18s ease, transform .18s ease, box-shadow .18s ease;
    }
    .btn-mes-teal:hover, .btn-mes-teal:focus, .btn-mes-teal.show {
        background-color: var(--mes-teal-hover);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(0, 158, 134, 0.35);
    }

    /* ============ Líneas técnicas ============ */
    .mes-line-card {
        background-color: var(--mes-item-bg);
        border-radius: var(--mes-radius-item);
        padding: 1rem 1.1rem;
        transition: background-color .18s ease;
    }
    .mes-line-card:hover { background-color: #EAF0F6; }
    .mes-line-ic {
        width: 32px; height: 32px; border-radius: 10px;
        background: var(--mes-card-bg); color: var(--mes-teal);
        display: inline-flex; align-items: center; justify-content: center; font-size: .85rem;
        border: 1px solid var(--mes-border);
    }
    .mes-progress { height: 8px; background-color: #E2E8F0; border-radius: 999px; overflow: hidden; }
    .mes-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--mes-teal), #1AD1B5);
        border-radius: 999px;
        transition: width .6s cubic-bezier(.22, 1, .36, 1);
    }

    .mes-pager { border-top: 1px solid var(--mes-border); }
    .mes-empty { text-align: center; padding: 2.5rem 1rem; color: var(--mes-muted); }
    .mes-empty i { font-size: 1.75rem; display: block; margin-bottom: .6rem; opacity: .6; }

    @keyframes mesFadeUp {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 767.98px) {
        .pbi-schedule-item { grid-template-columns: 60px minmax(0, 1fr); row-gap: .6rem; }
        .pbi-schedule-item .pbi-analyst,
        .pbi-schedule-item .pbi-action { grid-column: 2; }
        .mes-kpi-value { font-size: 2rem; }
    }
</style>
@endsection

@section('content')
@php
    $delta = (float) ($deltaSemanal ?? 0);
    $deltaPositivo = $delta >= 0;
    // $periodo proviene de request('periodo', 'dia') normalizado en el controlador
    $periodoActivo = $periodo;
    $segmentos = ['dia' => 'Hoy', 'semana' => 'Esta Semana', 'mes' => 'Este Mes'];
    $filtrosExtra = array_filter(['analista_id' => request('analista_id')]);
@endphp

<!-- Encabezado -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="mes-title fs-4">Dashboard Operativo</h1>
        <p class="mes-subtitle">
            <i class="fa-regular fa-calendar-check text-teal me-1"></i><span id="labelPeriodoActivo">{{ $labelPeriodo }}</span>
            <span id="labelAnalistaActivo" class="mes-badge mes-badge-teal ms-2 {{ empty($nombreAnalista) ? 'd-none' : '' }}">
                <i class="fa-solid fa-user-check"></i><span id="labelAnalistaTxt">{{ $nombreAnalista ?? '' }}</span>
            </span>
        </p>
    </div>
</div>

<!-- 2. FILA SUPERIOR · KPIs -->
<div class="row g-3 mb-4">
    <!-- KPI 1 -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="mes-card mes-kpi h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="mes-kpi-icon"><i class="fa-regular fa-calendar-check"></i></div>
                <div class="min-w-0">
                    <p class="mes-kpi-label">Máquinas Ingresadas</p>
                    <p class="mes-kpi-value" id="kpiTotalCargados">{{ number_format((int) $statsInventario['total_cargados']) }}</p>
                    <p class="mes-kpi-sub" id="kpiSubPeriodo">{{ $labelPeriodo }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 2 -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="mes-card mes-kpi h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="mes-kpi-icon"><i class="fa-solid fa-chart-line"></i></div>
                <div class="min-w-0">
                    <p class="mes-kpi-label">Efectividad Taller</p>
                    <p class="mes-kpi-value"><span id="kpiPorcentaje">{{ $statsInventario['porcentaje'] }}</span>%</p>
                    <span id="kpiDeltaBadge" class="mes-badge {{ $deltaPositivo ? 'mes-badge-teal' : 'mes-badge-red' }}">
                        <i class="fa-solid {{ $deltaPositivo ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                        <span id="kpiDeltaTxt">{{ $deltaPositivo ? '+' : '' }}{{ $delta }}% vs semana anterior</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 3 -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="mes-card mes-kpi h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="mes-kpi-icon"><i class="fa-solid fa-users"></i></div>
                <div class="min-w-0">
                    <p class="mes-kpi-label">Analistas Activos</p>
                    <p class="mes-kpi-value">
                        <span id="kpiAnalistasActivos">{{ (int) $analistasActivosHoy }}</span>
                        <small>/ <span id="kpiAnalistasTotal">{{ (int) $totalAnalistas }}</span></small>
                    </p>
                    <p class="mes-kpi-sub">Turno actual</p>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 4 -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="mes-card mes-kpi h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="mes-kpi-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="min-w-0">
                    <p class="mes-kpi-label">Equipos Pendientes / Baja</p>
                    <p class="mes-kpi-value" id="kpiPendientes">{{ number_format((int) $statsInventario['pendientes']) }}</p>
                    <span class="mes-badge mes-badge-amber">
                        <i class="fa-solid fa-circle-exclamation"></i>Requiere atención
                        <span class="opacity-75">· <span id="kpiBaja">{{ (int) $totalBaja }}</span> en baja</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. CUADRÍCULA PRINCIPAL -->
<div class="row g-3 mb-4">
    <!-- A. Cronograma de Intervenciones -->
    <div class="col-12 col-lg-8">
        <div class="mes-card h-100 d-flex flex-column">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-4 pb-3">
                <div>
                    <h2 class="mes-title">Cronograma de Intervenciones</h2>
                    <p class="mes-subtitle">Registro de máquinas atendidas ·
                        <span id="tablaCountBadge" class="fw-semibold text-dark">{{ $maquinasIntervenidas->total() }}</span> en total</p>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="#" id="btnDescargarExcel" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-2 fw-semibold px-3 py-1 shadow-sm" style="border-radius: 8px;">
                        <i class="fa-solid fa-file-excel text-success"></i>
                        <span>Descargar Bitácora</span>
                    </a>
                    <nav class="mes-segmented" aria-label="Filtro de período" id="segmentedPeriodo">
                        @foreach ($segmentos as $valor => $texto)
                            <a href="{{ route('dashboard', array_merge($filtrosExtra, ['periodo' => $valor])) }}"
                               data-periodo="{{ $valor }}"
                               class="mes-seg-btn text-decoration-none {{ $periodoActivo === $valor ? 'bg-teal text-white' : 'text-muted' }}"
                               @if ($periodoActivo === $valor) aria-current="page" @endif>{{ $texto }}</a>
                        @endforeach
                    </nav>
                </div>
            </div>

            <div class="px-4 pb-3 flex-grow-1">
                <div class="d-flex flex-column gap-2" id="listaCronograma">
                    @forelse ($maquinasIntervenidas as $m)
                        @php
                            $placa = $m['placa'];
                            $serial = $m['serial'];
                            $analista = trim((string) ($m['analista_intervencion'] ?? ''));
                            $partes = preg_split('/\s+/', $analista, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                            $iniciales = $analista !== ''
                                ? mb_strtoupper(mb_substr($partes[0], 0, 1) . (count($partes) > 1 ? mb_substr(end($partes), 0, 1) : ''))
                                : '?';
                        @endphp
                        <div class="pbi-schedule-item">
                            <div>
                                <div class="pbi-time">{{ $m['hora'] }}</div>
                                <div class="pbi-time-label">Hora</div>
                            </div>
                            <div class="min-w-0">
                                <div class="pbi-plate">{{ $placa }}</div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                    <span class="mes-badge mes-badge-{{ $m['modulo'] }}">{{ $m['modulo_label'] }}</span>
                                    <span class="mes-badge mes-badge-{{ $m['estado_tono'] }}">{{ $m['estado_label'] }}</span>
                                    <span class="pbi-serial"><i class="fa-solid fa-barcode me-1"></i>{{ $serial }}</span>
                                </div>
                            </div>
                            <div class="pbi-analyst">
                                @if ($analista !== '')
                                    <span class="pbi-avatar">{{ $iniciales }}</span>
                                    <span class="txt">{{ $analista }}</span>
                                @else
                                    <span class="text-muted">Sin analista</span>
                                @endif
                            </div>
                            <div class="pbi-action text-md-end">
                                @if (!empty($m['detalle_url']))
                                    <a href="{{ $m['detalle_url'] }}" class="btn-mes-outline d-inline-block">Ver Detalle</a>
                                @else
                                    <span class="btn-mes-outline d-inline-block disabled">Ver Detalle</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="mes-empty">
                            <i class="fa-regular fa-calendar-xmark"></i>
                            <span class="fw-semibold">No hay intervenciones registradas para este período.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mes-pager px-4 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="text-muted small">
                    Mostrando {{ $maquinasIntervenidas->firstItem() ?? 0 }}–{{ $maquinasIntervenidas->lastItem() ?? 0 }}
                    de {{ $maquinasIntervenidas->total() }} registros
                </span>
                @if ($maquinasIntervenidas->hasPages())
                    {{ $maquinasIntervenidas->links() }}
                @endif
            </div>
        </div>
    </div>

    <!-- B. Rendimiento de Líneas Técnicas -->
    <div class="col-12 col-lg-4">
        <div class="mes-card h-100 d-flex flex-column p-4">
            <div class="mb-3">
                <h2 class="mes-title">Disponibilidad y Capacidad</h2>
                <p class="mes-subtitle">Rendimiento de líneas técnicas · <span id="lineasPeriodoTxt">{{ $labelPeriodo }}</span></p>
            </div>

            <div class="d-flex flex-column gap-3 flex-grow-1" id="listaLineas">
                @foreach ($lineas as $l)
                    @php $porcentajeLinea = $l['pct']; @endphp
                    <div class="mes-line-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="mes-line-ic"><i class="fa-solid {{ $l['icono'] }}"></i></span>
                                <span class="fw-semibold" style="font-size:.9rem;">{{ $l['nombre'] }}</span>
                            </div>
                            <span class="fw-semibold" style="font-size:.95rem;">{{ number_format($l['total']) }} <span class="text-muted fw-normal">/ {{ number_format($l['capacidad']) }}</span></span>
                        </div>
                        <div class="mes-progress"><div class="mes-progress-bar" style="width: {{ $porcentajeLinea }}%;"></div></div>
                        <div class="text-muted mt-2" style="font-size:.75rem;"><strong class="text-dark">{{ $porcentajeLinea }}%</strong> Utilización</div>
                    </div>
                @endforeach
            </div>

            @canany(['cpus.registrar', 'soplado.registrar', 'portatiles.registrar'])
            <div class="dropdown mt-4">
                <button class="btn btn-mes-teal w-100" type="button" id="btnRegistrarIntervencion" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-plus me-1"></i> Registrar Intervención
                </button>
                <ul class="dropdown-menu w-100 shadow border-0 p-2" style="border-radius:12px;" aria-labelledby="btnRegistrarIntervencion">
                    @can('cpus.registrar')
                        <li><a class="dropdown-item rounded-2" href="{{ route('equipos.create') }}"><i class="fa-solid fa-desktop me-2 text-teal"></i>Diagnóstico CPU</a></li>
                    @endcan
                    @can('soplado.registrar')
                        <li><a class="dropdown-item rounded-2" href="{{ route('soplado.create') }}"><i class="fa-solid fa-wind me-2 text-teal"></i>Mantenimiento Soplado</a></li>
                    @endcan
                    @can('portatiles.registrar')
                        <li><a class="dropdown-item rounded-2" href="{{ route('portatiles.create') }}"><i class="fa-solid fa-laptop me-2 text-teal"></i>Portátiles</a></li>
                    @endcan
                </ul>
            </div>
            @endcanany
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        actualizarEnlaceExportar();
    });

    function actualizarEnlaceExportar() {
        const btn = document.getElementById('btnDescargarExcel');
        if (btn) {
            const periodoActual = '{{ $periodoActivo }}';
            const analistaActual = '{{ request("analista_id") }}';
            btn.href = `{{ route('dashboard.exportar') }}?periodo=${periodoActual}&analista_id=${analistaActual}`;
        }
    }
</script>
@endsection

