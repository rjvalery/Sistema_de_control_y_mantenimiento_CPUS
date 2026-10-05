@extends('layouts.app')
@section('title', 'Bitácora Soplado')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-wind me-2 text-info"></i>Bitácora de Soplado y Mantenimiento</h5>
        @can('soplado.registrar')
        <a href="{{ route('soplado.create') }}" class="btn btn-info btn-sm text-white"><i class="fa-solid fa-plus me-1"></i> Nuevo Registro</a>
        @endcan
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soplado.index') }}" class="row g-2 mb-4 align-items-center">
            <div class="col-md-4">
                <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar placa, traslado o analista..." value="{{ request('buscar') }}">
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
                <button type="submit" class="btn btn-info btn-sm text-white w-100"><i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar</button>
                @if(request()->hasAny(['buscar', 'fecha_desde', 'fecha_hasta', 'limite']))
                    <a href="{{ route('soplado.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros"><i class="fa-solid fa-xmark"></i></a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle table-sm border">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Placa</th>
                        <th>Traslado</th>
                        <th>Analista</th>
                        <th>Energiza</th>
                        <th>Da Video</th>
                        <th>Máquina Contenía</th>
                        <th class="text-center">Evidencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $row)
                    <tr>
                        <td>{{ $row->id }}</td>
                        <td>{{ $row->created_at ? $row->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td class="fw-bold text-primary">{{ $row->placa_id }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $row->num_traslado ?: 'N/A' }}</span></td>
                        <td>{{ $row->nombre_analista }}</td>
                        <td>
                            @if($row->energiza === 'Si')
                                <span class="badge bg-success-subtle text-success">Sí</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">No</span>
                            @endif
                        </td>
                        <td>
                            @if($row->da_video === 'Si')
                                <span class="badge bg-success-subtle text-success">Sí</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">No</span>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary-subtle text-secondary">{{ $row->maquina_contenia ?: 'Normal' }}</span></td>
                        <td class="text-center">
                            @if(!empty($row->foto_ruta) || !empty($row->evidencia))
                                <a href="{{ \App\Services\UploadService::routeEvidencia($row->foto_ruta ?? $row->evidencia, 'soplado', $row->created_at ?? $row->fecha_creacion, $row->placa_id) }}" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2" title="Ver Evidencia Fotográfica">
                                    <i class="fa-solid fa-image"></i>
                                </a>
                            @else
                                <span class="text-muted small">Sin foto</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-inbox fa-2x mb-2 d-block text-secondary"></i>
                            No se encontraron registros de soplado con los criterios seleccionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
            <div class="text-muted small">
                Mostrando <strong>{{ $registros->firstItem() ?? 0 }}</strong> a <strong>{{ $registros->lastItem() ?? 0 }}</strong> de <strong>{{ number_format($registros->total()) }}</strong> registros
                @if($totalFiltrados != $totalGeneral)
                    (filtrados de un total de {{ number_format($totalGeneral) }})
                @endif
            </div>
            <div>
                {{ $registros->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
