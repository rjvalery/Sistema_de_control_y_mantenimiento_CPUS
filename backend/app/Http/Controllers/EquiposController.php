<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\InventarioGeneral;
use App\Http\Requests\StoreEquipoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Exception;
use App\Traits\FiltraPorPeriodoYPermiso;
use App\Services\UploadService;

class EquiposController extends Controller
{
    use FiltraPorPeriodoYPermiso;

    public function index(Request $request)
    {
        $user = $request->user();

        // Verificación de permiso para consultar la bitácora de CPUs
        if (!$user || (!$user->can('cpus.ver_bitacora') && !$user->hasRole('admin'))) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a la bitácora de Diagnóstico CPU.');
        }

        $datos = $this->obtenerDatosPaginados(
            Equipo::query(),
            $request,
            ['placa_id', 'num_traslado', 'nombre_analista', 'estado_actual', 'tipo_gestion'],
            'fecha_creacion'
        );

        return view('equipos.index', $datos);
    }

    public function create()
    {
        if (!auth()->user()->can('cpus.registrar') && !auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para registrar nuevos diagnósticos de CPU.');
        }

        $analistas = \App\Models\Usuario::where('rol', 'analista')->where('activo', true)->get();
        return view('equipos.create', compact('analistas'));
    }

    public function store(StoreEquipoRequest $request)
    {
        if (!auth()->user()->can('cpus.registrar') && !auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para guardar diagnósticos de CPU.');
        }

        $placaFinal = $request->placa_id ?? $request->placa;
        $nombreArchivo = UploadService::procesarSubidaFisica($request, 'cpus', $placaFinal);

        $user = auth()->user();
        $nombreAnalista = ($user && $user->rol === 'analista') 
            ? $user->nombre 
            : ($request->nombre_analista ?: ($user->nombre ?? 'Sistema'));

        $descripcionNovedad = $request->descripcion_novedad ?: $request->descripcion_it;
        $novedadIt = $request->novedad_it ?: $request->descripcion_it;
        $solucionGarantias = $request->solucion_garantias;
        $timestampRegistro = $request->timestamp_registro ?: now()->toDateTimeString();

        $queVaIntervenir = $request->que_va_intervenir ?: $request->input('componentes');
        if (is_array($queVaIntervenir)) {
            $queVaIntervenir = implode(';', array_filter($queVaIntervenir));
        }

        $origenPieza = ($request->tipo_gestion === 'Intervencion') ? $request->origen_pieza : null;

        try {
            DB::transaction(function () use ($request, $nombreArchivo, $nombreAnalista, $descripcionNovedad, $novedadIt, $solucionGarantias, $timestampRegistro, $queVaIntervenir, $placaFinal, $origenPieza) {
                $equipo = Equipo::create([
                    'timestamp_registro'  => $timestampRegistro,
                    'nombre_analista'     => $nombreAnalista,
                    'num_traslado'        => $request->num_traslado,
                    'placa_id'            => $placaFinal,
                    'tipo_gestion'        => $request->tipo_gestion,
                    'energiza'            => $request->energiza,
                    'da_video'            => $request->da_video,
                    'estado_actual'       => $request->estado_actual,
                    'que_va_intervenir'   => $queVaIntervenir ?: null,
                    'origen_pieza'        => $origenPieza,
                    'descripcion_novedad' => $descripcionNovedad ?: null,
                    'novedad_it'          => $novedadIt ?: null,
                    'solucion_garantias'  => $solucionGarantias ?: null,
                    'motivo_baja'         => $request->motivo_baja,
                    'ubicacion_destino'   => $request->ubicacion_destino,
                    'foto_equipo'         => $nombreArchivo,
                    'evidencia'           => $nombreArchivo,
                    'fecha_creacion'      => now()
                ]);

                app(\App\Services\InventarioService::class)->marcarComoIntervenido(
                    $placaFinal,
                    'Diagnóstico CPU',
                    $nombreAnalista,
                    $request->num_traslado
                );
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Guardado correctamente']);
            }
            return redirect()->route('equipos.create')->with('msg', 'Guardado correctamente');

        } catch (Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }
}
