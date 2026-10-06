<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Permiso;
use App\Services\RbacService;

class TrazabilidadPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Sincronizar catálogo para dar de alta 'trazabilidad.ver'
        RbacService::sincronizarCatalogo();

        // 2. Obtener el permiso recién creado
        $permiso = Permiso::where('slug', 'trazabilidad.ver')->first();

        if ($permiso) {
            // Asignar al rol analista para que puedan buscar en los modulos sin error 403
            $rolAnalista = Rol::where('slug', 'analista')->first();
            if ($rolAnalista) {
                $rolAnalista->permisos()->syncWithoutDetaching([$permiso->id]);
            }
        }
    }
}
