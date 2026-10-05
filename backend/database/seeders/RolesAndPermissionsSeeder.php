<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\RbacService;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear y unificar catálogo canónico de permisos
        RbacService::sincronizarCatalogo();

        // 2. Crear Roles si no existen
        $rolAdmin = Rol::firstOrCreate(
            ['slug' => 'admin'],
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema']
        );
        
        $rolTecnico = Rol::firstOrCreate(
            ['slug' => 'analista'],
            ['nombre' => 'Técnico / Analista', 'descripcion' => 'Operador del taller']
        );
        
        $rolAuditor = Rol::firstOrCreate(
            ['slug' => 'auditor'],
            ['nombre' => 'Supervisor / Auditor', 'descripcion' => 'Acceso de solo lectura y reportes']
        );

        // 3. Asignar todos los permisos al Administrador
        $rolAdmin->permisos()->sync(\App\Models\Permiso::all());

        // 4. Migrar usuarios existentes: asignar rol basado en su campo 'rol'
        $usuarios = Usuario::all();
        foreach ($usuarios as $user) {
            $slug = strtolower($user->rol); // 'admin' o 'analista'
            $rol = Rol::where('slug', $slug)->first();
            if ($rol) {
                $user->rolesRelation()->syncWithoutDetaching([$rol->id]);
            }
        }
    }
}
