<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';
    
    protected $fillable = [
        'nombre',
        'slug',
        'descripcion'
    ];

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso', 'rol_id', 'permiso_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_rol', 'rol_id', 'usuario_id');
    }
}
