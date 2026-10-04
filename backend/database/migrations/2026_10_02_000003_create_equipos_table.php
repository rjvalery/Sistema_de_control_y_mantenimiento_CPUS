<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_analista', 120)->nullable()->index();
            $table->string('num_traslado', 100)->nullable()->index();
            $table->string('placa_id', 100)->nullable()->index();
            $table->string('tipo_gestion', 100)->nullable();
            $table->string('energiza', 10)->nullable();
            $table->string('da_video', 10)->nullable();
            $table->string('estado_actual', 150)->nullable();
            $table->string('que_va_intervenir', 150)->nullable();
            $table->string('origen_pieza', 150)->nullable();
            $table->string('serial_disco', 120)->nullable();
            $table->text('descripcion_novedad')->nullable();
            $table->string('motivo_baja', 255)->nullable();
            $table->string('ubicacion_destino', 150)->nullable();
            $table->text('foto_equipo')->nullable();
            $table->dateTime('fecha_creacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
