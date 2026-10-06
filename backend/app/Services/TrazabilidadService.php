<?php

namespace App\Services;

use App\Models\InventarioGeneral;
use App\Models\Equipo;
use App\Models\SopladoRegistro;
use App\Models\GarantiaPortatil;
use Carbon\Carbon;

class TrazabilidadService
{
    /**
     * Busca el traslado más reciente asociado al serial o placa
     */
    public function obtenerUltimoTraslado(array $terminos): string
    {
        $candidatosTraslado = [];

        $inv = InventarioGeneral::whereIn('placa_id', $terminos)->orWhereIn('serial', $terminos)
            ->whereNotNull('num_traslado')->where('num_traslado', '!=', '')
            ->orderBy('id', 'desc')->first();
        if ($inv) {
            $candidatosTraslado[] = ['num' => trim($inv->num_traslado), 'fecha' => $inv->created_at ?? $inv->fecha_intervencion ?? now()->subYears(10)];
        }

        $eq = Equipo::whereIn('placa_id', $terminos)
            ->whereNotNull('num_traslado')->where('num_traslado', '!=', '')
            ->orderBy('id', 'desc')->first();
        if ($eq) {
            $candidatosTraslado[] = ['num' => trim($eq->num_traslado), 'fecha' => $eq->fecha_creacion ?? $eq->created_at ?? now()];
        }

        $so = SopladoRegistro::whereIn('placa_id', $terminos)
            ->whereNotNull('num_traslado')->where('num_traslado', '!=', '')
            ->orderBy('id', 'desc')->first();
        if ($so) {
            $candidatosTraslado[] = ['num' => trim($so->num_traslado), 'fecha' => $so->created_at ?? $so->fecha_registro ?? now()];
        }

        $po = GarantiaPortatil::where(function($q) use ($terminos) {
            $q->whereIn('placa_id_equipo', $terminos)->orWhereIn('serial_disco', $terminos);
        })->whereNotNull('numero_traslado')->where('numero_traslado', '!=', '')
        ->orderBy('id', 'desc')->first();
        if ($po) {
            $candidatosTraslado[] = ['num' => trim($po->numero_traslado), 'fecha' => $po->created_at ?? $po->fecha_creacion ?? now()];
        }

        if (empty($candidatosTraslado)) {
            return '';
        }

        usort($candidatosTraslado, function($a, $b) {
            return Carbon::parse($b['fecha']) <=> Carbon::parse($a['fecha']);
        });

        return $candidatosTraslado[0]['num'];
    }

    /**
     * Retorna la información base del equipo
     */
    public function obtenerDatosBase(string $termino)
    {
        $inventario = InventarioGeneral::where('identificador_1', $termino)
            ->orWhere('identificador_2', $termino)
            ->orWhere('placa_id', $termino)
            ->orWhere('serial', $termino)
            ->orderBy('id', 'desc')
            ->first();

        $terminos = array_values(array_unique(array_filter([
            $termino, 
            $inventario?->placa_id, 
            $inventario?->serial, 
            $inventario?->identificador_1, 
            $inventario?->identificador_2
        ])));
        if (empty($terminos)) {
            $terminos = [$termino];
        }

        $ultimoTraslado = $this->obtenerUltimoTraslado($terminos);

        return [
            'inventario' => $inventario,
            'terminos' => $terminos,
            'datosBase' => [
                'id'           => $inventario ? $inventario->id : 0,
                'placa_id'     => $inventario?->placa_id ?? '',
                'serial'       => $inventario?->serial ?? '',
                'num_traslado' => $ultimoTraslado,
                'tipo_equipo'  => $inventario?->tipo_equipo ?? 'CPU / Escritorio',
                'marca'        => $inventario?->marca ?? '',
                'modelo'       => $inventario?->modelo ?? '',
                'ubicacion'    => $inventario?->ubicacion ?? '',
                'estado'       => $inventario?->estado ?? '',
            ]
        ];
    }

