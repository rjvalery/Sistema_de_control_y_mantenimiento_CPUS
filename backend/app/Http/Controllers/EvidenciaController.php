<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use App\Services\UploadService;

class EvidenciaController extends Controller
{
    /**
     * Sirve la evidencia fotográfica de manera segura (Streaming) desde la ruta física local.
     * Route: /evidencias/ver/{modulo}/{anio}/{mes}/{dia}/{archivo}
     */
    public function ver(Request $request, $modulo, $anio, $mes, $dia, $archivo)
    {
        // 1. Ruta base física estricta
        $basePath = env('EVIDENCIAS_PATH', 'C:\\Users\\LENOVO\\Pictures\\fotos');
        $basePath = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $basePath), DIRECTORY_SEPARATOR);
        $rutaFisica = $basePath . DIRECTORY_SEPARATOR . $anio . DIRECTORY_SEPARATOR . $mes . DIRECTORY_SEPARATOR . $dia . DIRECTORY_SEPARATOR . $archivo;

        if (File::exists($rutaFisica) && !File::isDirectory($rutaFisica)) {
            return response()->file($rutaFisica);
        }

        // 2. Comprobación en subcarpeta por módulo (ej: fotos/cpus/2026/Octubre/05/B88147.jpg)
        $moduloAliases = match(strtolower($modulo)) {
            'cpus', 'cpu', 'equipos', 'diagnostico' => ['cpus', 'diagnostico', 'equipos'],
            'portatiles', 'portatil', 'laptop'     => ['portatiles', 'portatil'],
            'soplado', 'mantenimiento'             => ['soplado', 'mantenimiento'],
            default                                => [strtolower($modulo)]
        };

        foreach ($moduloAliases as $mod) {
            $rutaModulo = $basePath . DIRECTORY_SEPARATOR . $mod . DIRECTORY_SEPARATOR . $anio . DIRECTORY_SEPARATOR . $mes . DIRECTORY_SEPARATOR . $dia . DIRECTORY_SEPARATOR . $archivo;
            if (File::exists($rutaModulo) && !File::isDirectory($rutaModulo)) {
                return response()->file($rutaModulo);
            }
        }

        // 3. Fallbacks de compatibilidad (raíz de fotos o directorio público)
        $fallbacks = [
            $basePath . DIRECTORY_SEPARATOR . $archivo,
            public_path("fotos/{$anio}/{$mes}/{$dia}/{$archivo}"),
            public_path("fotos/{$archivo}"),
            storage_path("app/public/evidencias/{$archivo}"),
        ];

        foreach ($fallbacks as $fb) {
            if (File::exists($fb) && !File::isDirectory($fb)) {
                return response()->file($fb);
            }
        }

        // Si no existe el archivo físico, servir placeholder SVG
        return UploadService::retornarPlaceholder($archivo);
    }

    /**
     * Muestra una imagen de forma segura comprobando autenticación y resolviendo rutas relativas.
     * Route: /evidencias/{path} (compatibilidad hacia atrás)
     */
    public function show(string $path)
    {
        $basePath = UploadService::getBasePath();
        $pathLimpio = ltrim($path, '/\\');

        $candidatos = [
            "{$basePath}/{$pathLimpio}",
            public_path("fotos/{$pathLimpio}"),
            storage_path("app/public/evidencias/{$pathLimpio}"),
            storage_path("app/public/{$pathLimpio}"),
            public_path($pathLimpio),
        ];

        foreach ($candidatos as $ruta) {
            $rutaNormalizada = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $ruta);
            if (File::exists($rutaNormalizada) && !File::isDirectory($rutaNormalizada)) {
                $mime = File::mimeType($rutaNormalizada) ?: 'image/jpeg';
                return response()->file($rutaNormalizada, [
                    'Content-Type'        => $mime,
                    'Content-Disposition' => 'inline; filename="' . basename($rutaNormalizada) . '"',
                    'Cache-Control'       => 'public, max-age=86400',
                ]);
            }
        }

        return UploadService::retornarPlaceholder(basename($path));
    }
}
