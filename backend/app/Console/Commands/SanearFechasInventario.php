<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventarioGeneral;
use Illuminate\Support\Facades\DB;

class SanearFechasInventario extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventario:sanear-fechas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza la fecha_intervencion con created_at si es nula y el equipo fue intervenido';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando saneamiento de fechas en inventario...');

        $afectados = InventarioGeneral::where('intervenido', 1)
            ->whereNull('fecha_intervencion')
            ->whereNotNull('created_at')
            ->update(['fecha_intervencion' => DB::raw('created_at')]);

        $this->info("Proceso completado. Se actualizaron {$afectados} registros.");
    }
}
