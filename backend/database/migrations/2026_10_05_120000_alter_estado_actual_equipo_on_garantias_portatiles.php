<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('garantias_portatiles')) {
            return;
        }

        foreach (['estado_actual_equipo', 'estado_final_equipo', 'tipo_gestion'] as $column) {
            if (!Schema::hasColumn('garantias_portatiles', $column)) {
                continue;
            }

            if (DB::getDriverName() === 'pgsql') {
                DB::statement("ALTER TABLE garantias_portatiles ALTER COLUMN {$column} TYPE VARCHAR(150)");
                DB::statement("ALTER TABLE garantias_portatiles ALTER COLUMN {$column} DROP NOT NULL");
            } else {
                DB::statement("ALTER TABLE garantias_portatiles MODIFY COLUMN {$column} VARCHAR(150) NULL");
            }
        }
    }

    public function down(): void
    {
        // No-op.
    }
};
