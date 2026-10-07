<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitorRegistro extends Model
{
    use HasFactory;

    protected $table = 'monitores_registros';

    protected $fillable = [
        'user_id',
        'nombre_analista',
        'serial',
        'placa',
        'numero_traslado',
        'tipo_gestion',
        'energiza',
        'da_video',
        'motivo_novedad',
        'estado_actual',
        'observaciones',
        'fecha_ingreso'
    ];

    protected $casts = [
        'energiza' => 'boolean',
        'da_video' => 'boolean',
        'fecha_ingreso' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
