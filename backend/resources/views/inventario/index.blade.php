@extends('layouts.app')

@section('title', 'Módulo de Inventario General')
@section('container_class', 'container-fluid px-3 px-xl-4 py-2')

@section('styles')
<style>
    .table-inventario-sticky thead th {
        position: sticky;
        top: 0;
        z-index: 2;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">

        <!-- ENCABEZADO -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">
                    <i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Inventario General
                </h1>
                <p class="text-muted mb-0">
                    Consulta, filtrado y gestión del parque de equipos en almacén.
                </p>
            </div>
            <div class="mt-2 mt-md-0 d-flex flex-wrap gap-2">
                @can('cargue_masivo.ejecutar')
                <a href="{{ route('cargue-masivo.index') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Ir a Cargue Masivo
                </a>
                @endcan
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver al Dashboard
                </a>
            </div>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {!! session('msg') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {!! session('error') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- PANEL DE MÉTRICAS -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Total Cargados (Cubic)</span>
                            <h3 class="fw-bold mb-0 text-dark">{{ number_format((int)($statsInventario['totalCargados'] ?? 0)) }}</h3>
                        </div>
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                            <i class="fa-solid fa-boxes-stacked fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Intervenidos en Sistema</span>
                            <h3 class="fw-bold mb-0 text-success">
                                {{ number_format((int)($statsInventario['totalIntervenidos'] ?? 0)) }}
                                <span class="badge bg-success-subtle text-success fs-6 border border-success-subtle ms-1">
                                    {{ $statsInventario['porcentajeAgregadas'] ?? 0 }}%
                                </span>
                            </h3>
                        </div>
                        <div class="bg-success-subtle text-success p-3 rounded-circle">
                            <i class="fa-solid fa-circle-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 bg-white border-start border-warning border-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Pendientes por Ingresar</span>
                            <h3 class="fw-bold mb-0 text-warning">{{ number_format((int)($statsInventario['totalPendientes'] ?? 0)) }}</h3>
                        </div>
                        <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                            <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 bg-white border-start border-info border-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Traslados Registrados</span>
                            <h3 class="fw-bold mb-0 text-info">{{ count($trasladosDisponibles) }}</h3>
                        </div>
                        <div class="bg-info-subtle text-info p-3 rounded-circle">
                            <i class="fa-solid fa-truck-ramp-box fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE REGISTROS -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h5 class="card-title fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-table-list text-primary me-2"></i>Inventario General
                            <span class="badge bg-primary ms-1">
                                {{ (!empty($filtro) || !empty($traslado) || !empty($busqueda)) ? "{$totalFiltrados} de {$totalRegistros}" : "{$totalRegistros} total" }}
                            </span>
                        </h5>
                    </div>
                </div>

                <form method="GET" action="{{ route('inventario.index') }}" class="row g-2 align-items-center border-top pt-3">
                    <div class="col-12 col-md-3 col-lg-3">
                        <select name="filtro" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos los Estados</option>
                            <option value="agregados" {{ ($filtro === 'agregados') ? 'selected' : '' }}>Ya agregados ({{ $statsInventario['totalIntervenidos'] ?? 0 }})</option>
                            <option value="pendientes" {{ ($filtro === 'pendientes') ? 'selected' : '' }}>Pendientes ({{ $statsInventario['totalPendientes'] ?? 0 }})</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 col-lg-2">
                        <select name="traslado" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos los traslados</option>
                            <option value="sin_traslado" {{ ($traslado === 'sin_traslado') ? 'selected' : '' }}>⚠️ Sin Traslado</option>
                            @foreach ($trasladosDisponibles as $t => $count)
                                <option value="{{ $t }}" {{ ($traslado === $t) ? 'selected' : '' }}>Traslado {{ $t }}</option>
                            @endforeach
                            @if (!empty($traslado) && $traslado !== 'sin_traslado' && !array_key_exists($traslado, $trasladosDisponibles))
                                <option value="{{ $traslado }}" selected>Traslado {{ $traslado }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-6 col-md-2 col-lg-2">
                        <select name="limite" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="50" {{ ($limite === 50) ? 'selected' : '' }}>50 filas</option>
                            <option value="100" {{ ($limite === 100) ? 'selected' : '' }}>100 filas</option>
                            <option value="250" {{ ($limite === 250) ? 'selected' : '' }}>250 filas</option>
                            <option value="500" {{ ($limite === 500) ? 'selected' : '' }}>500 filas</option>
                            <option value="1000" {{ ($limite === 1000) ? 'selected' : '' }}>1000 filas</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar Placa o Serial..." value="{{ $busqueda }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-2 col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-secondary btn-sm flex-fill fw-semibold">
                            <i class="fa-solid fa-filter me-1"></i> Filtrar
                        </button>
                        @if (!empty($busqueda) || !empty($filtro) || !empty($traslado) || $limite !== 250)
                            <a href="{{ route('inventario.index') }}" class="btn btn-outline-danger btn-sm" title="Restablecer">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto; overflow-x: auto;">
                    <table class="table table-hover table-striped align-middle mb-0 text-nowrap table-inventario-sticky">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>Traslado</th>
                                <th>Placa</th>
                                <th>Serial</th>
                                <th>Ref. Principal</th>
                                <th>Descripción</th>
                                <th>Ubicación</th>
                                <th>Estatus</th>
                                <th>Intervenido en Sistema</th>
                                <th>Observaciones</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($registros->count() > 0)
                                @foreach ($registros as $r)
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $r->id }}</td>
                                        <td>
                                            @if (!empty($r->num_traslado))
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                                    <i class="fa-solid fa-truck-ramp-box me-1"></i>{{ $r->num_traslado }}
                                                </span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td><strong>{{ $r->identificador_1 ?: ($r->placa_id ?: '—') }}</strong></td>
                                        <td><code>{{ $r->identificador_2 ?: ($r->serial ?: '—') }}</code></td>
                                        <td>{{ $r->ref_principal ?: ($r->modelo ?: '—') }}</td>
                                        <td><span class="badge bg-secondary">{{ $r->descripcion ?: ($r->tipo_equipo ?: '—') }}</span></td>
                                        <td>{{ $r->ubicacion_origen ?: ($r->ubicacion ?: '—') }}</td>
                                        <td>
                                            @php
                                                $estatusVal = $r->estado ?: ($r->verificado ?: 'Cargado');
                                                $esCargado = (strtolower(trim((string)$estatusVal)) === 'cargado');
                                            @endphp
                                            @if ($esCargado)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                                    <i class="fa-solid fa-cloud-arrow-up me-1"></i>Cargado
                                                </span>
                                            @else
                                                <span class="badge bg-info-subtle text-dark border">{{ $estatusVal }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($r->intervenido)
                                                <div>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">
                                                        <i class="fa-solid fa-circle-check me-1"></i>{{ $r->modulo_intervencion ?: 'Agregado al Sistema' }}
                                                    </span>
                                                    @if (!empty($r->analista_intervencion))
                                                        <div class="small text-muted mt-1">
                                                            <i class="fa-solid fa-user-check me-1 text-primary"></i>{{ $r->analista_intervencion }}
                                                        </div>
                                                    @endif
                                                    @if (!empty($r->fecha_intervencion))
                                                        <div class="small text-muted">
                                                            <i class="fa-solid fa-calendar-day me-1"></i>{{ date('d/m/Y H:i', strtotime($r->fecha_intervencion)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                    <i class="fa-solid fa-hourglass-start me-1"></i>Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="small text-muted" style="max-width: 180px; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $r->observaciones ?: '—' }}
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('inventario.show', $r->id) }}" class="btn btn-sm btn-outline-primary" title="Ver Detalles">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-magnifying-glass display-6 text-muted mb-3 d-block"></i>
                                        No se encontraron registros de inventario con los criterios seleccionados.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($registros->count() > 0)
                <div class="card-footer bg-light py-2 px-3 d-flex flex-wrap justify-content-between align-items-center">
                    <small class="text-muted">
                        Mostrando <strong>{{ number_format($registros->count()) }}</strong> de <strong>{{ number_format($totalFiltrados) }}</strong> registros coincidentes
                    </small>
                    <div class="mt-2 mt-sm-0">
                        {{ $registros->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
