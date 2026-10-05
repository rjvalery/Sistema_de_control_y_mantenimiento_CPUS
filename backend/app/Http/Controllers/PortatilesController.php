<?php

namespace App\Http\Controllers;

use App\Models\GarantiaPortatil;
use App\Models\InventarioGeneral;
use App\Http\Requests\StorePortatilRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PortatilesController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string)$request->query('buscar'));
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');
        $limite = (int) ($request->query('limite') ?? 50);
        if ($limite <= 0 || $limite > 500) {
            $limite = 50;
        }

        $query = GarantiaPortatil::query();

        if ($busqueda) {
            $query->where(function($q) use ($busqueda) {
                $q->where('placa_id_equipo', 'like', "%{$busqueda}%")
                  ->orWhere('numero_traslado', 'like', "%{$busqueda}%")
                  ->orWhere('numero_ticket', 'like', "%{$busqueda}%")
                  ->orWhere('nombre_analista', 'like', "%{$busqueda}%")
                  ->orWhere('estado_actual_equipo', 'like', "%{$busqueda}%");
            });
        }
        if ($fechaDesde) {
            $query->where('created_at', '>=', $fechaDesde . ' 00:00:00');
        }
        if ($fechaHasta) {
            $query->where('created_at', '<=', $fechaHasta . ' 23:59:59');
        }

        $registros = $query->orderBy('id', 'desc')->paginate($limite)->withQueryString();
        $totalGeneral = GarantiaPortatil::count();

        return view('portatiles.index', [
            'registros'      => $registros,
            'totalFiltrados' => $registros->total(),
            'totalGeneral'   => $totalGeneral,
            'busqueda'       => $busqueda,
            'fechaDesde'     => $fechaDesde,
            'fechaHasta'     => $fechaHasta,
            'limite'         => $limite,
        ]);
    }

    public function create()
    {
        $analistas = \App\Models\Usuario::where('rol', 'analista')->where('activo', true)->orderBy('nombre')->get();
        return view('portatiles.create', compact('analistas'));
    }

    public function ultimoRegistro(Request $request)
    {
        $placa = $request->query('placa');
        if (!$placa) {
            return response()->json(null);
        }

        $registro = GarantiaPortatil::where('placa_id_equipo', $placa)
                        ->orderBy('id', 'desc')
                        ->first();

        return response()->json($registro);
    }

    public function evidencia(Request $request)
    {
        $placaInicial = $request->query('placa', '');
        $analistas = \App\Models\Usuario::where('rol', 'analista')->where('activo', true)->orderBy('nombre')->get();
        return view('portatiles.evidencia', compact('placaInicial', 'analistas'));
    }

    public function guardarEvidencia(Request $request)
    {
        $request->validate([
            'placa_id_equipo' => 'required|string',
            'foto_equipo' => 'required|image|max:5120'
        ]);

        $placa = $request->placa_id_equipo;
        
        $registro = GarantiaPortatil::where('placa_id_equipo', $placa)
                        ->orderBy('id', 'desc')
                        ->first();

        if (!$registro) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró registro para la placa indicada. Por favor registre primero el diagnóstico.'
            ], 404);
        }

        if ($request->hasFile('foto_equipo')) {
            $fotoRuta = app(\App\Services\UploadService::class)->guardarEvidencia(
                $request->file('foto_equipo'), 
                $placa, 
                'portatil'
            );
            
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
        $fotoRuta = null;
        if ($request->hasFile('foto_equipo')) {
            $fotoRuta = app(\App\Services\UploadService::class)->guardarEvidencia(
                $request->file('foto_equipo'), 
                $request->placa_id_equipo, 
                'portatil'
            );
        }

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
            : ($estadoActual === 'Donacion' ? 'Donación' : $request->estado_final_equipo);

        $origenPieza = ($estadoActual === 'Reparado' && $reparadoPor) ? $reparadoPor : $request->origen_pieza;
        $garantia = ($estadoActual === 'Garantia') ? 'Aplica' : $request->garantia;

        try {
            DB::transaction(function () use ($request, $fotoRuta, $nombreAnalista, $diagnosticoFinal, $garantia, $estadoFinal, $indiquePieza, $indiqueFru, $origenPieza) {
                $data = [
                    'nombre_analista'                => $nombreAnalista,
                    'numero_traslado'                => $request->numero_traslado,
                    'placa_id_equipo'                => $request->placa_id_equipo,
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
                    $request->placa_id_equipo,
                    'Diagnóstico Portátiles',
                    $nombreAnalista,
                    $request->numero_traslado
                );
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Guardado correctamente']);
            }
            return redirect()->route('portatiles.create')->with('msg', 'Guardado correctamente');

        } catch (Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }
}
