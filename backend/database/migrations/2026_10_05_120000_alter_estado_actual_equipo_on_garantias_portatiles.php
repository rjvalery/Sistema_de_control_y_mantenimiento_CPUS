<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // En lugar de usar Schema::table, que puede fallar con ENUM si doctrine/dbal no está,
        // usamos sentencias DB crudas para forzar los campos conflictivos a VARCHAR(150)
        DB::statement("ALTER TABLE garantias_portatiles MODIFY COLUMN estado_actual_equipo VARCHAR(150) NULL");
        DB::statement("ALTER TABLE garantias_portatiles MODIFY COLUMN estado_final_equipo VARCHAR(150) NULL");
        DB::statement("ALTER TABLE garantias_portatiles MODIFY COLUMN tipo_gestion VARCHAR(150) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
