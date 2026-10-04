<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;
use App\Models\Rol;

class PermisosCatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = [
            'Dashboard' => [
                ['slug' => 'dashboard.ver_solo_propio', 'nombre' => 'Ver únicamente mis propios registros y dashboard personal', 'descripcion' => 'Restringe la vista de registros a los que el usuario haya creado'],
                ['slug' => 'dashboard.metricas_globales', 'nombre' => 'Ver métricas globales de todo el equipo', 'descripcion' => 'Permite ver estadísticas completas en el dashboard'],
            ],
            'Diagnóstico CPUs' => [
                ['slug' => 'cpus.ver', 'nombre' => 'Ver bitácora de CPUs', 'descripcion' => 'Acceso a la vista de registros de CPUs'],
                ['slug' => 'cpus.registrar', 'nombre' => 'Registrar nuevo diagnóstico', 'descripcion' => 'Crear un nuevo diagnóstico de CPU'],
                ['slug' => 'cpus.editar', 'nombre' => 'Permitir edición de diagnósticos', 'descripcion' => 'Editar registros existentes'],
                ['slug' => 'cpus.eliminar', 'nombre' => 'Permitir eliminación', 'descripcion' => 'Eliminar registros del sistema'],
            ],
            'Portátiles' => [
                ['slug' => 'portatiles.ver', 'nombre' => 'Ver bitácora de Portátiles', 'descripcion' => 'Acceso a registros de portátiles'],
                ['slug' => 'portatiles.registrar', 'nombre' => 'Registrar portátiles', 'descripcion' => 'Crear nuevo registro'],
                ['slug' => 'portatiles.editar', 'nombre' => 'Editar portátiles', 'descripcion' => 'Modificar registros'],
            ],
            'Soplado' => [
                ['slug' => 'soplado.ver', 'nombre' => 'Ver bitácora de Soplado', 'descripcion' => 'Acceso a registros de soplado'],
                ['slug' => 'soplado.registrar', 'nombre' => 'Registrar soplado', 'descripcion' => 'Crear nuevo registro'],
            ],
            'Inventario' => [
                ['slug' => 'inventario.cargue_masivo', 'nombre' => 'Acceso a cargue masivo de inventario', 'descripcion' => 'Subir plantillas y actualizar inventario'],
            ],
            'Usuarios' => [
                ['slug' => 'usuarios.ver', 'nombre' => 'Ver lista de usuarios', 'descripcion' => 'Acceso al módulo de gestión'],
                ['slug' => 'usuarios.crear', 'nombre' => 'Crear usuarios', 'descripcion' => 'Registrar nuevos empleados'],
                ['slug' => 'usuarios.editar', 'nombre' => 'Editar usuarios y roles', 'descripcion' => 'Modificar usuarios y resetear contraseñas'],
                ['slug' => 'usuarios.permisos', 'nombre' => 'Gestionar permisos dinámicos', 'descripcion' => 'Asignar o remover permisos específicos por usuario'],
            ]
        ];

        foreach ($catalogo as $modulo => $permisos) {
            foreach ($permisos as $p) {
                Permiso::updateOrCreate(
                    ['slug' => $p['slug']],
                    [
                        'nombre' => $p['nombre'],
                        'descripcion' => $p['descripcion'],
                        'modulo' => $modulo
                    ]
                );
            }
        }
        
        // Opcional: Asignar permisos básicos a analistas si queremos. 
        // Como el admin ya pasa por el Gate (hasRole('admin')), no necesita tenerlos registrados en la pivote explícitamente.
    }
}
