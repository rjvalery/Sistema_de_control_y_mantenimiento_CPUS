<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiademaItem extends Model
{
    use HasFactory;

    protected $table = 'diademas_items';

    protected $fillable = [
        'lote_id',
        'identificador',
        'marca_modelo',
        'estado',
        'motivo'
    ];

    public function lote()
    {
        return $this->belongsTo(DiademaLote::class, 'lote_id');
    }
}
