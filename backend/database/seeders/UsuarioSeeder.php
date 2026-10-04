<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::firstOrCreate(
            ['usuario' => 'Admin'],
            [
                'nombre' => 'Administrador del Sistema',
                'password' => Hash::make('Admin123*'),
                'rol' => 'admin',
                'activo' => 1,
                'permisos' => null,
                'created_at' => now(),
            ]
        );
    }
}
