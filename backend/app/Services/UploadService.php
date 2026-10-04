<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UploadService
{
    /**
     * Sube y organiza evidencias en storage/app/public/uploads/[categoria]/[AÑO]/[MES_TEXTO]/[DIA]/
     * Retorna la ruta relativa que debe ser guardada en la base de datos.
     */
    public function guardarEvidencia(?UploadedFile $file, string $placaId, string $categoria = 'diagnostico'): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        $meses = [
            '01' => 'Enero',      '02' => 'Febrero',   '03' => 'Marzo',
            '04' => 'Abril',      '05' => 'Mayo',      '06' => 'Junio',
            '07' => 'Julio',      '08' => 'Agosto',    '09' => 'Septiembre',
            '10' => 'Octubre',    '11' => 'Noviembre', '12' => 'Diciembre'
        ];

        $anio      = date('Y');
        $nombreMes = $meses[date('m')];
        $dia       = date('d');

        // Ruta relativa en el disco "public"
        $targetDir = "uploads/{$categoria}/{$anio}/{$nombreMes}/{$dia}";

        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        $placaLimpia = preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($placaId));
        
        if (empty($placaLimpia)) {
            $placaLimpia = 'evidencia_' . date('His');
        }

        $fileName = $placaLimpia . '.' . $ext;

        if (Storage::disk('fotos_servidor')->exists($targetDir . '/' . $fileName)) {
            $fileName = $placaLimpia . '_' . date('His') . '.' . $ext;
        }

        try {
            // Guarda el archivo en el disco personalizado 
            $path = $file->storeAs($targetDir, $fileName, 'fotos_servidor');
            
            // Retorna la ruta relativa
            return $path;
        } catch (\Throwable $e) {
            Log::error("UploadService: Error guardando el archivo en '{$targetDir}': " . $e->getMessage());
            return null;
        }
    }
}
