<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\InventarioGeneralObserver;

#[ObservedBy([InventarioGeneralObserver::class])]
class InventarioGeneral extends Model
{
    use HasFactory;

    protected $table = 'inventario_general';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'identificador_1',
        'identificador_2',
        'num_traslado',
        'ref_principal',
        'descripcion',
        'zona_origen',
        'ubicacion_origen',
        'verificado',
        'observaciones',
        'placa_id',
        'serial',
        'tipo_equipo',
        'marca',
        'modelo',
        'ubicacion',
        'estado',
        'datos_adicionales',
        'archivo_origen',
        'usuario_cargue',
        'intervenido',
        'fecha_intervencion',
        'modulo_intervencion',
        'analista_intervencion',
        'created_at',
    ];

    protected $casts = [
        'intervenido' => 'boolean',
        'fecha_intervencion' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Relación con el analista que intervino
     */
    public function analista()
    {
        return $this->belongsTo(Usuario::class, 'analista_intervencion', 'nombre');
    }

    /**
     * Relación con equipos (CPUs)
     */
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'placa_id', 'placa_id');
    }

    /**
     * Relación con soplados
     */
    public function soplados()
    {
        return $this->hasMany(SopladoRegistro::class, 'placa_id', 'placa_id');
    }

    /**
     * Relación con garantías portátiles
     */
    public function garantiasPortatiles()
    {
        return $this->hasMany(GarantiaPortatil::class, 'placa_id_equipo', 'placa_id');
    }

    /**
     * Busca un equipo en inventario por coincidencia exacta con sus identificadores únicos (placa o serial).
     */
    public static function buscarPorTermino(string $query)
    {
        $queryLimpia = trim($query);
        if ($queryLimpia === '') {
            return null;
        }

        return self::where('identificador_1', $queryLimpia)
            ->orWhere('identificador_2', $queryLimpia)
            ->orWhere('placa_id', $queryLimpia)
            ->orWhere('serial', $queryLimpia)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Busca el número de traslado asociado a un equipo en el sistema.
     */
    public static function buscarTrasladoEnSistema(string $query, ?self $equipo = null): ?string
    {
        $candidatosTraslado = [];
        if ($equipo && !empty($equipo->num_traslado)) {
            $fechaInv = $equipo->created_at ?? $equipo->fecha_intervencion ?? now()->subYears(10);
            $candidatosTraslado[] = ['num' => trim($equipo->num_traslado), 'fecha' => $fechaInv];
        }

        $terminos = [$query];
        if ($equipo) {
            foreach (['placa_id', 'serial', 'identificador_1', 'identificador_2'] as $campo) {
                if (!empty($equipo->$campo)) {
                    $terminos[] = trim($equipo->$campo);
                }
            }
        }
        $terminos = array_values(array_unique(array_filter($terminos)));

        if (!empty($terminos)) {
            // Buscar en inventario general adicional (en caso de múltiples cargas masivas)
            $inv = self::whereIn('placa_id', $terminos)->orWhereIn('serial', $terminos)
                ->whereNotNull('num_traslado')->where('num_traslado', '!=', '')
                ->orderBy('id', 'desc')->first();
            if ($inv && (!$equipo || $inv->id !== $equipo->id)) {
                $candidatosTraslado[] = ['num' => trim($inv->num_traslado), 'fecha' => $inv->created_at ?? $inv->fecha_intervencion ?? now()->subYears(10)];
            }

            // Buscar en equipos (Diagnóstico)
            $eq = Equipo::whereIn('placa_id', $terminos)
                ->whereNotNull('num_traslado')->where('num_traslado', '!=', '')
                ->orderBy('id', 'desc')->first();
            if ($eq) {
                $candidatosTraslado[] = ['num' => trim($eq->num_traslado), 'fecha' => $eq->fecha_creacion ?? $eq->created_at ?? now()];
            }

            // Buscar en soplado
            $so = SopladoRegistro::whereIn('placa_id', $terminos)
                ->whereNotNull('num_traslado')->where('num_traslado', '!=', '')
                ->orderBy('id', 'desc')->first();
            if ($so) {
                $candidatosTraslado[] = ['num' => trim($so->num_traslado), 'fecha' => $so->created_at ?? $so->fecha_registro ?? now()];
            }

            // Buscar en portátiles
            $po = GarantiaPortatil::where(function($q) use ($terminos) {
                $q->whereIn('placa_id_equipo', $terminos)->orWhereIn('serial_disco', $terminos);
            })->whereNotNull('numero_traslado')->where('numero_traslado', '!=', '')
            ->orderBy('id', 'desc')->first();
            if ($po) {
                $candidatosTraslado[] = ['num' => trim($po->numero_traslado), 'fecha' => $po->created_at ?? $po->fecha_creacion ?? now()];
            }
        }

        if (empty($candidatosTraslado)) {
            return null;
        }

        usort($candidatosTraslado, function($a, $b) {
            return \Carbon\Carbon::parse($b['fecha']) <=> \Carbon\Carbon::parse($a['fecha']);
        });

        return $candidatosTraslado[0]['num'];
    }

    /**
     * Relación con el historial de auditorías
     */
    public function audits()
    {
        return $this->morphMany(\App\Models\HistorialAuditoria::class, 'auditable');
    }
}
