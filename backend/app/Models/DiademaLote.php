<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiademaLote extends Model
{
    use HasFactory;

    protected $table = 'diademas_lotes';

    protected $fillable = [
        'user_id',
        'nombre_analista',
        'numero_traslado',
        'total_funcionales',
        'total_garantia',
        'total_baja',
        'total_unidades',
        'fecha_ingreso',
        'observaciones'
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(DiademaItem::class, 'lote_id');
    }
}
