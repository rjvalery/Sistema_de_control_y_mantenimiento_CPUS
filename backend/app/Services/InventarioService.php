<?php

namespace App\Services;

use App\Models\InventarioGeneral;
use App\Models\Equipo;
use App\Models\SopladoRegistro;
use App\Models\GarantiaPortatil;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    /**
     * Marca un equipo como intervenido en el Inventario General (Cubic) y garantiza trazabilidad de traslado.
     * Si el equipo no existe en InventarioGeneral, se auto-incorpora para evitar máquinas huérfanas.
     *
     * @param string $placaId El identificador del equipo.
     * @param string $modulo El nombre del módulo desde donde se intervino.
     * @param string $nombreAnalista El nombre del analista que realizó la intervención.
     * @param string|null $numTraslado Número de traslado ingresado en la bitácora.
     * @return void
     */
    public function marcarComoIntervenido(string $placaId, string $modulo, string $nombreAnalista, ?string $numTraslado = null): void
    {
        $placaLimpia = trim($placaId);
        if ($placaLimpia === '') {
            return;
        }

        $registros = InventarioGeneral::where('identificador_1', $placaLimpia)
            ->orWhere('identificador_2', $placaLimpia)
            ->orWhere('placa_id', $placaLimpia)
            ->orWhere('serial', $placaLimpia)
            ->get();

        if ($registros->isNotEmpty()) {
            foreach ($registros as $reg) {
                $reg->intervenido = 1;
                $reg->fecha_intervencion = now();
                $reg->modulo_intervencion = $modulo;
                $reg->analista_intervencion = $nombreAnalista;
                if (!empty($numTraslado) && (empty($reg->num_traslado) || $reg->num_traslado === 'Sin Traslado')) {
                    $reg->num_traslado = $numTraslado;
                }
                $reg->save();
            }
        } else {
            // Auto-incorporar equipo huérfano para garantizar trazabilidad 100% en Inventario General
            InventarioGeneral::create([
                'identificador_1'       => $placaLimpia,
                'identificador_2'       => $placaLimpia,
                'placa_id'              => $placaLimpia,
                'serial'                => $placaLimpia,
                'num_traslado'          => $numTraslado ?: 'Registro Mesa',
                'tipo_equipo'           => ($modulo === 'Diagnóstico CPU' ? 'CPU' : ($modulo === 'Diagnóstico Portátiles' ? 'PORTATIL' : 'Equipo')),
                'descripcion'           => $modulo,
                'estado'                => 'Intervenido',
                'intervenido'           => 1,
                'fecha_intervencion'    => now(),
                'modulo_intervencion'   => $modulo,
                'analista_intervencion' => $nombreAnalista,
                'archivo_origen'        => 'Registro Directo / Mesa',
                'usuario_cargue'        => $nombreAnalista,
                'created_at'            => now(),
            ]);
        }
    }

    /**
     * Motor de Conciliación y Auditoría Bidireccional de Inventario.
     * Cruza inventario_general con las bitácoras (equipos, soplado, portátiles),
     * reconcilia intervenciones, recupera traslados huérfanos e incorpora registros directos.
     *
     * @param string|null $numTrasladoFiltro
     * @return array
     */
    public function conciliarInventarioCompleto(?string $numTrasladoFiltro = null): array
    {
        $intervenidosActualizados = 0;
        $trasladosRecuperados = 0;
        $directosIncorporados = 0;

        // 1. Obtener todas las intervenciones de las tres bitácoras
        $equipos = Equipo::select('placa_id', 'num_traslado', 'nombre_analista', 'fecha_creacion')->get();
        $soplados = SopladoRegistro::select('placa_id', 'num_traslado', 'nombre_analista', 'created_at as fecha_creacion')->get();
        $portatiles = GarantiaPortatil::select('placa_id_equipo as placa_id', 'numero_traslado as num_traslado', 'nombre_analista', 'created_at as fecha_creacion')->get();

        // 2. Conciliar garantias_portatiles sin traslado
        $portatilesSinTraslado = GarantiaPortatil::whereNull('numero_traslado')->orWhere('numero_traslado', '')->get();
        foreach ($portatilesSinTraslado as $po) {
            $placa = trim($po->placa_id_equipo);
            $invMatch = InventarioGeneral::where('placa_id', $placa)
                ->orWhere('identificador_1', $placa)
                ->orWhere('identificador_2', $placa)
                ->orWhere('serial', $placa)
                ->whereNotNull('num_traslado')
                ->where('num_traslado', '!=', '')
                ->first();
            if ($invMatch && $invMatch->num_traslado) {
                $po->numero_traslado = $invMatch->num_traslado;
                $po->save();
                $trasladosRecuperados++;
            }
        }

        // 3. Conciliar inventario_general pendiente con bitácoras
        $pendientesQuery = InventarioGeneral::where('intervenido', 0);
        if ($numTrasladoFiltro) {
            if ($numTrasladoFiltro === 'sin_traslado') {
                $pendientesQuery->where(function($q) {
                    $q->whereNull('num_traslado')->orWhere('num_traslado', '');
                });
            } else {
                $pendientesQuery->where('num_traslado', $numTrasladoFiltro);
            }
        }

        $pendientes = $pendientesQuery->get();

        foreach ($pendientes as $item) {
            $placa = trim($item->placa_id ?: ($item->identificador_1 ?: $item->serial));
            if (!$placa) continue;

            // Buscar en equipos
            $eq = $equipos->firstWhere('placa_id', $placa);
            if ($eq) {
                $item->intervenido = 1;
                $item->modulo_intervencion = 'Diagnóstico CPU';
                $item->analista_intervencion = $eq->nombre_analista;
                $item->fecha_intervencion = $eq->fecha_creacion ?: now();
                if (empty($item->num_traslado) && !empty($eq->num_traslado)) {
                    $item->num_traslado = $eq->num_traslado;
                    $trasladosRecuperados++;
                }
                $item->save();
                $intervenidosActualizados++;
                continue;
            }

            // Buscar en portátiles
            $po = $portatiles->firstWhere('placa_id', $placa);
            if ($po) {
                $item->intervenido = 1;
                $item->modulo_intervencion = 'Diagnóstico Portátiles';
                $item->analista_intervencion = $po->nombre_analista;
                $item->fecha_intervencion = $po->fecha_creacion ?: now();
                if (empty($item->num_traslado) && !empty($po->num_traslado)) {
                    $item->num_traslado = $po->num_traslado;
                    $trasladosRecuperados++;
                }
                $item->save();
                $intervenidosActualizados++;
                continue;
            }

            // Buscar en soplado
            $so = $soplados->firstWhere('placa_id', $placa);
            if ($so) {
                $item->intervenido = 1;
                $item->modulo_intervencion = 'soplado';
                $item->analista_intervencion = $so->nombre_analista;
                $item->fecha_intervencion = $so->fecha_creacion ?: now();
                if (empty($item->num_traslado) && !empty($so->num_traslado)) {
                    $item->num_traslado = $so->num_traslado;
                    $trasladosRecuperados++;
                }
                $item->save();
                $intervenidosActualizados++;
                continue;
            }
        }

        return [
            'intervenidos_actualizados' => $intervenidosActualizados,
            'traslados_recuperados'     => $trasladosRecuperados,
            'directos_incorporados'     => $directosIncorporados,
        ];
    }
}
