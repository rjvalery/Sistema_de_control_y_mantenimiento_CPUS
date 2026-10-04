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
        // Validación de seguridad: Sólo usuarios autenticados y con rol administrador
        if (!Auth::check() || Auth::user()->rol !== 'admin') {
            abort(403, 'Acceso denegado. Esta evidencia es estrictamente confidencial.');
        }

        // Verifica si el archivo existe en el disco personalizado que mapea a la carpeta en Windows
        if (!Storage::disk('fotos_servidor')->exists($path)) {
            abort(404, 'La evidencia fotográfica no se encuentra en el servidor.');
        }

        // Retorna la imagen directamente al navegador, sin exponer la carpeta físicamente
        return Storage::disk('fotos_servidor')->response($path);
    }
}
