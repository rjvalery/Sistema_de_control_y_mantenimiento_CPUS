<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Inicio') - Sistema de Control CPUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background-color: #f8fafc; min-height: 100vh; display: flex; flex-direction: column; }
        .content-wrapper { flex: 1; }
        .navbar-brand { font-weight: 600; letter-spacing: -0.5px; }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fa-solid fa-microchip text-primary me-2"></i>Control CPUs
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}"><i class="fa-solid fa-gauge-high me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-desktop me-1"></i> Diagnóstico CPU
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('equipos.create') }}"><i class="fa-solid fa-plus me-2 text-primary"></i>Nuevo Registro</a></li>
                            <li><a class="dropdown-item" href="{{ route('equipos.index') }}"><i class="fa-solid fa-list me-2 text-secondary"></i>Bitácora</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-wind me-1"></i> Soplado
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('soplado.create') }}"><i class="fa-solid fa-plus me-2 text-info"></i>Nuevo Registro</a></li>
                            <li><a class="dropdown-item" href="{{ route('soplado.index') }}"><i class="fa-solid fa-list me-2 text-secondary"></i>Bitácora</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-laptop me-1"></i> Diagnóstico Portátiles
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('portatiles.create') }}"><i class="fa-solid fa-plus me-2 text-success"></i>Nuevo Diagnóstico</a></li>
                            <li><a class="dropdown-item" href="{{ route('portatiles.index') }}"><i class="fa-solid fa-list me-2 text-secondary"></i>Bitácora</a></li>
                        </ul>
                    </li>
                    @can('inventario.cargue_masivo')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('inventario.index') }}"><i class="fa-solid fa-boxes-stacked me-1"></i> Inventario</a>
                    </li>
                    @endcan
                    @can('usuarios.ver')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('usuarios.index') }}"><i class="fa-solid fa-users-gear me-1"></i> Usuarios</a>
                        </li>
                    @endcan
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user-shield me-1"></i> {{ auth()->user()->nombre ?? 'Usuario' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->usuario ?? '' }} ({{ auth()->user()->rol ?? 'analista' }})</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalCambiarPasswordPropia">
                                    <i class="fa-solid fa-key me-2 text-warning"></i> Cambiar mi contraseña
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger border-0 bg-transparent">
                                        <i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="content-wrapper py-4">
        <div class="@yield('container_class', 'container')">
            @if (session('msg'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>{{ session('msg') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Verifica los errores del formulario.
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Modal cambiar contraseña propia -->
    <div class="modal fade" id="modalCambiarPasswordPropia" tabindex="-1" aria-labelledby="modalCambiarPasswordPropiaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white py-3 px-4">
                    <h5 class="modal-title fs-6 fw-bold mb-0" id="modalCambiarPasswordPropiaLabel">
                        <i class="fa-solid fa-key me-2 text-warning"></i>Cambiar Mi Contraseña
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('usuarios.cambiar_password') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Contraseña Actual *</label>
                            <input type="password" name="password_actual" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nueva Contraseña *</label>
                            <input type="password" name="password_nueva" class="form-control" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Confirmar Nueva Contraseña *</label>
                            <input type="password" name="password_confirmar" class="form-control" required minlength="8">
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold">Actualizar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