    /**
     * Aplica las reglas de negocio para determinar alertas en Soplado y Diagnóstico
     */
    public function evaluarAlertaPorModulo(string $termino, string $modulo): array
    {
        $datos = $this->obtenerDatosBase($termino);
        $inventario = $datos['inventario'];
        $terminos = $datos['terminos'];
        $datosBase = $datos['datosBase'];

        if ($modulo === 'soplado') {
            $sopladoHoy = SopladoRegistro::whereIn('placa_id', $terminos)
                ->whereDate('created_at', Carbon::today('America/Bogota'))->first();

            if ($sopladoHoy) {
                return [
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
                ];
            }

            $historialPrevio = '';
            if ($inventario && $inventario?->intervenido) {
                $historialPrevio = "Intervención previa en " . ($inventario?->modulo_intervencion ?: 'otro módulo') . " el " . Carbon::parse($inventario?->fecha_intervencion)->format('d/m/Y') . " por " . $inventario?->analista_intervencion;
            }

            return [
                'encontrado' => true,
                'equipo' => array_merge($datosBase, [
                    'alerta'           => 'verde',
                    'mensaje'          => "Equipo apto para soplado.",
                    'historial_previo' => $historialPrevio,
                    'intervenido'      => false,
                ])
            ];
        }

        if ($modulo === 'diagnostico') {
            $diagnosticoPrevio = Equipo::whereIn('placa_id', $terminos)->latest('id')->first();
            $portatilPrevio = GarantiaPortatil::where(function($q) use ($terminos) {
                $q->whereIn('placa_id_equipo', $terminos)->orWhereIn('serial_disco', $terminos);
            })->latest('id')->first();

            if ($diagnosticoPrevio || $portatilPrevio) {
                $fechaDiag = $diagnosticoPrevio ? ($diagnosticoPrevio->fecha_creacion ?? $diagnosticoPrevio->created_at) : null;
                $fechaPort = $portatilPrevio ? ($portatilPrevio->created_at ?? $portatilPrevio->fecha_creacion) : null;

                if ($portatilPrevio && (!$diagnosticoPrevio || Carbon::parse($fechaPort) > Carbon::parse($fechaDiag))) {
                    $fecha = Carbon::parse($fechaPort)->format('d/m/Y H:i');
                    $falla = $portatilPrevio->falla_reportada ?? 'N/A';
                    return [
                        'encontrado' => true,
                        'equipo' => array_merge($datosBase, [
                            'placa_id'              => $portatilPrevio->placa_id_equipo ?: $datosBase['placa_id'],
                            'serial'                => $portatilPrevio->serial_disco ?: $datosBase['serial'],
                            'num_traslado'          => $datosBase['num_traslado'] ?: ($portatilPrevio->numero_traslado ?? ''),
                            'alerta'                => 'amarillo',
                            'mensaje'               => "Atención: Este equipo (Portátil/Tablet) cuenta con un registro previo realizado por " . ($portatilPrevio->usuario ?? 'N/A') . " el " . $fecha . ". Falla previa: " . $falla,
                            'intervenido'           => true,
                            'fecha_intervencion'    => $fechaPort,
                            'modulo_intervencion'   => 'Diagnóstico Portátil',
                            'analista_intervencion' => $portatilPrevio->usuario,
                        ])
                    ];
                }

                $fecha = Carbon::parse($fechaDiag)->format('d/m/Y H:i');
                $falla = $diagnosticoPrevio->falla_reportada ?? 'N/A';
                
                return [
                    'encontrado' => true,
                    'equipo' => array_merge($datosBase, [
                        'placa_id'              => $diagnosticoPrevio->placa_id ?: $datosBase['placa_id'],
                        'serial'                => $datosBase['serial'],
                        'num_traslado'          => $datosBase['num_traslado'] ?: ($diagnosticoPrevio->num_traslado ?? ''),
                        'alerta'                => 'amarillo',
                        'mensaje'               => "Atención: Este equipo ya cuenta con diagnóstico previo realizado por " . $diagnosticoPrevio->nombre_analista . " el " . $fecha . ". Motivo / Falla previa: " . $falla,
                        'intervenido'           => true,
                        'fecha_intervencion'    => $fechaDiag,
                        'modulo_intervencion'   => 'Diagnóstico CPU',
                        'analista_intervencion' => $diagnosticoPrevio->nombre_analista,
                    ])
                ];
            }

            $sopladoPrevio = SopladoRegistro::whereIn('placa_id', $terminos)->latest('id')->first();

            if ($inventario || $sopladoPrevio) {
                $historialPrevio = '';
                if ($sopladoPrevio) {
                    $historialPrevio = "Pasó por Soplado el " . Carbon::parse($sopladoPrevio->created_at)->format('d/m/Y') . " por " . $sopladoPrevio->nombre_analista;
                } elseif ($inventario && $inventario?->intervenido) {
                    $historialPrevio = "Intervención previa en " . ($inventario?->modulo_intervencion ?: 'otro módulo') . " el " . Carbon::parse($inventario?->fecha_intervencion)->format('d/m/Y') . " por " . $inventario?->analista_intervencion;
                }

                return [
                    'encontrado' => true,
                    'equipo' => array_merge($datosBase, [
                        'placa_id'         => $datosBase['placa_id'] ?: ($sopladoPrevio->placa_id ?? ''),
                        'serial'           => $datosBase['serial'],
                        'num_traslado'     => $datosBase['num_traslado'] ?: ($sopladoPrevio->num_traslado ?? ''),
                        'alerta'           => 'verde',
                        'mensaje'          => "Equipo apto para diagnóstico inicial.",
                        'historial_previo' => $historialPrevio,
                        'intervenido'      => false,
                    ])
                ];
            }
        }

        return [
            'encontrado' => false,
            'mensaje'    => 'Equipo no registrado previamente ni en cargue masivo.',
        ];
    }

