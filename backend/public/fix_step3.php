<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Artisan;

try {
    echo "Running step 3 migrations...\n";
    Artisan::call('migrate', ['--force' => true]);
    echo "Migrate Output: " . Artisan::output() . "\n";
    
    echo "Running step 3 seeder...\n";
    Artisan::call('db:seed', [
        '--class' => 'Database\\Seeders\\PermisosCatalogoSeeder',
        '--force' => true
    ]);
    echo "Seed Output: " . Artisan::output() . "\n";
    
    echo "SUCCESS!";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
