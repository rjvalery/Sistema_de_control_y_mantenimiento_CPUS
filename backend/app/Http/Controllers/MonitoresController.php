<?php

namespace App\Http\Controllers;

use App\Models\MonitorRegistro;
use App\Models\InventarioGeneral;
use App\Http\Requests\GuardarMonitorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MonitoresController extends Controller
{
    public function index(Request $request)
    {
        if (Schema::hasTable('monitores_registros')) {
            $query = MonitorRegistro::query();

            if ($request->filled('buscar')) {
                $buscar = $request->input('buscar');
                $query->where(function($q) use ($buscar) {
                    $q->where('serial', 'like', "%{$buscar}%")
                      ->orWhere('placa', 'like', "%{$buscar}%")
                      ->orWhere('numero_traslado', 'like', "%{$buscar}%")
                      ->orWhere('nombre_analista', 'like', "%{$buscar}%");
                });
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_ingreso', '>=', $request->input('fecha_desde'));
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_ingreso', '<=', $request->input('fecha_hasta'));
            }

            $limite = $request->input('limite', 50);
            $monitores = $query->orderBy('fecha_ingreso', 'desc')->paginate($limite);
        } else {
            $monitores = collect([]); // Fallback temporal antes de migrar
        }
        
        return response()->json(compact('monitores'));
    }

    public function create()
    {
        return response()->json($equipo);

        return response()->json([
            'encontrado' => !!$equipo,
            'modelo' => $equipo ? $equipo->modelo : null,
            'placa' => $equipo ? $equipo->placa_id : null,
            'numero_traslado' => $traslado ?: ''
        ]);
    }

    public function store(GuardarMonitorRequest $request)
    {
        $data = $request->validated();
        
        $user = auth()->user();
        $data['user_id'] = $user ? $user->id : null;
        $data['nombre_analista'] = $user ? ($user->name ?? $user->nombre) : 'Sistema';
        $data['fecha_ingreso'] = Carbon::now('America/Bogota');

        // Procesar checkboxes
        if ($data['tipo_gestion'] === 'diagnostico') {
            $data['energiza'] = $request->has('energiza') && $request->energiza ? true : false;
            $data['da_video'] = $request->has('da_video') && $request->da_video ? true : false;
        }

        MonitorRegistro::create($data);

        return redirect()->route('monitores.index')->with('success', 'Registro de monitor guardado correctamente.');
    }
}
