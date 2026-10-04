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
        $query = trim($request->input('query') ?? $request->input('termino'));

        if ($query === '' || strlen($query) < 3) {
            return response()->json([
                'encontrado' => false,
                'mensaje'    => 'Término de búsqueda muy corto (mínimo 3 caracteres).',
            ]);
        }

        $equipo = InventarioGeneral::where('identificador_1', $query)
            ->orWhere('identificador_2', $query)
            ->orWhere('placa_id', $query)
            ->orWhere('serial', $query)
            ->orderBy('id', 'desc')
            ->first();

        // Si se encuentra en inventario general, buscar traslado
        if ($equipo) {
            $numTraslado = $equipo->num_traslado;
            
            if (!$numTraslado) {
                // Buscar en las bitácoras si no tiene traslado en inventario
                $terminos = array_filter([$query, $equipo->placa_id, $equipo->serial, $equipo->identificador_1, $equipo->identificador_2]);
                
                $trasladoEq = Equipo::whereIn('placa_id', $terminos)->whereNotNull('num_traslado')->where('num_traslado', '!=', '')->value('num_traslado');
                $trasladoSo = SopladoRegistro::whereIn('placa_id', $terminos)->whereNotNull('num_traslado')->where('num_traslado', '!=', '')->value('num_traslado');
                $trasladoPo = GarantiaPortatil::whereIn('placa_id_equipo', $terminos)->whereNotNull('numero_traslado')->where('numero_traslado', '!=', '')->value('numero_traslado');

                $numTraslado = $trasladoEq ?? $trasladoSo ?? $trasladoPo;
            }

            return response()->json([
                'encontrado' => true,
                'equipo'     => [
                    'id'                    => $equipo->id,
                    'placa_id'              => $equipo->placa_id,
                    'serial'                => $equipo->serial,
                    'num_traslado'          => $numTraslado,
                    'tipo_equipo'           => $equipo->tipo_equipo,
                    'marca'                 => $equipo->marca,
                    'modelo'                => $equipo->modelo,
                    'ubicacion'             => $equipo->ubicacion,
                    'estado'                => $equipo->estado,
                    'intervenido'           => (bool) $equipo->intervenido,
                    'fecha_intervencion'    => $equipo->fecha_intervencion,
                    'modulo_intervencion'   => $equipo->modulo_intervencion,
                    'analista_intervencion' => $equipo->analista_intervencion,
                    'origen_datos'          => 'inventario_general',
                ],
            ]);
        }

        // Si no está en inventario masivo, buscar si ya fue registrado previamente en el sistema
        $historialEq = Equipo::where('placa_id', $query)->orderBy('id', 'desc')->first();
        if ($historialEq) {
            return response()->json([
                'encontrado' => true,
                'equipo'     => [
                    'id'                    => 0,
                    'placa_id'              => $historialEq->placa_id,
                    'serial'                => $historialEq->serial_disco ?? '',
                    'num_traslado'          => $historialEq->num_traslado,
                    'tipo_equipo'           => 'CPU / Escritorio',
                    'marca'                 => '',
                    'modelo'                => '',
                    'ubicacion'             => $historialEq->ubicacion_destino ?? '',
                    'estado'                => $historialEq->estado_actual ?? '',
                    'intervenido'           => true,
                    'fecha_intervencion'    => $historialEq->fecha_creacion,
                    'modulo_intervencion'   => 'Diagnóstico CPU',
                    'analista_intervencion' => $historialEq->nombre_analista,
                    'origen_datos'          => 'historial_sistema',
                ]
            ]);
        }

        $historialSo = SopladoRegistro::where('placa_id', $query)->orderBy('id', 'desc')->first();
        if ($historialSo) {
            return response()->json([
                'encontrado' => true,
                'equipo'     => [
                    'id'                    => 0,
                    'placa_id'              => $historialSo->placa_id,
                    'serial'                => '',
                    'num_traslado'          => $historialSo->num_traslado,
                    'tipo_equipo'           => 'Equipo',
                    'marca'                 => '',
                    'modelo'                => '',
                    'ubicacion'             => '',
                    'estado'                => '',
                    'intervenido'           => true,
                    'fecha_intervencion'    => $historialSo->created_at,
                    'modulo_intervencion'   => 'Soplado',
                    'analista_intervencion' => $historialSo->nombre_analista,
                    'origen_datos'          => 'historial_sistema',
                ]
            ]);
        }

        $historialPo = GarantiaPortatil::where('placa_id_equipo', $query)->orderBy('id', 'desc')->first();
        if ($historialPo) {
            return response()->json([
                'encontrado' => true,
                'equipo'     => [
                    'id'                    => 0,
                    'placa_id'              => $historialPo->placa_id_equipo,
                    'serial'                => $historialPo->serial_disco ?? '',
                    'num_traslado'          => $historialPo->numero_traslado,
                    'tipo_equipo'           => 'Portátil',
                    'marca'                 => '',
                    'modelo'                => '',
                    'ubicacion'             => '',
                    'estado'                => $historialPo->estado_actual_equipo,
                    'intervenido'           => true,
                    'fecha_intervencion'    => $historialPo->created_at,
                    'modulo_intervencion'   => 'Portátiles',
                    'analista_intervencion' => $historialPo->nombre_analista,
                    'origen_datos'          => 'historial_sistema',
                ]
            ]);
        }

        return response()->json([
            'encontrado' => false,
            'mensaje'    => 'Equipo no registrado previamente ni en cargue masivo.',
        ]);
    }
}
