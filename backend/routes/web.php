<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EquiposController;
use App\Http\Controllers\SopladoController;
use App\Http\Controllers\PortatilesController;
use App\Http\Controllers\UsuariosController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/metricas', [\App\Http\Controllers\DashboardController::class, 'metricas'])->name('dashboard.metricas');

    // Inventario (API para búsqueda AJAX)
    Route::get('/inventario/buscar-equipo', [\App\Http\Controllers\InventarioController::class, 'buscarEquipo'])->name('inventario.buscar');

    // Inventario (Cargue Masivo y Bitácora protegido por RBAC)
    Route::middleware('can:inventario.cargue_masivo')->group(function () {
        Route::get('/inventario', [\App\Http\Controllers\CargueMasivoController::class, 'index'])->name('inventario.index');
        Route::get('/inventario/plantilla', [\App\Http\Controllers\CargueMasivoController::class, 'plantilla'])->name('inventario.plantilla');
        Route::post('/inventario/procesar', [\App\Http\Controllers\CargueMasivoController::class, 'procesar'])->name('inventario.procesar');
        Route::get('/inventario/sincronizar', [\App\Http\Controllers\CargueMasivoController::class, 'sincronizar'])->name('inventario.sincronizar');
    });

    // Visor seguro de evidencias
    Route::get('/evidencias/{path}', [\App\Http\Controllers\EvidenciaController::class, 'show'])
        ->where('path', '.*')
        ->name('evidencias.show');

    // Equipos
    Route::get('/equipos/formulario', [EquiposController::class, 'create'])->name('equipos.create');
    Route::post('/equipos/guardar', [EquiposController::class, 'store'])->name('equipos.store');
    Route::get('/equipos/bitacora', [EquiposController::class, 'index'])->name('equipos.index');

    // Soplado
    Route::get('/soplado/formulario', [SopladoController::class, 'create'])->name('soplado.create');
    Route::get('/soplado/ultimo-registro', [SopladoController::class, 'ultimoRegistro'])->name('soplado.ultimo_registro');
    Route::post('/soplado/guardar', [SopladoController::class, 'store'])->name('soplado.store');
    Route::get('/soplado/bitacora', [SopladoController::class, 'index'])->name('soplado.index');

    // Portatiles
    Route::get('/portatiles/formulario', [PortatilesController::class, 'create'])->name('portatiles.create');
    Route::get('/portatiles/ultimo-registro', [PortatilesController::class, 'ultimoRegistro'])->name('portatiles.ultimo_registro');
    Route::get('/portatiles/evidencia', [PortatilesController::class, 'evidencia'])->name('portatiles.evidencia');
    Route::post('/portatiles/guardar-evidencia', [PortatilesController::class, 'guardarEvidencia'])->name('portatiles.guardarEvidencia');
    Route::post('/portatiles/guardar', [PortatilesController::class, 'store'])->name('portatiles.store');
    Route::get('/portatiles/bitacora', [PortatilesController::class, 'index'])->name('portatiles.index');

    // Usuarios (Gestión RBAC protegida a nivel de ruta)
    Route::middleware('can:usuarios.ver')->group(function () {
        Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios/crear', [UsuariosController::class, 'store'])->middleware('can:usuarios.crear')->name('usuarios.store');
        Route::post('/usuarios/editar', [UsuariosController::class, 'update'])->middleware('can:usuarios.editar')->name('usuarios.update');
        Route::get('/usuarios/{id}/permisos', [UsuariosController::class, 'permisos'])->middleware('can:usuarios.permisos')->name('usuarios.permisos');
        Route::post('/usuarios/{id}/permisos', [UsuariosController::class, 'guardarPermisos'])->middleware('can:usuarios.permisos')->name('usuarios.guardar_permisos');
    });

    Route::post('/usuarios/cambiar-password', [UsuariosController::class, 'cambiarPasswordPropia'])->name('usuarios.cambiar_password');
});
