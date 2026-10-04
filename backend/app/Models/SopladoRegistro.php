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
        'fecha_registro',
        'created_at',
        'fecha_creacion',
    ];

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
