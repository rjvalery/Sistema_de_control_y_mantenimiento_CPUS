@extends('layouts.app')

@section('title', 'Detalle del Equipo en Inventario')
@section('container_class', 'container-fluid px-3 px-xl-4 py-3')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-laptop-code text-primary me-2"></i>Detalle de Equipo
                </h5>
                <a href="{{ route('inventario.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i>Volver
                </a>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase mb-1">Identificador 1 (Placa)</label>
                        <div class="p-3 bg-light rounded border">{{ $equipo->identificador_1 ?: ($equipo->placa_id ?: '—') }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase mb-1">Identificador 2 (Serial)</label>
                        <div class="p-3 bg-light rounded border"><code>{{ $equipo->identificador_2 ?: ($equipo->serial ?: '—') }}</code></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase mb-1">Modelo / Referencia</label>
                        <div class="p-3 bg-light rounded border">{{ $equipo->ref_principal ?: ($equipo->modelo ?: '—') }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase mb-1">Ubicación</label>
                        <div class="p-3 bg-light rounded border">{{ $equipo->ubicacion_origen ?: ($equipo->ubicacion ?: '—') }}</div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small fw-bold text-uppercase mb-1">Estado en Inventario</label>
                        <div class="p-3 bg-light rounded border">
                            @if ($equipo->intervenido)
                                <span class="badge bg-success">Intervenido / Agregado a Sistema ({{ $equipo->modulo_intervencion }})</span>
                                @if($equipo->fecha_intervencion)
                                    <small class="text-muted ms-2">el {{ date('d/m/Y H:i', strtotime($equipo->fecha_intervencion)) }}</small>
                                @endif
                            @else
                                <span class="badge bg-warning text-dark">Pendiente por Intervenir</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small fw-bold text-uppercase mb-1">Observaciones Base</label>
                        <div class="p-3 bg-light rounded border">{{ $equipo->observaciones ?: 'Sin observaciones.' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
