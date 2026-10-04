<?php

namespace App\Http\Controllers;

use App\Models\SopladoRegistro;
use App\Models\InventarioGeneral;
use App\Http\Requests\StoreSopladoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class SopladoController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->query('buscar');
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');

        $query = SopladoRegistro::query();

        if ($busqueda) {
            $query->where(function($q) use ($busqueda) {
                $q->where('placa_id', 'like', "%{$busqueda}%")
                  ->orWhere('num_traslado', 'like', "%{$busqueda}%")
                  ->orWhere('nombre_analista', 'like', "%{$busqueda}%");
            });
        }
        if ($fechaDesde) {
            $query->where('created_at', '>=', $fechaDesde . ' 00:00:00');
        }
        if ($fechaHasta) {
            $query->where('created_at', '<=', $fechaHasta . ' 23:59:59');
        }

        $registros = $query->orderBy('id', 'desc')->get();

        return view('soplado.index', [
            'registros' => $registros,
            'totalFiltrados' => $registros->count(),
            'totalGeneral' => SopladoRegistro::count(),
            'busqueda' => $busqueda,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
        ]);
    }

    public function create()
    {
        $analistas = \App\Models\Usuario::where('rol', 'analista')->where('activo', true)->orderBy('nombre')->get();
        return view('soplado.create', compact('analistas'));
    }

    public function ultimoRegistro(Request $request)
    {
        $placa = $request->query('placa');
        if (!$placa) return response()->json(null);

        $registro = SopladoRegistro::where('placa_id', $placa)
                        ->orderBy('id', 'desc')
                        ->first();
        return response()->json($registro);
    }

    public function store(StoreSopladoRequest $request)
    {
        $fotoRuta = null;
        if ($request->hasFile('foto_equipo')) {
            $fotoRuta = app(\App\Services\UploadService::class)->guardarEvidencia(
                $request->file('foto_equipo'), 
                $request->placa_id, 
                'soplado'
            );
        }

        $user = auth()->user();
        $nombreAnalista = ($user && $user->rol === 'analista') 
            ? $user->nombre 
            : ($request->nombre_analista ?: ($user->nombre ?? 'Sistema'));

        $maquinaContenia = $request->maquina_contenia;
        $gelCucarachas = ($maquinaContenia === 'Cucaracha') ? ($request->gel_cucarachas ?: 'No') : 'No';

        try {
            DB::transaction(function () use ($request, $fotoRuta, $nombreAnalista, $maquinaContenia, $gelCucarachas) {
                SopladoRegistro::create([
                    'nombre_analista'  => $nombreAnalista,
                    'num_traslado'     => $request->num_traslado,
                    'placa_id'         => $request->placa_id,
                    'energiza'         => $request->energiza,
                    'da_video'         => $request->da_video,
                    'detecta_disco'    => $request->detecta_disco,
                    'ingreso_bios'     => $request->ingreso_bios,
                    'pasta_termica'    => $request->pasta_termica,
                    'maquina_contenia' => $maquinaContenia,
                    'gel_cucarachas'   => $gelCucarachas,
                    'foto_ruta'        => $fotoRuta,
                    'created_at'       => now(),
                ]);

                app(\App\Services\InventarioService::class)->marcarComoIntervenido(
                    $request->placa_id,
                    'soplado',
                    $nombreAnalista
                );
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Guardado correctamente']);
            }
            return redirect()->route('soplado.create')->with('msg', 'Guardado correctamente');

        } catch (Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }
}
