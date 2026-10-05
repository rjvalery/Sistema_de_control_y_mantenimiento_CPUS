<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\AiWorkerServiceInterface::class,
            \App\Services\FastApiWorkerService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Superadministrador tiene todos los permisos
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                return true;
            }

            // Evaluación dinámica: permite usar @can('slug.permiso') o middleware 'can:slug.permiso'
            if (method_exists($user, 'hasPermission') && $ability !== 'has-permission') {
                if ($user->hasPermission($ability)) {
                    return true;
                }
            }
        });

        // Registrar gate global para evaluar permisos con sintaxis Gate::authorize('has-permission', 'slug')
        Gate::define('has-permission', function ($user, $permissionSlug) {
            return method_exists($user, 'hasPermission') ? $user->hasPermission($permissionSlug) : false;
        });

        // Habilitar estilo nativo Bootstrap 5 para paginaciones de Laravel
        \Illuminate\Pagination\Paginator::useBootstrapFive();
    }
}
