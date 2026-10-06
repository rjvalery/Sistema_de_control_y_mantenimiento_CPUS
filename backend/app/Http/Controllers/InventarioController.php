<?php

namespace App\Http\Controllers;

use App\Models\InventarioGeneral;
use App\Models\Equipo;
use App\Models\SopladoRegistro;
use App\Models\GarantiaPortatil;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
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

        // Obtener datos base desde inventario general para autocompletar
        $inventario = InventarioGeneral::where('identificador_1', $termino)
            ->orWhere('identificador_2', $termino)
            ->orWhere('placa_id', $termino)
            ->orWhere('serial', $termino)
            ->orderBy('id', 'desc')
            ->first();

        // Obtener traslado si existe en inventario u otras tablas
        $numTraslado = $inventario ? $inventario->num_traslado : null;
        if (!$numTraslado) {
            $terminos = array_filter([$termino, $inventario->placa_id ?? null, $inventario->serial ?? null, $inventario->identificador_1 ?? null, $inventario->identificador_2 ?? null]);
            
            $trasladoEq = Equipo::whereIn('placa_id', $terminos)
                ->whereNotNull('num_traslado')->where('num_traslado', '!=', '')->latest('id')->value('num_traslado');
            
            $trasladoSo = SopladoRegistro::whereIn('placa_id', $terminos)
                ->whereNotNull('num_traslado')->where('num_traslado', '!=', '')->latest('id')->value('num_traslado');
            
            $trasladoPo = GarantiaPortatil::where(function($q) use ($terminos) {
                $q->whereIn('placa_id_equipo', $terminos)->orWhereIn('serial_disco', $terminos);
            })->whereNotNull('numero_traslado')->where('numero_traslado', '!=', '')->latest('id')->value('numero_traslado');
            
            $numTraslado = $trasladoEq ?? $trasladoSo ?? $trasladoPo;
        }

        $datosBase = [
            'id'           => $inventario ? $inventario->id : 0,
            'placa_id'     => $inventario->placa_id ?? '',
            'serial'       => $inventario->serial ?? '',
            'num_traslado' => $numTraslado ?? '',
            'tipo_equipo'  => $inventario->tipo_equipo ?? 'CPU / Escritorio',
            'marca'        => $inventario->marca ?? '',
            'modelo'       => $inventario->modelo ?? '',
            'ubicacion'    => $inventario->ubicacion ?? '',
            'estado'       => $inventario->estado ?? '',
        ];

        // 1. Lógica para el módulo de Soplado
        if ($modulo === 'soplado') {
            $sopladoHoy = SopladoRegistro::where('placa_id', $termino)
                ->whereDate('created_at', \Carbon\Carbon::today('America/Bogota'))->first();

            if ($sopladoHoy) {
                return response()->json([
                    'encontrado' => true,
                    'equipo' => array_merge($datosBase, [
                        'placa_id'              => $sopladoHoy->placa_id ?: $datosBase['placa_id'],
                        'alerta'                => 'amarillo',
                        'mensaje'               => "Atención: Este equipo ya fue soplado hoy por " . $sopladoHoy->nombre_analista,
                        'intervenido'           => true,
                        'fecha_intervencion'    => $sopladoHoy->created_at,
                        'modulo_intervencion'   => 'Soplado',
                        'analista_intervencion' => $sopladoHoy->nombre_analista,
                    ])
                ]);
            }

            $historialPrevio = '';
            if ($inventario && $inventario->intervenido) {
                $historialPrevio = "Intervención previa en " . ($inventario->modulo_intervencion ?: 'otro módulo') . " el " . \Carbon\Carbon::parse($inventario->fecha_intervencion)->format('d/m/Y') . " por " . $inventario->analista_intervencion;
            }

            return response()->json([
                'encontrado' => true,
                'equipo' => array_merge($datosBase, [
                    'alerta'           => 'verde',
                    'mensaje'          => "Equipo apto para soplado.",
                    'historial_previo' => $historialPrevio,
                    'intervenido'      => false,
                ])
            ]);
        }

        // 2. Lógica para el módulo de Diagnóstico CPU (por defecto)
        if ($modulo === 'diagnostico') {
            $diagnosticoPrevio = Equipo::where('placa_id', $termino)->latest('id')->first();

            if ($diagnosticoPrevio) {
                $fecha = \Carbon\Carbon::parse($diagnosticoPrevio->fecha_creacion ?? $diagnosticoPrevio->created_at)->format('d/m/Y H:i');
                $falla = $diagnosticoPrevio->falla_reportada ?? 'N/A';
                
                return response()->json([
                    'encontrado' => true,
                    'equipo' => array_merge($datosBase, [
                        'placa_id'              => $diagnosticoPrevio->placa_id ?: $datosBase['placa_id'],
                        'serial'                => $datosBase['serial'], // Diagnóstico no tiene serial
                        'num_traslado'          => $diagnosticoPrevio->num_traslado ?: $datosBase['num_traslado'],
                        'alerta'                => 'amarillo',
                        'mensaje'               => "Atención: Este equipo ya cuenta con diagnóstico previo realizado por " . $diagnosticoPrevio->nombre_analista . " el " . $fecha . ". Motivo / Falla previa: " . $falla,
                        'intervenido'           => true,
                        'fecha_intervencion'    => $diagnosticoPrevio->fecha_creacion ?? $diagnosticoPrevio->created_at,
                        'modulo_intervencion'   => 'Diagnóstico CPU',
                        'analista_intervencion' => $diagnosticoPrevio->nombre_analista,
                    ])
                ]);
            }

            // Si NO existe en 'equipos', verificar si existe en inventario o soplado como información adicional
            $sopladoPrevio = SopladoRegistro::where('placa_id', $termino)->latest('id')->first();

            if ($inventario || $sopladoPrevio) {
                $historialPrevio = '';
                if ($sopladoPrevio) {
                    $historialPrevio = "Pasó por Soplado el " . \Carbon\Carbon::parse($sopladoPrevio->created_at)->format('d/m/Y') . " por " . $sopladoPrevio->nombre_analista;
                } elseif ($inventario && $inventario->intervenido) {
                    $historialPrevio = "Intervención previa en " . ($inventario->modulo_intervencion ?: 'otro módulo') . " el " . \Carbon\Carbon::parse($inventario->fecha_intervencion)->format('d/m/Y') . " por " . $inventario->analista_intervencion;
                }

                return response()->json([
                    'encontrado' => true,
                    'equipo' => array_merge($datosBase, [
                        'placa_id'         => $datosBase['placa_id'] ?: ($sopladoPrevio->placa_id ?? ''),
                        'serial'           => $datosBase['serial'], // Soplado tampoco tiene serial
                        'num_traslado'     => $datosBase['num_traslado'] ?: ($sopladoPrevio->num_traslado ?? ''),
                        'alerta'           => 'verde',
                        'mensaje'          => "Equipo apto para diagnóstico inicial.",
                        'historial_previo' => $historialPrevio,
                        'intervenido'      => false, // False para que NO bloquee como alerta amarilla
                    ])
                ]);
            }
        }

        return response()->json([
            'encontrado' => false,
            'mensaje'    => 'Equipo no registrado previamente ni en cargue masivo.',
        ]);
    }
}
