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
        Schema::create('diademas_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lote_id');
            $table->string('identificador')->index();
            $table->string('marca_modelo')->nullable();
            $table->enum('estado', ['funcional', 'garantia', 'baja']);
            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->foreign('lote_id')->references('id')->on('diademas_lotes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diademas_items');
    }
};
