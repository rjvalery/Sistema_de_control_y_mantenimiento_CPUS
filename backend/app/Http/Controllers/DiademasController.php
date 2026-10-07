<?php

namespace App\Http\Controllers;

use App\Models\DiademaLote;
use App\Models\DiademaItem;
use App\Http\Requests\GuardarLoteDiademasRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DiademasController extends Controller
{
    public function index()
    {
        if (Schema::hasTable('diademas_lotes')) {
            $lotes = DiademaLote::withCount('items')->latest('fecha_ingreso')->paginate(15);
        } else {
            $lotes = collect([]); // Fallback
        }

        return view('diademas.index', compact('lotes'));
    }

    public function create()
    {
        $analistas = class_exists('\App\Models\Usuario') ? \App\Models\Usuario::where('rol', 'analista')->where('activo', true)->get() : [];
        return view('diademas.create', compact('analistas'));
    }

    public function store(GuardarLoteDiademasRequest $request)
    {
        try {
            DB::beginTransaction();

            $items = collect($request->items);
            
            $funcionales = $items->where('estado', 'funcional')->count();
            $garantias = $items->where('estado', 'garantia')->count();
            $bajas = $items->where('estado', 'baja')->count();
            
            $lote = DiademaLote::create([
                'user_id' => auth()->id(),
                'nombre_analista' => $request->nombre_analista ?? auth()->user()->name ?? auth()->user()->nombre,
                'numero_traslado' => $request->numero_traslado,
                'total_funcionales' => $funcionales,
                'total_garantia' => $garantias,
                'total_baja' => $bajas,
                'total_unidades' => count($request->items),
                'fecha_ingreso' => Carbon::now('America/Bogota'),
                'observaciones' => $request->observaciones,
            ]);

            $itemsData = $items->map(function ($item) use ($lote) {
                return [
                    'lote_id' => $lote->id,
                    'identificador' => $item['identificador'],
                    'marca_modelo' => $item['marca_modelo'] ?? null,
                    'estado' => $item['estado'],
                    'motivo' => $item['motivo'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            DiademaItem::insert($itemsData);

            DB::commit();

            return redirect()->route('diademas.index')->with('success', 'Lote de diademas registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al guardar el lote: ' . $e->getMessage())->withInput();
        }
    }
}
