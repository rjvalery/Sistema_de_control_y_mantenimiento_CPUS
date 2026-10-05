<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GarantiaPortatil extends Model
{
    use HasFactory;

    protected $table = 'garantias_portatiles';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre_analista',
        'numero_traslado',
        'placa_id_equipo',
        'tipo_gestion',
        'energiza',
        'da_video',
        'realizo_test_lenovo',
        'estado_actual_equipo',
        'diagnostico_laptop_intervenido',
        'garantia',
        'porque_solicita_garantia',
        'numero_ticket',
        'estado_final_equipo',
        'indique_pieza',
        'indique_fru',
        'pieza_intervenida',
        'origen_pieza',
        'motivo_baja',
        'serial_disco',
        'foto_ruta',
        'foto_equipo',
        'evidencia',
        'created_at',
        'fecha_creacion',
    ];

    /**
     * Mapeo de evidencia y foto_equipo al campo foto_ruta.
     */
    public function setEvidenciaAttribute($value)
    {
        $this->attributes['foto_ruta'] = $value;
    }

    public function getEvidenciaAttribute()
    {
        return $this->attributes['foto_ruta'] ?? null;
    }

    public function setFotoEquipoAttribute($value)
    {
        $this->attributes['foto_ruta'] = $value;
    }

    public function getFotoEquipoAttribute()
    {
        return $this->attributes['foto_ruta'] ?? null;
    }

    protected $casts = [
        'created_at' => 'datetime',
        'fecha_creacion' => 'datetime',
    ];

    /**
     * Relación con el analista (Usuario)
     */
    public function analista()
    {
        return $this->belongsTo(Usuario::class, 'nombre_analista', 'nombre');
    }

    /**
     * Relación con el inventario general por placa
     */
    public function inventarioGeneral()
    {
        return $this->belongsTo(InventarioGeneral::class, 'placa_id_equipo', 'placa_id');
    }
}
