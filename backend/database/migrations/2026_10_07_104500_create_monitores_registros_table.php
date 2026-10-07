<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monitores_registros', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('nombre_analista');
            $table->string('serial')->index();
            $table->string('placa')->nullable()->index();
            $table->string('numero_traslado')->nullable()->index();
            $table->enum('tipo_gestion', ['diagnostico', 'novedad', 'baja']);
            $table->boolean('energiza')->nullable();
            $table->boolean('da_video')->nullable();
            $table->text('motivo_novedad')->nullable();
            $table->enum('estado_actual', ['funcional', 'garantia', 'baja']);
            $table->text('observaciones')->nullable();
            $table->dateTime('fecha_ingreso')->index();
            $table->timestamps();

            // Foreign key to users
            $table->foreign('user_id')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitores_registros');
    }
};
