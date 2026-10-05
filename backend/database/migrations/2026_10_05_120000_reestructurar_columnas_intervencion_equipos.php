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
        // 1. Eliminar posibles tablas secundarias o pivote si existieran
        $tablasSecundarias = ['piezas', 'detalles_intervencion', 'discos_duros', 'cpu_piezas'];
        foreach ($tablasSecundarias as $tabla) {
            Schema::dropIfExists($tabla);
        }

        // 2. Modificaciones en la tabla 'equipos'
        if (Schema::hasTable('equipos')) {
            Schema::table('equipos', function (Blueprint $table) {
                // Columnas identificadas del desglose técnico a eliminar
                $columnasAEliminar = [
                    'tipo_ram',
                    'marca_ram',
                    'capacidad_ram',
                    'tipo_disco',
                    'marca_disco',
                    'capacidad_disco',
                    'serial_disco',
                ];

                $columnasExistentes = [];
                foreach ($columnasAEliminar as $col) {
                    if (Schema::hasColumn('equipos', $col)) {
                        $columnasExistentes[] = $col;
                    }
                }

                if (!empty($columnasExistentes)) {
                    $table->dropColumn($columnasExistentes);
                }

                // Asegurar existencia de 'origen_pieza'
                if (!Schema::hasColumn('equipos', 'origen_pieza')) {
                    $table->string('origen_pieza', 150)->nullable()->after('que_va_intervenir');
                }

                // Asegurar que 'estado_actual' permita 'Baja' (VARCHAR 150 nullable)
                if (Schema::hasColumn('equipos', 'estado_actual')) {
                    $table->string('estado_actual', 150)->nullable()->change();
                } else {
                    $table->string('estado_actual', 150)->nullable()->after('da_video');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('equipos')) {
            Schema::table('equipos', function (Blueprint $table) {
                if (!Schema::hasColumn('equipos', 'tipo_ram')) {
                    $table->string('tipo_ram', 100)->nullable();
                }
                if (!Schema::hasColumn('equipos', 'marca_ram')) {
                    $table->string('marca_ram', 100)->nullable();
                }
                if (!Schema::hasColumn('equipos', 'capacidad_ram')) {
                    $table->string('capacidad_ram', 100)->nullable();
                }
                if (!Schema::hasColumn('equipos', 'tipo_disco')) {
                    $table->string('tipo_disco', 100)->nullable();
                }
                if (!Schema::hasColumn('equipos', 'marca_disco')) {
                    $table->string('marca_disco', 100)->nullable();
                }
                if (!Schema::hasColumn('equipos', 'capacidad_disco')) {
                    $table->string('capacidad_disco', 100)->nullable();
                }
                if (!Schema::hasColumn('equipos', 'serial_disco')) {
                    $table->string('serial_disco', 120)->nullable();
                }
            });
        }
    }
};
