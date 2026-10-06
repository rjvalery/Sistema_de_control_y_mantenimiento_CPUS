<?php

namespace App\Http\Controllers;

use App\Models\InventarioGeneral;
use App\Models\Equipo;
use App\Models\SopladoRegistro;
use App\Models\GarantiaPortatil;
use App\Services\TrazabilidadService;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    protected TrazabilidadService $trazabilidadService;

    public function __construct(TrazabilidadService $trazabilidadService)
    {
        $this->trazabilidadService = $trazabilidadService;
    }

    /**
     * Endpoint API para consultar y sincronizar datos de un equipo en tiempo real al tipear la placa o serial.
     */
    public function buscarEquipo(Request $request)
    {
        $termino = trim($request->input('query') ?? $request->input('termino'));
        $modulo = $request->input('modulo', 'diagnostico');

        if ($termino === '' || strlen($termino) < 3) {
            return response()->json([
                'encontrado' => false,
                'mensaje'    => 'Término de búsqueda muy corto (mínimo 3 caracteres).',
            ]);
        }

        $resultado = $this->trazabilidadService->evaluarAlertaPorModulo($termino, $modulo);

        return response()->json($resultado);
    }
}
