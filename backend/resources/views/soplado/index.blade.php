@extends('layouts.app')
@section('title', 'Bitácora Soplado')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-list me-2 text-info"></i>Bitácora de Soplado</h5>
        <a href="{{ route('soplado.create') }}" class="btn btn-info btn-sm text-white"><i class="fa-solid fa-plus me-1"></i> Nuevo Registro</a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soplado.index') }}" class="row g-3 mb-4">
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
                        <th>Energiza</th>
                        <th>Da Video</th>
                        <th>Máquina Contenía</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $row)
                    <tr>
                        <td>{{ $row->id }}</td>
                        <td>{{ $row->created_at->format('d/m/Y H:i') }}</td>
                        <td class="fw-bold">{{ $row->placa_id }}</td>
                        <td>{{ $row->num_traslado }}</td>
                        <td>{{ $row->nombre_analista }}</td>
                        <td>{{ $row->energiza }}</td>
                        <td>{{ $row->da_video }}</td>
                        <td>{{ $row->maquina_contenia }}</td>
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
