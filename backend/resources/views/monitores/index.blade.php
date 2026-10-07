@extends('layouts.app')
@section('title', 'Bitácora de Monitores')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-desktop me-2 text-primary"></i>Reacondicionamiento de Monitores</h5>
        <a href="{{ route('monitores.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Nuevo Registro</a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('monitores.index') }}" class="row g-2 mb-4 align-items-center">
            <div class="col-md-4">
                <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar por serial, placa o analista..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="fecha_desde" class="form-control form-control-sm" title="Fecha desde" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="fecha_hasta" class="form-control form-control-sm" title="Fecha hasta" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-md-2">
                <select name="limite" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="25" {{ request('limite') == 25 ? 'selected' : '' }}>25 por página</option>
                    <option value="50" {{ (request('limite') == 50 || !request('limite')) ? 'selected' : '' }}>50 por página</option>
                    <option value="100" {{ request('limite') == 100 ? 'selected' : '' }}>100 por página</option>
                    <option value="200" {{ request('limite') == 200 ? 'selected' : '' }}>200 por página</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar</button>
                @if(request()->hasAny(['buscar', 'fecha_desde', 'fecha_hasta', 'limite']))
                    <a href="{{ route('monitores.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros"><i class="fa-solid fa-xmark"></i></a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle table-sm border">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"><i class="fa-regular fa-clock me-1"></i> Fecha y Hora</th>
                        <th>Serial / Placa</th>
                        <th>Traslado</th>
                        <th>Tipo Gestión</th>
                        <th>Diagnóstico / Motivo</th>
                        <th>Estado Actual</th>
                        <th>Analista</th>
                        <th class="text-center">Acciones / Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monitores as $monitor)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold text-dark">{{ $monitor->fecha_ingreso ? $monitor->fecha_ingreso->format('d/m/Y') : ($monitor->created_at ? $monitor->created_at->format('d/m/Y') : 'N/A') }}</div>
                            <div class="small text-muted">{{ $monitor->fecha_ingreso ? $monitor->fecha_ingreso->format('h:i:s A') : ($monitor->created_at ? $monitor->created_at->format('h:i:s A') : '') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-primary">{{ $monitor->serial }}</div>
                            <div class="small text-muted">{{ $monitor->placa ?: 'Sin placa' }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $monitor->numero_traslado ?: 'N/A' }}</span></td>
                        <td>
                            @if($monitor->tipo_gestion === 'diagnostico')
                                <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="fa-solid fa-stethoscope me-1"></i>Diagnóstico</span>
                            @elseif($monitor->tipo_gestion === 'novedad')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="fa-solid fa-triangle-exclamation me-1"></i>Novedad</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="fa-solid fa-trash-can me-1"></i>Baja</span>
                            @endif
                        </td>
                        <td>
                            @if($monitor->tipo_gestion === 'diagnostico')
                                <div class="small text-dark">
                                    <span class="fw-bold">Energiza:</span> {!! $monitor->energiza ? '<span class="text-success"><i class="fa-solid fa-check"></i> Sí</span>' : '<span class="text-danger"><i class="fa-solid fa-xmark"></i> No</span>' !!} | 
                                    <span class="fw-bold">Video:</span> {!! $monitor->da_video ? '<span class="text-success"><i class="fa-solid fa-check"></i> Sí</span>' : '<span class="text-danger"><i class="fa-solid fa-xmark"></i> No</span>' !!}
                                </div>
                            @elseif($monitor->tipo_gestion === 'novedad')
                                <div class="small text-muted text-truncate" style="max-width: 200px;" title="{{ $monitor->motivo_novedad }}">
                                    {{ Str::limit($monitor->motivo_novedad, 40) }}
                                </div>
                            @else
                                <span class="text-muted small">Baja autorizada</span>
                            @endif
                        </td>
                        <td>
                            @if($monitor->estado_actual === 'funcional')
                                <span class="badge bg-success">Funcional</span>
                            @elseif($monitor->estado_actual === 'garantia')
                                <span class="badge bg-warning text-dark">Garantía</span>
                            @else
                                <span class="badge bg-danger">Baja</span>
                            @endif
                        </td>
                        <td>{{ $monitor->nombre_analista }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" title="Ver Detalles" onclick="alert('Detalles del monitor #{{ $monitor->id }}')">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-inbox fa-2x mb-2 d-block text-secondary"></i>
                            No se encontraron registros de monitores con los criterios seleccionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
            <div class="text-muted small">
                @if(method_exists($monitores, 'total'))
                    Mostrando <strong>{{ $monitores->firstItem() ?? 0 }}</strong> a <strong>{{ $monitores->lastItem() ?? 0 }}</strong> de <strong>{{ number_format($monitores->total()) }}</strong> registros
                @else
                    Mostrando <strong>{{ $monitores->count() }}</strong> registros
                @endif
            </div>
            <div>
                @if(method_exists($monitores, 'links'))
                    {{ $monitores->withQueryString()->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
