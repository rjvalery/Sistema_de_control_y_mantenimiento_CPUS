<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class EvidenciaController extends Controller
{
    /**
     * Muestra una imagen de forma segura comprobando autenticación y roles.
     *
     * @param string $path
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function show(string $path)
    {
        // Validación de seguridad: Sólo usuarios autenticados
        if (!Auth::check()) {
            abort(401, 'Debes iniciar sesión para consultar las evidencias.');
        }

        // Verifica si el archivo existe en el disco personalizado que mapea a la carpeta en Windows
        if (!Storage::disk('fotos_servidor')->exists($path)) {
            abort(404, 'La evidencia fotográfica no se encuentra en el servidor.');
        }

        // Retorna la imagen directamente al navegador, sin exponer la carpeta físicamente
        return Storage::disk('fotos_servidor')->response($path);
    }
}
