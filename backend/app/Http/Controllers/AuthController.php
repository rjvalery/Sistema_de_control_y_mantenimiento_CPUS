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
    public function authenticate(AuthLoginRequest $request)
    {
        $key = 'login.attempts.' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['error' => 'Demasiados intentos. Espera un momento.'], 429);
        }

        $credentials = $request->only('usuario', 'password');

        $usuario = Usuario::where('usuario', $credentials['usuario'])->first();

        if ($usuario && Hash::check($credentials['password'], $usuario->password) && $usuario->activo) {
            Auth::login($usuario);
            RateLimiter::clear($key);
            
            // For Sanctum stateless
            $token = $usuario->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true, 
                'token' => $token,
                'user' => $usuario
            ]);
        }

        RateLimiter::hit($key, 5 * 60);

        return response()->json(['error' => 'Credenciales inválidas.'], 401);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }
        
        return response()->json(['success' => true, 'message' => 'Logged out']);
    }
}
