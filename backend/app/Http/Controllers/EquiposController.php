<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\InventarioGeneral;
use App\Http\Requests\StoreEquipoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class EquiposController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->query('buscar');
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');

        $query = Equipo::query();

        if ($busqueda) {
            $query->where(function($q) use ($busqueda) {
                $q->where('placa_id', 'like', "%{$busqueda}%")
                  ->orWhere('num_traslado', 'like', "%{$busqueda}%")
                  ->orWhere('nombre_analista', 'like', "%{$busqueda}%");
            });
        }
        if ($fechaDesde) {
            $query->where('fecha_creacion', '>=', $fechaDesde . ' 00:00:00');
        }
        if ($fechaHasta) {
            $query->where('fecha_creacion', '<=', $fechaHasta . ' 23:59:59');
        }

        $registros = $query->orderBy('id', 'desc')->get();
        $totalGeneral = Equipo::count();

        return view('equipos.index', [
            'registros' => $registros,
            'totalFiltrados' => $registros->count(),
            'totalGeneral' => $totalGeneral,
            'busqueda' => $busqueda,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
        ]);
    }

    public function create()
    {
        $analistas = \App\Models\Usuario::where('rol', 'analista')->where('activo', true)->get();
        return view('equipos.create', compact('analistas'));
    }

    public function store(StoreEquipoRequest $request)
    {
        $fotoRuta = null;
        if ($request->hasFile('foto_equipo')) {
            $fotoRuta = app(\App\Services\UploadService::class)->guardarEvidencia(
                $request->file('foto_equipo'), 
                $request->placa_id, 
                'diagnostico'
            );
        }

        $user = auth()->user();
        $nombreAnalista = ($user && $user->rol === 'analista') 
            ? $user->nombre 
            : ($request->nombre_analista ?: ($user->nombre ?? 'Sistema'));

        $serialDisco = $request->serial_disco ?: $request->serial_disco_baja;
        $descripcionNovedad = $request->descripcion_novedad ?: $request->descripcion_it;

        $novedadIt = $request->novedad_it ?: $request->descripcion_it;
        $solucionGarantias = $request->solucion_garantias;
        $timestampRegistro = $request->timestamp_registro ?: now()->toDateTimeString();

        $queVaIntervenir = $request->que_va_intervenir ?: $request->input('componentes');
        if (is_array($queVaIntervenir)) {
            $queVaIntervenir = implode(';', array_filter($queVaIntervenir));
        }

        try {
            DB::transaction(function () use ($request, $fotoRuta, $nombreAnalista, $serialDisco, $descripcionNovedad, $novedadIt, $solucionGarantias, $timestampRegistro, $queVaIntervenir) {
                Equipo::create([
                    'timestamp_registro'  => $timestampRegistro,
                    'nombre_analista'     => $nombreAnalista,
                    'num_traslado'        => $request->num_traslado,
                    'placa_id'            => $request->placa_id,
                    'tipo_gestion'        => $request->tipo_gestion,
                    'energiza'            => $request->energiza,
                    'da_video'            => $request->da_video,
                    'estado_actual'       => $request->estado_actual,
                    'que_va_intervenir'   => $queVaIntervenir ?: null,
                    'origen_pieza'        => $request->origen_pieza,
                    'serial_disco'        => $serialDisco ?: null,
                    'descripcion_novedad' => $descripcionNovedad ?: null,
                    'novedad_it'          => $novedadIt ?: null,
                    'solucion_garantias'  => $solucionGarantias ?: null,
                    'motivo_baja'         => $request->motivo_baja,
                    'ubicacion_destino'   => $request->ubicacion_destino,
                    'foto_equipo'         => $fotoRuta,
                    'fecha_creacion'      => now()
                ]);

                app(\App\Services\InventarioService::class)->marcarComoIntervenido(
                    $request->placa_id,
                    'Diagnóstico CPU',
                    $nombreAnalista
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
