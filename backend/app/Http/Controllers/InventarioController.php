<?php

namespace App\Http\Controllers;

use App\Models\InventarioGeneral;
use App\Models\Equipo;
use App\Models\SopladoRegistro;
use App\Models\GarantiaPortatil;
use App\Services\TrazabilidadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    protected TrazabilidadService $trazabilidadService;

    public function __construct(TrazabilidadService $trazabilidadService)
    {
        $this->trazabilidadService = $trazabilidadService;
    }

    public function index(Request $request)
    {
        if (!in_array(Auth::user()->rol, ['admin', 'analista'])) {
            return response()->json(['error' => 'Acceso denegado.'], 403);
        }

        $busqueda = trim($request->query('buscar'));
        $filtro   = trim($request->query('filtro')); 
        $traslado = trim($request->query('traslado'));
        $limite   = (int) ($request->query('limite') ?? 250);
        
        if ($limite <= 0 || $limite > 1000) {
            $limite = 250;
        }

        $query = InventarioGeneral::query();

        if ($busqueda) {
            $query->where(function($q) use ($busqueda) {
                $q->where('identificador_1', 'like', "%{$busqueda}%")
                  ->orWhere('identificador_2', 'like', "%{$busqueda}%")
                  ->orWhere('placa_id', 'like', "%{$busqueda}%")
                  ->orWhere('serial', 'like', "%{$busqueda}%")
                  ->orWhere('ref_principal', 'like', "%{$busqueda}%")
                  ->orWhere('descripcion', 'like', "%{$busqueda}%");
            });
        }

        if ($filtro === 'agregados') {
            $query->where('intervenido', 1);
        } elseif ($filtro === 'pendientes') {
            $query->where('intervenido', 0);
        }

        if ($traslado) {
            if ($traslado === 'sin_traslado') {
                $query->where(function($q) {
                    $q->whereNull('num_traslado')->orWhere('num_traslado', '');
                });
            } else {
                $query->where('num_traslado', $traslado);
            }
        }

        $registros = $query->orderBy('id', 'desc')->paginate($limite)->withQueryString();

        $totalCargados = InventarioGeneral::count();
        $totalIntervenidos = InventarioGeneral::where('intervenido', 1)->count();
        $totalPendientes = InventarioGeneral::where('intervenido', 0)->count();

        $statsInventario = [
            'totalCargados' => $totalCargados,
            'totalIntervenidos' => $totalIntervenidos,
            'totalPendientes' => $totalPendientes,
            'porcentajeAgregadas' => $totalCargados > 0 ? round(($totalIntervenidos / $totalCargados) * 100, 1) : 0,
        ];

        $trasladosDisponibles = InventarioGeneral::select('num_traslado', DB::raw('count(*) as total'))
            ->whereNotNull('num_traslado')
            ->where('num_traslado', '!=', '')
            ->groupBy('num_traslado')
            ->orderBy('num_traslado', 'asc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->num_traslado => $item->total];
            })->toArray();

        return view('inventario.index', [
            'registros'            => $registros,
            'totalRegistros'       => $statsInventario['totalCargados'],
            'statsInventario'      => $statsInventario,
            'busqueda'             => $busqueda,
            'filtro'               => $filtro,
            'traslado'             => $traslado,
            'limite'               => $limite,
            'trasladosDisponibles' => $trasladosDisponibles,
            'totalFiltrados'       => $registros->total(),
            'esAdmin'              => Auth::user()->rol === 'admin',
        ]);
    }

    public function show($id)
    {
        if (!in_array(Auth::user()->rol, ['admin', 'analista'])) {
            return response()->json(['error' => 'Acceso denegado.'], 403);
        }

        $equipo = InventarioGeneral::findOrFail($id);
        return response()->json(compact('equipo'));
    }

    public function sincronizar(Request $request)
    {
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasPermission('inventario.ver')) {
            abort(403, 'No tienes permiso para sincronizar el inventario.');
        }

        $traslado = trim($request->query('traslado'));
        
        $inventarioService = app(\App\Services\InventarioService::class);
        $resultados = $inventarioService->conciliarInventarioCompleto(!empty($traslado) ? $traslado : null);

        $msg = "Sincronización y Conciliación completada con éxito. ";
        $msg .= "Se actualizaron {$resultados['intervenidos_actualizados']} máquinas intervenidas y se recuperaron {$resultados['traslados_recuperados']} números de traslado.";

        return redirect()->route('inventario.index', $traslado ? ['traslado' => $traslado] : [])
                         ->with('msg', $msg);
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
