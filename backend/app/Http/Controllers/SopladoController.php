<?php

namespace App\Http\Controllers;

use App\Models\SopladoRegistro;
use App\Models\InventarioGeneral;
use App\Http\Requests\StoreSopladoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Services\UploadService;
use App\Traits\FiltraPorPeriodoYPermiso;
use Exception;

class SopladoController extends Controller
{
    use FiltraPorPeriodoYPermiso;

    public function index(Request $request)
    {
        $user = $request->user();

        // Verificación de permiso para consultar la bitácora de Soplado
        if (!$user || (!$user->can('soplado.ver_bitacora') && !$user->hasRole('admin'))) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a la bitácora de Mantenimiento / Soplado.');
        }

        $datos = $this->obtenerDatosPaginados(
            SopladoRegistro::query(),
            $request,
            ['placa_id', 'num_traslado', 'nombre_analista', 'maquina_contenia'],
            'created_at'
        );

        return view('soplado.index', $datos);
    }

    public function create()
    {
        if (!auth()->user()->can('soplado.registrar') && !auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para registrar nuevos mantenimientos de soplado.');
        }

        $analistas = \App\Models\Usuario::where('rol', 'analista')->where('activo', true)->orderBy('nombre')->get();
        return view('soplado.create', compact('analistas'));
    }

    public function ultimoRegistro(Request $request)
    {
        $placa = $request->query('placa');
        if (!$placa) return response()->json(null);

        return response()->json(\App\Services\TrazabilidadService::obtenerUltimoRegistro($placa, 'soplado'));
    }

    public function store(StoreSopladoRequest $request)
    {
        if (!auth()->user()->can('soplado.registrar') && !auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para registrar soplado.');
        }

        $placaFinal = $request->placa_id ?? $request->placa;
        $fotoRuta = UploadService::procesarSubidaFisica($request, 'soplado', $placaFinal);

        $user = auth()->user();
        $nombreAnalista = ($user && $user->rol === 'analista') 
            ? $user->nombre 
            : ($request->nombre_analista ?: ($user->nombre ?? 'Sistema'));

        $maquinaContenia = $request->maquina_contenia;
        $gelCucarachas = ($maquinaContenia === 'Cucaracha') ? ($request->gel_cucarachas ?: 'No') : 'No';

        try {
            DB::transaction(function () use ($request, $fotoRuta, $nombreAnalista, $maquinaContenia, $gelCucarachas, $placaFinal) {
                $registro = SopladoRegistro::create([
                    'nombre_analista'  => $nombreAnalista,
                    'num_traslado'     => $request->num_traslado,
                    'placa_id'         => $placaFinal,
                    'energiza'         => $request->energiza,
                    'da_video'         => $request->da_video,
                    'detecta_disco'    => $request->detecta_disco,
                    'ingreso_bios'     => $request->ingreso_bios,
                    'pasta_termica'    => $request->pasta_termica,
                    'maquina_contenia' => $maquinaContenia,
                    'gel_cucarachas'   => $gelCucarachas,
                    'foto_ruta'        => $fotoRuta,
                    'foto_equipo'      => $fotoRuta,
                    'evidencia'        => $fotoRuta,
                    'fecha_registro'   => now(),
                    'created_at'       => now(),
                ]);

                app(\App\Services\InventarioService::class)->marcarComoIntervenido(
                    $placaFinal,
                    'soplado',
                    $nombreAnalista,
                    $request->num_traslado
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
