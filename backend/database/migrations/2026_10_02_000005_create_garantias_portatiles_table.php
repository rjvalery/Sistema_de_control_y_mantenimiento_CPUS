<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garantias_portatiles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_analista', 120)->nullable()->index();
            $table->string('numero_traslado', 100)->nullable()->index();
            $table->string('placa_id_equipo', 100)->nullable()->index();
            $table->string('tipo_gestion', 100)->nullable();
            $table->string('energiza', 10)->nullable();
            $table->string('da_video', 10)->nullable();
            $table->string('realizo_test_lenovo', 10)->nullable();
            $table->string('estado_actual_equipo', 150)->nullable();
            $table->text('diagnostico_laptop_intervenido')->nullable();
            $table->string('garantia', 20)->nullable();
            $table->text('porque_solicita_garantia')->nullable();
            $table->string('numero_ticket', 100)->nullable()->index();
            $table->string('estado_final_equipo', 150)->nullable();
            $table->string('indique_pieza', 150)->nullable();
            $table->string('indique_fru', 150)->nullable();
            $table->string('pieza_intervenida', 150)->nullable();
            $table->string('origen_pieza', 150)->nullable();
            $table->string('motivo_baja', 255)->nullable();
            $table->string('serial_disco', 120)->nullable();
            $table->text('foto_ruta')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('fecha_creacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garantias_portatiles');
    }
};
