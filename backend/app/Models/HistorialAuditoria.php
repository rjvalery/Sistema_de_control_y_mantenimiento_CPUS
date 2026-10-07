<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialAuditoria extends Model
{
    public $timestamps = false; 

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'event',
        'user_id',
        'old_values',
        'new_values',
        'ip_address',
        'created_at'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime'
    ];

    public function auditable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
