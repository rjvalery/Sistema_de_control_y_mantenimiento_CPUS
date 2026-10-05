<?php

use Illuminate\Database\Migrations\Migration;
use App\Services\UploadService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migra y regulariza fotos físicas existentes hacia C:\Users\LENOVO\Pictures\fotos
        UploadService::migrarFotosExistentes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo
    }
};
