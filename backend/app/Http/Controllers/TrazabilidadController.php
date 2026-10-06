<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TrazabilidadService;

class TrazabilidadController extends Controller
{
    protected TrazabilidadService $trazabilidadService;

    public function __construct(TrazabilidadService $trazabilidadService)
    {
        $this->trazabilidadService = $trazabilidadService;
    }

    public function index()
    {
        return view('trazabilidad.index');
    }

    public function buscar(Request $request)
    {
        $termino = trim($request->input('termino'));

        if (!$termino || strlen($termino) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor ingresa un término de al menos 3 caracteres.'
            ]);
        }

        $historial = $this->trazabilidadService->obtenerHistorialTimeline($termino);

        if (empty($historial) || empty($historial['timeline'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron registros de historial para este equipo.'
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $historial
        ]);
    }
}
