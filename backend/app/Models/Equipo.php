<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'timestamp_registro',
        'nombre_analista',
        'num_traslado',
        'placa_id',
        'tipo_gestion',
        'energiza',
        'da_video',
        'estado_actual',
        'que_va_intervenir',
        'origen_pieza',
        'tipo_ram',
        'marca_ram',
        'capacidad_ram',
        'tipo_disco',
        'marca_disco',
        'capacidad_disco',
        'motivo_baja',
        'descripcion_novedad',
        'serial_disco',
        'novedad_it',
        'solucion_garantias',
        'ubicacion_destino',
        'foto_equipo',
        'evidencia',
        'fecha_creacion',
    ];

    /**
     * Mapeo del atributo evidencia al campo físico en base de datos foto_equipo.
     */
    public function setEvidenciaAttribute($value)
    {
        $this->attributes['foto_equipo'] = $value;
    }

    public function getEvidenciaAttribute()
    {
        return $this->attributes['foto_equipo'] ?? null;
    }

    protected $casts = [
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
