<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UploadService;

class MigrarEvidenciasFotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evidencias:migrar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra y regulariza fotos de evidencia hacia C:\Users\LENOVO\Pictures\fotos\{Año}\{Mes}\{Dia}';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando proceso de regularización y migración de evidencias...');

        $resultado = UploadService::migrarFotosExistentes();

        $this->info("Proceso completado.");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Destino Base', $resultado['baseDestino']],
                ['Fotos Migradas / Aseguradas', $resultado['migrados']],
                ['Errores', $resultado['errores']]
            ]
        );

        return Command::SUCCESS;
    }
}
