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
        // Implicitly grant "admin" role all permissions
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });

        // Registrar gate global para evaluar cualquier permiso por slug
        // Ejemplo: @can('inventario.cargue_masivo')
        Gate::define('has-permission', function ($user, $permissionSlug) {
            return $user->hasPermission($permissionSlug);
        });
    }
}
