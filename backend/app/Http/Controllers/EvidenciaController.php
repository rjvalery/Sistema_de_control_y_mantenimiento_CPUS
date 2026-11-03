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
        try {
            $basePath = UploadService::getBasePath();

            // Normalización y variantes del módulo
            $moduloAliases = match(strtolower($modulo)) {
                'cpus', 'cpu', 'equipos', 'diagnostico' => ['diagnostico', 'cpus', 'equipos'],
                'portatiles', 'portatil', 'laptop'     => ['portatiles', 'portatil'],
                'soplado', 'mantenimiento'             => ['soplado', 'mantenimiento'],
                default                                => [strtolower($modulo)]
            };

            // Variantes de nombre de mes (español, numérico con y sin cero)
            $mesesVariantes = array_unique(array_filter([
                $mes,
                is_numeric($mes) ? UploadService::getNombreMes((int)$mes) : null,
                is_numeric($mes) ? str_pad($mes, 2, '0', STR_PAD_LEFT) : null,
                is_numeric($mes) ? (string)(int)$mes : null,
                ucfirst(strtolower($mes)),
                strtolower($mes),
            ]));

            // Variantes de día (ej: "05" y "5")
            $diasVariantes = array_unique(array_filter([
                $dia,
                str_pad($dia, 2, '0', STR_PAD_LEFT),
                is_numeric($dia) ? (string)(int)$dia : null,
            ]));

            // Variantes de archivo (nombre directo o decodificado)
            $archivosVariantes = array_unique(array_filter([
                $archivo,
                urldecode($archivo),
            ]));

            // 1. Búsqueda prioritaria con subcarpeta por módulo
            foreach ($moduloAliases as $mod) {
                foreach ($mesesVariantes as $mV) {
                    foreach ($diasVariantes as $dV) {
                        foreach ($archivosVariantes as $aV) {
                            $rutaModulo = $basePath . DIRECTORY_SEPARATOR . $mod . DIRECTORY_SEPARATOR . $anio . DIRECTORY_SEPARATOR . $mV . DIRECTORY_SEPARATOR . $dV . DIRECTORY_SEPARATOR . $aV;
                            if (File::exists($rutaModulo) && !File::isDirectory($rutaModulo)) {
                                $mime = File::mimeType($rutaModulo) ?: 'image/jpeg';
                                return response()->file($rutaModulo, [
                                    'Content-Type'        => $mime,
                                    'Content-Disposition' => 'inline; filename="' . basename($rutaModulo) . '"',
                                    'Cache-Control'       => 'public, max-age=86400',
                                ]);
                            }
                        }
                    }
                }
            }

            // 2. Ruta física sin subcarpeta de módulo (compatibilidad fotos/{anio}/{mes}/{dia}/{archivo})
            foreach ($mesesVariantes as $mV) {
                foreach ($diasVariantes as $dV) {
                    foreach ($archivosVariantes as $aV) {
                        $rutaFisica = $basePath . DIRECTORY_SEPARATOR . $anio . DIRECTORY_SEPARATOR . $mV . DIRECTORY_SEPARATOR . $dV . DIRECTORY_SEPARATOR . $aV;
                        if (File::exists($rutaFisica) && !File::isDirectory($rutaFisica)) {
                            $mime = File::mimeType($rutaFisica) ?: 'image/jpeg';
                            return response()->file($rutaFisica, [
                                'Content-Type'        => $mime,
                                'Content-Disposition' => 'inline; filename="' . basename($rutaFisica) . '"',
                                'Cache-Control'       => 'public, max-age=86400',
                            ]);
                        }
                    }
                }
            }

            // 3. Fallbacks de compatibilidad (raíz de fotos o directorio público/storage)
            foreach ($archivosVariantes as $aV) {
                $fallbacks = [
                    $basePath . DIRECTORY_SEPARATOR . $aV,
                    public_path("fotos/{$anio}/{$mes}/{$dia}/{$aV}"),
                    public_path("fotos/{$aV}"),
                    public_path("uploads/{$aV}"),
                    storage_path("app/public/evidencias/{$aV}"),
                    storage_path("app/public/{$aV}"),
                ];

                foreach ($fallbacks as $fb) {
                    if (File::exists($fb) && !File::isDirectory($fb)) {
                        $mime = File::mimeType($fb) ?: 'image/jpeg';
                        return response()->file($fb, [
                            'Content-Type'        => $mime,
                            'Content-Disposition' => 'inline; filename="' . basename($fb) . '"',
                            'Cache-Control'       => 'public, max-age=86400',
                        ]);
                    }
                }
            }

            // Si no existe el archivo físico, servir placeholder SVG elegante
            return UploadService::retornarPlaceholder($archivo);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("EvidenciaController: Error al servir imagen: " . $e->getMessage());
            return UploadService::retornarPlaceholder($archivo);
        }
    }

    /**
     * Muestra una imagen de forma segura comprobando autenticación y resolviendo rutas relativas.
     * Route: /evidencias/{path} (compatibilidad hacia atrás)
     */
    public function show(string $path)
    {
        try {
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
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("EvidenciaController: Error al mostrar imagen por path: " . $e->getMessage());
            return UploadService::retornarPlaceholder(basename($path));
        }
    }
}
