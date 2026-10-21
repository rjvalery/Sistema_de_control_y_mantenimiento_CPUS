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
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a la bitácora de Diagnóstico Portátiles.');
        }

        $datos = $this->obtenerDatosPaginados(
            GarantiaPortatil::query(),
            $request,
            [
                'placa_id_equipo',
                'numero_traslado',
                'numero_ticket',
                'nombre_analista',
                'estado_actual_equipo',
                'diagnostico_laptop_intervenido',
                'porque_solicita_garantia',
                'motivo_baja'
            ],
            'created_at'
        );

        return view('portatiles.index', $datos);
    }

    public function create()
    {
        if (!auth()->user()->can('portatiles.registrar') && !auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para registrar nuevos diagnósticos de portátiles.');
        }

        $analistas = \App\Models\Usuario::where('rol', 'analista')->where('activo', true)->orderBy('nombre')->get();
        return view('portatiles.create', compact('analistas'));
    }

    public function ultimoRegistro(Request $request)
    {
        $termino = trim((string)$request->query('placa'));
        if (!$termino) {
            return response()->json(null);
        }

        // 1. Buscar diagnóstico en GarantiaPortatil por placa o por serial
        $diag = GarantiaPortatil::where('placa_id_equipo', $termino)
                    ->orWhere('serial', $termino)
                    ->orderBy('id', 'desc')
                    ->first();

        // 2. Si no se encuentra, cruzar con InventarioGeneral
        $inv = \App\Models\InventarioGeneral::buscarPorTermino($termino);
        if (!$diag && $inv) {
            $placaReal = $inv->placa_id;
            $serialReal = $inv->serial ?: ($inv->identificador_2 ?: null);
            $diag = GarantiaPortatil::where(function ($q) use ($placaReal, $serialReal) {
                        if (!empty($placaReal)) $q->where('placa_id_equipo', $placaReal);
                        if (!empty($serialReal)) $q->orWhere('serial', $serialReal);
                    })
                    ->orderBy('id', 'desc')
                    ->first();
        }

        return response()->json([
            'diagnostico' => $diag,
            'tiene_foto'  => !empty($diag?->foto_ruta),
            'inventario'  => $inv,
            'placa_id'    => $diag?->placa_id_equipo ?: ($inv?->placa_id ?: $termino),
            'serial'      => $diag?->serial ?: ($inv?->serial ?: ($inv?->identificador_2 ?: null)),
        ]);
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
            'foto_equipo'     => 'nullable|image|max:10240',
            'evidencia'       => 'nullable|image|max:10240',
            'foto'            => 'nullable|image|max:10240',
        ]);

        $termino = trim((string)($request->placa_id_equipo ?? $request->placa));
        $serialFinal = trim((string)$request->serial);
        $placaFinal = $termino;

        // 1. Resolver término cruzando con Inventario General (por si es placa o serial)
        $inv = \App\Models\InventarioGeneral::buscarPorTermino($termino);
        if ($inv) {
            if (empty($serialFinal)) {
                $serialFinal = $inv->serial ?: ($inv->identificador_2 ?: null);
            }
            if (strcasecmp($inv->serial, $termino) === 0 && !empty($inv->placa_id)) {
                $placaFinal = $inv->placa_id;
            } elseif (empty($placaFinal) && !empty($inv->placa_id)) {
                $placaFinal = $inv->placa_id;
            }
        }

        // 2. Buscar si ya existe diagnóstico en GarantiaPortatil por placa, serial o término
        $registro = GarantiaPortatil::where(function ($q) use ($termino, $placaFinal, $serialFinal) {
                        $q->where('placa_id_equipo', $termino)
                          ->orWhere('placa_id_equipo', $placaFinal)
                          ->orWhere('serial', $termino);
                        if (!empty($serialFinal)) {
                            $q->orWhere('serial', $serialFinal);
                        }
                    })
                    ->orderBy('id', 'desc')
                    ->first();

        $fotoRuta = UploadService::procesarSubidaFisica($request, 'portatiles', $placaFinal);

        // 3. Si ya existe un registro previo, actualizarlo con la foto y asegurar su serial
        if ($registro) {
            if ($fotoRuta) {
                $registro->foto_ruta = $fotoRuta;
            }
            if (empty($registro->serial) && !empty($serialFinal)) {
                $registro->serial = $serialFinal;
            }
            $registro->save();

            return response()->json([
                'status'   => 'success',
                'placa_id' => $registro->placa_id_equipo,
                'message'  => $fotoRuta 
                    ? 'Evidencia guardada y enlazada correctamente.' 
                    : 'Registro verificado correctamente (sin foto adjunta).'
            ]);
        }

        // 4. Si NO existe diagnóstico previo, crearlo automáticamente para no bloquear al analista
        $user = auth()->user();
        $nombreAnalista = ($user && $user->rol === 'analista') 
            ? $user->nombre 
            : ($request->nombre_analista ?: ($user->nombre ?? 'Sistema'));

        $numeroTraslado = $inv ? ($inv->num_traslado ?: 'S/N') : 'S/N';

        GarantiaPortatil::create([
            'nombre_analista'                => $nombreAnalista,
            'numero_traslado'                => $numeroTraslado,
            'placa_id_equipo'                => $placaFinal,
            'serial'                         => $serialFinal ?: null,
            'tipo_gestion'                   => 'Diagnostico',
            'energiza'                       => 'Si',
            'da_video'                       => 'Si',
            'realizo_test_lenovo'            => 'No',
            'estado_actual_equipo'           => 'Pendiente diagnóstico',
            'diagnostico_laptop_intervenido' => 'Evidencia fotográfica inicial registrada',
            'foto_ruta'                      => $fotoRuta,
            'created_at'                     => now(),
        ]);

        return response()->json([
            'status'   => 'success',
            'placa_id' => $placaFinal,
            'message'  => $fotoRuta 
                ? 'Evidencia guardada y nuevo registro creado exitosamente.' 
                : 'Registro creado correctamente (sin foto adjunta).'
        ]);
    }

    public function store(StorePortatilRequest $request)
    {
        if (!auth()->user()->can('portatiles.registrar') && !auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para registrar intervenciones de portátiles.');
        }

        $placa = trim((string)($request->placa_id_equipo ?? $request->placa));
        $serialFinal = trim((string)$request->serial);

        // Resolver serial o placa si uno de ellos falta
        if (!empty($placa)) {
            $inv = \App\Models\InventarioGeneral::buscarPorTermino($placa);
            if ($inv) {
                if (empty($serialFinal)) {
                    $serialFinal = $inv->serial ?: ($inv->identificador_2 ?: null);
                }
                // Si el analista ingresó el serial en el campo de placa y existe la placa real en inventario
                if (strcasecmp($inv->serial, $placa) === 0 && !empty($inv->placa_id)) {
                    $placa = $inv->placa_id;
                }
            }
        }

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
        $comentarioBaja = $request->comentario_baja;

        if ($estadoActual === 'Reparado') {
            $diagnosticoFinal = $reparadoPor ? "[$reparadoPor] $comentarioRep" : $comentarioRep;
        } elseif ($estadoActual === 'Baja' && !empty($comentarioBaja)) {
            $diagnosticoFinal = $comentarioBaja;
        } else {
            $diagnosticoFinal = $request->diagnostico_laptop_intervenido;
        }

        $estadoFinal = ($estadoActual === 'Reparado' && $reparadoPor) 
            ? "Reparado por $reparadoPor" 
            : ($estadoActual === 'Donacion' ? 'Donación' : ($request->estado_final_equipo ?: $estadoActual));

        $origenPieza = ($estadoActual === 'Reparado' && $reparadoPor) ? $reparadoPor : $request->origen_pieza;
        $garantia = ($estadoActual === 'Garantia') ? 'Aplica' : $request->garantia;

        try {
            DB::transaction(function () use ($request, $fotoRuta, $nombreAnalista, $diagnosticoFinal, $garantia, $estadoFinal, $indiquePieza, $indiqueFru, $origenPieza, $placa, $serialFinal) {
                $data = [
                    'nombre_analista'                => $nombreAnalista,
                    'numero_traslado'                => $request->numero_traslado,
                    'placa_id_equipo'                => $placa,
                    'serial'                         => $serialFinal ?: null,
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
                    $request->numero_traslado,
                    $serialFinal
                );
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Guardado correctamente']);
            }
            return redirect()->route('portatiles.create')->with('msg', 'Guardado correctamente');

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en PortatilesController@store: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }
}
