<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Artisan;

try {
    echo "MIGRATIONS TABLE CONTENT:\n";
    $mig = DB::table('migrations')->get();
    foreach($mig as $m) {
        echo "- {$m->migration}\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
