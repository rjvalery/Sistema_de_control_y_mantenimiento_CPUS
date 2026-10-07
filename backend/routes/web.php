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
    Route::get('/dashboard/exportar-bitacora', [\App\Http\Controllers\DashboardController::class, 'exportarBitacora'])->name('dashboard.exportar');

    // Inventario (API para búsqueda AJAX)
    Route::get('/inventario/buscar-equipo', [\App\Http\Controllers\InventarioController::class, 'buscarEquipo'])->name('inventario.buscar');

    // Módulo Inventario
    Route::middleware(['auth', 'can:inventario.ver'])->group(function () {
        Route::get('/inventario', [\App\Http\Controllers\InventarioController::class, 'index'])->name('inventario.index');
        Route::get('/inventario/sincronizar', [\App\Http\Controllers\InventarioController::class, 'sincronizar'])->name('inventario.sincronizar');
        Route::get('/inventario/{id}', [\App\Http\Controllers\InventarioController::class, 'show'])->name('inventario.show')->where('id', '[0-9]+');
    });

    // Módulo Cargue Masivo (Control de acceso restringido a admin/supervisores)
    Route::middleware(['auth', 'can:cargue_masivo.ejecutar'])->group(function () {
        Route::get('/cargue-masivo', [\App\Http\Controllers\CargueMasivoController::class, 'index'])->name('cargue-masivo.index');
        Route::post('/cargue-masivo/procesar', [\App\Http\Controllers\CargueMasivoController::class, 'procesar'])->name('cargue-masivo.procesar');
        Route::get('/cargue-masivo/plantilla', [\App\Http\Controllers\CargueMasivoController::class, 'descargarPlantilla'])->name('cargue-masivo.plantilla');
    });

    // Visor seguro de evidencias técnicas
    Route::get('/evidencias/ver/{modulo}/{anio}/{mes}/{dia}/{archivo}', [\App\Http\Controllers\EvidenciaController::class, 'ver'])->name('evidencias.ver');
    Route::get('/evidencias/{path}', [\App\Http\Controllers\EvidenciaController::class, 'show'])
        ->where('path', '.*')
        ->name('evidencias.show');

    // Diagnóstico CPUs (Protegido por RBAC)
    Route::get('/equipos/bitacora', [EquiposController::class, 'index'])->middleware('can:cpus.ver_bitacora')->name('equipos.index');
    Route::middleware('can:cpus.registrar')->group(function () {
        Route::get('/equipos/formulario', [EquiposController::class, 'create'])->name('equipos.create');
        Route::post('/equipos/guardar', [EquiposController::class, 'store'])->name('equipos.store');
    });

    // Mantenimiento / Soplado (Protegido por RBAC)
    Route::get('/soplado/bitacora', [SopladoController::class, 'index'])->middleware('can:soplado.ver_bitacora')->name('soplado.index');
    Route::middleware('can:soplado.registrar')->group(function () {
        Route::get('/soplado/formulario', [SopladoController::class, 'create'])->name('soplado.create');
        Route::get('/soplado/ultimo-registro', [SopladoController::class, 'ultimoRegistro'])->name('soplado.ultimo_registro');
        Route::post('/soplado/guardar', [SopladoController::class, 'store'])->name('soplado.store');
    });

    // Diagnóstico Portátiles (Protegido por RBAC)
    Route::get('/portatiles/bitacora', [PortatilesController::class, 'index'])->middleware('can:portatiles.ver_bitacora')->name('portatiles.index');
    Route::middleware('can:portatiles.registrar')->group(function () {
        Route::get('/portatiles/formulario', [PortatilesController::class, 'create'])->name('portatiles.create');
        Route::get('/portatiles/ultimo-registro', [PortatilesController::class, 'ultimoRegistro'])->name('portatiles.ultimo_registro');
        Route::get('/portatiles/evidencia', [PortatilesController::class, 'evidencia'])->name('portatiles.evidencia');
        Route::post('/portatiles/guardar-evidencia', [PortatilesController::class, 'guardarEvidencia'])->name('portatiles.guardarEvidencia');
        Route::post('/portatiles/guardar', [PortatilesController::class, 'store'])->name('portatiles.store');
    });

    // Usuarios (Gestión RBAC protegida a nivel de ruta)
    Route::middleware('can:usuarios.ver')->group(function () {
        Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios/crear', [UsuariosController::class, 'store'])->middleware('can:usuarios.crear')->name('usuarios.store');
        Route::post('/usuarios/editar', [UsuariosController::class, 'update'])->middleware('can:usuarios.editar')->name('usuarios.update');
        Route::post('/usuarios/{id}/eliminar', [UsuariosController::class, 'destroy'])->middleware('can:usuarios.eliminar')->name('usuarios.destroy');
        Route::delete('/usuarios/{id}', [UsuariosController::class, 'destroy'])->middleware('can:usuarios.eliminar');
        Route::get('/usuarios/{id}/permisos', [UsuariosController::class, 'permisos'])->middleware('can:usuarios.permisos')->name('usuarios.permisos');
        Route::post('/usuarios/{id}/permisos', [UsuariosController::class, 'guardarPermisos'])->middleware('can:usuarios.permisos')->name('usuarios.guardar_permisos');
    });

    Route::post('/usuarios/cambiar-password', [UsuariosController::class, 'cambiarPasswordPropia'])->name('usuarios.cambiar_password');

    // Módulo de Trazabilidad (Hoja de Vida)
    Route::middleware('can:trazabilidad.ver')->group(function () {
        Route::get('/trazabilidad', [\App\Http\Controllers\TrazabilidadController::class, 'index'])->name('trazabilidad.index');
        Route::get('/trazabilidad/buscar', [\App\Http\Controllers\TrazabilidadController::class, 'buscar'])->name('trazabilidad.buscar');
    });

    // Módulo de Reacondicionamiento de Monitores
    Route::prefix('monitores')->name('monitores.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MonitoresController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\MonitoresController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\MonitoresController::class, 'store'])->name('store');
        Route::get('/buscar/{serial}', [\App\Http\Controllers\MonitoresController::class, 'buscarEquipo'])->name('buscar');
    });

    // Módulo de Recepción de Diademas por Lote
    Route::prefix('diademas')->name('diademas.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DiademasController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\DiademasController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\DiademasController::class, 'store'])->name('store');
    });
});
