@extends('layouts.app')

@section('title', 'Bitácora de Diademas por Lote')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-headset me-2 text-primary"></i>Bitácora de Diademas Recibidas</h5>
        <a href="{{ route('diademas.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Recepción por Lote</a>
    </div>
    <div class="card-body">
        
        <div class="table-responsive mt-3">
            <table class="table table-hover align-middle table-sm border">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"><i class="fa-regular fa-clock me-1"></i> Fecha y Hora</th>
                        <th>N° Traslado</th>
                        <th class="text-center">Total Unidades</th>
                        <th class="text-center">Desglose</th>
                        <th>Analista</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lotes as $lote)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold text-dark">{{ $lote->fecha_ingreso ? $lote->fecha_ingreso->format('d/m/Y') : ($lote->created_at ? $lote->created_at->format('d/m/Y') : 'N/A') }}</div>
                            <div class="small text-muted">{{ $lote->fecha_ingreso ? $lote->fecha_ingreso->format('h:i:s A') : ($lote->created_at ? $lote->created_at->format('h:i:s A') : '') }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $lote->numero_traslado }}</span></td>
                        <td class="text-center fw-bold fs-6">{{ $lote->total_unidades }}</td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle" title="Funcionales">{{ $lote->total_funcionales }} F</span>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle" title="Garantías">{{ $lote->total_garantia }} G</span>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="Bajas">{{ $lote->total_baja }} B</span>
                        </td>
                        <td>{{ $lote->nombre_analista }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" title="Ver Detalles" onclick="alert('Detalles del lote #{{ $lote->id }}')">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-inbox fa-2x mb-2 d-block text-secondary"></i>
                            No se encontraron registros de lotes de diademas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            @if(method_exists($lotes, 'links'))
                {{ $lotes->links() }}
            @endif
        </div>
    </div>
</div>
@endsection
