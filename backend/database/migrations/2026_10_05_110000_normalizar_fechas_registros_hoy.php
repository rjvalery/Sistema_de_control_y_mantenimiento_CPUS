<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. inventario_general
        if (Schema::hasTable('inventario_general')) {
            if (Schema::hasColumn('inventario_general', 'fecha_intervencion')) {
                DB::statement("UPDATE inventario_general 
                    SET fecha_intervencion = DATE_SUB(fecha_intervencion, INTERVAL 5 HOUR)
                    WHERE DATE(fecha_intervencion) = '2026-10-05' 
                      AND HOUR(fecha_intervencion) >= 12");
            }
            if (Schema::hasColumn('inventario_general', 'created_at')) {
                DB::statement("UPDATE inventario_general 
                    SET created_at = DATE_SUB(created_at, INTERVAL 5 HOUR)
                    WHERE DATE(created_at) = '2026-10-05' 
                      AND HOUR(created_at) >= 12");
            }
        }

        // 2. equipos
        if (Schema::hasTable('equipos') && Schema::hasColumn('equipos', 'fecha_creacion')) {
            DB::statement("UPDATE equipos 
                SET fecha_creacion = DATE_SUB(fecha_creacion, INTERVAL 5 HOUR)
                WHERE DATE(fecha_creacion) = '2026-10-05' 
                  AND HOUR(fecha_creacion) >= 12");
        }

        // 3. soplado_registros
        if (Schema::hasTable('soplado_registros')) {
            if (Schema::hasColumn('soplado_registros', 'created_at')) {
                DB::statement("UPDATE soplado_registros 
                    SET created_at = DATE_SUB(created_at, INTERVAL 5 HOUR)
                    WHERE DATE(created_at) = '2026-10-05' 
                      AND HOUR(created_at) >= 12");
            }
            if (Schema::hasColumn('soplado_registros', 'fecha_registro')) {
                DB::statement("UPDATE soplado_registros 
                    SET fecha_registro = DATE_SUB(fecha_registro, INTERVAL 5 HOUR)
                    WHERE DATE(fecha_registro) = '2026-10-05' 
                      AND HOUR(fecha_registro) >= 12");
            }
        }

        // 4. garantias_portatiles
        if (Schema::hasTable('garantias_portatiles') && Schema::hasColumn('garantias_portatiles', 'created_at')) {
            DB::statement("UPDATE garantias_portatiles 
                SET created_at = DATE_SUB(created_at, INTERVAL 5 HOUR)
                WHERE DATE(created_at) = '2026-10-05' 
                  AND HOUR(created_at) >= 12");
        }
    }

    public function down(): void
    {
        // No-op
    }
};
