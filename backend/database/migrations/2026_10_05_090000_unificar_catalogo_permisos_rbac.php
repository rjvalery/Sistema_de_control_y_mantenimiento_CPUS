<?php

use Illuminate\Database\Migrations\Migration;
use App\Services\RbacService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        RbacService::sincronizarCatalogo();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo
    }
};
