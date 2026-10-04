<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_permiso', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('usuario_id');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreignId('permiso_id')->constrained('permisos')->onDelete('cascade');
        });

        // Agregamos la columna 'descripcion' a la tabla permisos si no existe (el usuario lo pidió)
        if (!Schema::hasColumn('permisos', 'descripcion')) {
            Schema::table('permisos', function (Blueprint $table) {
                $table->string('descripcion')->nullable()->after('modulo');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_permiso');
        
        if (Schema::hasColumn('permisos', 'descripcion')) {
            Schema::table('permisos', function (Blueprint $table) {
                $table->dropColumn('descripcion');
            });
        }
    }
};
