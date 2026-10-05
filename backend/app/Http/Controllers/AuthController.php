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

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withInput()->with('error', 'Demasiados intentos. Espera un momento.');
        }

        $credentials = $request->only('usuario', 'password');

        $usuario = Usuario::where('usuario', $credentials['usuario'])->first();

        if ($usuario && Hash::check($credentials['password'], $usuario->password) && $usuario->activo) {
            Auth::login($usuario);
            RateLimiter::clear($key);
            $request->session()->regenerate();
            $request->session()->forget('url.intended');
            return redirect()->route('dashboard');
        }

        RateLimiter::hit($key, 5 * 60);

        return back()->withInput()->with('error', 'Credenciales inválidas.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
