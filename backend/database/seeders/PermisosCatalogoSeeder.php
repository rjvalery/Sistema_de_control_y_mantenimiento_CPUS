<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\RbacService;

class PermisosCatalogoSeeder extends Seeder
{
    public function run(): void
    {
        RbacService::sincronizarCatalogo();
    }
}
