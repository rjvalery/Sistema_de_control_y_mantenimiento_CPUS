<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function normalize(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = DB::getDriverName();
        $shift = $driver === 'pgsql'
            ? "{$column} - INTERVAL '5 hours'"
            : "DATE_SUB({$column}, INTERVAL 5 HOUR)";
        $date = $driver === 'pgsql' ? "DATE({$column})" : "DATE({$column})";
        $hour = $driver === 'pgsql' ? "EXTRACT(HOUR FROM {$column})" : "HOUR({$column})";

        DB::statement("UPDATE {$table} SET {$column} = {$shift} WHERE {$date} = '2026-10-05' AND {$hour} >= 12");
    }

    public function up(): void
    {
        foreach ([
            ['inventario_general', 'fecha_intervencion'],
            ['inventario_general', 'created_at'],
            ['equipos', 'fecha_creacion'],
            ['soplado_registros', 'created_at'],
            ['soplado_registros', 'fecha_registro'],
            ['garantias_portatiles', 'created_at'],
        ] as [$table, $column]) {
            $this->normalize($table, $column);
        }
    }

    public function down(): void
    {
        // No-op.
    }
};
