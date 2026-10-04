<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soplado_registros', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_analista', 120)->nullable()->index();
            $table->string('num_traslado', 100)->nullable()->index();
            $table->string('placa_id', 100)->nullable()->index();
            $table->string('energiza', 10)->nullable();
            $table->string('da_video', 10)->nullable();
            $table->string('detecta_disco', 10)->nullable();
            $table->string('ingreso_bios', 10)->nullable();
            $table->string('pasta_termica', 10)->nullable();
            $table->string('maquina_contenia', 255)->nullable();
            $table->string('gel_cucarachas', 10)->nullable();
            $table->text('foto_ruta')->nullable();
            $table->dateTime('fecha_creacion')->nullable();
            $table->dateTime('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soplado_registros');
    }
};
