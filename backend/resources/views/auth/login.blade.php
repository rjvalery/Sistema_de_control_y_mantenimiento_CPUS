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
                <p class="text-muted small mb-0">Ingrese con su usuario del sistema</p>
            </div>

            @if (session('msg'))
                <div class="alert alert-success d-flex align-items-center py-2" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    <div>{{ session('msg') }}</div>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger d-flex align-items-center py-2" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.authenticate') }}" method="post" autocomplete="off">
                @csrf
                <div class="mb-3">
                    <label for="usuario" class="form-label fw-semibold">Usuario o Nombre</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-user"></i></span>
                        <input type="text" class="form-control" id="usuario" name="usuario" value="{{ old('usuario') }}" placeholder="Ej: admin o rjvalery" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" title="Mostrar/Ocultar contraseña">
                            <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    <div id="capsLockWarning" class="form-text text-warning d-none">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Bloq Mayús (Caps Lock) está activado
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Ingresar
                </button>
            </form>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const toggleIcon = document.getElementById('togglePasswordIcon');
        const capsWarning = document.getElementById('capsLockWarning');

        toggleBtn.addEventListener('click', function() {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            toggleIcon.classList.toggle('fa-eye', !isPassword);
            toggleIcon.classList.toggle('fa-eye-slash', isPassword);
        });

        passwordInput.addEventListener('keyup', function(e) {
            if (e.getModifierState && e.getModifierState('CapsLock')) {
                capsWarning.classList.remove('d-none');
            } else {
                capsWarning.classList.add('d-none');
            }
        });
    </script>
</body>
</html>
