<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "TABLES:\n";
    $tables = DB::select('SHOW TABLES');
    foreach($tables as $t) {
        $val = array_values((array)$t)[0];
        echo "- $val\n";
    }

    echo "\nMIGRATIONS CONTENT:\n";
    if (Schema::hasTable('migrations')) {
        $mig = DB::table('migrations')->get();
        foreach($mig as $m) {
            echo "- {$m->migration}\n";
        }
    } else {
        echo "Table 'migrations' does not exist.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
