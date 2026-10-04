<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('usuario', 60)->unique();
            $table->string('password', 255);
            $table->string('rol', 30)->default('admin');
            $table->boolean('activo')->default(1);
            $table->string('permisos', 255)->nullable();
            $table->dateTime('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
