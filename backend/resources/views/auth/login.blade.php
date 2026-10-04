<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Sistema de Control CPUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { min-height: 100vh; background: linear-gradient(160deg, #0f172a 0%, #1e3a5f 55%, #2563eb 100%); }
        .login-card { max-width: 420px; border: 0; border-radius: 1rem; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
    <div class="card login-card shadow-lg w-100">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="fa-solid fa-microchip text-primary fa-2x mb-2"></i>
                <h1 class="h4 mb-1">Control CPUs</h1>
                <p class="text-muted small mb-0">Ingrese con su usuario de administración</p>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.authenticate') }}" method="post" autocomplete="off">
                @csrf
                <div class="mb-3">
                    <label for="usuario" class="form-label fw-semibold">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" value="{{ old('usuario') }}" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Ingresar
                </button>
            </form>
        </div>
    </div>
</body>
</html>
