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
        Schema::create('diademas_lotes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('nombre_analista');
            $table->string('numero_traslado')->index();
            $table->integer('total_funcionales')->default(0);
            $table->integer('total_garantia')->default(0);
            $table->integer('total_baja')->default(0);
            $table->integer('total_unidades')->default(0);
            $table->dateTime('fecha_ingreso')->index();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diademas_lotes');
    }
};
