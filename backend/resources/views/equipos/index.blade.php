@extends('layouts.app')
@section('title', 'Bitácora Equipos')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-list me-2 text-primary"></i>Bitácora de Diagnóstico CPU</h5>
        <a href="{{ route('equipos.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Nuevo Registro</a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('equipos.index') }}" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar placa, traslado o analista..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar</button>
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
                        <th>Gestión</th>
                        <th>Video</th>
                        <th>Estado Final</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $row)
                    <tr>
                        <td>{{ $row->id }}</td>
                        <td>{{ $row->fecha_creacion->format('d/m/Y H:i') }}</td>
                        <td class="fw-bold">{{ $row->placa_id }}</td>
                        <td>{{ $row->num_traslado }}</td>
                        <td>{{ $row->nombre_analista }}</td>
                        <td><span class="badge bg-info text-dark">{{ $row->tipo_gestion }}</span></td>
                        <td>{{ $row->da_video }}</td>
                        <td>{{ $row->estado_actual }}</td>
                        <td class="text-center">
                            @can('diagnostico.registrar')
                                <button class="btn btn-sm btn-outline-primary" title="Editar"><i class="fa-solid fa-pen"></i></button>
                            @endcan
                            @can('registros.eliminar')
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-3 text-muted">No se encontraron registros.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
