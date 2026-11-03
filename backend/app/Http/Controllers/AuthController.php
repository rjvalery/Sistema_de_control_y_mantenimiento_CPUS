<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthLoginRequest;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function authenticate(AuthLoginRequest $request)
    {
        $key = 'login_attempt_' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withInput()->with('error', "Demasiados intentos fallidos. Por favor espera {$seconds} segundos antes de intentar nuevamente.");
        }

        $usuarioInput = trim($request->input('usuario'));
        $password = (string) $request->input('password');

        // Búsqueda flexible: por usuario exacto, insensible a mayúsculas/minúsculas o por nombre completo
        $usuario = Usuario::where('usuario', $usuarioInput)
            ->orWhereRaw('LOWER(TRIM(usuario)) = ?', [strtolower($usuarioInput)])
            ->orWhereRaw('LOWER(TRIM(nombre)) = ?', [strtolower($usuarioInput)])
            ->first();

        if (!$usuario) {
            RateLimiter::hit($key, 3 * 60);
            return back()->withInput()->with('error', 'El usuario ingresado no existe en el sistema.');
        }

        // Validación de contraseña con soporte para bcrypt y hashes legados si correspondiese
        $passwordValida = false;
        if (Hash::check($password, $usuario->password)) {
            $passwordValida = true;
        } elseif ($usuario->password === $password || $usuario->password === md5($password)) {
            // Contraseña legada o sin hash: migrar automáticamente a bcrypt
            $usuario->password = Hash::make($password);
            $usuario->save();
            $passwordValida = true;
        }

        if (!$passwordValida) {
            RateLimiter::hit($key, 3 * 60);
            return back()->withInput()->with('error', 'Contraseña incorrecta. Por favor verifica tus credenciales.');
        }

        if (!$usuario->activo) {
            return back()->withInput()->with('error', 'Tu usuario se encuentra inactivo. Comunícate con el administrador.');
        }

        Auth::login($usuario);
        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
