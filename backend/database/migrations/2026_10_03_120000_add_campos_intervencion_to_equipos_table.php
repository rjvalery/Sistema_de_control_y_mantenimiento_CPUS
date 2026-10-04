<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('tipo_ram', 100)->nullable()->after('origen_pieza');
            $table->string('marca_ram', 100)->nullable()->after('tipo_ram');
            $table->string('capacidad_ram', 100)->nullable()->after('marca_ram');
            $table->string('tipo_disco', 100)->nullable()->after('capacidad_ram');
            $table->string('marca_disco', 100)->nullable()->after('tipo_disco');
            $table->string('capacidad_disco', 100)->nullable()->after('marca_disco');
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_ram', 'marca_ram', 'capacidad_ram',
                'tipo_disco', 'marca_disco', 'capacidad_disco'
            ]);
        });
    }
};
