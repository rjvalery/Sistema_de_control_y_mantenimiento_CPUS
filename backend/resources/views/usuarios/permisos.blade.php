@extends('layouts.app')

@section('title', 'Asignar Permisos a ' . $usuario->nombre)

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h4 class="mb-0 fw-bold"><i class="fa-solid fa-user-shield text-primary me-2"></i>Gestión de Permisos</h4>
        <p class="text-muted small mb-0 mt-1">Configuración de accesos dinámicos para el usuario: <strong>{{ $usuario->nombre }}</strong> ({{ $usuario->usuario }})</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a Usuarios
        </a>
    </div>
</div>

<div class="alert alert-info border-info-subtle shadow-sm small">
    <i class="fa-solid fa-circle-info me-2"></i>
    <strong>Guía de Permisos:</strong> Los permisos marcados en color grisáceo (<i class="fa-solid fa-lock text-muted mx-1"></i>) son 
    heredados por el rol (ej: Administrador o Analista) y no se pueden remover desde esta pantalla. 
    Selecciona las casillas para agregar o quitar excepciones directas al usuario.
</div>

<form action="{{ route('usuarios.guardar_permisos', $usuario->id) }}" method="POST">
    @csrf
    
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
        @foreach($permisosPorModulo as $modulo => $permisos)
        <div class="col">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-light border-bottom-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-layer-group text-secondary me-2"></i>{{ $modulo }}</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($permisos as $permiso)
                            @php
                                $tienePorRol = in_array($permiso->id, $permisosPorRol);
                                $tieneDirecto = in_array($permiso->id, $permisosDirectos);
                            @endphp
                            
                            <li class="list-group-item py-3 {{ $tienePorRol ? 'bg-light bg-opacity-50' : '' }}">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" 
                                           id="permiso_{{ $permiso->id }}" 
                                           name="permisos[]" 
                                           value="{{ $permiso->id }}"
                                           {{ ($tienePorRol || $tieneDirecto) ? 'checked' : '' }}
                                           {{ $tienePorRol ? 'disabled' : '' }}>
                                           
                                    <label class="form-check-label ms-2 d-block w-100 cursor-pointer" for="permiso_{{ $permiso->id }}">
                                        <span class="fw-semibold {{ $tienePorRol ? 'text-muted' : 'text-dark' }}">
                                            {{ $permiso->nombre }}
                                            @if($tienePorRol)
                                                <i class="fa-solid fa-lock text-muted float-end" title="Heredado del Rol"></i>
                                            @endif
                                        </span>
                                        @if($permiso->descripcion)
                                            <small class="d-block text-muted lh-sm mt-1" style="font-size: 0.75rem;">
                                                {{ $permiso->descripcion }}
                                            </small>
                                        @endif
                                    </label>
                                    
                                    @if($tienePorRol)
                                        <!-- Envío silencioso del permiso si está disabled para no perderlo si también lo tiene directo -->
                                        @if($tieneDirecto)
                                            <input type="hidden" name="permisos[]" value="{{ $permiso->id }}">
                                        @endif
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body bg-light text-end">
            <button type="submit" class="btn btn-primary fw-bold shadow-sm px-4">
                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Permisos
            </button>
        </div>
    </div>
</form>

@endsection
