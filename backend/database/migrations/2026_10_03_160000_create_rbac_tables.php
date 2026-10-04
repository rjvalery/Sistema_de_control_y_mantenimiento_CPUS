<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('modulo')->nullable();
            $table->timestamps();
        });

        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permiso_id')->constrained('permisos')->onDelete('cascade');
        });

        // We will link users to roles. Since the `usuarios` table already has a string `rol` column, 
        // we can either add a `rol_id` to it, or create a pivot table `usuario_rol`.
        // A pivot table allows a user to have multiple roles. The prompt mentions:
        // "Tablas pivote 'rol_permiso' y 'usuario_rol' (o vincula mediante llaves foráneas a la tabla de usuarios existente)."
        // Let's create `usuario_rol` to be flexible and standard.
        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id();
            // CodeIgniter 4 creó usuarios.id como INT UNSIGNED, por lo que debemos usar unsignedInteger en lugar de foreignId (BIGINT)
            $table->unsignedInteger('usuario_id')->nullable();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            
            $table->foreignId('rol_id')->constrained('roles')->onDelete('cascade');
        });

        // We will migrate existing users' string roles to the new RBAC structure inside a Seeder, 
        // but let's drop or ignore the old `rol` column later if needed. For now, leave it so we don't break old code immediately.
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_rol');
        Schema::dropIfExists('rol_permiso');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('roles');
    }
};
