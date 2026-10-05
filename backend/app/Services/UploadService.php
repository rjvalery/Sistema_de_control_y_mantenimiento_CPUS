<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UploadService
{
    /**
     * Mapeo de meses en español.
     */
    const MESES = [
        1  => 'Enero',      2  => 'Febrero',   3  => 'Marzo',
        4  => 'Abril',      5  => 'Mayo',      6  => 'Junio',
        7  => 'Julio',      8  => 'Agosto',    9  => 'Septiembre',
        10 => 'Octubre',    11 => 'Noviembre', 12 => 'Diciembre'
    ];

    /**
     * Obtiene la ruta física base donde se almacenan las fotos.
     */
    public static function getBasePath(): string
    {
        $base = env('EVIDENCIAS_PATH', 'C:\\Users\\LENOVO\\Pictures\\fotos');
        $base = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $base), DIRECTORY_SEPARATOR);

        if (!File::isDirectory($base)) {
            try {
                File::makeDirectory($base, 0777, true, true);
            } catch (\Throwable $e) {
                Log::warning("UploadService: No se pudo crear basePath físico '{$base}': " . $e->getMessage());
            }
        }

        return $base;
    }

    /**
     * Retorna el nombre del mes en español.
     */
    public static function getNombreMes(int|string $mes): string
    {
        $num = (int)$mes;
        return self::MESES[$num] ?? ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));
    }

    /**
     * Procesa la subida y almacenamiento físico de la evidencia según las reglas estrictas:
     * C:\Users\LENOVO\Pictures\fotos\{Año}\{Mes}\{Dia}\{PLACA}.jpg
     */
    public static function procesarSubidaFisica($request, string $modulo = 'cpus', ?string $placaForzada = null): ?string
    {
        if (!$request->hasFile('evidencia') && !$request->hasFile('foto') && !$request->hasFile('foto_equipo') && !$request->hasFile('foto_ruta')) {
            return null;
        }

        $archivo = $request->file('evidencia') ?? $request->file('foto') ?? $request->file('foto_equipo') ?? $request->file('foto_ruta');
        if (!$archivo || !$archivo->isValid()) {
            return null;
        }

        $anio = now()->format('Y');
        $mesesEspanol = self::MESES;
        $mes = $mesesEspanol[(int)now()->format('n')] ?? ucfirst(now()->locale('es')->translatedFormat('F')); // Ej: Octubre
        $dia = now()->format('d'); // Ej: 05

        // Ruta base física
        $basePath = env('EVIDENCIAS_PATH', 'C:\\Users\\LENOVO\\Pictures\\fotos');
        $basePath = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $basePath), DIRECTORY_SEPARATOR);

        // Carpeta destino: C:\Users\LENOVO\Pictures\fotos\2026\Octubre\05
        $directorioDestino = $basePath . DIRECTORY_SEPARATOR . $anio . DIRECTORY_SEPARATOR . $mes . DIRECTORY_SEPARATOR . $dia;

        if (!File::isDirectory($directorioDestino)) {
            File::makeDirectory($directorioDestino, 0777, true, true);
        }

        // Nombre del archivo basado en la placa
        $placa = $placaForzada ?? $request->placa ?? $request->placa_id ?? $request->placa_id_equipo ?? 'EQUIPO';
        $placaLimpia = trim(strtoupper($placa));
        $extension = $archivo->getClientOriginalExtension() ?: 'jpg';
        $nombreArchivo = $placaLimpia . '.' . $extension;

        // Mover físicamente el archivo
        $archivo->move($directorioDestino, $nombreArchivo);

        // Redundancia en subcarpeta del módulo para soporte total (ej: /fotos/cpus/2026/Octubre/05/B88147.jpg)
        $directorioModulo = $basePath . DIRECTORY_SEPARATOR . $modulo . DIRECTORY_SEPARATOR . $anio . DIRECTORY_SEPARATOR . $mes . DIRECTORY_SEPARATOR . $dia;
        if (!File::isDirectory($directorioModulo)) {
            File::makeDirectory($directorioModulo, 0777, true, true);
        }
        @copy($directorioDestino . DIRECTORY_SEPARATOR . $nombreArchivo, $directorioModulo . DIRECTORY_SEPARATOR . $nombreArchivo);

        return $nombreArchivo;
    }

    /**
     * Sube y organiza evidencias en C:\Users\LENOVO\Pictures\fotos\{Año}\{Nombre_Mes}\{Dia}\{PLACA}.jpg
     * Crea subdirectorios automáticamente con permisos de escritura.
     * Retorna el nombre del archivo o ruta esperada.
     */
    public function guardarEvidencia(?UploadedFile $file, string $placaId, string $categoria = 'cpus'): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        $basePath  = self::getBasePath();
        $anio      = now()->format('Y');
        $mes       = self::getNombreMes(now()->format('n'));
        $dia       = now()->format('d');

        // Subcarpeta patrón: {Año}/{Nombre_Mes}/{Dia}
        $directorioDestino = $basePath . DIRECTORY_SEPARATOR . $anio . DIRECTORY_SEPARATOR . $mes . DIRECTORY_SEPARATOR . $dia;

        if (!File::isDirectory($directorioDestino)) {
            File::makeDirectory($directorioDestino, 0777, true, true);
        }

        // Sanitizar nombre de archivo basado en la placa
        $placaLimpia = trim(strtoupper($placaId));
        if (empty($placaLimpia)) {
            $placaLimpia = 'EQUIPO_' . date('His');
        }
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $nombreArchivo = $placaLimpia . '.' . $extension;

        try {
            // Mover archivo directamente a la ruta física local
            $file->move($directorioDestino, $nombreArchivo);

            // Redundancia en la subcarpeta del módulo
            $moduloDir = $basePath . DIRECTORY_SEPARATOR . $categoria . DIRECTORY_SEPARATOR . $anio . DIRECTORY_SEPARATOR . $mes . DIRECTORY_SEPARATOR . $dia;
            if (!File::isDirectory($moduloDir)) {
                File::makeDirectory($moduloDir, 0777, true, true);
            }
            @copy($directorioDestino . DIRECTORY_SEPARATOR . $nombreArchivo, $moduloDir . DIRECTORY_SEPARATOR . $nombreArchivo);

            return $nombreArchivo;
        } catch (\Throwable $e) {
            Log::error("UploadService: Error guardando evidencia en '{$directorioDestino}': " . $e->getMessage());
            return null;
        }
    }

    /**
     * Construye de manera dinámica y segura la URL hacia route('evidencias.ver', ...)
     * abstrayendo si el campo en BD viene con ruta antigua, completa o solo nombre de archivo.
     */
    public static function routeEvidencia(?string $fotoCampo, string $modulo = 'cpus', $fecha = null, ?string $placa = null): string
    {
        if (empty($fotoCampo)) {
            return '#';
        }

        $fotoLimpia = str_replace('\\', '/', trim($fotoCampo));

        // Normalizar módulo
        $moduloSlug = match(strtolower($modulo)) {
            'equipos', 'diagnostico', 'cpu', 'cpus' => 'cpus',
            'portatiles', 'portatil', 'laptop'     => 'portatiles',
            'soplado', 'mantenimiento'             => 'soplado',
            default                                => strtolower($modulo)
        };

        // Si la ruta ya contiene el patrón {Año}/{Mes}/{Dia}/{Archivo}
        if (preg_match('/(\d{4})\/([^\/]+)\/(\d{1,2})\/([^\/]+)$/', $fotoLimpia, $m)) {
            return route('evidencias.ver', [
                'modulo'  => $moduloSlug,
                'anio'    => $m[1],
                'mes'     => $m[2],
                'dia'     => str_pad($m[3], 2, '0', STR_PAD_LEFT),
                'archivo' => $m[4]
            ]);
        }

        // Si no coincide con el patrón completo, calcular a partir de la fecha del registro
        $dateObj = null;
        if (!empty($fecha)) {
            try {
                $dateObj = Carbon::parse($fecha);
            } catch (\Throwable $e) {}
        }
        if (!$dateObj) {
            $dateObj = now();
        }

        $anio    = $dateObj->format('Y');
        $mes     = self::getNombreMes($dateObj->format('n'));
        $dia     = $dateObj->format('d');
        $archivo = basename($fotoLimpia);

        return route('evidencias.ver', [
            'modulo'  => $moduloSlug,
            'anio'    => $anio,
            'mes'     => $mes,
            'dia'     => $dia,
            'archivo' => $archivo
        ]);
    }

    /**
     * Retorna una respuesta SVG vectorizada elegante cuando no se encuentra la imagen.
     */
    public static function retornarPlaceholder(string $archivo = 'Evidencia'): \Illuminate\Http\Response
    {
        $archivoSafe = htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8');
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400">
    <rect width="100%" height="100%" fill="#0f172a"/>
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#1e293b"/>
            <stop offset="100%" stop-color="#0f172a"/>
        </linearGradient>
    </defs>
    <rect width="100%" height="100%" fill="url(#grad)"/>
    <circle cx="300" cy="160" r="55" fill="#334155"/>
    <path d="M280 160 l15 15 l30 -30" stroke="#38bdf8" stroke-width="5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    <text x="300" y="255" font-family="-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif" font-size="20" font-weight="bold" fill="#f8fafc" text-anchor="middle">
        Evidencia No Disponible
    </text>
    <text x="300" y="285" font-family="-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif" font-size="13" fill="#94a3b8" text-anchor="middle">
        {$archivoSafe}
    </text>
    <text x="300" y="320" font-family="-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif" font-size="11" fill="#64748b" text-anchor="middle">
        El archivo físico no se encontró en C:\Users\LENOVO\Pictures\fotos
    </text>
</svg>
SVG;

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml',
            'Cache-Control' => 'no-cache, private'
        ]);
    }

    /**
     * Regulariza y migra todas las fotos previas desde backend/public o storage/app
     * hacia la ruta física canónica C:\Users\LENOVO\Pictures\fotos/{Año}/{Nombre_Mes}/{Dia}/{PLACA}.jpg
     */
    public static function migrarFotosExistentes(): array
    {
        $baseDestino = self::getBasePath();
        $migrados = 0;
        $errores = 0;

        $directoriosOrigen = [
            storage_path('app/public/evidencias'),
            storage_path('app/public/uploads'),
            public_path('uploads'),
        ];

        foreach ($directoriosOrigen as $dir) {
            if (!File::exists($dir)) continue;

            $archivos = File::allFiles($dir);
            foreach ($archivos as $file) {
                $ext = strtolower($file->getExtension());
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    continue;
                }

                $filePath = $file->getRealPath();
                $filename = $file->getFilename();

                // Intentar extraer fecha de la ruta o del archivo
                $anio = null;
                $mes = null;
                $dia = null;

                $relPath = str_replace('\\', '/', $filePath);
                if (preg_match('/(\d{4})\/([^\/]+)\/(\d{1,2})\//', $relPath, $m)) {
                    $anio = $m[1];
                    $mes  = is_numeric($m[2]) ? self::getNombreMes($m[2]) : $m[2];
                    $dia  = str_pad($m[3], 2, '0', STR_PAD_LEFT);
                }

                if (!$anio) {
                    $timestamp = $file->getMTime();
                    $anio = date('Y', $timestamp);
                    $mes  = self::getNombreMes(date('n', $timestamp));
                    $dia  = date('d', $timestamp);
                }

                $destinoDir = $baseDestino . DIRECTORY_SEPARATOR . $anio . DIRECTORY_SEPARATOR . $mes . DIRECTORY_SEPARATOR . $dia;
                if (!File::exists($destinoDir)) {
                    File::makeDirectory($destinoDir, 0755, true, true);
                }

                $destinoPath = $destinoDir . DIRECTORY_SEPARATOR . $filename;
                if (!File::exists($destinoPath)) {
                    if (@copy($filePath, $destinoPath)) {
                        $migrados++;
                    } else {
                        $errores++;
                    }
                }
            }
        }

        return [
            'migrados'    => $migrados,
            'errores'     => $errores,
            'baseDestino' => $baseDestino
        ];
    }
}
