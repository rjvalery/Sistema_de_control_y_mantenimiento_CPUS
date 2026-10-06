<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltraPorPeriodoYPermiso
{
    /**
     * Aplica filtros de búsqueda, fechas en zona America/Bogota y reglas de privacidad.
     * Retorna el arreglo de datos listo para la vista.
     */
    public function obtenerDatosPaginados(Builder $query, Request $request, array $searchFields = [], string $dateColumn = 'created_at', string $analistaColumn = 'nombre_analista'): array
    {
        $user = $request->user();
        
        $busqueda = trim((string)$request->query('buscar'));
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');
        $limite = (int) ($request->query('limite') ?? 50);
        if ($limite <= 0 || $limite > 500) {
            $limite = 50;
        }

        $modelClass = get_class($query->getModel());
        
        $esPropio = $user && !$user->hasRole('admin') && $user->tienePermiso('dashboard.ver_solo_propio');
        
        // Filtro de privacidad
        if ($esPropio) {
            $query->where($analistaColumn, $user->nombre);
        }

        if ($busqueda && !empty($searchFields)) {
            $query->where(function ($q) use ($busqueda, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', "%{$busqueda}%");
                }
            });
        }

        if ($fechaDesde) {
            $inicio = Carbon::parse($fechaDesde, 'America/Bogota')->startOfDay()->toDateTimeString();
            $query->where($dateColumn, '>=', $inicio);
        }

        if ($fechaHasta) {
            $fin = Carbon::parse($fechaHasta, 'America/Bogota')->endOfDay()->toDateTimeString();
            $query->where($dateColumn, '<=', $fin);
        }

        $registros = $query->orderBy($query->getModel()->getKeyName(), 'desc')->paginate($limite)->withQueryString();
        
        $totalGeneral = $esPropio
            ? $modelClass::where($analistaColumn, $user->nombre)->count()
            : $modelClass::count();

        return [
            'registros'      => $registros,
            'totalFiltrados' => $registros->total(),
            'totalGeneral'   => $totalGeneral,
            'busqueda'       => $busqueda,
            'fechaDesde'     => $fechaDesde,
            'fechaHasta'     => $fechaHasta,
            'limite'         => $limite,
        ];
    }
}
