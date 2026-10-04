<?php

namespace App\Services;

use App\Models\InventarioGeneral;

class InventarioService
{
    /**
     * Marca un equipo como intervenido en el Inventario General (Cubic).
     *
     * @param string $placaId El identificador del equipo.
     * @param string $modulo El nombre del módulo desde donde se intervino.
     * @param string $nombreAnalista El nombre del analista que realizó la intervención.
     * @return void
     */
    public function marcarComoIntervenido(string $placaId, string $modulo, string $nombreAnalista): void
    {
        InventarioGeneral::where('identificador_1', $placaId)
            ->orWhere('identificador_2', $placaId)
            ->orWhere('placa_id', $placaId)
            ->orWhere('serial', $placaId)
            ->update([
                'intervenido'           => 1,
                'fecha_intervencion'    => now(),
                'modulo_intervencion'   => $modulo,
                'analista_intervencion' => $nombreAnalista
            ]);
    }
}
