<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

try {
    echo "Starting final fix...\n";
    
    // 1. Drop tables if they exist to start fresh and avoid "table already exists"
    Schema::dropIfExists('usuario_rol');
    Schema::dropIfExists('rol_permiso');
    Schema::dropIfExists('permisos');
    Schema::dropIfExists('roles');
    
    // Remove it from migrations table just in case it's there
    DB::table('migrations')->where('migration', '2026_10_03_160000_create_rbac_tables')->delete();
    
    echo "Tables dropped.\n";
    
    // Check schema of usuarios
    $columns = DB::select("SHOW COLUMNS FROM usuarios LIKE 'id'");
    echo "usuarios.id schema: ";
    print_r($columns);
    echo "\n";
    
    // 3. Run seeder
    Artisan::call('db:seed', [
        '--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder',
        '--force' => true
    ]);
    echo "Seed Output: " . Artisan::output() . "\n";
    
    echo "SUCCESS! Everything is ready.";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