    /**
     * Retorna un array consolidado y ordenado cronológicamente con TODOS los eventos de la máquina para la Hoja de Vida
     */
    public function obtenerHistorialTimeline(string $termino): array
    {
        $datos = $this->obtenerDatosBase($termino);
        $terminos = $datos['terminos'];

        if (empty($terminos)) return [];

        $timeline = [];

        // 1. Ingresos y Cargues (InventarioGeneral)
        $cargues = InventarioGeneral::whereIn('placa_id', $terminos)->orWhereIn('serial', $terminos)->get();
        foreach ($cargues as $c) {
            $timeline[] = [
                'tipo' => 'ingreso',
                'modulo' => 'Cargue / Inventario Base',
                'fecha' => Carbon::parse($c->created_at ?? now())->format('Y-m-d H:i:s'),
                'fecha_obj' => Carbon::parse($c->created_at ?? now()),
                'analista' => $c->usuario_cargue ?? 'Sistema',
                'traslado' => $c->num_traslado,
                'detalles' => array_filter([
                    'Ubicación Origen' => trim(($c->zona_origen ?? '') . ' ' . ($c->ubicacion_origen ?? '')),
                    'Estado Cargue' => $c->verificado,
                    'Observaciones' => $c->observaciones
                ]),
                'icono' => 'fa-box-open',
                'color' => 'primary'
            ];
        }

        // 2. Mantenimientos de Soplado
        $soplados = SopladoRegistro::whereIn('placa_id', $terminos)->get();
        foreach ($soplados as $s) {
            $timeline[] = [
                'tipo' => 'mantenimiento',
                'modulo' => 'Mantenimiento y Soplado',
                'fecha' => Carbon::parse($s->created_at)->format('Y-m-d H:i:s'),
                'fecha_obj' => Carbon::parse($s->created_at),
                'analista' => $s->nombre_analista,
                'traslado' => $s->num_traslado,
                'detalles' => array_filter([
                    'Estado Inicial' => $s->estado_limpieza_inicial,
                    'Estado Final' => $s->estado_limpieza_final,
                    'Observaciones' => $s->observaciones
                ]),
                'icono' => 'fa-wind',
                'color' => 'info'
            ];
        }

        // 3. Diagnósticos de CPU
        $equipos = Equipo::whereIn('placa_id', $terminos)->get();
        foreach ($equipos as $e) {
            $timeline[] = [
                'tipo' => 'diagnostico',
                'modulo' => 'Diagnóstico CPU',
                'fecha' => Carbon::parse($e->fecha_creacion ?? $e->created_at)->format('Y-m-d H:i:s'),
                'fecha_obj' => Carbon::parse($e->fecha_creacion ?? $e->created_at),
                'analista' => $e->nombre_analista,
                'traslado' => $e->num_traslado,
                'detalles' => array_filter([
                    'Falla Reportada' => $e->falla_reportada,
                    'Estado Técnico' => $e->estado_tecnico,
                    'Diagnóstico' => $e->diagnostico,
                    'Procedimiento' => $e->procedimiento
                ]),
                'icono' => 'fa-microchip',
                'color' => 'warning'
            ];
        }

        // 4. Diagnósticos Portátiles
        $portatiles = GarantiaPortatil::where(function($q) use ($terminos) {
            $q->whereIn('placa_id_equipo', $terminos)->orWhereIn('serial_disco', $terminos);
        })->get();
        foreach ($portatiles as $p) {
            $timeline[] = [
                'tipo' => 'diagnostico',
                'modulo' => 'Garantía Portátil',
                'fecha' => Carbon::parse($p->created_at ?? $p->fecha_creacion)->format('Y-m-d H:i:s'),
                'fecha_obj' => Carbon::parse($p->created_at ?? $p->fecha_creacion),
                'analista' => $p->usuario,
                'traslado' => $p->numero_traslado,
                'detalles' => array_filter([
                    'Falla Reportada' => $p->falla_reportada,
                    'Estado' => $p->estado,
                    'Diagnóstico' => $p->diagnostico,
                    'Acciones' => $p->acciones
                ]),
                'icono' => 'fa-laptop-medical',
                'color' => 'danger'
            ];
        }

        // Ordenar cronológicamente descendente (más reciente primero)
        usort($timeline, function($a, $b) {
            return $b['fecha_obj'] <=> $a['fecha_obj'];
        });

        return [
            'equipo_base' => $datos['datosBase'],
            'timeline' => $timeline
        ];
    }
}
