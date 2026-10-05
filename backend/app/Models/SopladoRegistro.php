<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SopladoRegistro extends Model
{
    use HasFactory;

    protected $table = 'soplado_registros';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre_analista',
        'num_traslado',
        'placa_id',
        'energiza',
        'da_video',
        'detecta_disco',
        'ingreso_bios',
        'pasta_termica',
        'maquina_contenia',
        'gel_cucarachas',
        'foto_ruta',
        'foto_equipo',
        'evidencia',
        'fecha_registro',
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
        'fecha_registro' => 'datetime',
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
        return $this->belongsTo(InventarioGeneral::class, 'placa_id', 'placa_id');
    }
}
