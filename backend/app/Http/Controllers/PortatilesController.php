<?php

namespace App\Http\Controllers;

use App\Models\GarantiaPortatil;
use App\Models\InventarioGeneral;
use App\Http\Requests\StorePortatilRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Services\UploadService;
use App\Traits\FiltraPorPeriodoYPermiso;
use Exception;

class PortatilesController extends Controller
{
    use FiltraPorPeriodoYPermiso;

    public function index(Request $request)
    {
        $user = $request->user();

        // Verificación de permiso para consultar la bitácora de Portátiles
        if (!$user || (!$user->can('portatiles.ver_bitacora') && !$user->hasRole('admin'))) {
            return response()->json(['error' => 'No tienes permisos para acceder a la bitácora de Diagnóstico Portátiles.'], 403);
        }

        $datos = $this->obtenerDatosPaginados(
            GarantiaPortatil::query(),
            $request,
            ['placa_id_equipo', 'numero_traslado', 'numero_ticket', 'nombre_analista', 'estado_actual_equipo'],
            'created_at'
        );

        return response()->json($datos);
    }

    public function create()
    {
        if (!auth()->user()->can('portatiles.registrar') && !auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'No tienes permisos para registrar nuevos diagnósticos de portátiles.'], 403);
        }

        $analistas = \App\Models\Usuario::where('rol', 'analista')->where('activo', true)->orderBy('nombre')->get();
        return response()->json(compact('analistas'));
    }

    public function ultimoRegistro(Request $request)
    {
        $placa = $request->query('placa');
        if (!$placa) {
            return response()->json(null);
        }

        return response()->json(\App\Services\TrazabilidadService::obtenerUltimoRegistro($placa, 'portatiles'));
    }

    public function evidencia(Request $request)
    {
        $placaInicial = $request->query('placa', '');
        $analistas = \App\Models\Usuario::where('rol', 'analista')->where('activo', true)->orderBy('nombre')->get();
        return response()->json(compact('placaInicial', 'analistas'));
    }

    public function guardarEvidencia(Request $request)
    {
        $request->validate([
            'placa_id_equipo' => 'required|string',
            'foto_equipo' => 'nullable|image|max:10240',
            'evidencia' => 'nullable|image|max:10240',
            'foto' => 'nullable|image|max:10240',
        ]);

        $placa = $request->placa_id_equipo ?? $request->placa;
        
        $registro = GarantiaPortatil::where('placa_id_equipo', $placa)
                        ->orderBy('id', 'desc')
                        ->first();

        if (!$registro) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró registro para la placa indicada. Por favor registre primero el diagnóstico.'
            ], 404);
        }

        $fotoRuta = UploadService::procesarSubidaFisica($request, 'portatiles', $placa);
        if ($fotoRuta) {
            $registro->foto_ruta = $fotoRuta;
            $registro->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Evidencia guardada y enlazada correctamente.'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No se recibió ninguna imagen válida.'
        ], 400);
    }

    public function store(StorePortatilRequest $request)
    {
        if (!auth()->user()->can('portatiles.registrar') && !auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'No tienes permisos para registrar intervenciones de portátiles.'], 403);
        }

        $placa = $request->placa_id_equipo ?? $request->placa;
        $fotoRuta = UploadService::procesarSubidaFisica($request, 'portatiles', $placa);

        $user = auth()->user();
        $nombreAnalista = ($user && $user->rol === 'analista') 
            ? $user->nombre 
            : ($request->nombre_analista ?: ($user->nombre ?? 'Sistema'));

        $estadoActual = $request->estado_actual_equipo;

        $indiquePieza = is_array($request->indique_pieza) 
            ? implode(', ', array_filter($request->indique_pieza))
            : $request->indique_pieza;

        $indiqueFru = is_array($request->indique_fru) 
            ? implode(', ', array_filter($request->indique_fru))
            : $request->indique_fru;

        $reparadoPor = $request->reparado_por;
        $comentarioRep = $request->comentario_reparado;
        $diagnosticoFinal = ($estadoActual === 'Reparado') 
            ? ($reparadoPor ? "[$reparadoPor] $comentarioRep" : $comentarioRep)
            : $request->diagnostico_laptop_intervenido;

        $estadoFinal = ($estadoActual === 'Reparado' && $reparadoPor) 
            ? "Reparado por $reparadoPor" 
            : ($estadoActual === 'Donacion' ? 'Donación' : ($request->estado_final_equipo ?: $estadoActual));

        $origenPieza = ($estadoActual === 'Reparado' && $reparadoPor) ? $reparadoPor : $request->origen_pieza;
        $garantia = ($estadoActual === 'Garantia') ? 'Aplica' : $request->garantia;

        try {
            DB::transaction(function () use ($request, $fotoRuta, $nombreAnalista, $diagnosticoFinal, $garantia, $estadoFinal, $indiquePieza, $indiqueFru, $origenPieza, $placa) {
                $data = [
                    'nombre_analista'                => $nombreAnalista,
                    'numero_traslado'                => $request->numero_traslado,
                    'placa_id_equipo'                => $placa,
                    'tipo_gestion'                   => $request->tipo_gestion,
                    'energiza'                       => $request->energiza,
                    'da_video'                       => $request->da_video,
                    'realizo_test_lenovo'            => $request->realizo_test_lenovo,
                    'estado_actual_equipo'           => $request->estado_actual_equipo,
                    'diagnostico_laptop_intervenido' => $diagnosticoFinal,
                    'garantia'                       => $garantia,
                    'porque_solicita_garantia'       => $request->porque_solicita_garantia,
                    'numero_ticket'                  => $request->numero_ticket,
                    'estado_final_equipo'            => $estadoFinal,
                    'indique_pieza'                  => $indiquePieza,
                    'indique_fru'                    => $indiqueFru,
                    'pieza_intervenida'              => $request->pieza_intervenida,
                    'origen_pieza'                   => $origenPieza,
                    'motivo_baja'                    => $request->motivo_baja,
                    'serial_disco'                   => $request->serial_disco,
                    'created_at'                     => now(),
                ];

                if ($fotoRuta) {
                    $data['foto_ruta'] = $fotoRuta;
                }

                GarantiaPortatil::create($data);

                app(\App\Services\InventarioService::class)->marcarComoIntervenido(
                    $placa,
                    'Diagnóstico Portátiles',
                    $nombreAnalista,
                    $request->numero_traslado
                );
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Guardado correctamente']);
            }
            return response()->json(['message' => 'Guardado correctamente']);

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en PortatilesController@store: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
            }
            return response()->json(['error' => 'Error al guardar: ' . $e->getMessage()], 500);
        }
    }
}
