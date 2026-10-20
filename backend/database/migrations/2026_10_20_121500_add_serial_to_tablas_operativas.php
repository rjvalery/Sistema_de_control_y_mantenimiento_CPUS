<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar serial a 'equipos' (Diagnóstico CPU)
        if (Schema::hasTable('equipos') && !Schema::hasColumn('equipos', 'serial')) {
            Schema::table('equipos', function (Blueprint $table) {
                $table->string('serial', 100)->nullable()->index()->after('placa_id');
            });
        }

        // 2. Agregar serial a 'garantias_portatiles' (Diagnóstico Portátiles)
        if (Schema::hasTable('garantias_portatiles') && !Schema::hasColumn('garantias_portatiles', 'serial')) {
            Schema::table('garantias_portatiles', function (Blueprint $table) {
                $table->string('serial', 100)->nullable()->index()->after('placa_id_equipo');
            });
        }

        // 3. Agregar serial a 'soplado_registros' (Soplado)
        if (Schema::hasTable('soplado_registros') && !Schema::hasColumn('soplado_registros', 'serial')) {
            Schema::table('soplado_registros', function (Blueprint $table) {
                $table->string('serial', 100)->nullable()->index()->after('placa_id');
            });
        }

        // 4. Backfill retroactivo de seriales desde inventario_general con COLLATE homogéneo
        if (Schema::hasTable('inventario_general')) {
            // Equipos
            DB::statement("
                UPDATE equipos e 
                JOIN inventario_general i ON (
                    e.placa_id COLLATE utf8mb4_unicode_ci = i.placa_id COLLATE utf8mb4_unicode_ci 
                    OR e.placa_id COLLATE utf8mb4_unicode_ci = i.identificador_1 COLLATE utf8mb4_unicode_ci 
                    OR e.placa_id COLLATE utf8mb4_unicode_ci = i.identificador_2 COLLATE utf8mb4_unicode_ci 
                    OR e.placa_id COLLATE utf8mb4_unicode_ci = i.serial COLLATE utf8mb4_unicode_ci
                )
                SET e.serial = COALESCE(NULLIF(i.serial, ''), NULLIF(i.identificador_2, ''))
                WHERE e.serial IS NULL 
                  AND (NULLIF(i.serial, '') IS NOT NULL OR NULLIF(i.identificador_2, '') IS NOT NULL)
            ");

            // Garantías Portátiles
            DB::statement("
                UPDATE garantias_portatiles g 
                JOIN inventario_general i ON (
                    g.placa_id_equipo COLLATE utf8mb4_unicode_ci = i.placa_id COLLATE utf8mb4_unicode_ci 
                    OR g.placa_id_equipo COLLATE utf8mb4_unicode_ci = i.identificador_1 COLLATE utf8mb4_unicode_ci 
                    OR g.placa_id_equipo COLLATE utf8mb4_unicode_ci = i.identificador_2 COLLATE utf8mb4_unicode_ci 
                    OR g.placa_id_equipo COLLATE utf8mb4_unicode_ci = i.serial COLLATE utf8mb4_unicode_ci
                )
                SET g.serial = COALESCE(NULLIF(i.serial, ''), NULLIF(i.identificador_2, ''))
                WHERE g.serial IS NULL 
                  AND (NULLIF(i.serial, '') IS NOT NULL OR NULLIF(i.identificador_2, '') IS NOT NULL)
            ");

            // Soplado
            DB::statement("
                UPDATE soplado_registros s 
                JOIN inventario_general i ON (
                    s.placa_id COLLATE utf8mb4_unicode_ci = i.placa_id COLLATE utf8mb4_unicode_ci 
                    OR s.placa_id COLLATE utf8mb4_unicode_ci = i.identificador_1 COLLATE utf8mb4_unicode_ci 
                    OR s.placa_id COLLATE utf8mb4_unicode_ci = i.identificador_2 COLLATE utf8mb4_unicode_ci 
                    OR s.placa_id COLLATE utf8mb4_unicode_ci = i.serial COLLATE utf8mb4_unicode_ci
                )
                SET s.serial = COALESCE(NULLIF(i.serial, ''), NULLIF(i.identificador_2, ''))
                WHERE s.serial IS NULL 
                  AND (NULLIF(i.serial, '') IS NOT NULL OR NULLIF(i.identificador_2, '') IS NOT NULL)
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('equipos') && Schema::hasColumn('equipos', 'serial')) {
            Schema::table('equipos', function (Blueprint $table) {
                $table->dropIndex(['serial']);
                $table->dropColumn('serial');
            });
        }

        if (Schema::hasTable('garantias_portatiles') && Schema::hasColumn('garantias_portatiles', 'serial')) {
            Schema::table('garantias_portatiles', function (Blueprint $table) {
                $table->dropIndex(['serial']);
                $table->dropColumn('serial');
            });
        }

        if (Schema::hasTable('soplado_registros') && Schema::hasColumn('soplado_registros', 'serial')) {
            Schema::table('soplado_registros', function (Blueprint $table) {
                $table->dropIndex(['serial']);
                $table->dropColumn('serial');
            });
        }
    }
};
