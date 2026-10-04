<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class FixMigrationsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:migrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convierte la tabla de migraciones de CodeIgniter 4 a Laravel 11';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando reparación de la tabla de migraciones...');

        // 1. Renombrar la tabla vieja de CodeIgniter si existe y se llama 'migrations'
        if (Schema::hasTable('migrations') && !Schema::hasColumn('migrations', 'migration')) {
            $this->info('Tabla de migraciones de CI4 detectada. Renombrando a ci4_migrations...');
            DB::statement('RENAME TABLE migrations TO ci4_migrations');
        }

        // 2. Instalar la tabla nativa de migraciones de Laravel
        $this->info('Instalando tabla de migraciones de Laravel...');
        Artisan::call('migrate:install');

        // 3. Registrar todas las migraciones anteriores como ya ejecutadas
        $this->info('Registrando migraciones existentes...');
        $files = glob(database_path('migrations/*.php'));
        
        foreach($files as $file) {
            $name = basename($file, '.php');
            
            // Si no es la migración RBAC, la marcamos como ya ejecutada para que no tire error de "tabla ya existe"
            if($name !== '2026_10_03_160000_create_rbac_tables') {
                DB::table('migrations')->insertOrIgnore([
                    'migration' => $name,
                    'batch' => 1
                ]);
            }
        }

        $this->info('¡Reparación completada! Ya puedes ejecutar: php artisan migrate');
    }
}
