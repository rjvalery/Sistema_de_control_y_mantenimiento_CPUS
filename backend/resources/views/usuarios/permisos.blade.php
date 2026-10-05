@extends('layouts.app')

@section('title', 'Permisos de ' . $usuario->nombre)

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h4 class="mb-0 fw-bold"><i class="fa-solid fa-shield-halved text-primary me-2"></i>Gestión de Permisos Granulares</h4>
        <p class="text-muted small mb-0 mt-1">
            Configuración de accesos dinámicos para: <strong>{{ $usuario->nombre }}</strong> 
            <span class="badge bg-light text-dark border ms-1">{{ $usuario->usuario }}</span>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">Rol: {{ ucfirst($usuario->rol ?? 'analista') }}</span>
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a Usuarios
        </a>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-3 rounded shadow-sm border mb-4">
    <div class="d-flex align-items-center text-muted small">
        <i class="fa-solid fa-circle-info text-info fs-5 me-2"></i>
        <span>Los cambios asignarán excepciones directas a este usuario. Los permisos con candado (<i class="fa-solid fa-lock text-muted mx-1"></i>) son heredados por su rol principal.</span>
    </div>
    <div class="mt-2 mt-sm-0 d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" id="btnMarcarTodos">
            <i class="fa-solid fa-check-double me-1"></i> Marcar Todos
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDesmarcarTodos">
            <i class="fa-solid fa-square-minus me-1"></i> Desmarcar Todos
        </button>
    </div>
</div>

<form action="{{ route('usuarios.guardar_permisos', $usuario->id) }}" method="POST" id="formPermisos">
    @csrf
    
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">
        @php
            $iconosModulo = [
                'Diagnóstico CPUs'                  => 'fa-solid fa-desktop text-primary',
                'Diagnóstico Portátiles'            => 'fa-solid fa-laptop text-success',
                'Mantenimiento / Soplado'           => 'fa-solid fa-wind text-info',
                'Inventario'                        => 'fa-solid fa-boxes-stacked text-warning',
                'Usuarios y Roles'                  => 'fa-solid fa-users-gear text-secondary',
                'Dashboard / Filtros de Privacidad' => 'fa-solid fa-user-shield text-danger',
            ];
        @endphp

        @foreach($permisosPorModulo as $modulo => $permisos)
        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-light border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="{{ $iconosModulo[$modulo] ?? 'fa-solid fa-layer-group text-primary' }} me-2"></i>{{ $modulo }}
                    </h6>
                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-muted btn-toggle-modulo" style="font-size: 0.75rem;" data-modulo="{{ Str::slug($modulo) }}">
                        Alternar
                    </button>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush modulo-group-{{ Str::slug($modulo) }}">
                        @foreach($permisos as $permiso)
                            @php
                                $tienePorRol = in_array($permiso->id, $permisosPorRol);
                                $tieneDirecto = in_array($permiso->id, $permisosDirectos);
                                $activo = ($tienePorRol || $tieneDirecto);
                            @endphp
                            
                            <li class="list-group-item py-3 {{ $tienePorRol ? 'bg-light bg-opacity-75' : '' }} border-bottom-0">
                                <div class="form-check form-switch mb-0 d-flex align-items-start">
                                    <input class="form-check-input mt-1 flex-shrink-0 permiso-checkbox" type="checkbox" role="switch" 
                                           id="permiso_{{ $permiso->id }}" 
                                           name="permisos[]" 
                                           value="{{ $permiso->id }}"
                                           {{ $activo ? 'checked' : '' }}
                                           {{ $tienePorRol ? 'disabled' : '' }}>
                                           
                                    <label class="form-check-label ms-3 flex-grow-1 cursor-pointer" for="permiso_{{ $permiso->id }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold {{ $tienePorRol ? 'text-muted' : 'text-dark' }}" style="font-size: 0.9rem;">
                                                {{ $permiso->nombre }}
                                            </span>
                                            @if($tienePorRol)
                                                <span class="badge bg-secondary-subtle text-secondary border small ms-2" title="Heredado por rol">
                                                    <i class="fa-solid fa-lock me-1"></i>Rol
                                                </span>
                                            @endif
                                        </div>
                                        @if($permiso->descripcion)
                                            <small class="d-block text-muted lh-sm mt-1" style="font-size: 0.78rem;">
                                                {{ $permiso->descripcion }}
                                            </small>
                                        @endif
                                    </label>
                                    
                                    @if($tienePorRol && $tieneDirecto)
                                        <input type="hidden" name="permisos[]" value="{{ $permiso->id }}">
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

    <div class="card border-0 shadow-sm sticky-bottom mb-4 bg-white border-top">
        <div class="card-body py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="fa-solid fa-floppy-disk me-1"></i> Asegúrate de guardar los cambios antes de salir.
            </span>
            <div class="d-flex gap-2">
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary px-3">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary fw-bold shadow-sm px-4">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Permisos
                </button>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnMarcar = document.getElementById('btnMarcarTodos');
    const btnDesmarcar = document.getElementById('btnDesmarcarTodos');

    if (btnMarcar) {
        btnMarcar.addEventListener('click', function() {
            document.querySelectorAll('.permiso-checkbox:not(:disabled)').forEach(cb => {
                cb.checked = true;
            });
        });
    }

    if (btnDesmarcar) {
        btnDesmarcar.addEventListener('click', function() {
            document.querySelectorAll('.permiso-checkbox:not(:disabled)').forEach(cb => {
                cb.checked = false;
            });
        });
    }

    document.querySelectorAll('.btn-toggle-modulo').forEach(btn => {
        btn.addEventListener('click', function() {
            const moduloSlug = this.dataset.modulo;
            const checks = document.querySelectorAll('.modulo-group-' + moduloSlug + ' .permiso-checkbox:not(:disabled)');
            const anyUnchecked = Array.from(checks).some(c => !c.checked);
            checks.forEach(c => c.checked = anyUnchecked);
        });
    });
});
</script>
@endsection
