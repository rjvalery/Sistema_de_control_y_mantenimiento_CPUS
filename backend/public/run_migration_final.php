<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

try {
    echo "Check DB:\n";
    $mig = DB::table('migrations')->get();
    foreach($mig as $m) {
        if (str_contains($m->migration, '180000')) {
            echo "DELETING {$m->migration}\n";
            DB::table('migrations')->where('migration', $m->migration)->delete();
        }
    }
    
    echo "Running migrate...\n";
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output() . "\n";
    
    echo "Running seeder...\n";
    Artisan::call('db:seed', [
        '--class' => 'Database\\Seeders\\PermisosCatalogoSeeder',
        '--force' => true
    ]);
    echo Artisan::output() . "\n";
    
    echo "DONE.";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
