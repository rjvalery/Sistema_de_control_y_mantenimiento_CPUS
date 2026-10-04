<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_general', function (Blueprint $table) {
            $table->id();
            $table->string('identificador_1', 100)->nullable()->index();
            $table->string('identificador_2', 100)->nullable()->index();
            $table->string('num_traslado', 100)->nullable()->index();
            $table->string('ref_principal', 150)->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->string('zona_origen', 100)->nullable();
            $table->string('ubicacion_origen', 150)->nullable();
            $table->string('verificado', 50)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('placa_id', 100)->nullable()->index();
            $table->string('serial', 100)->nullable()->index();
            $table->string('tipo_equipo', 80)->nullable();
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 150)->nullable();
            $table->string('ubicacion', 150)->nullable();
            $table->string('estado', 80)->nullable();
            $table->text('datos_adicionales')->nullable();
            $table->string('archivo_origen', 255)->nullable();
            $table->string('usuario_cargue', 120)->nullable();
            $table->boolean('intervenido')->default(0)->index();
            $table->dateTime('fecha_intervencion')->nullable();
            $table->string('modulo_intervencion', 50)->nullable();
            $table->string('analista_intervencion', 120)->nullable();
            $table->dateTime('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_general');
    }
};
