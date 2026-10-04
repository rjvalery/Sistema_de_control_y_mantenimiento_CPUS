<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Usuario;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Permisos
        $permisos = [
            // Gestión de Usuarios
            ['nombre' => 'Ver Usuarios', 'slug' => 'usuarios.ver', 'modulo' => 'Usuarios'],
            ['nombre' => 'Crear Usuarios', 'slug' => 'usuarios.crear', 'modulo' => 'Usuarios'],
            ['nombre' => 'Editar Usuarios', 'slug' => 'usuarios.editar', 'modulo' => 'Usuarios'],
            
            // Diagnóstico y Mantenimiento
            ['nombre' => 'Registrar Diagnóstico', 'slug' => 'diagnostico.registrar', 'modulo' => 'Diagnóstico'],
            ['nombre' => 'Ver Bitácora', 'slug' => 'bitacora.ver', 'modulo' => 'Bitácora'],
            
            // Cargue Masivo
            ['nombre' => 'Acceder Cargue Masivo', 'slug' => 'inventario.cargue_masivo', 'modulo' => 'Inventario'],
            
            // Acciones Avanzadas (Borrar, Editar histórico)
            ['nombre' => 'Eliminar Registros', 'slug' => 'registros.eliminar', 'modulo' => 'Sistema'],
        ];

        foreach ($permisos as $p) {
            Permiso::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // 2. Crear Roles
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

        // 3. Asignar Permisos a Roles
        // Admin tiene todos los permisos
        $rolAdmin->permisos()->sync(Permiso::all());
        
        // Técnico tiene permisos operativos básicos
        $permisosTecnico = Permiso::whereIn('slug', [
            'diagnostico.registrar',
            'bitacora.ver'
        ])->get();
        $rolTecnico->permisos()->sync($permisosTecnico);

        // Auditor tiene permisos de vista
        $permisosAuditor = Permiso::whereIn('slug', [
            'bitacora.ver',
            'usuarios.ver'
        ])->get();
        $rolAuditor->permisos()->sync($permisosAuditor);

        // 4. Migrar usuarios existentes: asignar el rol basado en su columna string 'rol'
        $usuarios = Usuario::all();
        foreach ($usuarios as $user) {
            $slug = strtolower($user->rol); // 'admin' o 'analista'
            $rol = Rol::where('slug', $slug)->first();
            if ($rol) {
                // Sincronizar (evitar duplicados)
                $user->rolesRelation()->syncWithoutDetaching([$rol->id]);
            }
        }
    }
}
