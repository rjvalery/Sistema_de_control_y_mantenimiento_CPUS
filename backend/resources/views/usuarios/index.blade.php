@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h4 class="mb-0 fw-bold"><i class="fa-solid fa-users-gear text-primary me-2"></i>Gestión de Usuarios</h4>
        <p class="text-muted small mb-0 mt-1">Administración de accesos y roles del sistema.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        @can('usuarios.crear')
        <button type="button" class="btn btn-primary shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
            <i class="fa-solid fa-user-plus me-1"></i> Nuevo Usuario
        </button>
        @endcan
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nombre</th>
                        <th>Usuario (Login)</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-center pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $user)
                        <tr>
                            <td class="ps-4 fw-semibold text-dark">{{ $user->nombre }}</td>
                            <td class="text-muted">{{ $user->usuario }}</td>
                            <td>
                                @foreach($user->rolesRelation as $r)
                                    <span class="badge bg-{{ $r->slug === 'admin' ? 'danger' : ($r->slug === 'analista' ? 'primary' : 'info') }} text-white">
                                        {{ $r->nombre }}
                                    </span>
                                @endforeach
                                @if($user->rolesRelation->isEmpty())
                                    <span class="badge bg-secondary">Sin Rol</span>
                                @endif
                            </td>
                            <td>
                                @if($user->activo)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-check-circle me-1"></i>Activo</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="fa-solid fa-ban me-1"></i>Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                @can('usuarios.editar')
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEditarUsuario{{ $user->id }}"
                                        title="Editar Usuario">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                @endcan
                                
                                @can('usuarios.permisos')
                                <a href="{{ route('usuarios.permisos', $user->id) }}" class="btn btn-sm btn-outline-info" title="Permisos Específicos">
                                    <i class="fa-solid fa-user-lock"></i>
                                </a>
                                @endcan
                                
                                @can('usuarios.eliminar')
                                    @if(auth()->id() !== $user->id)
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEliminarUsuario{{ $user->id }}"
                                            title="Eliminar Usuario">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="No puedes eliminar tu propia cuenta">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    @endif
                                @endcan
                            </td>
                        </tr>

                        <!-- Modal Editar -->
                        @can('usuarios.editar')
                        <div class="modal fade" id="modalEditarUsuario{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen text-primary me-2"></i>Editar Usuario</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('usuarios.update') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $user->id }}">
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Nombre Completo</label>
                                                <input type="text" name="nombre" class="form-control" value="{{ $user->nombre }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Usuario (Login)</label>
                                                <input type="text" name="usuario" class="form-control" value="{{ $user->usuario }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Rol</label>
                                                <select name="rol_id" class="form-select" required>
                                                    @foreach($roles as $rol)
                                                        <option value="{{ $rol->id }}" {{ $user->rolesRelation->contains('id', $rol->id) ? 'selected' : '' }}>
                                                            {{ $rol->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Estado</label>
                                                <select name="activo" class="form-select" required>
                                                    <option value="1" {{ $user->activo ? 'selected' : '' }}>Activo (Permitir acceso)</option>
                                                    <option value="0" {{ !$user->activo ? 'selected' : '' }}>Inactivo (Bloquear acceso)</option>
                                                </select>
                                            </div>
                                            <hr>
                                            <div class="mb-2">
                                                <label class="form-label fw-bold small text-danger"><i class="fa-solid fa-key me-1"></i>Cambiar Contraseña (Opcional)</label>
                                                <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
                                                <div class="form-text small">Mínimo 6 caracteres.</div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary btn-sm fw-bold">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endcan

                        <!-- Modal Eliminar Usuario -->
                        @can('usuarios.eliminar')
                        @if(auth()->id() !== $user->id)
                        <div class="modal fade" id="modalEliminarUsuario{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Confirmar Eliminación</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('usuarios.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body p-4 text-center">
                                            <div class="display-6 text-danger mb-3">
                                                <i class="fa-solid fa-circle-exclamation"></i>
                                            </div>
                                            <h6 class="fw-bold mb-2">¿Estás seguro de que deseas eliminar este usuario?</h6>
                                            <p class="text-muted small mb-3">
                                                Esta acción eliminará la cuenta de <strong>{{ $user->nombre }}</strong> (<code>{{ $user->usuario }}</code>) y cancelará todos sus accesos al sistema.
                                            </p>
                                            <div class="alert alert-warning py-2 small text-start mb-0">
                                                <i class="fa-solid fa-info-circle me-1"></i>
                                                <strong>Nota de trazabilidad:</strong> Los registros técnicos e intervenciones históricas asociados a este nombre se preservarán en las bitácoras para auditoría técnica.
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-danger btn-sm fw-bold">
                                                <i class="fa-solid fa-trash me-1"></i> Sí, Eliminar Usuario
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endcan
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-users-slash fs-4 d-block mb-2 text-secondary opacity-50"></i>
                                No hay usuarios registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Crear Usuario -->
@can('usuarios.crear')
<div class="modal fade" id="modalCrearUsuario" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i>Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('usuarios.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nombre Completo *</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Usuario de Login *</label>
                        <input type="text" name="usuario" class="form-control" value="{{ old('usuario') }}" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Contraseña *</label>
                        <input type="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
                        <div class="form-text small">Mínimo 6 caracteres.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Rol Asignado *</label>
                        <select name="rol_id" class="form-select" required>
                            <option value="" disabled {{ old('rol_id') === null ? 'selected' : '' }}>-- Seleccione una opción --</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}" {{ old('rol_id') == $rol->id ? 'selected' : '' }}>{{ $rol->nombre }} ({{ $rol->descripcion }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection
