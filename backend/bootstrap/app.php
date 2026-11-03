<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'portatiles/guardar',
            'portatiles/guardar-evidencia',
            'equipos/guardar',
            'soplado/guardar',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El token de seguridad ha expirado. Por favor, recargue la página.'
                ], 419);
            }
            return redirect()->route('login')->with('error', 'Su sesión o enlace expiró por inactividad. Por favor ingrese sus credenciales nuevamente.');
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permisos para realizar esta acción.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Acceso restringido: No cuentas con los permisos asignados para acceder a ese módulo.');
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'El token de seguridad ha expirado. Por favor recargue la página.'
                    ], 419);
                }
                return redirect()->route('login')->with('error', 'Su sesión expiró por inactividad. Por favor intente ingresar nuevamente.');
            }

            if ($e->getStatusCode() === 403) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No tienes permisos para realizar esta acción.'
                    ], 403);
                }
                return redirect()->route('dashboard')->with('error', 'Acceso restringido: No cuentas con los permisos asignados para acceder a ese módulo.');
            }
        });
    })->create();
